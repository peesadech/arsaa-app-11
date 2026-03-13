<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // Share settings with all views
        try {
            $setting = \App\Models\Setting::first() ?? new \App\Models\Setting(['app_name' => 'Laravel']);
            \Illuminate\Support\Facades\View::share('setting', $setting);
        } catch (\Exception $e) {
            // Handle cases where table might not exist yet during migration
        }
    }
}
