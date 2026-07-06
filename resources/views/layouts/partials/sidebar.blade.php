@php
    $appName    = $setting->app_name ?? config('app.name');
    $appLogoUrl = !empty($setting?->app_logo) ? asset('storage/'.$setting->app_logo) : null;
    $appInitial = mb_strtoupper(mb_substr($appName, 0, 1));

    $isAdmin = auth()->check() && collect(auth()->user()?->getRoleNames() ?? [])
        ->map(fn($r) => strtoupper($r))->intersect(['ADMIN', 'SUPERADMIN'])->isNotEmpty();

    // เมนูสำหรับครู (ไม่ใช่ admin) — เห็นเฉพาะของตัวเอง
    $teacherNav = [
        ['label' => __('Dashboard'), 'route' => 'teacher.dashboard', 'icon' => 'home'],
        ['label' => __('Attendance'), 'route' => 'class-sessions.today', 'icon' => 'clipboard'],
        ['label' => __('My Scores'), 'route' => 'teacher.scores.index', 'icon' => 'award'],
        ['label' => __('Behavior Scores'), 'route' => 'behavior-records.index', 'icon' => 'shield'],
    ];

    // เมนูระบบโรงเรียน (admin) — จัดกลุ่มตามโครงสร้าง myTripsBackend
    // item ที่ยังไม่มี route ให้ใส่ 'disabled' => true (แสดงแบบจาง กันลิงก์เสีย)
    $adminNav = [
        ['label' => __('Dashboard'), 'route' => 'admin.dashboard', 'icon' => 'home'],

        ['label' => __('Teaching'), 'icon' => 'book', 'children' => [
            ['label' => __('Attendance'),      'route' => 'class-sessions.today'],
            ['label' => __('Record Scores'),   'route' => 'admin.student-scores.index'],
            ['label' => __('Behavior Scores'), 'route' => 'behavior-records.index'],
            ['label' => __('Academic Results'),'route' => 'admin.student-reports.class-scores'],
        ]],

        ['label' => __('Student'), 'icon' => 'users', 'children' => [
            ['label' => __('Students'),           'route' => 'admin.students.index'],
            ['label' => __('Student Classrooms'), 'route' => 'admin.student-enrollments.index'],
        ]],

        ['label' => __('Reports'), 'icon' => 'chart', 'children' => [
            ['label' => __('Attendance Report'),    'route' => 'admin.attendance-reports.index'],
            ['label' => __('Student Reports'),      'route' => 'admin.student-reports.index'],
            ['label' => __('Academic Results'),     'route' => 'admin.student-reports.class-scores'],
            ['label' => __('Incomplete Documents'), 'route' => 'admin.student-reports.incomplete-documents'],
        ]],

        ['label' => __('Yearly/Semester'), 'icon' => 'cog', 'children' => [
            ['label' => __('Timetable'),                 'route' => 'admin.timetable.index'],
            ['label' => __('Yearly/Semester Schedule'),  'route' => 'admin.yearly-schedule.index'],
            ['label' => __('Semester Setup'),            'route' => 'admin.term-setup.index'],
            ['label' => __('Teacher Term Status'),       'route' => 'admin.teacher-term-status.index'],
            ['label' => __('Subject Weights'),           'route' => 'admin.course-weights.index'],
        ]],

        // System Config — รวม Academic + Facilities + Master Data เดิม (อยู่เหนือ Settings)
        ['label' => __('System Config'), 'icon' => 'layers', 'children' => [
            // Academic
            ['label' => __('Global Schedule'), 'route' => 'admin.global-schedule.index'],
            ['label' => __('Academic Years'),  'route' => 'admin.academic-years.index'],
            ['label' => __('Semesters'),       'route' => 'admin.semesters.index'],
            ['label' => __('Education Levels'),'route' => 'admin.education-levels.index'],
            ['label' => __('Subject Groups'),  'route' => 'admin.subject-groups.index'],
            ['label' => __('Course Types'),    'route' => 'admin.course-types.index'],
            ['label' => __('Courses'),         'route' => 'admin.courses.index'],
            ['label' => __('Opened Courses'),  'route' => 'admin.opened-courses.index'],
            ['label' => __('Teachers'),        'route' => 'admin.teachers.index'],
            // Facilities
            ['label' => __('Buildings'),  'route' => 'admin.buildings.index'],
            ['label' => __('Floors'),     'route' => 'admin.floors.index'],
            ['label' => __('Rooms'),      'route' => 'admin.rooms.index'],
            ['label' => __('Classrooms'), 'route' => 'admin.classrooms.index'],
            // Master Data
            ['label' => __('Grade Levels'),        'route' => 'admin.grades.index'],
            ['label' => __('Grading Schemes'),     'route' => 'admin.grading-schemes.index'],
            ['label' => __('Student Master Data'), 'route' => 'admin.student-master.index'],
            ['label' => __('Attendance Status'),   'route' => 'admin.attendance-statuses.index'],
            ['label' => __('Merit Scores'),        'route' => 'admin.behavior-scores.index', 'params' => ['type' => 'merit']],
            ['label' => __('Demerit Scores'),      'route' => 'admin.behavior-scores.index', 'params' => ['type' => 'demerit']],
            ['label' => __('Student Status'),      'disabled' => true],
            ['label' => __('Score Type'),          'disabled' => true],
        ]],

        ['label' => __('Settings'), 'icon' => 'cog', 'children' => [
            ['label' => __('Users'),       'route' => 'admin.users.index'],
            ['label' => __('Permissions'), 'route' => 'admin.roles-permissions'],
            ['label' => __('Languages'),   'route' => 'admin.languages.index'],
            ['label' => __('System Settings'), 'route' => 'admin.settings.index'],
        ]],
    ];

    $nav = $isAdmin ? $adminNav : $teacherNav;
@endphp
<aside :class="mobileMenu ? 'translate-x-0' : '-translate-x-full lg:translate-x-0'"
       class="fixed inset-y-0 left-0 z-40 w-64 flex flex-col bg-white border-r border-slate-200 transform transition-transform duration-200 lg:static lg:translate-x-0 lg:transform-none">
    <div class="h-16 flex items-center gap-2 px-5 border-b border-slate-200 shrink-0">
        @if ($appLogoUrl)
            <img src="{{ $appLogoUrl }}" alt="{{ $appName }}"
                 class="h-9 w-9 rounded-xl object-contain bg-white border border-slate-200">
        @else
            <div class="h-9 w-9 rounded-xl bg-brand-600 text-white flex items-center justify-center font-bold">{{ $appInitial }}</div>
        @endif
        <div class="leading-tight min-w-0">
            <div class="font-semibold text-slate-900 truncate">{{ $appName }}</div>
            <div class="text-xs text-slate-500">{{ __('Backoffice') }}</div>
        </div>
        <button type="button" @click="mobileMenu = false"
                class="ml-auto lg:hidden btn-ghost p-2 rounded-lg" aria-label="Close menu">
            <x-icon name="x" class="h-5 w-5" />
        </button>
    </div>
    <nav class="flex-1 overflow-y-auto p-3 space-y-1">
        @foreach ($nav as $item)
            @if (!empty($item['children']))
                @php
                    $anyActive = collect($item['children'])->contains(function ($c) {
                        return !empty($c['route']) && Route::has($c['route']) && request()->routeIs($c['route'].'*');
                    });
                @endphp
                <div x-data="{ open: {{ $anyActive ? 'true' : 'false' }} }">
                    <button type="button" @click="open = !open"
                            class="sidebar-link w-full justify-between {{ $anyActive ? 'active' : '' }}">
                        <span class="flex items-center gap-3">
                            <x-icon :name="$item['icon']" class="h-5 w-5" />
                            <span>{{ $item['label'] }}</span>
                        </span>
                        <x-icon name="chevron-down" class="h-4 w-4 transition-transform" x-bind:class="open ? 'rotate-180' : ''" />
                    </button>
                    <div x-show="open" x-collapse x-cloak class="mt-1 ml-4 pl-3 border-l border-slate-200 space-y-0.5">
                        @foreach ($item['children'] as $child)
                            @php
                                $disabled   = !empty($child['disabled']);
                                $params     = $child['params'] ?? [];
                                $hasRoute   = !$disabled && !empty($child['route']) && Route::has($child['route']);
                                $href       = $hasRoute ? route($child['route'], $params) : '#';
                                $childActive= $hasRoute && request()->routeIs($child['route'].'*');
                                // ถ้าเมนูใช้ route param เดียวกัน (เช่น type) ให้ active เฉพาะตัวที่ param ตรง
                                foreach ($params as $pKey => $pVal) {
                                    if ((string) request()->route($pKey) !== (string) $pVal) {
                                        $childActive = false;
                                    }
                                }
                            @endphp
                            @if ($disabled)
                                <span class="sidebar-link text-sm py-2 opacity-40 cursor-not-allowed"
                                      title="{{ __('Coming soon') }}">{{ $child['label'] }}</span>
                            @else
                                <a href="{{ $href }}" class="sidebar-link text-sm py-2 {{ $childActive ? 'active' : '' }}">
                                    <span>{{ $child['label'] }}</span>
                                </a>
                            @endif
                        @endforeach
                    </div>
                </div>
            @else
                @php
                    $hasRoute = !empty($item['route']) && Route::has($item['route']);
                    $href = $hasRoute ? route($item['route']) : '#';
                    $active = $hasRoute && request()->routeIs($item['route'].'*');
                @endphp
                <a href="{{ $href }}" class="sidebar-link {{ $active ? 'active' : '' }}">
                    <x-icon :name="$item['icon']" class="h-5 w-5" />
                    <span>{{ $item['label'] }}</span>
                </a>
            @endif
        @endforeach
    </nav>
</aside>
