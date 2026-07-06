<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Concerns\HandlesScoreEntry;
use App\Http\Controllers\Controller;
use App\Models\AcademicYear;
use App\Models\CurrentAcademicSetting;
use App\Models\OpenedClassroom;
use App\Models\OpenedCourse;
use App\Models\Semester;
use App\Models\StudentScore;
use App\Services\StudentScoreService;
use Illuminate\Http\Request;

class StudentScoreController extends Controller
{
    use HandlesScoreEntry;

    public function __construct(private StudentScoreService $scoreService)
    {
    }

    /* ---- hooks for HandlesScoreEntry ---- */

    protected function authorizeCourse(OpenedCourse $openedCourse): void
    {
        // แอดมิน/งานทะเบียน เข้าถึงได้ทุกวิชา (route group จำกัดสิทธิ์ระดับหน้าแล้ว)
    }

    protected function routePrefix(): string
    {
        return 'admin.student-scores';
    }

    protected function gridView(): string
    {
        return 'admin.student-scores.entry';
    }

    protected function summaryTeacherId(OpenedCourse $openedCourse, Request $request): ?int
    {
        $teacherId = $request->input('teacher_id');
        return $teacherId !== null && $teacherId !== '' ? (int) $teacherId : null;
    }

    /* ---- listing ---- */

    private function resolveYearSemester(Request $request): array
    {
        $yearId = $request->session()->get('current_academic_year_id');
        $semesterId = $request->session()->get('current_semester_id');

        if (!$yearId || !$semesterId) {
            $setting = CurrentAcademicSetting::latest()->first();
            $yearId = $setting?->academic_year_id;
            $semesterId = $setting?->semester_id;
        }

        return [$yearId, $semesterId];
    }

    /**
     * เลือก ห้อง → วิชา ของเทอมปัจจุบัน
     */
    public function index(Request $request)
    {
        [$yearId, $semesterId] = $this->resolveYearSemester($request);

        $academicYear = AcademicYear::find($yearId);
        $semester = Semester::find($semesterId);

        $openedClassrooms = OpenedClassroom::where('academic_year_id', $yearId)
            ->where('semester_id', $semesterId)
            ->with('grade', 'classroom')
            ->get();

        $selectedGradeId = (int) $request->query('grade_id');
        $selectedClassroomId = (int) $request->query('classroom_id');

        $openedCourses = collect();
        if ($selectedGradeId && $selectedClassroomId) {
            $openedCourses = OpenedCourse::where('academic_year_id', $yearId)
                ->where('semester_id', $semesterId)
                ->where('grade_id', $selectedGradeId)
                ->where('classroom_id', $selectedClassroomId)
                ->with('course.subjectGroup', 'course.teachers')
                ->get();

            // จำนวนนักเรียนที่มีคะแนนแล้ว ต่อวิชา
            $scored = StudentScore::whereIn('opened_course_id', $openedCourses->pluck('id'))
                ->selectRaw('opened_course_id, count(*) as total')
                ->groupBy('opened_course_id')
                ->pluck('total', 'opened_course_id');

            $openedCourses->each(fn($oc) => $oc->scored_count = $scored[$oc->id] ?? 0);
        }

        return view('admin.student-scores.index', compact(
            'academicYear', 'semester', 'openedClassrooms',
            'selectedGradeId', 'selectedClassroomId', 'openedCourses'
        ));
    }
}
