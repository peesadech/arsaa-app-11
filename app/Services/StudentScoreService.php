<?php

namespace App\Services;

use App\Models\GradeSetting;
use App\Models\OpenedCourse;
use App\Models\Student;
use App\Models\StudentEnrollment;
use App\Models\StudentScore;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;

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

    /**
     * บันทึกคะแนนทั้งห้องของวิชาหนึ่ง — คำนวณคะแนนรวม + เกรด + ผ่าน/ไม่ผ่าน อัตโนมัติ
     * $rows: [student_id => ['score_collect','score_midterm','score_final','remark']]
     */
    public function saveScores(OpenedCourse $openedCourse, array $rows, ?int $teacherId): int
    {
        $saved = 0;

        foreach ($rows as $studentId => $row) {
            $collect = ($row['score_collect'] ?? '') !== '' && $row['score_collect'] !== null ? (float) $row['score_collect'] : null;
            $midterm = ($row['score_midterm'] ?? '') !== '' && $row['score_midterm'] !== null ? (float) $row['score_midterm'] : null;
            $final = ($row['score_final'] ?? '') !== '' && $row['score_final'] !== null ? (float) $row['score_final'] : null;

            // ไม่กรอกอะไรเลยและไม่มี record เดิม → ข้าม
            $existing = StudentScore::where('student_id', $studentId)
                ->where('opened_course_id', $openedCourse->id)
                ->first();
            if ($collect === null && $midterm === null && $final === null && empty($row['remark']) && !$existing) {
                continue;
            }

            $total = ($collect !== null || $midterm !== null || $final !== null)
                ? round(($collect ?? 0) + ($midterm ?? 0) + ($final ?? 0), 2)
                : null;

            $grade = null;
            $resultStatus = null;
            if ($total !== null) {
                $setting = GradeSetting::gradeForScore($total);
                $grade = $setting?->grade;
                $resultStatus = $setting
                    ? ($setting->is_pass ? StudentScore::RESULT_PASS : StudentScore::RESULT_FAIL)
                    : null;
            }

            StudentScore::updateOrCreate(
                ['student_id' => $studentId, 'opened_course_id' => $openedCourse->id],
                [
                    'teacher_id' => $teacherId,
                    'score_collect' => $collect,
                    'score_midterm' => $midterm,
                    'score_final' => $final,
                    'total_score' => $total,
                    'grade' => $grade,
                    'result_status' => $resultStatus,
                    'remark' => $row['remark'] ?? null,
                    'updated_by' => Auth::id(),
                ]
            );
            $saved++;
        }

        return $saved;
    }
}
