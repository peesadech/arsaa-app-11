<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Grade;
use Illuminate\Http\Request;
use Yajra\DataTables\Facades\DataTables;

class GradeController extends Controller
{
    public function index()
    {
        return view('admin.grades.index');
    }

    public function data(Request $request)
    {
        $grades = Grade::select('grades.*');

        if ($request->filled('status')) {
            $grades->where('status', $request->status);
        }

        return DataTables::of($grades)
            ->addColumn('status', function ($grade) {
                $statusText = $grade->status == 1 ? 'Active' : 'Not Active';
                $colorClass = $grade->status == 1 ? 'bg-emerald-50 text-emerald-600' : 'bg-rose-50 text-rose-600';
                return '<span class="px-2 py-1 rounded-lg ' . $colorClass . ' text-[10px] font-bold uppercase tracking-wider">' . $statusText . '</span>';
            })
            ->addColumn('action', function ($grade) {
                $editUrl = route('admin.grades.edit', $grade->id);
                $btn = '<div class="flex space-x-2">';
                $btn .= '<a href="' . $editUrl . '" class="inline-flex items-center justify-center w-8 h-8 rounded-lg bg-white border border-gray-100 text-amber-500 hover:bg-amber-50 transition-all duration-200 shadow-sm" title="Edit Grade"><i class="fas fa-edit text-xs"></i></a>';
                $btn .= '<button type="button" onclick="confirmDelete(' . $grade->id . ', \'' . addslashes($grade->name_th) . ' / ' . addslashes($grade->name_en) . '\')" class="inline-flex items-center justify-center w-8 h-8 rounded-lg bg-white border border-gray-100 text-rose-500 hover:bg-rose-50 transition-all duration-200 shadow-sm" title="Delete Grade"><i class="fas fa-trash-alt text-xs"></i></button>';
                $btn .= '</div>';
                return $btn;
            })
            ->rawColumns(['status', 'action'])
            ->make(true);
    }

    public function create()
    {
        return view('admin.grades.save');
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name_th' => 'required|string|max:255',
            'name_en' => 'required|string|max:255',
            'description' => 'nullable|string',
            'status' => 'required|in:1,2',
        ]);

        Grade::create($data);

        return redirect()->route('admin.grades.index')->with('status', 'Grade created successfully!');
    }

    public function edit($id)
    {
        $grade = Grade::findOrFail($id);
        return view('admin.grades.save', compact('grade'));
    }

    public function update(Request $request, $id)
    {
        $grade = Grade::findOrFail($id);
        $data = $request->validate([
            'name_th' => 'required|string|max:255',
            'name_en' => 'required|string|max:255',
            'description' => 'nullable|string',
            'status' => 'required|in:1,2',
        ]);

        $grade->update($data);

        return redirect()->route('admin.grades.index')->with('status', 'Grade updated successfully!');
    }

    public function destroy($id)
    {
        $grade = Grade::findOrFail($id);
        $grade->delete();
        return redirect()->route('admin.grades.index')->with('status', 'Grade deleted successfully!');
    }
}
