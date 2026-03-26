<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Spatie\Permission\Models\Permission;

class PermissionController extends Controller
{
    public function index()
    {
        return view('admin.permissions.index');
    }

    public function data()
    {
        try {
            $query = Permission::query();
            return datatables()->of($query)
                ->addColumn('action', function ($row) {
                    $editUrl = route('admin.permissions.edit', $row->id);
                    $btn = '<div class="space-x-2">';
                    $btn .= '<a href="' . $editUrl . '" class="inline-flex items-center justify-center w-10 h-10 rounded-xl bg-white border border-gray-200 text-amber-500 hover:border-amber-400 hover:bg-amber-50 transition-all duration-200 shadow-sm hover:shadow-md" title="Edit Permission"><i class="fas fa-pen-nib text-sm"></i></a>';
                    $btn .= '<button type="button" onclick="confirmDelete(' . $row->id . ', \'' . addslashes($row->name) . '\')" class="inline-flex items-center justify-center w-10 h-10 rounded-xl bg-white border border-gray-200 text-rose-500 hover:border-rose-400 hover:bg-rose-50 transition-all duration-200 shadow-sm hover:shadow-md" title="Delete Permission"><i class="fas fa-trash-alt text-sm"></i></button>';
                    $btn .= '</div>';
                    return $btn;
                })
                ->rawColumns(['action'])
                ->make(true);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    public function create()
    {
        return view('admin.permissions.save');
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name'       => 'required|string|unique:permissions,name',
            'guard_name' => 'required|string',
        ]);

        Permission::create($data);

        return redirect()->route('admin.permissions')->with('status', 'Permission created successfully!');
    }

    public function edit($id)
    {
        $permission = Permission::findOrFail($id);
        return view('admin.permissions.save', compact('permission'));
    }

    public function update(Request $request, $id)
    {
        $permission = Permission::findOrFail($id);
        $data = $request->validate([
            'name'       => 'required|string|unique:permissions,name,' . $id,
            'guard_name' => 'required|string',
        ]);

        $permission->update($data);

        return redirect()->route('admin.permissions')->with('status', 'Permission updated successfully!');
    }

    public function destroy($id)
    {
        $permission = Permission::findOrFail($id);
        $permission->delete();

        return redirect()->route('admin.permissions')->with('status', 'Permission removed successfully!');
    }
}
