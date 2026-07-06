<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AcademicYear;
use App\Models\Course;
use App\Models\CurrentAcademicSetting;
use App\Models\EducationLevel;
use App\Models\Semester;
use App\Models\SubjectGroup;
use App\Models\Teacher;
use App\Models\TeacherTermCourse;
use App\Models\TeacherTermStatus;
use App\Services\TeacherSubstitutionService;
use App\Services\TeacherTermStatusService;
use Illuminate\Http\Request;

class TeacherTermStatusController extends Controller
{
    public function __construct(
        private TeacherTermStatusService $service,
        private TeacherSubstitutionService $substitutionService,
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

    /**
     * Build the teachers query (joined with term status) applying filters + sort.
     */
    private function buildTeacherQuery(Request $request, $yearId, $semesterId)
    {
        $teachers = Teacher::select('teachers.*')
            ->leftJoin('teacher_term_statuses as tts', function ($join) use ($yearId, $semesterId) {
                $join->on('teachers.id', '=', 'tts.teacher_id')
                    ->where('tts.academic_year_id', $yearId)
                    ->where('tts.semester_id', $semesterId);
            })
            ->addSelect([
                'tts.status as term_status',
                'tts.can_be_scheduled',
                'tts.max_periods_per_day',
                'tts.max_periods_per_week',
                'tts.notes as term_notes',
            ]);

        // Filter by master status
        if ($request->filled('master_status')) {
            $teachers->where('teachers.status', $request->master_status);
        }

        // Filter by term status
        if ($request->filled('term_status')) {
            if ($request->term_status === 'no_record') {
                $teachers->whereNull('tts.id');
            } else {
                $teachers->where('tts.status', $request->term_status);
            }
        }

        // Filter by can_be_scheduled
        if ($request->filled('can_schedule')) {
            if ($request->can_schedule === '1') {
                $teachers->where(function ($q) {
                    $q->whereNull('tts.id')->orWhere('tts.can_be_scheduled', true);
                });
            } else {
                $teachers->where('tts.can_be_scheduled', false);
            }
        }

        // Search by teacher name
        if ($request->filled('search')) {
            $s = $request->search;
            $teachers->where('teachers.name', 'like', "%{$s}%");
        }

        // Sort
        $sortMap = [
            'name'             => 'teachers.name',
            'status'           => 'teachers.status',
            'term_status'      => 'tts.status',
            'can_be_scheduled' => 'tts.can_be_scheduled',
        ];
        $sortBy = $sortMap[$request->get('sort_by')] ?? 'teachers.id';
        $sortOrder = $request->get('sort_order') === 'asc' ? 'asc' : 'desc';
        $teachers->orderBy($sortBy, $sortOrder);

        return $teachers;
    }

    public function index(Request $request)
    {
        [$yearId, $semesterId] = $this->resolveYearSemester($request);
        $academicYear = $yearId ? AcademicYear::find($yearId) : null;
        $semester = $semesterId ? Semester::find($semesterId) : null;
        $summary = ($yearId && $semesterId) ? $this->service->getTermStatusSummary($yearId, $semesterId) : null;

        $teachers = null;
        if ($yearId && $semesterId) {
            $perPage = (int) $request->get('per_page', 10);
            $teachers = $this->buildTeacherQuery($request, $yearId, $semesterId)
                ->paginate($perPage)->withQueryString();
        }

        return view('admin.teacher-term-status.index', compact('academicYear', 'semester', 'summary', 'teachers'));
    }

    public function data(Request $request)
    {
        [$yearId, $semesterId] = $this->resolveYearSemester($request);
        $perPage = (int) $request->get('per_page', 10);
        $teachers = $this->buildTeacherQuery($request, $yearId, $semesterId)
            ->paginate($perPage)->withQueryString();

        return response()->json([
            'html' => view('admin.teacher-term-status._rows', compact('teachers'))->render(),
            'meta' => [
                'total'        => $teachers->total(),
                'per_page'     => $teachers->perPage(),
                'current_page' => $teachers->currentPage(),
                'last_page'    => $teachers->lastPage(),
                'from'         => $teachers->firstItem() ?? 0,
                'to'           => $teachers->lastItem() ?? 0,
            ],
        ]);
    }

    public function edit(Request $request, $teacherId)
    {
        [$yearId, $semesterId] = $this->resolveYearSemester($request);
        $teacher = Teacher::with('courses')->findOrFail($teacherId);
        $academicYear = AcademicYear::find($yearId);
        $semester = Semester::find($semesterId);

        $termStatus = $this->service->getOrCreate($teacherId, $yearId, $semesterId);
        $logs = $termStatus->logs()->with('changedByUser')->orderByDesc('changed_at')->get();

        // Term courses: if exists use term-specific, otherwise fallback to global course_teacher
        $termCourseIds = TeacherTermCourse::where('teacher_id', $teacherId)
            ->where('academic_year_id', $yearId)
            ->where('semester_id', $semesterId)
            ->pluck('course_id')
            ->toArray();

        $hasTermCourses = !empty($termCourseIds);
        $currentCourseIds = $hasTermCourses ? $termCourseIds : $teacher->courses->pluck('id')->toArray();

        // Load actual course objects for selected IDs (with relations for JS init)
        $selectedCourses = Course::whereIn('id', $currentCourseIds)
            ->with('grade.educationLevel', 'semester', 'subjectGroup')
            ->get();

        // Data for course modal & schedule grid (same as teacher save)
        $educationLevels = EducationLevel::where('status', 1)->get();
        $subjectGroups = SubjectGroup::where('status', 1)->get();
        $semesters = Semester::where('status', 1)->get();

        // Term unavailable periods (fallback to global)
        $termUnavailable = $termStatus->unavailable_periods ?? $teacher->unavailable_periods ?? [];

        // จำนวนคาบที่ครูคนนี้ถูกจัดไว้ในตารางเรียนที่ใช้งานอยู่ (สำหรับ flow สอนแทน)
        $activeSolution = $this->substitutionService->getActiveSolution($yearId, $semesterId);
        $scheduledPeriodsCount = $activeSolution
            ? $this->substitutionService->getAffectedEntries($activeSolution->id, $teacherId)->count()
            : 0;

        return view('admin.teacher-term-status.save', compact(
            'teacher', 'academicYear', 'semester', 'termStatus', 'logs',
            'currentCourseIds', 'hasTermCourses', 'selectedCourses',
            'educationLevels', 'subjectGroups', 'semesters', 'termUnavailable',
            'scheduledPeriodsCount'
        ));
    }

    public function update(Request $request, $teacherId)
    {
        [$yearId, $semesterId] = $this->resolveYearSemester($request);

        $data = $request->validate([
            'status' => 'required|in:' . implode(',', TeacherTermStatus::STATUSES),
            'can_be_scheduled' => 'nullable|boolean',
            'max_periods_per_day' => 'nullable|integer|min:1|max:20',
            'max_periods_per_week' => 'nullable|integer|min:1|max:100',
            'effective_from' => 'nullable|date',
            'effective_until' => 'nullable|date|after_or_equal:effective_from',
            'notes' => 'nullable|string|max:1000',
            'reason' => 'nullable|string|max:500',
            'course_ids' => 'nullable|array',
            'course_ids.*' => 'integer|exists:courses,id',
            'unavailable_periods' => 'nullable|string',
        ]);

        $termStatus = $this->service->getOrCreate($teacherId, $yearId, $semesterId);

        $unavailablePeriods = $data['unavailable_periods']
            ? json_decode($data['unavailable_periods'], true)
            : null;

        $this->service->updateStatus(
            $termStatus,
            $data['status'],
            $request->has('can_be_scheduled') ? (bool) $data['can_be_scheduled'] : null,
            $data['reason'] ?? null,
            collect($data)->only(['max_periods_per_day', 'max_periods_per_week', 'effective_from', 'effective_until', 'notes'])->merge([
                'unavailable_periods' => $unavailablePeriods,
            ])->toArray(),
        );

        // Sync term courses
        $courseIds = $data['course_ids'] ?? [];
        TeacherTermCourse::where('teacher_id', $teacherId)
            ->where('academic_year_id', $yearId)
            ->where('semester_id', $semesterId)
            ->delete();

        foreach ($courseIds as $courseId) {
            TeacherTermCourse::create([
                'teacher_id' => $teacherId,
                'academic_year_id' => $yearId,
                'semester_id' => $semesterId,
                'course_id' => $courseId,
            ]);
        }

        // ถ้าครูถูกปิดไม่ให้จัดตาราง แต่ยังมีคาบค้างในตารางเรียนที่ใช้งานอยู่ → พาไป flow สอนแทน
        $termStatus = $termStatus->fresh();
        if (!$termStatus->can_be_scheduled) {
            $activeSolution = $this->substitutionService->getActiveSolution($yearId, $semesterId);
            $affectedCount = $activeSolution
                ? $this->substitutionService->getAffectedEntries($activeSolution->id, $teacherId)->count()
                : 0;

            if ($affectedCount > 0) {
                return redirect()->route('admin.teacher-substitution.show', $teacherId)
                    ->with('status', __('Status updated. This teacher still has :count scheduled periods — please assign substitutes.', ['count' => $affectedCount]));
            }
        }

        return redirect()->route('admin.teacher-term-status.edit', $teacherId)
            ->with('status', __('updated successfully!'));
    }

    public function bulkInitialize(Request $request)
    {
        [$yearId, $semesterId] = $this->resolveYearSemester($request);

        if (!$yearId || !$semesterId) {
            return back()->with('error', __('Please select academic year and semester first'));
        }

        $count = $this->service->bulkInitialize($yearId, $semesterId);

        return back()->with('status', __('Initialized :count teachers', ['count' => $count]));
    }

    public function bulkUpdate(Request $request)
    {
        [$yearId, $semesterId] = $this->resolveYearSemester($request);

        $data = $request->validate([
            'teacher_ids' => 'required|array|min:1',
            'teacher_ids.*' => 'integer|exists:teachers,id',
            'status' => 'required|in:' . implode(',', TeacherTermStatus::STATUSES),
            'reason' => 'nullable|string|max:500',
        ]);

        $count = 0;
        foreach ($data['teacher_ids'] as $teacherId) {
            $termStatus = $this->service->getOrCreate($teacherId, $yearId, $semesterId);
            $this->service->updateStatus($termStatus, $data['status'], null, $data['reason'] ?? null);
            $count++;
        }

        return back()->with('status', __('Updated :count teachers', ['count' => $count]));
    }
}
