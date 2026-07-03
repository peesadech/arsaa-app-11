<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AcademicYear;
use App\Models\CurrentAcademicSetting;
use App\Models\GradeSetting;
use App\Models\OpenedClassroom;
use App\Models\OpenedCourse;
use App\Models\Semester;
use App\Models\StudentScore;
use App\Services\StudentScoreService;
use Illuminate\Http\Request;

class StudentScoreController extends Controller
{
    public function __construct(private StudentScoreService $scoreService)
    {
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

    /**
     * หน้ากรอกคะแนนของวิชาหนึ่ง — แสดงนักเรียนทุกคนในห้องนั้น
     */
    public function entry($openedCourseId)
    {
        $openedCourse = OpenedCourse::with('course.teachers', 'grade', 'classroom', 'academicYear', 'semester')
            ->findOrFail($openedCourseId);

        $enrollments = $this->scoreService->enrollmentsForCourse($openedCourse);

        $scores = StudentScore::where('opened_course_id', $openedCourse->id)
            ->get()
            ->keyBy('student_id');

        $gradeSettings = GradeSetting::orderBy('sort_order')->get();

        return view('admin.student-scores.entry', compact('openedCourse', 'enrollments', 'scores', 'gradeSettings'));
    }

    public function save(Request $request, $openedCourseId)
    {
        $openedCourse = OpenedCourse::findOrFail($openedCourseId);

        $data = $request->validate([
            'teacher_id' => 'nullable|exists:teachers,id',
            'scores' => 'required|array',
            'scores.*.score_collect' => 'nullable|numeric|min:0|max:100',
            'scores.*.score_midterm' => 'nullable|numeric|min:0|max:100',
            'scores.*.score_final' => 'nullable|numeric|min:0|max:100',
            'scores.*.remark' => 'nullable|string|max:255',
        ]);

        $saved = $this->scoreService->saveScores($openedCourse, $data['scores'], $data['teacher_id'] ?? null);

        return redirect()->route('admin.student-scores.entry', $openedCourse->id)
            ->with('status', __(':count scores saved', ['count' => $saved]));
    }
}
