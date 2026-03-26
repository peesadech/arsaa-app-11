<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;

class RoleManagementController extends Controller
{
    public function index()
    {
        return view('admin.roles-permissions');
    }
}
