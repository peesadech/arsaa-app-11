<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Building;
use Illuminate\Http\Request;
use Yajra\DataTables\Facades\DataTables;

class BuildingController extends Controller
{
    public function index()
    {
        return view('admin.buildings.index');
    }

    public function data(Request $request)
    {
        $buildings = Building::select('buildings.*');

        if ($request->filled('status')) {
            $buildings->where('status', $request->status);
        }

        return DataTables::of($buildings)
            ->addColumn('status', function ($building) {
                $statusText = $building->status == 1 ? 'Active' : 'Not Active';
                $colorClass = $building->status == 1 ? 'bg-emerald-50 text-emerald-600' : 'bg-rose-50 text-rose-600';
                return '<span class="px-2 py-1 rounded-lg ' . $colorClass . ' text-[10px] font-bold uppercase tracking-wider">' . $statusText . '</span>';
            })
            ->addColumn('action', function ($building) {
                $editUrl = route('admin.buildings.edit', $building->id);
                $btn = '<div class="flex justify-end space-x-2">';
                $btn .= '<a href="' . $editUrl . '" class="inline-flex items-center justify-center w-8 h-8 rounded-lg bg-white border border-gray-100 text-amber-500 hover:bg-amber-50 transition-all duration-200 shadow-sm" title="Edit"><i class="fas fa-edit text-xs"></i></a>';
                $btn .= '<button type="button" onclick="confirmDelete(' . $building->id . ', \'' . addslashes($building->name_th) . ' / ' . addslashes($building->name_en) . '\')" class="inline-flex items-center justify-center w-8 h-8 rounded-lg bg-white border border-gray-100 text-rose-500 hover:bg-rose-50 transition-all duration-200 shadow-sm" title="Delete"><i class="fas fa-trash-alt text-xs"></i></button>';
                $btn .= '</div>';
                return $btn;
            })
            ->rawColumns(['status', 'action'])
            ->make(true);
    }

    public function create()
    {
        return view('admin.buildings.save');
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name_th' => 'required|string|max:255',
            'name_en' => 'required|string|max:255',
            'description' => 'nullable|string',
            'status' => 'required|in:1,2',
        ]);

        Building::create($data);

        return redirect()->route('admin.buildings.index')->with('status', 'Building created successfully!');
    }

    public function edit($id)
    {
        $building = Building::findOrFail($id);
        return view('admin.buildings.save', compact('building'));
    }

    public function update(Request $request, $id)
    {
        $building = Building::findOrFail($id);
        $data = $request->validate([
            'name_th' => 'required|string|max:255',
            'name_en' => 'required|string|max:255',
            'description' => 'nullable|string',
            'status' => 'required|in:1,2',
        ]);

        $building->update($data);

        return redirect()->route('admin.buildings.index')->with('status', 'Building updated successfully!');
    }

    public function destroy($id)
    {
        $building = Building::findOrFail($id);
        $building->delete();
        return redirect()->route('admin.buildings.index')->with('status', 'Building deleted successfully!');
    }
}
