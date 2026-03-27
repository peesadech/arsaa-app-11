<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\EducationLevel;
use Illuminate\Http\Request;
use Yajra\DataTables\Facades\DataTables;

class EducationLevelController extends Controller
{
    public function index()
    {
        return view('admin.education-levels.index');
    }

    public function data(Request $request)
    {
        $levels = EducationLevel::select('education_levels.*');

        if ($request->filled('status')) {
            $levels->where('status', $request->status);
        }

        return DataTables::of($levels)
            ->addColumn('status', function ($level) {
                $statusText = $level->status == 1 ? 'Active' : 'Not Active';
                $colorClass = $level->status == 1 ? 'bg-emerald-50 text-emerald-600' : 'bg-rose-50 text-rose-600';
                return '<span class="px-2 py-1 rounded-lg ' . $colorClass . ' text-[10px] font-bold uppercase tracking-wider">' . $statusText . '</span>';
            })
            ->addColumn('action', function ($level) {
                $editUrl = route('admin.education-levels.edit', $level->id);
                $btn = '<div class="flex justify-end space-x-2">';
                $btn .= '<a href="' . $editUrl . '" class="inline-flex items-center justify-center w-8 h-8 rounded-lg bg-white border border-gray-100 text-amber-500 hover:bg-amber-50 transition-all duration-200 shadow-sm" title="Edit"><i class="fas fa-edit text-xs"></i></a>';
                $btn .= '<button type="button" onclick="confirmDelete(' . $level->id . ', \'' . addslashes($level->name_th) . ' / ' . addslashes($level->name_en) . '\')" class="inline-flex items-center justify-center w-8 h-8 rounded-lg bg-white border border-gray-100 text-rose-500 hover:bg-rose-50 transition-all duration-200 shadow-sm" title="Delete"><i class="fas fa-trash-alt text-xs"></i></button>';
                $btn .= '</div>';
                return $btn;
            })
            ->rawColumns(['status', 'action'])
            ->make(true);
    }

    public function create()
    {
        return view('admin.education-levels.save');
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name_th' => 'required|string|max:255',
            'name_en' => 'required|string|max:255',
            'description' => 'nullable|string',
            'status' => 'required|in:1,2',
        ]);

        EducationLevel::create($data);

        return redirect()->route('admin.education-levels.index')->with('status', 'Education Level created successfully!');
    }

    public function edit($id)
    {
        $educationLevel = EducationLevel::findOrFail($id);
        return view('admin.education-levels.save', compact('educationLevel'));
    }

    public function update(Request $request, $id)
    {
        $educationLevel = EducationLevel::findOrFail($id);
        $data = $request->validate([
            'name_th' => 'required|string|max:255',
            'name_en' => 'required|string|max:255',
            'description' => 'nullable|string',
            'status' => 'required|in:1,2',
        ]);

        $educationLevel->update($data);

        return redirect()->route('admin.education-levels.index')->with('status', 'Education Level updated successfully!');
    }

    public function destroy($id)
    {
        $educationLevel = EducationLevel::findOrFail($id);
        $educationLevel->delete();
        return redirect()->route('admin.education-levels.index')->with('status', 'Education Level deleted successfully!');
    }
}
