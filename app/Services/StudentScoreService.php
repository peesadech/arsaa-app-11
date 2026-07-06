<?php

namespace App\Services;

use App\Models\GradeSetting;
use App\Models\OpenedCourse;
use App\Models\ScoreItem;
use App\Models\Student;
use App\Models\StudentEnrollment;
use App\Models\StudentScore;
use App\Models\StudentScoreItem;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class StudentScoreService
{
    /**
     * นักเรียนในห้องของ opened course (เรียงตามชื่อ)
     */
    public function enrollmentsForCourse(OpenedCourse $openedCourse): Collection
    {
        return StudentEnrollment::where('academic_year_id', $openedCourse->academic_year_id)
            ->where('semester_id', $openedCourse->semester_id)
            ->where('grade_id', $openedCourse->grade_id)
            ->where('classroom_id', $openedCourse->classroom_id)
            ->where('status', StudentEnrollment::STATUS_ENROLLED)
            ->with('student')
            ->orderBy(Student::select('name_th')->whereColumn('students.id', 'student_enrollments.student_id'))
            ->get();
    }

    /* ============================================================
     |  รายการคะแนน (score_items)
     * ============================================================ */

    /**
     * ทำให้วิชานี้มีรายการคะแนนพร้อมใช้ — ถ้ายังไม่มีเลย สร้าง template เริ่มต้น
     * (คะแนนเก็บ 60 / กลางภาค 20 / ปลายภาค 20) แล้ว backfill จาก student_scores เดิม
     * เพื่อไม่ให้ข้อมูลคะแนนที่บันทึกไว้แบบ 3 ช่องหายไป
     */
    public function ensureItems(OpenedCourse $openedCourse): Collection
    {
        $items = $openedCourse->scoreItems()->get();
        if ($items->isNotEmpty()) {
            return $items;
        }

        return DB::transaction(function () use ($openedCourse) {
            $template = [
                ['category' => 'assignment', 'name' => 'คะแนนเก็บระหว่างภาค', 'full_score' => 60, 'legacy' => 'score_collect'],
                ['category' => 'midterm',    'name' => 'สอบกลางภาค',           'full_score' => 20, 'legacy' => 'score_midterm'],
                ['category' => 'final',      'name' => 'สอบปลายภาค',           'full_score' => 20, 'legacy' => 'score_final'],
            ];

            $legacyScores = StudentScore::where('opened_course_id', $openedCourse->id)->get();

            $created = collect();
            foreach ($template as $i => $def) {
                $item = ScoreItem::create([
                    'opened_course_id' => $openedCourse->id,
                    'category' => $def['category'],
                    'name' => $def['name'],
                    'full_score' => $def['full_score'],
                    'weight' => null, // คะแนนดิบ (รวมตรง ๆ)
                    'counts_toward_total' => true,
                    'sort_order' => $i + 1,
                    'is_active' => true,
                    'created_by' => Auth::id(),
                ]);

                // backfill ค่าจากคอลัมน์เดิม
                foreach ($legacyScores as $ls) {
                    $val = $ls->{$def['legacy']};
                    if ($val !== null) {
                        StudentScoreItem::create([
                            'score_item_id' => $item->id,
                            'student_id' => $ls->student_id,
                            'score' => $val,
                            'updated_by' => Auth::id(),
                        ]);
                    }
                }

                $created->push($item);
            }

            return $created;
        });
    }

    public function addItem(OpenedCourse $openedCourse, array $data): ScoreItem
    {
        $nextSort = (int) $openedCourse->scoreItems()->max('sort_order') + 1;

        return ScoreItem::create([
            'opened_course_id' => $openedCourse->id,
            'category' => $data['category'] ?? 'other',
            'name' => $data['name'],
            'full_score' => $data['full_score'] ?? 0,
            'weight' => ($data['weight'] ?? '') !== '' ? $data['weight'] : null,
            'counts_toward_total' => (bool) ($data['counts_toward_total'] ?? true),
            'sort_order' => $nextSort,
            'is_active' => true,
            'created_by' => Auth::id(),
        ]);
    }

    public function updateItem(ScoreItem $item, array $data): ScoreItem
    {
        $item->update([
            'category' => $data['category'] ?? $item->category,
            'name' => $data['name'] ?? $item->name,
            'full_score' => $data['full_score'] ?? $item->full_score,
            'weight' => array_key_exists('weight', $data)
                ? (($data['weight'] ?? '') !== '' ? $data['weight'] : null)
                : $item->weight,
            'counts_toward_total' => array_key_exists('counts_toward_total', $data)
                ? (bool) $data['counts_toward_total']
                : $item->counts_toward_total,
        ]);

        // คะแนนเต็ม/น้ำหนักเปลี่ยน → คำนวณสรุปใหม่ทั้งห้อง
        $this->recomputeSummary($item->openedCourse);

        return $item;
    }

    public function deleteItem(ScoreItem $item): void
    {
        $openedCourse = $item->openedCourse;
        $item->delete(); // cascade ลบ student_score_items
        $this->recomputeSummary($openedCourse);
    }

    /* ============================================================
     |  บันทึกคะแนน
     * ============================================================ */

    /**
     * บันทึกคะแนนทั้งตาราง (Batch Save)
     * $rows: [student_id => [score_item_id => score, ...]]
     * คืนจำนวนช่องที่บันทึก
     */
    public function saveItemScores(OpenedCourse $openedCourse, array $rows): int
    {
        $items = $openedCourse->scoreItems()->get()->keyBy('id');
        $saved = 0;

        DB::transaction(function () use ($rows, $items, &$saved) {
            foreach ($rows as $studentId => $cells) {
                foreach ($cells as $itemId => $raw) {
                    $item = $items->get($itemId);
                    if (!$item) {
                        continue; // รายการไม่ใช่ของวิชานี้ → ข้าม
                    }
                    $score = $this->normalizeScore($raw, (float) $item->full_score);

                    $existing = StudentScoreItem::where('score_item_id', $itemId)
                        ->where('student_id', $studentId)
                        ->first();

                    if ($score === null && !$existing) {
                        continue; // ว่าง + ไม่มีของเดิม → ข้าม
                    }

                    StudentScoreItem::updateOrCreate(
                        ['score_item_id' => $itemId, 'student_id' => $studentId],
                        ['score' => $score, 'updated_by' => Auth::id()]
                    );
                    $saved++;
                }
            }
        });

        $this->recomputeSummary($openedCourse);

        return $saved;
    }

    /**
     * บันทึกช่องเดียว (Auto Save) — คืนคะแนนรวม/เกรดของนักเรียนคนนั้นหลังบันทึก
     */
    public function saveCell(ScoreItem $item, int $studentId, $raw): array
    {
        $score = $this->normalizeScore($raw, (float) $item->full_score);

        StudentScoreItem::updateOrCreate(
            ['score_item_id' => $item->id, 'student_id' => $studentId],
            ['score' => $score, 'updated_by' => Auth::id()]
        );

        $summary = $this->recomputeSummaryForStudent($item->openedCourse, $studentId);

        return [
            'score' => $score,
            'total' => $summary['total'],
            'grade' => $summary['grade'],
            'result_status' => $summary['result_status'],
        ];
    }

    /* ============================================================
     |  คำนวณสรุป (total + grade + ผ่าน/ไม่ผ่าน) จากรายการคะแนน
     * ============================================================ */

    public function recomputeSummary(OpenedCourse $openedCourse, ?int $teacherId = null): void
    {
        $enrollments = $this->enrollmentsForCourse($openedCourse);
        foreach ($enrollments as $enrollment) {
            $this->recomputeSummaryForStudent($openedCourse, $enrollment->student_id, $teacherId);
        }
    }

    public function recomputeSummaryForStudent(OpenedCourse $openedCourse, int $studentId, ?int $teacherId = null): array
    {
        $items = $openedCourse->scoreItems()->where('is_active', true)->get();
        $entries = StudentScoreItem::whereIn('score_item_id', $items->pluck('id'))
            ->where('student_id', $studentId)
            ->get()
            ->keyBy('score_item_id');

        $total = null;
        foreach ($items as $item) {
            if (!$item->counts_toward_total) {
                continue;
            }
            $entry = $entries->get($item->id);
            if (!$entry || $entry->score === null) {
                continue;
            }
            $contribution = $this->contribution((float) $entry->score, $item);
            $total = ($total ?? 0) + $contribution;
        }

        $grade = null;
        $resultStatus = null;
        if ($total !== null) {
            $total = round($total, 2);
            $setting = GradeSetting::gradeForScore($total);
            $grade = $setting?->grade;
            $resultStatus = $setting
                ? ($setting->is_pass ? StudentScore::RESULT_PASS : StudentScore::RESULT_FAIL)
                : null;
        }

        // ไม่มีคะแนนเลย และไม่มีแถวสรุปเดิม → ไม่ต้องสร้าง
        $existing = StudentScore::where('student_id', $studentId)
            ->where('opened_course_id', $openedCourse->id)
            ->first();
        if ($total === null && !$existing) {
            return ['total' => null, 'grade' => null, 'result_status' => null];
        }

        $payload = [
            'total_score' => $total,
            'grade' => $grade,
            'result_status' => $resultStatus,
            'updated_by' => Auth::id(),
        ];
        if ($teacherId !== null) {
            $payload['teacher_id'] = $teacherId;
        }

        StudentScore::updateOrCreate(
            ['student_id' => $studentId, 'opened_course_id' => $openedCourse->id],
            $payload
        );

        return ['total' => $total, 'grade' => $grade, 'result_status' => $resultStatus];
    }

    /**
     * คะแนนที่รายการนี้คิดเข้าคะแนนรวม
     * - ไม่ตั้งน้ำหนัก (weight = null): คิดคะแนนดิบตรง ๆ
     * - ตั้งน้ำหนัก: (score / full_score) * weight
     */
    private function contribution(float $score, ScoreItem $item): float
    {
        if ($item->weight === null) {
            return $score;
        }
        $full = (float) $item->full_score;
        if ($full <= 0) {
            return 0.0;
        }
        return $score / $full * (float) $item->weight;
    }

    /**
     * แปลงค่า input เป็นคะแนน — ว่าง = null, ตัดไม่ให้เกินคะแนนเต็ม/ต่ำกว่า 0
     */
    private function normalizeScore($raw, float $fullScore): ?float
    {
        if ($raw === null || $raw === '' || !is_numeric($raw)) {
            return null;
        }
        $val = (float) $raw;
        if ($val < 0) {
            $val = 0;
        }
        if ($fullScore > 0 && $val > $fullScore) {
            $val = $fullScore;
        }
        return round($val, 2);
    }

    /* ============================================================
     |  ข้อมูลสำหรับหน้า grid + import/export
     * ============================================================ */

    /**
     * เมทริกซ์คะแนน: [student_id => [score_item_id => score]]
     */
    public function scoreMatrix(OpenedCourse $openedCourse): array
    {
        $itemIds = $openedCourse->scoreItems()->pluck('id');
        $matrix = [];
        StudentScoreItem::whereIn('score_item_id', $itemIds)
            ->get(['score_item_id', 'student_id', 'score'])
            ->each(function ($row) use (&$matrix) {
                $matrix[$row->student_id][$row->score_item_id] = $row->score;
            });

        return $matrix;
    }
}
