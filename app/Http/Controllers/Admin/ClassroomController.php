<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Classroom;
use Illuminate\Http\Request;
use Yajra\DataTables\Facades\DataTables;

class ClassroomController extends Controller
{
    public function index()
    {
        return view('admin.classrooms.index');
    }

    public function data(Request $request)
    {
        $classrooms = Classroom::select('classrooms.*');

        if ($request->filled('status')) {
            $classrooms->where('status', $request->status);
        }

        return DataTables::of($classrooms)
            ->addColumn('status', function ($classroom) {
                $statusText = $classroom->status == 1 ? 'Active' : 'Not Active';
                $colorClass = $classroom->status == 1 ? 'bg-emerald-50 text-emerald-600' : 'bg-rose-50 text-rose-600';
                return '<span class="px-2 py-1 rounded-lg ' . $colorClass . ' text-[10px] font-bold uppercase tracking-wider">' . $statusText . '</span>';
            })
            ->addColumn('action', function ($classroom) {
                $editUrl = route('admin.classrooms.edit', $classroom->id);
                $btn = '<div class="flex space-x-2">';
                $btn .= '<a href="' . $editUrl . '" class="inline-flex items-center justify-center w-8 h-8 rounded-lg bg-white border border-gray-100 text-amber-500 hover:bg-amber-50 transition-all duration-200 shadow-sm" title="Edit Classroom"><i class="fas fa-edit text-xs"></i></a>';
                $btn .= '<button type="button" onclick="confirmDelete(' . $classroom->id . ', \'' . addslashes($classroom->name) . '\')" class="inline-flex items-center justify-center w-8 h-8 rounded-lg bg-white border border-gray-100 text-rose-500 hover:bg-rose-50 transition-all duration-200 shadow-sm" title="Delete Classroom"><i class="fas fa-trash-alt text-xs"></i></button>';
                $btn .= '</div>';
                return $btn;
            })
            ->rawColumns(['status', 'action'])
            ->make(true);
    }

    public function create()
    {
        return view('admin.classrooms.save');
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name' => 'required|string|max:255|unique:classrooms,name',
            'status' => 'required|in:1,2',
        ]);

        Classroom::create($data);

        return redirect()->route('admin.classrooms.index')->with('status', 'Classroom created successfully!');
    }

    public function edit($id)
    {
        $classroom = Classroom::findOrFail($id);
        return view('admin.classrooms.save', compact('classroom'));
    }

    public function update(Request $request, $id)
    {
        $classroom = Classroom::findOrFail($id);
        $data = $request->validate([
            'name' => 'required|string|max:255|unique:classrooms,name,' . $id,
            'status' => 'required|in:1,2',
        ]);

        $classroom->update($data);

        return redirect()->route('admin.classrooms.index')->with('status', 'Classroom updated successfully!');
    }

    public function destroy($id)
    {
        $classroom = Classroom::findOrFail($id);
        $classroom->delete();
        return redirect()->route('admin.classrooms.index')->with('status', 'Classroom deleted successfully!');
    }
}
