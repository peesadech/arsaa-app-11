<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Foundation\Auth\AuthenticatesUsers;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;

class LoginController extends Controller implements HasMiddleware
{
    use AuthenticatesUsers;

    protected function redirectTo(): string
    {
        $user = auth()->user();
        if ($user) {
            $roles = $user->getRoleNames()->map(fn($r) => strtoupper($r));
            if ($roles->intersect(['SUPERADMIN', 'ADMIN'])->isNotEmpty()) {
                return route('admin.dashboard');
            }
        }

        return route('profile.index');
    }

    public static function middleware(): array
    {
        return [
            new Middleware('guest', except: ['logout']),
        ];
    }

    protected function credentials(\Illuminate\Http\Request $request)
    {
        $credentials = $request->only($this->username(), 'password');
        $credentials['status'] = 1;

        return $credentials;
    }

    protected function authenticated(\Illuminate\Http\Request $request, $user)
    {
        $setting = \App\Models\CurrentAcademicSetting::first();
        if ($setting) {
            $request->session()->put('current_academic_year_id', $setting->academic_year_id);
            $request->session()->put('current_semester_id', $setting->semester_id);
        }

        $userRoles = $user->getRoleNames()->map(fn($r) => strtoupper($r));
        if ($userRoles->intersect(['SUPERADMIN', 'ADMIN'])->isNotEmpty()) {
            return redirect()->route('admin.dashboard');
        }

        return redirect()->route('profile.index');
    }
}
