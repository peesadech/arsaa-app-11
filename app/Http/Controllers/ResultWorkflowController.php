<?php

namespace App\Http\Controllers;

use App\Models\AcademicYear;
use App\Models\CourseResultSubmission;
use App\Models\CurrentAcademicSetting;
use App\Models\OpenedCourse;
use App\Models\Semester;
use App\Models\StudentScore;
use App\Models\Teacher;
use App\Services\StudentScoreService;
use App\Services\TeacherAccountService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ResultWorkflowController extends Controller
{
    public function __construct(
        private TeacherAccountService $accountService,
        private StudentScoreService $scoreService,
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

    private function isAdmin(): bool
    {
        return Auth::user()->getRoleNames()
            ->map(fn($r) => strtoupper($r))
            ->intersect(['ADMIN', 'SUPERADMIN'])
            ->isNotEmpty();
    }

    private function currentTeacher(): ?Teacher
    {
        return $this->accountService->teacherForUser(Auth::user());
    }

    private function isCourseTeacher(OpenedCourse $oc, ?Teacher $teacher): bool
    {
        if (!$teacher) {
            return false;
        }
        return $teacher->openedCoursesForTerm($oc->academic_year_id, $oc->semester_id)->contains('id', $oc->id);
    }

    private function isSubjectHead(OpenedCourse $oc, ?Teacher $teacher): bool
    {
        if (!$teacher) {
            return false;
        }
        return (int) optional($oc->course?->subjectGroup)->head_teacher_id === (int) $teacher->id;
    }

    /** การกระทำที่ผู้ใช้ทำได้กับ submission นี้ (คืน array ของ action keys) */
    public function allowedActions(OpenedCourse $oc, ?CourseResultSubmission $sub): array
    {
        $status = $sub?->status ?? CourseResultSubmission::STATUS_DRAFT;
        $teacher = $this->currentTeacher();
        $admin = $this->isAdmin();
        $isTeacher = $this->isCourseTeacher($oc, $teacher);
        $isHead = $this->isSubjectHead($oc, $teacher);
        $actions = [];

        if (in_array($status, [CourseResultSubmission::STATUS_DRAFT, CourseResultSubmission::STATUS_REJECTED], true)) {
            if ($admin || $isTeacher) {
                $actions[] = 'submit';
            }
        }
        if ($status === CourseResultSubmission::STATUS_SUBMITTED && ($admin || $isHead)) {
            $actions[] = 'review';
            $actions[] = 'reject';
        }
        if ($status === CourseResultSubmission::STATUS_REVIEWED && $admin) {
            $actions[] = 'approve';
            $actions[] = 'reject';
        }
        if ($status === CourseResultSubmission::STATUS_APPROVED && $admin) {
            $actions[] = 'publish';
            $actions[] = 'reject';
        }

        return $actions;
    }

    public function submissionFor(OpenedCourse $oc): CourseResultSubmission
    {
        return CourseResultSubmission::firstOrCreate(
            ['opened_course_id' => $oc->id],
            ['status' => CourseResultSubmission::STATUS_DRAFT]
        );
    }

    public function index(Request $request)
    {
        [$yearId, $semesterId] = $this->resolveYearSemester($request);
        $academicYear = AcademicYear::find($yearId);
        $semester = Semester::find($semesterId);

        $admin = $this->isAdmin();
        $teacher = $this->currentTeacher();

        $query = OpenedCourse::with(['course.subjectGroup', 'grade', 'classroom'])
            ->where('academic_year_id', $yearId)
            ->where('semester_id', $semesterId);

        if (!$admin) {
            // ครู: วิชาที่ตัวเองสอน + วิชาที่เป็นหัวหน้ากลุ่มสาระ
            $taughtIds = $teacher ? $teacher->openedCoursesForTerm($yearId, $semesterId)->pluck('id') : collect();
            $headGroupIds = \App\Models\SubjectGroup::where('head_teacher_id', $teacher?->id)->pluck('id');
            $query->where(function ($q) use ($taughtIds, $headGroupIds) {
                $q->whereIn('id', $taughtIds)
                  ->orWhereHas('course', fn($c) => $c->whereIn('subject_group_id', $headGroupIds));
            });
        }

        $openedCourses = $query->get();
        $subs = CourseResultSubmission::whereIn('opened_course_id', $openedCourses->pluck('id'))
            ->get()->keyBy('opened_course_id');

        $rows = $openedCourses->map(function ($oc) use ($subs) {
            $sub = $subs->get($oc->id);
            return [
                'oc' => $oc,
                'sub' => $sub,
                'status' => $sub?->status ?? CourseResultSubmission::STATUS_DRAFT,
                'actions' => $this->allowedActions($oc, $sub),
            ];
        })->sortBy(fn($r) => [$r['oc']->grade->name_th ?? '', $r['oc']->classroom->name ?? ''])->values();

        $isAdmin = $admin;

        return view('result-workflow.index', compact('academicYear', 'semester', 'rows', 'isAdmin'));
    }

    private function canView(OpenedCourse $oc): bool
    {
        if ($this->isAdmin()) {
            return true;
        }
        $teacher = $this->currentTeacher();
        return $this->isCourseTeacher($oc, $teacher) || $this->isSubjectHead($oc, $teacher);
    }

    public function show(Request $request, $openedCourseId)
    {
        $oc = OpenedCourse::with('course.subjectGroup', 'grade', 'classroom', 'academicYear', 'semester')
            ->findOrFail($openedCourseId);
        abort_unless($this->canView($oc), 403);

        $sub = CourseResultSubmission::with(['submittedBy', 'reviewedBy', 'approvedBy', 'publishedBy'])
            ->where('opened_course_id', $oc->id)->first();

        $actions = $this->allowedActions($oc, $sub);
        $enrollments = $this->scoreService->enrollmentsForCourse($oc);
        $scores = \App\Models\StudentScore::where('opened_course_id', $oc->id)->get()->keyBy('student_id');

        return view('result-workflow.show', compact('oc', 'sub', 'actions', 'enrollments', 'scores'));
    }

    public function transition(Request $request, $openedCourseId, string $action)
    {
        $oc = OpenedCourse::with('course.subjectGroup')->findOrFail($openedCourseId);
        $sub = $this->submissionFor($oc);

        abort_unless(in_array($action, $this->allowedActions($oc, $sub), true), 403, __('Action not allowed'));

        $now = now();
        switch ($action) {
            case 'submit':
                $sub->fill(['status' => CourseResultSubmission::STATUS_SUBMITTED, 'submitted_by' => Auth::id(), 'submitted_at' => $now, 'reject_reason' => null]);
                break;
            case 'review':
                $sub->fill(['status' => CourseResultSubmission::STATUS_REVIEWED, 'reviewed_by' => Auth::id(), 'reviewed_at' => $now]);
                break;
            case 'approve':
                $sub->fill(['status' => CourseResultSubmission::STATUS_APPROVED, 'approved_by' => Auth::id(), 'approved_at' => $now]);
                break;
            case 'publish':
                $sub->fill(['status' => CourseResultSubmission::STATUS_PUBLISHED, 'published_by' => Auth::id(), 'published_at' => $now]);
                break;
            case 'reject':
                $data = $request->validate(['reject_reason' => 'nullable|string|max:255']);
                $sub->fill(['status' => CourseResultSubmission::STATUS_REJECTED, 'reject_reason' => $data['reject_reason'] ?? null]);
                break;
        }
        $sub->save();

        return back()->with('status', __('Workflow updated'));
    }
}
