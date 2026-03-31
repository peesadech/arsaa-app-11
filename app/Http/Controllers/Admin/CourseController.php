<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Course;
use App\Models\Grade;
use App\Models\Semester;
use App\Models\SubjectGroup;
use Illuminate\Http\Request;
use Yajra\DataTables\Facades\DataTables;

class CourseController extends Controller
{
    public function index()
    {
        $subjectGroups = SubjectGroup::where('status', 1)->get();
        return view('admin.courses.index', compact('subjectGroups'));
    }

    public function data(Request $request)
    {
        $courses = Course::with(['grade', 'semester', 'subjectGroup'])->select('courses.*');

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
            ->rawColumns(['subject_group_name', 'status', 'action'])
            ->make(true);
    }

    public function create()
    {
        $grades = Grade::all();
        $semesters = Semester::all();
        $subjectGroups = SubjectGroup::where('status', 1)->get();
        return view('admin.courses.save', compact('grades', 'semesters', 'subjectGroups'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name' => 'required|string|max:255|unique:courses,name',
            'grade_id' => 'required|exists:grades,id',
            'semester_id' => 'required|exists:semesters,id',
            'subject_group_id' => 'required|exists:subject_groups,id',
            'periods_per_week' => 'required|integer|min:1|max:20',
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
        return view('admin.courses.save', compact('course', 'grades', 'semesters', 'subjectGroups'));
    }

    public function update(Request $request, $id)
    {
        $course = Course::findOrFail($id);
        $data = $request->validate([
            'name' => 'required|string|max:255|unique:courses,name,' . $id,
            'grade_id' => 'required|exists:grades,id',
            'semester_id' => 'required|exists:semesters,id',
            'subject_group_id' => 'required|exists:subject_groups,id',
            'periods_per_week' => 'required|integer|min:1|max:20',
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
