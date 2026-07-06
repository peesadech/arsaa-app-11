<?php

namespace App\Http\Controllers\Concerns;

use App\Models\GradeSetting;
use App\Models\OpenedCourse;
use App\Models\ScoreItem;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

/**
 * ตรรกะหน้ากรอกคะแนนแบบ Excel + จัดการรายการคะแนน + Import/Export
 * ใช้ร่วมกันระหว่าง Admin และ Teacher — ต่างกันแค่การตรวจสิทธิ์เข้าถึงวิชา
 *
 * คลาสที่ใช้ trait นี้ต้องมี:
 *   - property $scoreService (StudentScoreService)
 *   - authorizeCourse(OpenedCourse): void   ตรวจสิทธิ์ / abort
 *   - routePrefix(): string                 เช่น 'admin.student-scores'
 *   - gridView(): string                    ชื่อ view ตารางกรอกคะแนน
 *   - summaryTeacherId(OpenedCourse, Request): ?int
 */
trait HandlesScoreEntry
{
    /** หน้ากรอกคะแนนแบบตาราง */
    public function entry(Request $request, $openedCourseId)
    {
        $openedCourse = OpenedCourse::with('course.subjectGroup', 'grade', 'classroom', 'academicYear', 'semester')
            ->findOrFail($openedCourseId);
        $this->authorizeCourse($openedCourse);

        $this->scoreService->ensureItems($openedCourse);

        $items = $openedCourse->scoreItems()->get();
        $enrollments = $this->scoreService->enrollmentsForCourse($openedCourse);
        $matrix = $this->scoreService->scoreMatrix($openedCourse);
        $summaries = $openedCourse->studentScores()->get()->keyBy('student_id');
        $gradeSettings = GradeSetting::orderBy('sort_order')->get();

        return view($this->gridView(), [
            'routePrefix' => $this->routePrefix(),
            'openedCourse' => $openedCourse,
            'items' => $items,
            'enrollments' => $enrollments,
            'matrix' => $matrix,
            'summaries' => $summaries,
            'gradeSettings' => $gradeSettings,
            'categories' => ScoreItem::CATEGORIES,
        ]);
    }

    /** เพิ่มรายการคะแนน */
    public function storeItem(Request $request, $openedCourseId)
    {
        $openedCourse = OpenedCourse::findOrFail($openedCourseId);
        $this->authorizeCourse($openedCourse);

        $data = $request->validate([
            'category' => 'required|string|in:' . implode(',', array_keys(ScoreItem::CATEGORIES)),
            'name' => 'required|string|max:150',
            'full_score' => 'required|numeric|min:0|max:1000',
            'weight' => 'nullable|numeric|min:0|max:1000',
            'counts_toward_total' => 'nullable|boolean',
        ]);

        $this->scoreService->addItem($openedCourse, $data);

        return back()->with('status', __('Score item added'));
    }

    /** แก้ไขรายการคะแนน (ชื่อ / เต็ม / น้ำหนัก) */
    public function updateItem(Request $request, $openedCourseId, $itemId)
    {
        $openedCourse = OpenedCourse::findOrFail($openedCourseId);
        $this->authorizeCourse($openedCourse);

        $item = ScoreItem::where('opened_course_id', $openedCourse->id)->findOrFail($itemId);

        $data = $request->validate([
            'category' => 'required|string|in:' . implode(',', array_keys(ScoreItem::CATEGORIES)),
            'name' => 'required|string|max:150',
            'full_score' => 'required|numeric|min:0|max:1000',
            'weight' => 'nullable|numeric|min:0|max:1000',
            'counts_toward_total' => 'nullable|boolean',
        ]);
        $data['counts_toward_total'] = $request->boolean('counts_toward_total');

        $this->scoreService->updateItem($item, $data);

        return back()->with('status', __('Score item updated'));
    }

    /** แก้ไขหลายรายการคะแนนพร้อมกัน (บันทึกปุ่มเดียว) */
    public function updateItems(Request $request, $openedCourseId)
    {
        $openedCourse = OpenedCourse::findOrFail($openedCourseId);
        $this->authorizeCourse($openedCourse);

        $categories = implode(',', array_keys(ScoreItem::CATEGORIES));
        $validated = $request->validate([
            'items' => 'required|array',
            'items.*.category' => 'required|string|in:' . $categories,
            'items.*.name' => 'required|string|max:150',
            'items.*.full_score' => 'required|numeric|min:0|max:1000',
            'items.*.weight' => 'nullable|numeric|min:0|max:1000',
        ]);

        // คะแนนรวม (เฉพาะรายการที่นับเข้าเกรด) ต้องเท่ากับ 100 ถึงจะบันทึกได้
        $weightSum = 0.0;
        foreach ($validated['items'] as $itemId => $row) {
            if (!$request->boolean("items.$itemId.counts_toward_total")) {
                continue;
            }
            $weight = ($row['weight'] ?? '') !== '' ? (float) $row['weight'] : (float) $row['full_score'];
            $weightSum += $weight;
        }
        if (abs(round($weightSum, 2) - 100) >= 0.01) {
            return back()->withInput()->withErrors([
                'items' => __('Total that counts toward the grade must equal 100 before saving.'),
            ]);
        }

        $items = $openedCourse->scoreItems()->get()->keyBy('id');

        DB::transaction(function () use ($validated, $items, $request) {
            foreach ($validated['items'] as $itemId => $row) {
                $item = $items->get((int) $itemId);
                if (!$item) {
                    continue;
                }
                $this->scoreService->updateItem($item, [
                    'category' => $row['category'],
                    'name' => $row['name'],
                    'full_score' => $row['full_score'],
                    'weight' => $row['weight'] ?? null,
                    'counts_toward_total' => $request->boolean("items.$itemId.counts_toward_total"),
                ]);
            }
        });

        return back()->with('status', __('Score items updated'));
    }

    /** จัดเรียงลำดับรายการคะแนนใหม่ (drag & drop) */
    public function reorderItems(Request $request, $openedCourseId)
    {
        $openedCourse = OpenedCourse::findOrFail($openedCourseId);
        $this->authorizeCourse($openedCourse);

        $data = $request->validate([
            'order' => 'required|array',
            'order.*' => 'integer',
        ]);

        $validIds = $openedCourse->scoreItems()->pluck('id')->all();
        DB::transaction(function () use ($data, $validIds) {
            $sort = 1;
            foreach ($data['order'] as $id) {
                if (in_array((int) $id, $validIds, true)) {
                    ScoreItem::where('id', $id)->update(['sort_order' => $sort++]);
                }
            }
        });

        return response()->json(['ok' => true]);
    }

    /** ลบรายการคะแนน */
    public function destroyItem(Request $request, $openedCourseId, $itemId)
    {
        $openedCourse = OpenedCourse::findOrFail($openedCourseId);
        $this->authorizeCourse($openedCourse);

        $item = ScoreItem::where('opened_course_id', $openedCourse->id)->findOrFail($itemId);
        $this->scoreService->deleteItem($item);

        return back()->with('status', __('Score item deleted'));
    }

    /** บันทึกทั้งตาราง (Batch Save) */
    public function save(Request $request, $openedCourseId)
    {
        $openedCourse = OpenedCourse::findOrFail($openedCourseId);
        $this->authorizeCourse($openedCourse);

        $data = $request->validate([
            'scores' => 'nullable|array',
            'scores.*' => 'array',
            'scores.*.*' => 'nullable|numeric|min:0',
        ]);

        // อัปเดต teacher_id บนสรุป (ครู = ตัวเอง, admin = ตามที่เลือก/คงเดิม)
        $teacherId = $this->summaryTeacherId($openedCourse, $request);

        $saved = $this->scoreService->saveItemScores($openedCourse, $data['scores'] ?? []);
        if ($teacherId !== null) {
            $this->scoreService->recomputeSummary($openedCourse, $teacherId);
        }

        return redirect()->route($this->routePrefix() . '.entry', $openedCourse->id)
            ->with('status', __(':count scores saved', ['count' => $saved]));
    }

    /** Auto Save รายช่อง (JSON) */
    public function cell(Request $request, $openedCourseId)
    {
        $openedCourse = OpenedCourse::findOrFail($openedCourseId);
        $this->authorizeCourse($openedCourse);

        $data = $request->validate([
            'score_item_id' => 'required|integer',
            'student_id' => 'required|integer',
            'score' => 'nullable|numeric|min:0',
        ]);

        $item = ScoreItem::where('opened_course_id', $openedCourse->id)->findOrFail($data['score_item_id']);
        $result = $this->scoreService->saveCell($item, (int) $data['student_id'], $data['score'] ?? null);

        return response()->json([
            'ok' => true,
            'score' => $result['score'],
            'total' => $result['total'],
            'grade' => $result['grade'],
            'result_status' => $result['result_status'],
        ]);
    }

    /** Export คะแนนเป็น CSV (เปิดด้วย Excel ได้) */
    public function export(Request $request, $openedCourseId)
    {
        $openedCourse = OpenedCourse::with('course', 'grade', 'classroom')->findOrFail($openedCourseId);
        $this->authorizeCourse($openedCourse);

        $this->scoreService->ensureItems($openedCourse);
        $items = $openedCourse->scoreItems()->where('is_active', true)->get();
        $enrollments = $this->scoreService->enrollmentsForCourse($openedCourse);
        $matrix = $this->scoreService->scoreMatrix($openedCourse);
        $summaries = $openedCourse->studentScores()->get()->keyBy('student_id');

        // header: รหัส, ชื่อ, [รายการคะแนน... โดยฝัง id ไว้ท้าย], รวม, เกรด
        $header = [__('Student Code'), __('Name')];
        foreach ($items as $item) {
            $header[] = $item->name . ' [' . $item->id . ']';
        }
        $header[] = __('Total');
        $header[] = __('Grade');

        $rows = [$header];
        foreach ($enrollments as $enrollment) {
            $student = $enrollment->student;
            $row = [$student->student_code, $student->name_th];
            foreach ($items as $item) {
                $row[] = $matrix[$student->id][$item->id] ?? '';
            }
            $summary = $summaries->get($student->id);
            $row[] = $summary?->total_score ?? '';
            $row[] = $summary?->grade ?? '';
            $rows[] = $row;
        }

        $csv = "\xEF\xBB\xBF"; // BOM ให้ Excel อ่านภาษาไทยถูก
        foreach ($rows as $row) {
            $csv .= implode(',', array_map(
                fn($v) => '"' . str_replace('"', '""', (string) $v) . '"',
                $row
            )) . "\r\n";
        }

        $filename = 'scores_' . ($openedCourse->course->code ?? $openedCourse->id) . '.csv';

        return response($csv)
            ->header('Content-Type', 'text/csv; charset=utf-8')
            ->header('Content-Disposition', "attachment; filename=\"{$filename}\"");
    }

    /** Import คะแนนจาก CSV — จับคู่นักเรียนด้วยรหัส, จับคู่คอลัมน์ด้วย id ที่ฝังในหัวตาราง [id] */
    public function import(Request $request, $openedCourseId)
    {
        $openedCourse = OpenedCourse::findOrFail($openedCourseId);
        $this->authorizeCourse($openedCourse);

        $request->validate([
            'file' => 'required|file|mimes:csv,txt|max:5120',
        ]);

        $items = $openedCourse->scoreItems()->get()->keyBy('id');
        $enrollments = $this->scoreService->enrollmentsForCourse($openedCourse);
        $studentByCode = $enrollments->mapWithKeys(fn($e) => [(string) $e->student->student_code => $e->student->id]);

        $path = $request->file('file')->getRealPath();
        $handle = fopen($path, 'r');
        if ($handle === false) {
            return back()->withErrors(['file' => __('Cannot read file')]);
        }

        $header = fgetcsv($handle);
        if ($header === false) {
            fclose($handle);
            return back()->withErrors(['file' => __('Empty file')]);
        }
        // ลบ BOM ออกจากคอลัมน์แรก
        if (isset($header[0])) {
            $header[0] = preg_replace('/^\xEF\xBB\xBF/', '', $header[0]);
        }

        // แมปตำแหน่งคอลัมน์ → score_item_id จาก "... [id]"
        $colToItem = [];
        foreach ($header as $idx => $col) {
            if (preg_match('/\[(\d+)\]\s*$/', (string) $col, $m)) {
                $itemId = (int) $m[1];
                if ($items->has($itemId)) {
                    $colToItem[$idx] = $itemId;
                }
            }
        }

        $rows = [];
        $imported = 0;
        DB::transaction(function () use ($handle, $colToItem, $studentByCode, $items, &$imported) {
            while (($line = fgetcsv($handle)) !== false) {
                $code = trim((string) ($line[0] ?? ''));
                if ($code === '' || !$studentByCode->has($code)) {
                    continue;
                }
                $studentId = $studentByCode->get($code);
                foreach ($colToItem as $idx => $itemId) {
                    if (!array_key_exists($idx, $line)) {
                        continue;
                    }
                    $raw = trim((string) $line[$idx]);
                    if ($raw === '') {
                        continue;
                    }
                    $this->scoreService->saveCell($items->get($itemId), $studentId, $raw);
                    $imported++;
                }
            }
        });
        fclose($handle);

        return redirect()->route($this->routePrefix() . '.entry', $openedCourse->id)
            ->with('status', __(':count scores imported', ['count' => $imported]));
    }
}
