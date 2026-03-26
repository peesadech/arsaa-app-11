@php
    $setting = \App\Models\Setting::first() ?? new \App\Models\Setting(['theme' => 'light']);
    $theme = $setting->theme ?? 'light';
    $isDark = $theme === 'dark';

    $navItems = [
      
    ];
@endphp


