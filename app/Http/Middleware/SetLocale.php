<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use App\Models\Language;

class SetLocale
{
    public function handle(Request $request, Closure $next)
    {
        $locale = session('locale');

        if (!$locale) {
            $default = Language::getDefault();
            $locale = $default ? $default->code : config('app.locale', 'en');
        }

        app()->setLocale($locale);

        return $next($request);
    }
}
