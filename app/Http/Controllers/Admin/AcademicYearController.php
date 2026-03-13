<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AcademicYear;
use Illuminate\Http\Request;
use Yajra\DataTables\Facades\DataTables;

class AcademicYearController extends Controller
{
    public function index()
    {
        return view('admin.academic-years.index');
    }

    public function data(Request $request)
    {
        $years = AcademicYear::select('academic_years.*');

        if ($request->filled('status')) {
            $years->where('status', $request->status);
        }

        return DataTables::of($years)
            ->addColumn('status', function ($year) {
                $statusText = $year->status == 1 ? 'Active' : 'Not Active';
                $colorClass = $year->status == 1 ? 'bg-emerald-50 text-emerald-600' : 'bg-rose-50 text-rose-600';
                return '<span class="px-2 py-1 rounded-lg ' . $colorClass . ' text-[10px] font-bold uppercase tracking-wider">' . $statusText . '</span>';
            })
            ->addColumn('action', function ($year) {
                $editUrl = route('admin.academic-years.edit', $year->id);
                $btn = '<div class="flex space-x-2">';
                $btn .= '<a href="' . $editUrl . '" class="inline-flex items-center justify-center w-8 h-8 rounded-lg bg-white border border-gray-100 text-amber-500 hover:bg-amber-50 transition-all duration-200 shadow-sm" title="Edit Year"><i class="fas fa-edit text-xs"></i></a>';
                $btn .= '<button type="button" onclick="confirmDelete(' . $year->id . ', \'' . $year->year . '\')" class="inline-flex items-center justify-center w-8 h-8 rounded-lg bg-white border border-gray-100 text-rose-500 hover:bg-rose-50 transition-all duration-200 shadow-sm" title="Delete Year"><i class="fas fa-trash-alt text-xs"></i></button>';
                $btn .= '</div>';
                return $btn;
            })
            ->rawColumns(['status', 'action'])
            ->make(true);
    }

    public function create()
    {
        return view('admin.academic-years.save');
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'year' => 'required|integer|unique:academic_years,year',
            'status' => 'required|in:1,2',
        ]);

        AcademicYear::create($data);

        return redirect()->route('admin.academic-years.index')->with('status', 'Academic Year created successfully!');
    }

    public function edit($id)
    {
        $academicYear = AcademicYear::findOrFail($id);
        return view('admin.academic-years.save', compact('academicYear'));
    }

    public function update(Request $request, $id)
    {
        $academicYear = AcademicYear::findOrFail($id);
        $data = $request->validate([
            'year' => 'required|integer|unique:academic_years,year,' . $id,
            'status' => 'required|in:1,2',
        ]);

        $academicYear->update($data);

        return redirect()->route('admin.academic-years.index')->with('status', 'Academic Year updated successfully!');
    }

    public function destroy($id)
    {
        $year = AcademicYear::findOrFail($id);
        $year->delete();
        return redirect()->route('admin.academic-years.index')->with('status', 'Academic Year deleted successfully!');
    }
}
