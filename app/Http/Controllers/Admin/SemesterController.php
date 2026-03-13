<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Semester;
use Illuminate\Http\Request;
use Yajra\DataTables\Facades\DataTables;

class SemesterController extends Controller
{
    public function index()
    {
        return view('admin.semesters.index');
    }

    public function data(Request $request)
    {
        $semesters = Semester::select('semesters.*');

        if ($request->filled('status')) {
            $semesters->where('status', $request->status);
        }

        return DataTables::of($semesters)
            ->addColumn('status', function ($semester) {
                $statusText = $semester->status == 1 ? 'Active' : 'Not Active';
                $colorClass = $semester->status == 1 ? 'bg-emerald-50 text-emerald-600' : 'bg-rose-50 text-rose-600';
                return '<span class="px-2 py-1 rounded-lg ' . $colorClass . ' text-[10px] font-bold uppercase tracking-wider">' . $statusText . '</span>';
            })
            ->addColumn('action', function ($semester) {
                $editUrl = route('admin.semesters.edit', $semester->id);
                $btn = '<div class="flex space-x-2">';
                $btn .= '<a href="' . $editUrl . '" class="inline-flex items-center justify-center w-8 h-8 rounded-lg bg-white border border-gray-100 text-amber-500 hover:bg-amber-50 transition-all duration-200 shadow-sm" title="Edit"><i class="fas fa-edit text-xs"></i></a>';
                $btn .= '<button type="button" onclick="confirmDelete(' . $semester->id . ', \'' . $semester->semester_number . '\')" class="inline-flex items-center justify-center w-8 h-8 rounded-lg bg-white border border-gray-100 text-rose-500 hover:bg-rose-50 transition-all duration-200 shadow-sm" title="Delete"><i class="fas fa-trash-alt text-xs"></i></button>';
                $btn .= '</div>';
                return $btn;
            })
            ->rawColumns(['status', 'action'])
            ->make(true);
    }

    public function create()
    {
        return view('admin.semesters.save');
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'semester_number' => 'required|integer|unique:semesters,semester_number',
            'status' => 'required|in:1,2',
        ]);

        Semester::create($data);

        return redirect()->route('admin.semesters.index')->with('status', 'Semester created successfully!');
    }

    public function edit($id)
    {
        $semester = Semester::findOrFail($id);
        return view('admin.semesters.save', compact('semester'));
    }

    public function update(Request $request, $id)
    {
        $semester = Semester::findOrFail($id);
        $data = $request->validate([
            'semester_number' => 'required|integer|unique:semesters,semester_number,' . $id,
            'status' => 'required|in:1,2',
        ]);

        $semester->update($data);

        return redirect()->route('admin.semesters.index')->with('status', 'Semester updated successfully!');
    }

    public function destroy($id)
    {
        $semester = Semester::findOrFail($id);
        $semester->delete();
        return redirect()->route('admin.semesters.index')->with('status', 'Semester deleted successfully!');
    }
}
