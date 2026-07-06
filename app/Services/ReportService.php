<?php

namespace App\Services;

use App\Models\BehaviorScore;
use App\Models\CourseWeight;
use App\Models\GradeSetting;
use App\Models\OpenedCourse;
use App\Models\Student;
use App\Models\StudentBehaviorRecord;
use App\Models\StudentEnrollment;
use App\Models\StudentScore;
use Illuminate\Support\Collection;

class ReportService
{
    /** แปลงเกรดเป็นแต้ม (GPA 4.0) — เกรดตัวเลขใช้ค่าตรง, เกรดตัวอักษร map มาตรฐาน, ผลพิเศษ = null */
    public function gradePoint(?string $grade): ?float
    {
        if ($grade === null || $grade === '') {
            return null;
        }
        if (is_numeric($grade)) {
            return (float) $grade;
        }
        $map = [
            'A' => 4.0, 'A-' => 3.7, 'B+' => 3.5, 'B' => 3.0, 'B-' => 2.7,
            'C+' => 2.5, 'C' => 2.0, 'C-' => 1.7, 'D+' => 1.5, 'D' => 1.0, 'D-' => 0.7, 'F' => 0.0,
        ];
        return $map[strtoupper($grade)] ?? null;
    }

    /** enrollment ที่ active ของนักเรียนในเทอมนั้น */
    public function enrollment(int $studentId, int $yearId, int $semesterId): ?StudentEnrollment
    {
        return StudentEnrollment::where('student_id', $studentId)
            ->where('academic_year_id', $yearId)
            ->where('semester_id', $semesterId)
            ->where('status', StudentEnrollment::STATUS_ENROLLED)
            ->first();
    }

    /**
     * รายงานผลการเรียนรายคนต่อเทอม: รายวิชา + คะแนนถ่วงน้ำหนัก + เกรดรวม + GPA + ความประพฤติ
     */
    public function studentTermReport(int $studentId, int $yearId, int $semesterId): array
    {
        $student = Student::find($studentId);
        $enrollment = $this->enrollment($studentId, $yearId, $semesterId);

        $subjects = collect();
        if ($enrollment) {
            $openedCourses = OpenedCourse::with('course.subjectGroup')
                ->where('academic_year_id', $yearId)
                ->where('semester_id', $semesterId)
                ->where('grade_id', $enrollment->grade_id)
                ->where('classroom_id', $enrollment->classroom_id)
                ->get();

            $scores = StudentScore::where('student_id', $studentId)
                ->whereIn('opened_course_id', $openedCourses->pluck('id'))
                ->get()->keyBy('opened_course_id');

            $weights = CourseWeight::where('academic_year_id', $yearId)
                ->where('semester_id', $semesterId)
                ->where('grade_id', $enrollment->grade_id)
                ->get()->keyBy('course_id');

            $subjects = $openedCourses->map(function ($oc) use ($scores, $weights) {
                $score = $scores->get($oc->id);
                $weight = $weights->get($oc->course_id);
                return [
                    'opened_course' => $oc,
                    'name' => $oc->course->name ?? '?',
                    'subject_group' => $oc->course->subjectGroup->name_th ?? null,
                    'weight' => $weight ? (float) $weight->weight : null,
                    'total' => $score && $score->total_score !== null ? (float) $score->total_score : null,
                    'grade' => $score ? ($score->displayGrade() === '-' ? null : $score->displayGrade()) : null,
                    'result_status' => $score?->result_status,
                    'grade_point' => $score ? $this->gradePoint($score->grade) : null,
                ];
            })->sortByDesc('weight')->values();
        }

        // คะแนนรวมถ่วงน้ำหนัก (ตามสัดส่วนวิชา)
        $wSum = 0.0; $wScore = 0.0; $gpaW = 0.0; $gpaSum = 0.0;
        foreach ($subjects as $s) {
            if ($s['total'] === null) {
                continue;
            }
            $w = $s['weight'] ?? 0;
            if ($w > 0) {
                $wScore += $s['total'] * $w;
                $wSum += $w;
                if ($s['grade_point'] !== null) {
                    $gpaW += $s['grade_point'] * $w;
                    $gpaSum += $w;
                }
            }
        }
        $weightedScore = $wSum > 0 ? round($wScore / $wSum, 2) : null;
        $gpa = $gpaSum > 0 ? round($gpaW / $gpaSum, 2) : null;

        $overall = $weightedScore !== null ? GradeSetting::gradeForScore($weightedScore) : null;

        // ความประพฤติ (ความดี/ความชั่ว)
        $behavior = StudentBehaviorRecord::where('student_id', $studentId)
            ->where('academic_year_id', $yearId)
            ->where('semester_id', $semesterId)
            ->get();
        $merit = round($behavior->where('type', BehaviorScore::TYPE_MERIT)->sum(fn($r) => (float) $r->score), 2);
        $demerit = round($behavior->where('type', BehaviorScore::TYPE_DEMERIT)->sum(fn($r) => (float) $r->score), 2);

        return [
            'student' => $student,
            'enrollment' => $enrollment,
            'subjects' => $subjects,
            'weighted_score' => $weightedScore,
            'overall_grade' => $overall?->grade,
            'overall_pass' => $overall ? (bool) $overall->is_pass : null,
            'gpa' => $gpa,
            'weight_total' => round($wSum, 2),
            'merit' => $merit,
            'demerit' => $demerit,
            'behavior_net' => round($merit + $demerit, 2),
            'behavior_records' => $behavior,
        ];
    }

    /* ============================================================
     |  ใบผลการเรียน (เทอม/ปี) แบบแยกช่วง 期中/期末/平时
     * ============================================================ */

    /** normalize คะแนนช่วง (กลุ่ม score_items) เป็น 0-100 : Σscore/Σfull×100 ; null ถ้าไม่มีคะแนน */
    private function normalizeSection(Collection $items, array $entries): ?float
    {
        $sumScore = 0.0; $sumFull = 0.0; $has = false;
        foreach ($items as $it) {
            if (!array_key_exists($it->id, $entries) || $entries[$it->id] === null) {
                continue;
            }
            $has = true;
            $sumScore += (float) $entries[$it->id];
            $sumFull += (float) $it->full_score;
        }
        if (!$has) {
            return null;
        }
        return $sumFull > 0 ? round($sumScore / $sumFull * 100, 2) : 0.0;
    }

    /** นับ สาย/ขาด/ลา ของนักเรียนในเทอม จากระบบเช็กชื่อ */
    public function attendanceCounts(int $studentId, int $yearId, int $semesterId): array
    {
        $rows = \App\Models\ClassSessionStudent::query()
            ->join('class_sessions', 'class_sessions.id', '=', 'class_session_students.class_session_id')
            ->leftJoin('attendance_statuses', 'attendance_statuses.id', '=', 'class_session_students.attendance_status_id')
            ->where('class_session_students.student_id', $studentId)
            ->where('class_sessions.academic_year_id', $yearId)
            ->where('class_sessions.semester_id', $semesterId)
            ->selectRaw('SUM(attendance_statuses.is_late) as late, SUM(attendance_statuses.is_leave) as leave_, SUM(attendance_statuses.is_count_as_absent) as absent')
            ->first();

        return [
            'late' => (int) ($rows->late ?? 0),
            'leave' => (int) ($rows->leave_ ?? 0),
            'absent' => (int) ($rows->absent ?? 0),
        ];
    }

    /**
     * คำนวณผลรายคนทั้งห้อง แบบแยกช่วง (สำหรับจัดอันดับ + ใบผลการเรียน)
     * คืน ['section_weight'=>, 'count'=>, 'students'=>Collection[ per student: sections, overall, ranks... ]]
     */
    public function classTermCards(int $yearId, int $semesterId, int $gradeId, int $classroomId): array
    {
        $sw = \App\Models\GradeSectionWeight::forGrade($yearId, $semesterId, $gradeId);

        $openedCourses = OpenedCourse::with('course.subjectGroup')
            ->where('academic_year_id', $yearId)->where('semester_id', $semesterId)
            ->where('grade_id', $gradeId)->where('classroom_id', $classroomId)
            ->get();

        $weights = CourseWeight::where('academic_year_id', $yearId)->where('semester_id', $semesterId)
            ->where('grade_id', $gradeId)->get()->keyBy('course_id');

        $items = \App\Models\ScoreItem::whereIn('opened_course_id', $openedCourses->pluck('id'))
            ->where('is_active', true)->get()->groupBy('opened_course_id');

        $entries = \App\Models\StudentScoreItem::whereIn('score_item_id', $items->flatten()->pluck('id'))
            ->get()->groupBy('student_id');

        $enrollments = StudentEnrollment::where('academic_year_id', $yearId)->where('semester_id', $semesterId)
            ->where('grade_id', $gradeId)->where('classroom_id', $classroomId)
            ->where('status', StudentEnrollment::STATUS_ENROLLED)
            ->with('student')->get();

        // ความประพฤติ 操行评量 (หัวข้อ + คะแนนรายคน)
        $criteria = \App\Models\ConductCriterion::active()->orderBy('sort_order')->orderBy('id')->get();
        $conductScores = \App\Models\StudentConductScore::where('academic_year_id', $yearId)
            ->where('semester_id', $semesterId)
            ->whereIn('student_id', $enrollments->pluck('student_id'))
            ->get()->groupBy('student_id');

        $students = $enrollments->map(function ($en) use ($openedCourses, $items, $entries, $weights, $sw, $yearId, $semesterId, $criteria, $conductScores) {
            $studentId = $en->student_id;
            $myEntries = ($entries->get($studentId) ?? collect())->keyBy('score_item_id')
                ->map(fn($e) => $e->score)->toArray();

            $subjects = $openedCourses->map(function ($oc) use ($items, $myEntries, $weights) {
                $ocItems = $items->get($oc->id) ?? collect();
                $mid = $ocItems->where('category', 'midterm');
                $fin = $ocItems->where('category', 'final');
                $col = $ocItems->whereNotIn('category', ['midterm', 'final'])->where('counts_toward_total', true);
                $w = $weights->get($oc->course_id);
                $wv = $w ? (float) $w->weight : 0.0;

                $ms = $this->normalizeSection($mid, $myEntries);
                $fs = $this->normalizeSection($fin, $myEntries);
                $cs = $this->normalizeSection($col, $myEntries);

                return [
                    'name' => $oc->course->name ?? '?',
                    'weight' => $wv,
                    'midterm' => $ms, 'final' => $fs, 'collect' => $cs,
                    'midterm_w' => $ms !== null ? round($ms * $wv / 100, 2) : null,
                    'final_w' => $fs !== null ? round($fs * $wv / 100, 2) : null,
                    'collect_w' => $cs !== null ? round($cs * $wv / 100, 2) : null,
                ];
            });

            // รวมแต่ละช่วง (各段得分 比分) = Σ (考分 × น้ำหนักวิชา/100)
            $midTotal = round($subjects->sum(fn($s) => $s['midterm_w'] ?? 0), 2);
            $finTotal = round($subjects->sum(fn($s) => $s['final_w'] ?? 0), 2);
            $colTotal = round($subjects->sum(fn($s) => $s['collect_w'] ?? 0), 2);

            // คะแนนแต่ละช่วง = รวมช่วง × สัดส่วนช่วง/100
            $midScore = round($midTotal * (float) $sw->midterm_weight / 100, 2);
            $finScore = round($finTotal * (float) $sw->final_weight / 100, 2);
            $colScore = round($colTotal * (float) $sw->collect_weight / 100, 2);

            // ความดี/ความชั่ว → 加/扣分
            $behavior = StudentBehaviorRecord::where('student_id', $studentId)
                ->where('academic_year_id', $yearId)->where('semester_id', $semesterId)->get();
            $behaviorNet = round($behavior->sum(fn($r) => (float) $r->score), 2);

            // ความประพฤติ 操行评量
            $myConduct = ($conductScores->get($en->student_id) ?? collect())->keyBy('conduct_criterion_id');
            $conduct = $criteria->map(fn($c) => [
                'name' => $c->name,
                'name_cn' => $c->name_cn,
                'max' => (float) $c->max_score,
                'score' => $myConduct->has($c->id) && $myConduct->get($c->id)->score !== null ? (float) $myConduct->get($c->id)->score : null,
            ])->values();
            $conductVals = $conduct->pluck('score')->filter(fn($v) => $v !== null);
            $conductAvg = $conductVals->count() ? round($conductVals->avg(), 2) : null;

            $hasScore = $subjects->contains(fn($s) => $s['midterm'] !== null || $s['final'] !== null || $s['collect'] !== null);
            $termScore = $hasScore ? round($midScore + $finScore + $colScore + $behaviorNet, 2) : null;
            $overall = $termScore !== null ? GradeSetting::gradeForScore($termScore) : null;

            return [
                'student_id' => $studentId,
                'student' => $en->student,
                'subjects' => $subjects,
                'mid_total' => $midTotal, 'fin_total' => $finTotal, 'col_total' => $colTotal,
                'mid_score' => $midScore, 'fin_score' => $finScore, 'col_score' => $colScore,
                'behavior_net' => $behaviorNet,
                'merit' => round($behavior->where('type', BehaviorScore::TYPE_MERIT)->sum(fn($r) => (float) $r->score), 2),
                'demerit' => round($behavior->where('type', BehaviorScore::TYPE_DEMERIT)->sum(fn($r) => (float) $r->score), 2),
                'term_score' => $termScore,
                'overall_grade' => $overall?->grade,
                'overall_pass' => $overall ? (bool) $overall->is_pass : null,
                'attendance' => $this->attendanceCounts($studentId, $yearId, $semesterId),
                'conduct' => $conduct,
                'conduct_avg' => $conductAvg,
            ];
        });

        // จัดอันดับ (กลางภาค/ปลายภาค/เทอม)
        $students = $this->assignRank($students, 'mid_score', 'rank_mid');
        $students = $this->assignRank($students, 'fin_score', 'rank_fin');
        $students = $this->assignRank($students, 'term_score', 'rank_term');

        return [
            'section_weight' => $sw,
            'count' => $students->count(),
            'students' => $students,
        ];
    }

    /** ใส่อันดับตาม key (มากไปน้อย, ค่า null = ไม่จัดอันดับ) */
    private function assignRank(Collection $students, string $scoreKey, string $rankKey): Collection
    {
        $sorted = $students->sortByDesc(fn($s) => $s[$scoreKey] ?? -1)->values();
        $rank = 0; $prev = null; $i = 0;
        $rankMap = [];
        foreach ($sorted as $s) {
            $i++;
            if (($s[$scoreKey] ?? null) === null) { $rankMap[$s['student_id']] = null; continue; }
            if ($prev === null || $s[$scoreKey] < $prev) { $rank = $i; }
            $rankMap[$s['student_id']] = $rank;
            $prev = $s[$scoreKey];
        }
        return $students->map(function ($s) use ($rankMap, $rankKey) {
            $s[$rankKey] = $rankMap[$s['student_id']] ?? null;
            return $s;
        });
    }

    /** ใบผลการเรียนรายคน แบบเทอม (แยกช่วง) */
    public function studentTermCard(int $studentId, int $yearId, int $semesterId): array
    {
        $student = Student::find($studentId);
        $enrollment = $this->enrollment($studentId, $yearId, $semesterId);
        if (!$enrollment) {
            return ['student' => $student, 'enrollment' => null, 'data' => null];
        }
        $class = $this->classTermCards($yearId, $semesterId, $enrollment->grade_id, $enrollment->classroom_id);
        $mine = $class['students']->firstWhere('student_id', $studentId);

        return [
            'student' => $student,
            'enrollment' => $enrollment,
            'section_weight' => $class['section_weight'],
            'class_size' => $class['count'],
            'data' => $mine,
        ];
    }

    /** ใบผลการเรียนรายคน แบบปี (รวม 2 เทอม) */
    public function studentYearCard(int $studentId, int $yearId): array
    {
        $semesters = \App\Models\Semester::orderBy('semester_number')->get();
        $terms = [];
        foreach ($semesters as $sem) {
            $card = $this->studentTermCard($studentId, $yearId, $sem->id);
            if ($card['enrollment']) {
                $terms[$sem->semester_number] = $card;
            }
        }
        $t1 = $terms[1]['data'] ?? null;
        $t2 = $terms[2]['data'] ?? null;
        $avg = function ($a, $b) {
            $vals = array_values(array_filter([$a, $b], fn($v) => $v !== null));
            return count($vals) ? round(array_sum($vals) / count($vals), 2) : null;
        };
        $yearTerm = $avg($t1['term_score'] ?? null, $t2['term_score'] ?? null);
        $overall = $yearTerm !== null ? GradeSetting::gradeForScore($yearTerm) : null;

        $year = [
            'mid_score' => $avg($t1['mid_score'] ?? null, $t2['mid_score'] ?? null),
            'fin_score' => $avg($t1['fin_score'] ?? null, $t2['fin_score'] ?? null),
            'col_score' => $avg($t1['col_score'] ?? null, $t2['col_score'] ?? null),
            'term_score' => $yearTerm,
            'overall_grade' => $overall?->grade,
            'overall_pass' => $overall ? (bool) $overall->is_pass : null,
            'merit' => round(($t1['merit'] ?? 0) + ($t2['merit'] ?? 0), 2),
            'demerit' => round(($t1['demerit'] ?? 0) + ($t2['demerit'] ?? 0), 2),
        ];

        return [
            'student' => Student::find($studentId),
            'terms' => $terms,
            'year' => $year,
        ];
    }

    /**
     * รายงานรายห้อง: คะแนนรวมถ่วงน้ำหนัก + เกรด + จัดอันดับ (rank)
     */
    public function classReport(int $yearId, int $semesterId, int $gradeId, int $classroomId): Collection
    {
        $enrollments = StudentEnrollment::where('academic_year_id', $yearId)
            ->where('semester_id', $semesterId)
            ->where('grade_id', $gradeId)
            ->where('classroom_id', $classroomId)
            ->where('status', StudentEnrollment::STATUS_ENROLLED)
            ->with('student')
            ->get();

        $rows = $enrollments->map(function ($en) use ($yearId, $semesterId) {
            $r = $this->studentTermReport($en->student_id, $yearId, $semesterId);
            return [
                'student' => $en->student,
                'weighted_score' => $r['weighted_score'],
                'overall_grade' => $r['overall_grade'],
                'overall_pass' => $r['overall_pass'],
                'gpa' => $r['gpa'],
                'behavior_net' => $r['behavior_net'],
            ];
        });

        // จัดอันดับตามคะแนนรวม (ไม่มีคะแนน = ท้ายสุด)
        $sorted = $rows->sortByDesc(fn($r) => $r['weighted_score'] ?? -1)->values();
        $rank = 0; $prev = null; $i = 0;
        return $sorted->map(function ($r) use (&$rank, &$prev, &$i) {
            $i++;
            if ($r['weighted_score'] === null) {
                $r['rank'] = null;
            } else {
                if ($prev === null || $r['weighted_score'] < $prev) {
                    $rank = $i;
                }
                $r['rank'] = $rank;
                $prev = $r['weighted_score'];
            }
            return $r;
        });
    }
}
