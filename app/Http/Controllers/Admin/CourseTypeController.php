<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\CourseType;
use Illuminate\Http\Request;
use Yajra\DataTables\Facades\DataTables;

class CourseTypeController extends Controller
{
    public function index()
    {
        return view('admin.course-types.index');
    }

    public function data(Request $request)
    {
        $courseTypes = CourseType::select('course_types.*');

        if ($request->filled('status')) {
            $courseTypes->where('status', $request->status);
        }

        return DataTables::of($courseTypes)
            ->addColumn('status', function ($courseType) {
                $statusText = $courseType->status == 1 ? 'Active' : 'Not Active';
                $colorClass = $courseType->status == 1 ? 'bg-emerald-50 text-emerald-600' : 'bg-rose-50 text-rose-600';
                return '<span class="px-2 py-1 rounded-lg ' . $colorClass . ' text-[10px] font-bold uppercase tracking-wider">' . $statusText . '</span>';
            })
            ->addColumn('action', function ($courseType) {
                $editUrl = route('admin.course-types.edit', $courseType->id);
                $btn = '<div class="flex justify-end space-x-2">';
                $btn .= '<a href="' . $editUrl . '" class="inline-flex items-center justify-center w-8 h-8 rounded-lg bg-white border border-gray-100 text-amber-500 hover:bg-amber-50 transition-all duration-200 shadow-sm" title="Edit"><i class="fas fa-edit text-xs"></i></a>';
                $btn .= '<button type="button" onclick="confirmDelete(' . $courseType->id . ', \'' . addslashes($courseType->name_th) . ' / ' . addslashes($courseType->name_en) . '\')" class="inline-flex items-center justify-center w-8 h-8 rounded-lg bg-white border border-gray-100 text-rose-500 hover:bg-rose-50 transition-all duration-200 shadow-sm" title="Delete"><i class="fas fa-trash-alt text-xs"></i></button>';
                $btn .= '</div>';
                return $btn;
            })
            ->rawColumns(['status', 'action'])
            ->make(true);
    }

    public function create()
    {
        return view('admin.course-types.save');
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name_th' => 'required|string|max:255',
            'name_en' => 'required|string|max:255',
            'description' => 'nullable|string',
            'status' => 'required|in:1,2',
        ]);

        CourseType::create($data);

        return redirect()->route('admin.course-types.index')->with('status', 'Course type created successfully!');
    }

    public function edit($id)
    {
        $courseType = CourseType::findOrFail($id);
        return view('admin.course-types.save', compact('courseType'));
    }

    public function update(Request $request, $id)
    {
        $courseType = CourseType::findOrFail($id);
        $data = $request->validate([
            'name_th' => 'required|string|max:255',
            'name_en' => 'required|string|max:255',
            'description' => 'nullable|string',
            'status' => 'required|in:1,2',
        ]);

        $courseType->update($data);

        return redirect()->route('admin.course-types.index')->with('status', 'Course type updated successfully!');
    }

    public function destroy($id)
    {
        $courseType = CourseType::findOrFail($id);
        $courseType->delete();
        return redirect()->route('admin.course-types.index')->with('status', 'Course type deleted successfully!');
    }
}
