<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Floor;
use App\Models\Building;
use Illuminate\Http\Request;
use Yajra\DataTables\Facades\DataTables;

class FloorController extends Controller
{
    public function index()
    {
        return view('admin.floors.index');
    }

    public function data(Request $request)
    {
        $floors = Floor::with('building')->select('floors.*');

        if ($request->filled('status')) {
            $floors->where('status', $request->status);
        }

        if ($request->filled('building_id')) {
            $floors->where('building_id', $request->building_id);
        }

        return DataTables::of($floors)
            ->addColumn('building_name', function ($floor) {
                return $floor->building?->name_th ?? '-';
            })
            ->addColumn('status', function ($floor) {
                $statusText = $floor->status == 1 ? 'Active' : 'Not Active';
                $colorClass = $floor->status == 1 ? 'bg-emerald-50 text-emerald-600' : 'bg-rose-50 text-rose-600';
                return '<span class="px-2 py-1 rounded-lg ' . $colorClass . ' text-[10px] font-bold uppercase tracking-wider">' . $statusText . '</span>';
            })
            ->addColumn('action', function ($floor) {
                $editUrl = route('admin.floors.edit', $floor->id);
                $btn = '<div class="flex justify-end space-x-2">';
                $btn .= '<a href="' . $editUrl . '" class="inline-flex items-center justify-center w-8 h-8 rounded-lg bg-white border border-gray-100 text-amber-500 hover:bg-amber-50 transition-all duration-200 shadow-sm" title="Edit"><i class="fas fa-edit text-xs"></i></a>';
                $btn .= '<button type="button" onclick="confirmDelete(' . $floor->id . ', \'' . addslashes($floor->name_th) . ' / ' . addslashes($floor->name_en) . '\')" class="inline-flex items-center justify-center w-8 h-8 rounded-lg bg-white border border-gray-100 text-rose-500 hover:bg-rose-50 transition-all duration-200 shadow-sm" title="Delete"><i class="fas fa-trash-alt text-xs"></i></button>';
                $btn .= '</div>';
                return $btn;
            })
            ->rawColumns(['status', 'action'])
            ->make(true);
    }

    public function create()
    {
        $buildings = Building::where('status', 1)->orderBy('name_th')->get();
        return view('admin.floors.save', compact('buildings'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'building_id' => 'required|exists:buildings,id',
            'name_th' => 'required|string|max:255',
            'name_en' => 'required|string|max:255',
            'description' => 'nullable|string',
            'status' => 'required|in:1,2',
        ]);

        Floor::create($data);

        return redirect()->route('admin.floors.index')->with('status', 'Floor created successfully!');
    }

    public function edit($id)
    {
        $floor = Floor::findOrFail($id);
        $buildings = Building::where('status', 1)->orderBy('name_th')->get();
        return view('admin.floors.save', compact('floor', 'buildings'));
    }

    public function update(Request $request, $id)
    {
        $floor = Floor::findOrFail($id);
        $data = $request->validate([
            'building_id' => 'required|exists:buildings,id',
            'name_th' => 'required|string|max:255',
            'name_en' => 'required|string|max:255',
            'description' => 'nullable|string',
            'status' => 'required|in:1,2',
        ]);

        $floor->update($data);

        return redirect()->route('admin.floors.index')->with('status', 'Floor updated successfully!');
    }

    public function destroy($id)
    {
        $floor = Floor::findOrFail($id);
        $floor->delete();
        return redirect()->route('admin.floors.index')->with('status', 'Floor deleted successfully!');
    }
}
