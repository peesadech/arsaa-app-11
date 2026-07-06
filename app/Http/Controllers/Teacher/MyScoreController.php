<?php

namespace App\Http\Controllers\Teacher;

use App\Http\Controllers\Concerns\HandlesScoreEntry;
use App\Http\Controllers\Controller;
use App\Models\AcademicYear;
use App\Models\CurrentAcademicSetting;
use App\Models\OpenedCourse;
use App\Models\Semester;
use App\Models\StudentScore;
use App\Models\Teacher;
use App\Services\StudentScoreService;
use App\Services\TeacherAccountService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class MyScoreController extends Controller
{
    use HandlesScoreEntry;

    public function __construct(
        private StudentScoreService $scoreService,
        private TeacherAccountService $accountService,
    ) {
    }

    /* ---- hooks for HandlesScoreEntry ---- */

    protected function authorizeCourse(OpenedCourse $openedCourse): void
    {
        $teacher = $this->currentTeacher();
        abort_unless(
            $this->myOpenedCourses($teacher, $openedCourse->academic_year_id, $openedCourse->semester_id)
                ->contains('id', $openedCourse->id),
            403,
            __('You can only record scores for your own courses')
        );
    }

    protected function routePrefix(): string
    {
        return 'teacher.scores';
    }

    protected function gridView(): string
    {
        return 'teacher.scores.entry';
    }

    protected function summaryTeacherId(OpenedCourse $openedCourse, Request $request): ?int
    {
        return $this->currentTeacher()->id;
    }

    /* ---- helpers ---- */

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

    private function currentTeacher(): Teacher
    {
        $teacher = $this->accountService->teacherForUser(Auth::user());
        abort_unless($teacher, 403, __('No teacher record linked to this account'));

        return $teacher;
    }

    private function myOpenedCourses(Teacher $teacher, int $yearId, int $semesterId)
    {
        return $teacher->openedCoursesForTerm($yearId, $semesterId);
    }

    /* ---- listing ---- */

    public function index(Request $request)
    {
        [$yearId, $semesterId] = $this->resolveYearSemester($request);
        $teacher = $this->currentTeacher();

        $academicYear = AcademicYear::find($yearId);
        $semester = Semester::find($semesterId);

        $openedCourses = ($yearId && $semesterId)
            ? $this->myOpenedCourses($teacher, $yearId, $semesterId)
            : collect();

        $scored = StudentScore::whereIn('opened_course_id', $openedCourses->pluck('id'))
            ->selectRaw('opened_course_id, count(*) as total')
            ->groupBy('opened_course_id')
            ->pluck('total', 'opened_course_id');

        $openedCourses->each(fn($oc) => $oc->scored_count = $scored[$oc->id] ?? 0);

        return view('teacher.scores.index', compact('teacher', 'academicYear', 'semester', 'openedCourses'));
    }
}
