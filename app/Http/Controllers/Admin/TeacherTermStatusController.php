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
use App\Services\TeacherTermStatusService;
use Illuminate\Http\Request;
use Yajra\DataTables\Facades\DataTables;

class TeacherTermStatusController extends Controller
{
    public function __construct(private TeacherTermStatusService $service)
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

    public function index(Request $request)
    {
        [$yearId, $semesterId] = $this->resolveYearSemester($request);
        $academicYear = $yearId ? AcademicYear::find($yearId) : null;
        $semester = $semesterId ? Semester::find($semesterId) : null;
        $summary = ($yearId && $semesterId) ? $this->service->getTermStatusSummary($yearId, $semesterId) : null;

        return view('admin.teacher-term-status.index', compact('academicYear', 'semester', 'summary'));
    }

    public function data(Request $request)
    {
        [$yearId, $semesterId] = $this->resolveYearSemester($request);

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

        return DataTables::of($teachers)
            ->addColumn('avatar', function ($teacher) {
                $path = $teacher->image_path ? asset($teacher->image_path) : 'https://ui-avatars.com/api/?name=' . urlencode($teacher->name) . '&color=7F9CF5&background=EBF4FF';
                return '<img src="' . $path . '" class="w-10 h-10 rounded-xl object-cover shadow-sm border border-gray-100" alt="">';
            })
            ->addColumn('master_status_badge', function ($teacher) {
                $text = $teacher->status == 1 ? __('Active') : __('Not Active');
                $color = $teacher->status == 1 ? 'bg-emerald-50 text-emerald-600' : 'bg-rose-50 text-rose-600';
                return '<span class="px-2 py-1 rounded-lg ' . $color . ' text-[10px] font-bold uppercase tracking-wider">' . $text . '</span>';
            })
            ->addColumn('term_status_badge', function ($teacher) {
                $status = $teacher->term_status;
                if (!$status) {
                    return '<span class="px-2 py-1 rounded-lg bg-gray-50 text-gray-400 text-[10px] font-bold uppercase tracking-wider">' . __('No Record') . '</span>';
                }
                $colors = [
                    'available' => 'bg-emerald-50 text-emerald-600',
                    'unavailable' => 'bg-rose-50 text-rose-600',
                    'leave' => 'bg-amber-50 text-amber-600',
                    'partial' => 'bg-blue-50 text-blue-600',
                    'transferred' => 'bg-purple-50 text-purple-600',
                    'resigned_term' => 'bg-gray-100 text-gray-600',
                ];
                $color = $colors[$status] ?? 'bg-gray-50 text-gray-500';
                return '<span class="px-2 py-1 rounded-lg ' . $color . ' text-[10px] font-bold uppercase tracking-wider">' . __(ucfirst(str_replace('_', ' ', $status))) . '</span>';
            })
            ->addColumn('schedule_badge', function ($teacher) {
                if ($teacher->term_status === null) {
                    $can = $teacher->status == 1;
                } else {
                    $can = (bool) $teacher->can_be_scheduled;
                }
                $text = $can ? __('Yes') : __('No');
                $color = $can ? 'text-emerald-600' : 'text-rose-600';
                return '<span class="font-bold text-xs ' . $color . '">' . $text . '</span>';
            })
            ->addColumn('max_load', function ($teacher) {
                $parts = [];
                if ($teacher->max_periods_per_day) $parts[] = $teacher->max_periods_per_day . '/' . __('Day');
                if ($teacher->max_periods_per_week) $parts[] = $teacher->max_periods_per_week . '/' . __('Week');
                return $parts ? '<span class="text-xs text-gray-500">' . implode(', ', $parts) . '</span>' : '<span class="text-xs text-gray-300">-</span>';
            })
            ->addColumn('action', function ($teacher) {
                $url = route('admin.teacher-term-status.edit', $teacher->id);
                return '<a href="' . $url . '" class="inline-flex items-center justify-center w-8 h-8 rounded-lg bg-white border border-gray-100 text-amber-500 hover:bg-amber-50 transition-all shadow-sm" title="' . __('Edit') . '"><i class="fas fa-edit text-xs"></i></a>';
            })
            ->rawColumns(['avatar', 'master_status_badge', 'term_status_badge', 'schedule_badge', 'max_load', 'action'])
            ->make(true);
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

        return view('admin.teacher-term-status.save', compact(
            'teacher', 'academicYear', 'semester', 'termStatus', 'logs',
            'currentCourseIds', 'hasTermCourses', 'selectedCourses',
            'educationLevels', 'subjectGroups', 'semesters', 'termUnavailable'
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
