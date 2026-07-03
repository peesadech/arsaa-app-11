<?php

namespace App\Http\Controllers\Teacher;

use App\Http\Controllers\Controller;
use App\Models\AcademicYear;
use App\Models\CurrentAcademicSetting;
use App\Models\Semester;
use App\Models\StudentEnrollment;
use App\Models\StudentScore;
use App\Models\TimetableEntry;
use App\Models\TimetableSolution;
use App\Models\YearlySchedule;
use App\Services\TeacherAccountService;
use Illuminate\Support\Facades\Auth;

class TeacherDashboardController extends Controller
{
    public function __construct(private TeacherAccountService $accountService)
    {
    }

    public function index()
    {
        $teacher = $this->accountService->teacherForUser(Auth::user());
        abort_unless($teacher, 403, __('No teacher record linked to this account'));

        // ปีการศึกษา + เทอม อ้างอิงจาก admin config (CurrentAcademicSetting)
        $setting = CurrentAcademicSetting::latest()->first();
        $yearId = $setting?->academic_year_id;
        $semesterId = $setting?->semester_id;

        $academicYear = $yearId ? AcademicYear::find($yearId) : null;
        $semester = $semesterId ? Semester::find($semesterId) : null;

        $openedCourses = ($yearId && $semesterId)
            ? $teacher->openedCoursesForTerm($yearId, $semesterId)
            : collect();

        // ความคืบหน้าการบันทึกคะแนน ต่อวิชา
        $scored = StudentScore::whereIn('opened_course_id', $openedCourses->pluck('id'))
            ->selectRaw('opened_course_id, count(*) as total')
            ->groupBy('opened_course_id')
            ->pluck('total', 'opened_course_id');

        // จำนวนนักเรียนต่อห้อง (ไว้เทียบกับจำนวนที่กรอกคะแนนแล้ว)
        $roomKeys = $openedCourses->map(fn($oc) => $oc->grade_id . '-' . $oc->classroom_id)->unique();
        $studentCounts = ($yearId && $semesterId)
            ? StudentEnrollment::where('academic_year_id', $yearId)
                ->where('semester_id', $semesterId)
                ->where('status', StudentEnrollment::STATUS_ENROLLED)
                ->selectRaw('grade_id, classroom_id, count(*) as total')
                ->groupBy('grade_id', 'classroom_id')
                ->get()
                ->keyBy(fn($r) => $r->grade_id . '-' . $r->classroom_id)
            : collect();

        $openedCourses->each(function ($oc) use ($scored, $studentCounts) {
            $oc->scored_count = $scored[$oc->id] ?? 0;
            $oc->student_count = $studentCounts[$oc->grade_id . '-' . $oc->classroom_id]->total ?? 0;
        });

        // จัดกลุ่มเป็น: ห้อง → รายวิชา
        $rooms = $openedCourses
            ->groupBy(fn($oc) => $oc->grade_id . '-' . $oc->classroom_id)
            ->map(fn($group) => [
                'grade' => $group->first()->grade,
                'classroom' => $group->first()->classroom,
                'student_count' => $group->first()->student_count,
                'courses' => $group->values(),
            ])
            ->values();

        // ตารางสอนจากตารางเรียนที่ใช้งานอยู่ (ถ้ามี)
        $entries = collect();
        $yearlySchedules = collect();
        if ($yearId && $semesterId) {
            $activeSolution = TimetableSolution::whereHas('generation', fn($q) => $q
                    ->where('academic_year_id', $yearId)
                    ->where('semester_id', $semesterId)
                    ->whereIn('status', ['completed', 'manual']))
                ->where('is_selected', true)
                ->first();

            if ($activeSolution) {
                $entries = TimetableEntry::where('solution_id', $activeSolution->id)
                    ->where('teacher_id', $teacher->id)
                    ->with('openedCourse.course.subjectGroup', 'openedCourse.classroom', 'openedCourse.grade', 'room')
                    ->get();

                $yearlySchedules = YearlySchedule::where('academic_year_id', $yearId)
                    ->where('semester_id', $semesterId)
                    ->get();
            }
        }

        $stats = [
            'rooms' => $rooms->count(),
            'courses' => $openedCourses->count(),
            'periods_per_week' => $entries->count(),
            'scores_recorded' => $openedCourses->sum('scored_count'),
        ];

        return view('teacher.dashboard', compact(
            'teacher', 'academicYear', 'semester', 'rooms', 'stats', 'entries', 'yearlySchedules'
        ));
    }
}
