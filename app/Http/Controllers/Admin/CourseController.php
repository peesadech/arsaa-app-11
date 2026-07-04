<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Course;
use App\Models\Grade;
use App\Models\Semester;
use App\Models\SubjectGroup;
use App\Models\CourseType;
use App\Models\GradingScheme;
use Illuminate\Http\Request;
use Yajra\DataTables\Facades\DataTables;

class CourseController extends Controller
{
    public function index(Request $request)
    {
        $query = Course::with(['grade', 'semester', 'subjectGroup', 'gradingScheme', 'courseType.gradingScheme']);

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('subject_group_id')) {
            $query->where('subject_group_id', $request->subject_group_id);
        }

        if ($request->filled('search')) {
            $s = $request->search;
            $query->where('name', 'like', "%{$s}%");
        }

        $sortBy = in_array($request->get('sort_by'), ['name', 'status', 'id'])
            ? $request->get('sort_by') : 'id';
        $sortOrder = $request->get('sort_order') === 'asc' ? 'asc' : 'desc';
        $query->orderBy($sortBy, $sortOrder);

        $perPage = (int) $request->get('per_page', 10);
        $courses = $query->paginate($perPage)->withQueryString();

        if ($request->wantsJson()) {
            return response()->json([
                'html' => view('admin.courses._rows', compact('courses'))->render(),
                'meta' => [
                    'total'        => $courses->total(),
                    'per_page'     => $courses->perPage(),
                    'current_page' => $courses->currentPage(),
                    'last_page'    => $courses->lastPage(),
                    'from'         => $courses->firstItem() ?? 0,
                    'to'           => $courses->lastItem() ?? 0,
                ],
            ]);
        }

        $subjectGroups = SubjectGroup::where('status', 1)->get();

        return view('admin.courses.index', compact('courses', 'subjectGroups'));
    }

    public function data(Request $request)
    {
        $courses = Course::with(['grade', 'semester', 'subjectGroup', 'gradingScheme', 'courseType.gradingScheme'])->select('courses.*');

        if ($request->filled('status')) {
            $courses->where('courses.status', $request->status);
        }

        if ($request->filled('subject_group_id')) {
            $courses->where('courses.subject_group_id', $request->subject_group_id);
        }

        return DataTables::of($courses)
            ->addColumn('grade_name', function ($course) {
                return $course->grade ? ($course->grade->name_th . ' / ' . $course->grade->name_en) : '-';
            })
            ->addColumn('semester_name', function ($course) {
                return $course->semester ? $course->semester->semester_number : '-';
            })
            ->addColumn('subject_group_name', function ($course) {
                if (! $course->subjectGroup) return '<span class="text-gray-400 text-[10px] italic">-</span>';
                return '<span class="px-2 py-0.5 rounded-full bg-indigo-50 text-indigo-600 text-[10px] font-bold">' . e($course->subjectGroup->name_th) . '</span>';
            })
            ->addColumn('grading_scheme', function ($course) {
                $scheme = $course->resolveGradingScheme();

                if (! $scheme) {
                    return '<span class="px-2 py-1 rounded-lg bg-rose-50 text-rose-500 text-[10px] font-bold">' . e(__('Grading not set')) . '</span>';
                }

                // ที่มา: กำหนดเองในรายวิชา (override) หรือสืบทอดจากประเภทวิชา
                $fromCourse = $course->grading_scheme_id !== null;
                $source = $fromCourse ? __('From subject course') : __('From course type');

                // สีตามชนิดผล: เกรด = indigo, ผ่าน/ไม่ผ่าน = emerald
                $isPassFail = $scheme->result_type === \App\Models\GradingScheme::RESULT_TYPE_PASS_FAIL;
                $badgeColor = $isPassFail ? 'bg-emerald-50 text-emerald-600' : 'bg-indigo-50 text-indigo-600';
                $srcColor = $fromCourse ? 'text-indigo-400' : 'text-gray-400';

                return '<div class="flex flex-col gap-1">'
                    . '<span class="w-fit px-2 py-0.5 rounded-full ' . $badgeColor . ' text-[10px] font-bold">' . e($scheme->name) . '</span>'
                    . '<span class="text-[9px] font-bold uppercase tracking-wide ' . $srcColor . '">' . e($source) . '</span>'
                    . '</div>';
            })
            ->addColumn('status', function ($course) {
                $statusText = $course->status == 1 ? 'Active' : 'Not Active';
                $colorClass = $course->status == 1 ? 'bg-emerald-50 text-emerald-600' : 'bg-rose-50 text-rose-600';
                return '<span class="px-2 py-1 rounded-lg ' . $colorClass . ' text-[10px] font-bold uppercase tracking-wider">' . $statusText . '</span>';
            })
            ->addColumn('action', function ($course) {
                $editUrl = route('admin.courses.edit', $course->id);
                $btn = '<div class="flex justify-end space-x-2">';
                $btn .= '<a href="' . $editUrl . '" class="inline-flex items-center justify-center w-8 h-8 rounded-lg bg-white border border-gray-100 text-amber-500 hover:bg-amber-50 transition-all duration-200 shadow-sm" title="Edit Course"><i class="fas fa-edit text-xs"></i></a>';
                $btn .= '<button type="button" onclick="confirmDelete(' . $course->id . ', \'' . addslashes($course->name) . '\')" class="inline-flex items-center justify-center w-8 h-8 rounded-lg bg-white border border-gray-100 text-rose-500 hover:bg-rose-50 transition-all duration-200 shadow-sm" title="Delete Course"><i class="fas fa-trash-alt text-xs"></i></button>';
                $btn .= '</div>';
                return $btn;
            })
            ->rawColumns(['subject_group_name', 'grading_scheme', 'status', 'action'])
            ->make(true);
    }

    public function create()
    {
        $grades = Grade::all();
        $semesters = Semester::all();
        $subjectGroups = SubjectGroup::where('status', 1)->get();
        $courseTypes = CourseType::where('status', 1)->get();
        $gradingSchemes = GradingScheme::where('status', 1)->get();
        return view('admin.courses.create', compact('grades', 'semesters', 'subjectGroups', 'courseTypes', 'gradingSchemes'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name' => 'required|string|max:255|unique:courses,name',
            'grade_id' => 'required|exists:grades,id',
            'semester_id' => 'required|exists:semesters,id',
            'subject_group_id' => 'required|exists:subject_groups,id',
            'course_type_id' => 'required|exists:course_types,id',
            'grading_scheme_id' => 'nullable|exists:grading_schemes,id',
            'periods_per_week' => 'required|integer|min:1|max:20',
            'periods_per_session' => 'required|integer|min:1|max:10',
            'preferred_days' => 'nullable|array',
            'preferred_days.*' => 'integer|min:1|max:7',
            'status' => 'required|in:1,2',
        ]);

        Course::create($data);

        return redirect()->route('admin.courses.index')->with('status', 'Course created successfully!');
    }

    public function edit($id)
    {
        $course = Course::findOrFail($id);
        $grades = Grade::all();
        $semesters = Semester::all();
        $subjectGroups = SubjectGroup::where('status', 1)->get();
        $courseTypes = CourseType::where('status', 1)->get();
        $gradingSchemes = GradingScheme::where('status', 1)->get();
        return view('admin.courses.edit', compact('course', 'grades', 'semesters', 'subjectGroups', 'courseTypes', 'gradingSchemes'));
    }

    public function update(Request $request, $id)
    {
        $course = Course::findOrFail($id);
        $data = $request->validate([
            'name' => 'required|string|max:255|unique:courses,name,' . $id,
            'grade_id' => 'required|exists:grades,id',
            'semester_id' => 'required|exists:semesters,id',
            'subject_group_id' => 'required|exists:subject_groups,id',
            'course_type_id' => 'required|exists:course_types,id',
            'grading_scheme_id' => 'nullable|exists:grading_schemes,id',
            'periods_per_week' => 'required|integer|min:1|max:20',
            'periods_per_session' => 'required|integer|min:1|max:10',
            'preferred_days' => 'nullable|array',
            'preferred_days.*' => 'integer|min:1|max:7',
            'status' => 'required|in:1,2',
        ]);

        $course->update($data);

        return redirect()->route('admin.courses.index')->with('status', 'Course updated successfully!');
    }

    public function destroy($id)
    {
        $course = Course::findOrFail($id);
        $course->delete();
        return redirect()->route('admin.courses.index')->with('status', 'Course deleted successfully!');
    }
}
