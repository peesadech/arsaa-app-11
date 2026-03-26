<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Auth;

class WelcomeController extends Controller
{
    public function index()
    {
        if (Auth::check()) {
            return redirect()->route('home');
        }
        $setting = \App\Models\Setting::first() ?? new \App\Models\Setting(['app_name' => 'Laravel']);
        return view('welcome', compact('setting'));
    }
}
