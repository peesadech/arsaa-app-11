@php
    $setting = \App\Models\Setting::first() ?? new \App\Models\Setting(['theme' => 'light']);
    $theme = $setting->theme ?? 'light';
    $isDark = $theme === 'dark';

    $navItems = [
        ['label' => 'Dashboard',        'route' => 'admin.dashboard',       'icon' => 'fa-tachometer-alt'],
        ['label' => 'Users',            'route' => 'admin.users.index',      'icon' => 'fa-users'],
        ['label' => 'Roles',            'route' => 'admin.roles-permissions','icon' => 'fa-user-shield'],
        ['label' => 'User Assignments', 'route' => 'admin.user-assignments', 'icon' => 'fa-user-tag'],
        ['label' => 'Permissions',      'route' => 'admin.permissions',      'icon' => 'fa-key'],
        ['label' => 'Grades',           'route' => 'admin.grades.index',     'icon' => 'fa-layer-group'],
        ['label' => 'Classrooms',       'route' => 'admin.classrooms.index', 'icon' => 'fa-chalkboard'],
        ['label' => 'Courses',          'route' => 'admin.courses.index',    'icon' => 'fa-book'],
        ['label' => 'Academic Years',   'route' => 'admin.academic-years.index','icon' => 'fa-calendar-alt'],
        ['label' => 'Semesters',        'route' => 'admin.semesters.index',  'icon' => 'fa-list-ol'],
        ['label' => 'Settings',         'route' => 'admin.settings.index',   'icon' => 'fa-cogs', 'superAdminOnly' => true],
    ];
@endphp

<div class="w-full {{ $isDark ? 'bg-[#1c1d1e] border-[#3a3b3c]' : 'bg-white border-gray-200' }} border-b shadow-sm overflow-x-auto">
    <div class="max-w-7xl mx-auto px-4">
        <div class="flex items-center space-x-1 py-1.5 min-w-max">
            @foreach($navItems as $item)
                @if(!empty($item['superAdminOnly']))
                    @role('SuperAdmin')
                    <a href="{{ route($item['route']) }}"
                       class="flex items-center gap-1.5 px-3 py-1.5 rounded-lg text-xs font-bold transition-all duration-150 whitespace-nowrap
                              {{ request()->routeIs($item['route']) || request()->routeIs($item['route'].'.*')
                                 ? ($isDark ? 'bg-indigo-600 text-white' : 'bg-indigo-600 text-white')
                                 : ($isDark ? 'text-gray-300 hover:bg-[#3a3b3c] hover:text-white' : 'text-gray-600 hover:bg-gray-100 hover:text-indigo-600') }}">
                        <i class="fas {{ $item['icon'] }} text-[10px]"></i>
                        {{ $item['label'] }}
                    </a>
                    @endrole
                @else
                    <a href="{{ route($item['route']) }}"
                       class="flex items-center gap-1.5 px-3 py-1.5 rounded-lg text-xs font-bold transition-all duration-150 whitespace-nowrap
                              {{ request()->routeIs($item['route']) || request()->routeIs($item['route'].'.*')
                                 ? ($isDark ? 'bg-indigo-600 text-white' : 'bg-indigo-600 text-white')
                                 : ($isDark ? 'text-gray-300 hover:bg-[#3a3b3c] hover:text-white' : 'text-gray-600 hover:bg-gray-100 hover:text-indigo-600') }}">
                        <i class="fas {{ $item['icon'] }} text-[10px]"></i>
                        {{ $item['label'] }}
                    </a>
                @endif
            @endforeach
        </div>
    </div>
</div>
