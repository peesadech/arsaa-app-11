<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use Illuminate\Http\Request;
use Yajra\DataTables\Facades\DataTables;

class RoleManagementController extends Controller
{
    public function index()
    {
        return view('admin.roles-permissions.index');
    }

    public function data(Request $request)
    {
        $roles = Role::with('permissions')->select('roles.*');

        return DataTables::of($roles)
            ->addColumn('permissions_list', function ($role) {
                $badges = '';
                foreach ($role->permissions as $permission) {
                    $badges .= '<span class="inline-flex items-center px-2 py-0.5 rounded text-[10px] font-bold bg-indigo-50 text-indigo-600 mr-1 mb-1 border border-indigo-100">' . $permission->name . '</span>';
                }
                return $badges ?: '<span class="text-gray-400 italic text-[10px]">No permissions assigned</span>';
            })
            ->addColumn('action', function ($role) {
                $editUrl = route('admin.roles.edit', $role->id);
                $btn = '<div class="flex justify-end space-x-2">';
                $btn .= '<a href="' . $editUrl . '" class="inline-flex items-center justify-center w-8 h-8 rounded-lg bg-white border border-gray-100 text-amber-500 hover:bg-amber-50 transition-all duration-200 shadow-sm" title="Edit"><i class="fas fa-edit text-xs"></i></a>';
                $btn .= '<button type="button" onclick="confirmDelete(' . $role->id . ', \'' . addslashes($role->name) . '\')" class="inline-flex items-center justify-center w-8 h-8 rounded-lg bg-white border border-gray-100 text-rose-500 hover:bg-rose-50 transition-all duration-200 shadow-sm" title="Delete"><i class="fas fa-trash-alt text-xs"></i></button>';
                $btn .= '</div>';
                return $btn;
            })
            ->rawColumns(['permissions_list', 'action'])
            ->make(true);
    }

    public function create()
    {
        $permissions = Permission::all();
        return view('admin.roles-permissions.save', compact('permissions'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name' => 'required|string|max:255|unique:roles,name',
            'permissions' => 'nullable|array',
            'permissions.*' => 'exists:permissions,id',
        ]);

        $role = Role::create(['name' => $data['name']]);
        
        if (isset($data['permissions'])) {
            $permissions = Permission::whereIn('id', $data['permissions'])->get();
            $role->syncPermissions($permissions);
        }

        return redirect()->route('admin.roles-permissions')->with('status', 'Role created successfully!');
    }

    public function edit($id)
    {
        $role = Role::with('permissions')->findOrFail($id);
        $permissions = Permission::all();
        return view('admin.roles-permissions.save', compact('role', 'permissions'));
    }

    public function update(Request $request, $id)
    {
        $role = Role::findOrFail($id);
        $data = $request->validate([
            'name' => 'required|string|max:255|unique:roles,name,' . $id,
            'permissions' => 'nullable|array',
            'permissions.*' => 'exists:permissions,id',
        ]);

        $role->update(['name' => $data['name']]);
        
        if (isset($data['permissions'])) {
            $permissions = Permission::whereIn('id', $data['permissions'])->get();
            $role->syncPermissions($permissions);
        } else {
            $role->syncPermissions([]);
        }

        return redirect()->route('admin.roles-permissions')->with('status', 'Role updated successfully!');
    }

    public function destroy($id)
    {
        $role = Role::findOrFail($id);
        if ($role->name === 'SuperAdmin' || $role->name === 'admin') {
            return redirect()->route('admin.roles-permissions')->with('error', 'Cannot delete system roles!');
        }
        $role->delete();
        return redirect()->route('admin.roles-permissions')->with('status', 'Role deleted successfully!');
    }
}
