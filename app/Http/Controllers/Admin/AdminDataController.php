<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Spatie\Permission\Models\Role;

class AdminDataController extends Controller
{
    public function roles()
    {
        $roles = Role::with('permissions')->get();
        return datatables()->of($roles)
            ->addColumn('permissions_list', function ($row) {
                $badges = '';
                foreach ($row->permissions as $p) {
                    $badges .= '<span class="px-2 py-0.5 mr-1 mb-1 rounded-full bg-indigo-50 text-indigo-600 text-[10px] font-bold">' . $p->name . '</span>';
                }
                return $badges ?: '<span class="text-gray-400 text-[10px] italic">None</span>';
            })
            ->addColumn('action', function ($row) {
                return '<button type="button" onclick="confirmDeleteRole(' . $row->id . ', \'' . addslashes($row->name) . '\')" class="inline-flex items-center justify-center w-8 h-8 rounded-lg bg-white border border-gray-100 text-rose-500 hover:bg-rose-50 transition-all duration-200 shadow-sm"><i class="fas fa-trash-alt text-xs"></i></button>';
            })
            ->rawColumns(['permissions_list', 'action'])
            ->make(true);
    }

    public function users()
    {
        $users = User::with('roles')->get();
        return datatables()->of($users)
            ->addColumn('roles_assignment', function ($row) {
                $roles  = Role::all();
                $select = '<select multiple class="user-role-select w-full px-3 py-2 rounded-xl bg-gray-50 border-0 text-xs focus:ring-2 focus:ring-indigo-500 transition-all font-medium" data-user-id="' . $row->id . '" style="height: 80px;">';
                foreach ($roles as $role) {
                    $selected = $row->hasRole($role->name) ? 'selected' : '';
                    $select  .= '<option value="' . $role->name . '" ' . $selected . '>' . $role->name . '</option>';
                }
                $select .= '</select>';
                return $select;
            })
            ->rawColumns(['roles_assignment'])
            ->make(true);
    }
}
