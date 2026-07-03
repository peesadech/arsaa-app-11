<?php

namespace App\Http\Controllers\Teacher;

use App\Http\Controllers\Controller;
use App\Models\AcademicYear;
use App\Models\CurrentAcademicSetting;
use App\Models\GradeSetting;
use App\Models\OpenedCourse;
use App\Models\Semester;
use App\Models\StudentScore;
use App\Models\Teacher;
use App\Models\TeacherTermCourse;
use App\Services\StudentScoreService;
use App\Services\TeacherAccountService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class MyScoreController extends Controller
{
    public function __construct(
        private StudentScoreService $scoreService,
        private TeacherAccountService $accountService,
    ) {
    }

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

    /**
     * วิชาที่ครูคนนี้สอนในเทอมปัจจุบัน (ห้อง × วิชา)
     */
    private function myOpenedCourses(Teacher $teacher, int $yearId, int $semesterId)
    {
        return $teacher->openedCoursesForTerm($yearId, $semesterId);
    }

    public function index(Request $request)
    {
        [$yearId, $semesterId] = $this->resolveYearSemester($request);
        $teacher = $this->currentTeacher();

        $academicYear = AcademicYear::find($yearId);
        $semester = Semester::find($semesterId);

        $openedCourses = ($yearId && $semesterId)
            ? $this->myOpenedCourses($teacher, $yearId, $semesterId)
            : collect();

        // จำนวนนักเรียนที่มีคะแนนแล้ว ต่อวิชา
        $scored = StudentScore::whereIn('opened_course_id', $openedCourses->pluck('id'))
            ->selectRaw('opened_course_id, count(*) as total')
            ->groupBy('opened_course_id')
            ->pluck('total', 'opened_course_id');

        $openedCourses->each(fn($oc) => $oc->scored_count = $scored[$oc->id] ?? 0);

        return view('teacher.scores.index', compact('teacher', 'academicYear', 'semester', 'openedCourses'));
    }

    public function entry(Request $request, $openedCourseId)
    {
        $teacher = $this->currentTeacher();
        $openedCourse = OpenedCourse::with('course.subjectGroup', 'grade', 'classroom', 'academicYear', 'semester')
            ->findOrFail($openedCourseId);

        // เข้าได้เฉพาะวิชาของตัวเอง
        abort_unless(
            $this->myOpenedCourses($teacher, $openedCourse->academic_year_id, $openedCourse->semester_id)->contains('id', $openedCourse->id),
            403,
            __('You can only record scores for your own courses')
        );

        $enrollments = $this->scoreService->enrollmentsForCourse($openedCourse);
        $scores = StudentScore::where('opened_course_id', $openedCourse->id)->get()->keyBy('student_id');
        $gradeSettings = GradeSetting::orderBy('sort_order')->get();

        return view('teacher.scores.entry', compact('teacher', 'openedCourse', 'enrollments', 'scores', 'gradeSettings'));
    }

    public function save(Request $request, $openedCourseId)
    {
        $teacher = $this->currentTeacher();
        $openedCourse = OpenedCourse::findOrFail($openedCourseId);

        abort_unless(
            $this->myOpenedCourses($teacher, $openedCourse->academic_year_id, $openedCourse->semester_id)->contains('id', $openedCourse->id),
            403,
            __('You can only record scores for your own courses')
        );

        $data = $request->validate([
            'scores' => 'required|array',
            'scores.*.score_collect' => 'nullable|numeric|min:0|max:100',
            'scores.*.score_midterm' => 'nullable|numeric|min:0|max:100',
            'scores.*.score_final' => 'nullable|numeric|min:0|max:100',
            'scores.*.remark' => 'nullable|string|max:255',
        ]);

        // teacher_id บังคับเป็นตัวเอง
        $saved = $this->scoreService->saveScores($openedCourse, $data['scores'], $teacher->id);

        return redirect()->route('teacher.scores.entry', $openedCourse->id)
            ->with('status', __(':count scores saved', ['count' => $saved]));
    }
}
