<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;

class UserAssignmentController extends Controller
{
    public function index()
    {
        return view('admin.user-assignments');
    }
}
