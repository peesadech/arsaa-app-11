<!doctype html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <!-- CSRF Token -->
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ $setting->app_name }}</title>
    @if($setting->app_logo)
        <link rel="icon" type="image/png" href="{{ asset('storage/' . $setting->app_logo) }}">
    @endif

    <!-- Scripts -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="{{ asset('js/app.js') }}"></script>

    <!-- Fonts -->
    <link rel="dns-prefetch" href="//fonts.gstatic.com">
    <link href="https://fonts.googleapis.com/css?family=Nunito" rel="stylesheet">

    <!-- Styles -->
    <link href="{{ asset('css/app.css') }}" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    <script src="https://cdn.tailwindcss.com?plugins=forms,typography,aspect-ratio"></script>
    <script defer src="https://cdn.jsdelivr.net/npm/@alpinejs/collapse@3.x.x/dist/cdn.min.js"></script>
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
    <script>
        // Suppress Tailwind CDN warning in development
        tailwind.config = {
            darkMode: 'class',
            corePlugins: {
                preflight: false, // Prevent conflicts with Bootstrap
            }
        }
    </script>
    <style>
        /* Global Dark Mode Transitions */
        body {
            transition: background-color 0.3s ease, color 0.3s ease;
        }
        
        /* Dark Mode Global Overrides */
        .dark body {
            background-color: #18191a !important;
            color: #e4e6eb !important;
        }

        .dark .navbar {
            background-color: #242526 !important;
            border-bottom: 1px solid #3a3b3c !important;
            box-shadow: 0 1px 2px 0 rgba(0, 0, 0, 0.2) !important;
        }

        .dark .navbar-brand, .dark .nav-link {
            color: #e4e6eb !important;
        }

        .dark .navbar-brand:hover, .dark .nav-link:hover {
            color: #6366f1 !important;
        }

        .dark .bg-white {
            background-color: #242526 !important;
        }

        .dark .bg-gray-50, .dark .bg-gray-50\/50 {
            background-color: #18191a !important;
        }

        .dark .text-gray-900, .dark .text-slate-800, .dark .text-gray-800, .dark .text-gray-700 {
            color: #ffffff !important;
        }

        .dark .text-gray-500, .dark .text-slate-600 {
            color: #cbd5e1 !important;
        }

        .dark .border-gray-100, .dark .border-gray-50, .dark .border-gray-200 {
            border-color: #3a3b3c !important;
        }

        .dark .shadow-xl, .dark .shadow-lg, .dark .shadow-md {
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.3), 0 2px 4px -1px rgba(0, 0, 0, 0.2) !important;
        }

        /* Specific fix for card backgrounds in dark mode */
        .dark .rounded-3xl.bg-white, .dark .bg-white {
            background-color: #242526 !important;
        }

        /* Form Controls Dark Mode */
        .dark input, .dark select, .dark textarea {
            background-color: #3a3b3c !important;
            border-color: #4e4f50 !important;
            color: #e4e6eb !important;
        }

        .dark input::placeholder {
            color: #b0b3b8 !important;
        }

        .dark .bg-gray-100 {
            background-color: #3a3b3c !important;
        }

        .dark .hover\:bg-gray-50:hover, .dark .hover\:bg-gray-100:hover {
            background-color: #4e4f50 !important;
        }

        .dark .bg-indigo-50, .dark .bg-sky-50, .dark .bg-rose-50, .dark .bg-emerald-50, .dark .bg-amber-50, .dark .bg-teal-50 {
            background-color: rgba(99, 102, 241, 0.1) !important;
        }

        .dark .text-indigo-600, .dark .text-sky-600, .dark .text-rose-600, .dark .text-emerald-600, .dark .text-amber-600, .dark .text-teal-600 {
            color: #818cf8 !important; /* Lighter indigo for better contrast */
        }

        /* DataTables Dark Mode Overrides */
        .dark table.dataTable, .dark table.dataTable tr, .dark table.dataTable td, .dark table.dataTable th {
            background-color: #242526 !important;
            color: #e4e6eb !important;
            border-color: #3a3b3c !important;
        }

        .dark table.dataTable.stripe tbody tr.odd, .dark table.dataTable.display tbody tr.odd {
            background-color: #18191a !important;
        }

        .dark table.dataTable.hover tbody tr:hover, .dark table.dataTable.display tbody tr:hover {
            background-color: #3a3b3c !important;
        }

        .dark .dataTables_wrapper .dataTables_length, .dark .dataTables_wrapper .dataTables_filter, .dark .dataTables_wrapper .dataTables_info, .dark .dataTables_wrapper .dataTables_processing, .dark .dataTables_wrapper .dataTables_paginate {
            color: #b0b3b8 !important;
        }

        .dark .dataTables_wrapper .dataTables_paginate .paginate_button {
            color: #e4e6eb !important;
            background: #3a3b3c !important;
            border: 1px solid #4e4f50 !important;
        }

        .dark .dataTables_wrapper .dataTables_paginate .paginate_button.current, .dark .dataTables_wrapper .dataTables_paginate .paginate_button.current:hover {
            background: #6366f1 !important;
            color: white !important;
            border-color: #6366f1 !important;
        }

        .dark .dataTables_wrapper .dataTables_paginate .paginate_button:hover {
            background: #4e4f50 !important;
            border-color: #6366f1 !important;
        }

        .dark .dataTables_wrapper .dataTables_paginate .paginate_button.disabled, .dark .dataTables_wrapper .dataTables_paginate .paginate_button.disabled:hover, .dark .dataTables_wrapper .dataTables_paginate .paginate_button.disabled:active {
            background: #18191a !important;
            color: #4e4f50 !important;
            border-color: #3a3b3c !important;
        }
        /* Flash message slide-in animation */
        @keyframes fadeInUp {
            from {
                opacity: 0;
                transform: translateY(20px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }
        .animate-fade-in-up {
            animation: fadeInUp 0.4s ease-out;
        }
    </style>
    @stack('styles')
</head>
<body class="{{ $setting->theme === 'dark' ? 'dark' : '' }}">
    <div id="app">
        <nav class="navbar navbar-expand-md navbar-light bg-white shadow-sm">
            <div class="container">
                <a class="navbar-brand flex items-center gap-2" href="{{ url('/') }}">
                    @if($setting->app_logo)
                        <img src="{{ asset('storage/' . $setting->app_logo) }}" alt="Logo" class="h-8 w-auto">
                    @endif
                    <span>{{ $setting->app_name }}</span>
                </a>
                @auth
                @hasanyrole('admin|SuperAdmin')
                @if(request()->routeIs('admin.dashboard') || request()->routeIs('home'))
                <a href="javascript:void(0)" onclick="document.getElementById('academicYearModal').style.display='flex'" class="inline-flex items-center text-xs font-bold ml-2 gap-1.5 px-3 py-1.5 rounded-full border transition-all cursor-pointer {{ session('current_academic_year_id') ? 'bg-indigo-50 text-indigo-600 border-indigo-200 hover:bg-indigo-100' : 'bg-amber-50 text-amber-600 border-amber-200 hover:bg-amber-100' }}">
                    <i class="fas fa-graduation-cap text-[10px]"></i>
                    @if(session('current_academic_year_id') && session('current_semester_id'))
                        @php
                            $sessionYear = \App\Models\AcademicYear::find(session('current_academic_year_id'));
                            $sessionSemester = \App\Models\Semester::find(session('current_semester_id'));
                        @endphp
                        {{ $sessionYear->year ?? '' }}/{{ $sessionSemester->semester_number ?? '' }}
                    @else
                        เลือกปีการศึกษา
                    @endif
                    <i class="fas fa-chevron-down text-[8px] ml-0.5"></i>
                </a>
                @endif
                @endhasanyrole
                @endauth
                <div class="flex items-center space-x-4">
                    @auth
                        @include('components.user-dropdown')
                    @else
                        <div class="space-x-4">
                            <a href="{{ route('login') }}" class="text-sm font-bold text-slate-600 hover:text-indigo-600 transition-colors">{{ __('Login') }}</a>
                            @if (Route::has('register') && $setting->registration_enabled)
                                <a href="{{ route('register') }}" class="px-4 py-2 bg-indigo-600 text-white text-sm font-bold rounded-full shadow-lg hover:bg-indigo-700 transition-all">{{ __('Register') }}</a>
                            @endif
                        </div>
                    @endauth
                </div>
            </div>
        </nav>

        @auth
        @hasanyrole('admin|SuperAdmin')
        @include('components.admin-nav')
        @endhasanyrole
        @endauth

        <main class="py-0">
            @yield('content')
        </main>
    </div>

    <!-- Global Flash Message (bottom-right overlay) -->
    @if (session('status'))
    <div id="globalFlashMessage" class="fixed bottom-6 right-6 z-[9999] max-w-sm w-full animate-fade-in-up">
        <div class="flex items-center p-4 rounded-2xl shadow-2xl backdrop-blur-sm" style="background: rgba(16, 185, 129, 0.85);">
            <div class="flex-shrink-0 mr-3">
                <i class="fas fa-check-circle text-white text-xl"></i>
            </div>
            <div class="flex-1">
                <p class="text-sm font-bold text-white">{{ session('status') }}</p>
            </div>
            <button onclick="document.getElementById('globalFlashMessage').remove()" class="flex-shrink-0 ml-3 transition-colors hover:opacity-80" style="border:0;background:none;">
                <i class="fas fa-times text-sm text-rose-400"></i>
            </button>
        </div>
    </div>
    @endif
    <script>
        // Ensure Bootstrap collapse works correctly
        $(document).ready(function() {
            // Prevent any auto-hide behavior
            $('.navbar-collapse').on('show.bs.collapse', function() {
                $(this).data('manual', true);
            });
            
            $('.navbar-collapse').on('hide.bs.collapse', function() {
                if ($(this).data('manual')) {
                    $(this).data('manual', false);
                }
            });
        });

        // Auto-fade flash messages after 3 seconds
        $(document).ready(function() {
            setTimeout(function() {
                $('#globalFlashMessage').animate({ opacity: 0, bottom: '-20px' }, 500, function() {
                    $(this).remove();
                });
                $('.flash-message').animate({ opacity: 0 }, 500, function() {
                    $(this).slideUp(300, function() { $(this).remove(); });
                });
            }, 3000);
        });
    </script>
    @stack('scripts')

    @auth
    @hasanyrole('admin|SuperAdmin')
    <!-- Academic Year & Semester Selection Modal -->
    <div id="academicYearModal" style="display:none" class="fixed inset-0 z-[9999] items-center justify-center bg-black/50 backdrop-blur-sm" onclick="if(event.target===this)this.style.display='none'">
        <div class="bg-white dark:bg-[#242526] rounded-2xl shadow-2xl w-full max-w-lg mx-4 overflow-hidden">
            <div class="p-6 border-b border-gray-100 dark:border-[#3a3b3c]">
                <div class="flex items-center justify-between">
                    <div class="flex items-center gap-3">
                        <div class="w-10 h-10 rounded-xl bg-indigo-50 dark:bg-indigo-900/30 flex items-center justify-center">
                            <i class="fas fa-graduation-cap text-indigo-600 dark:text-indigo-400"></i>
                        </div>
                        <div>
                            <h3 class="font-bold text-lg text-gray-900 dark:text-white">ภาคเรียน & ปีการศึกษา</h3>
                            <p class="text-xs text-gray-500 dark:text-gray-400">เลือกภาคเรียนและปีการศึกษา</p>
                        </div>
                    </div>
                    <button onclick="document.getElementById('academicYearModal').style.display='none'" class="transition-colors hover:opacity-80" style="border:0;background:none;">
                        <i class="fas fa-times text-sm text-rose-400"></i>
                    </button>
                </div>
            </div>
            <form method="POST" action="{{ route('admin.academic-years.set-current') }}">
                @csrf
                <div class="p-4">
                    @php
                        $academicYears = \App\Models\AcademicYear::where('status', 1)->orderBy('year', 'desc')->get();
                        $semesters = \App\Models\Semester::where('status', 1)->orderBy('semester_number', 'asc')->get();
                    @endphp

                    <!-- Semester Selection -->
                    <label class="block text-xs font-bold text-gray-500 dark:text-gray-400 uppercase tracking-wider mb-2">
                        <i class="fas fa-book-open mr-1"></i> ภาคเรียน
                    </label>
                    <div class="flex gap-2 mb-4">
                        @forelse($semesters as $sem)
                        <label class="flex-1 flex items-center justify-center gap-2 p-3 rounded-xl transition-all duration-200 cursor-pointer border-2 {{ session('current_semester_id') == $sem->id ? 'bg-emerald-50 dark:bg-emerald-900/30 border-emerald-300 dark:border-emerald-700' : 'bg-gray-50 dark:bg-[#18191a] hover:bg-gray-100 dark:hover:bg-[#3a3b3c] border-gray-200 dark:border-[#3a3b3c]' }}">
                            <input type="radio" name="semester_id" value="{{ $sem->id }}" class="text-emerald-600 focus:ring-emerald-500" {{ session('current_semester_id') == $sem->id ? 'checked' : '' }}>
                            <span class="font-bold text-sm text-gray-900 dark:text-white">ภาคเรียนที่ {{ $sem->semester_number }}</span>
                        </label>
                        @empty
                        <div class="w-full text-center py-4 text-gray-400">
                            <p class="text-sm font-medium">ไม่มีภาคเรียนที่เปิดใช้งาน</p>
                        </div>
                        @endforelse
                    </div>

                    <!-- Academic Year Selection -->
                    <label class="block text-xs font-bold text-gray-500 dark:text-gray-400 uppercase tracking-wider mb-2">
                        <i class="fas fa-calendar-alt mr-1"></i> ปีการศึกษา
                    </label>
                    <div class="max-h-48 overflow-y-auto mb-2 space-y-1">
                        @forelse($academicYears as $ay)
                        <label class="w-full flex items-center justify-between p-3 rounded-xl transition-all duration-200 cursor-pointer border-2 {{ session('current_academic_year_id') == $ay->id ? 'bg-indigo-50 dark:bg-indigo-900/30 border-indigo-300 dark:border-indigo-700' : 'bg-gray-50 dark:bg-[#18191a] hover:bg-gray-100 dark:hover:bg-[#3a3b3c] border-gray-200 dark:border-[#3a3b3c]' }}">
                            <div class="flex items-center gap-3">
                                <input type="radio" name="academic_year_id" value="{{ $ay->id }}" class="text-indigo-600 focus:ring-indigo-500" {{ session('current_academic_year_id') == $ay->id ? 'checked' : '' }}>
                                <div class="w-10 h-10 rounded-xl {{ session('current_academic_year_id') == $ay->id ? 'bg-indigo-600 text-white' : 'bg-gray-100 dark:bg-zinc-700 text-gray-500 dark:text-gray-400' }} flex items-center justify-center font-bold text-sm">
                                    {{ $ay->year }}
                                </div>
                                <span class="font-bold text-sm text-gray-900 dark:text-white">ปีการศึกษา {{ $ay->year }}</span>
                            </div>
                            @if(session('current_academic_year_id') == $ay->id)
                            <i class="fas fa-check-circle text-indigo-600 dark:text-indigo-400"></i>
                            @endif
                        </label>
                        @empty
                        <div class="text-center py-6 text-gray-400">
                            <i class="fas fa-calendar-times text-2xl mb-2"></i>
                            <p class="text-sm font-medium">ไม่มีปีการศึกษาที่เปิดใช้งาน</p>
                        </div>
                        @endforelse
                    </div>
                </div>

                <div class="p-4 border-t border-gray-100 dark:border-[#3a3b3c] flex justify-end gap-2">
                    <button type="button" onclick="document.getElementById('academicYearModal').style.display='none'" class="px-4 py-2 text-sm font-bold text-gray-600 dark:text-gray-400 bg-gray-100 dark:bg-[#3a3b3c] rounded-xl hover:bg-gray-200 dark:hover:bg-[#4a4b4c] transition-all">
                        ยกเลิก
                    </button>
                    <button type="submit" class="px-6 py-2 text-sm font-bold text-white bg-indigo-600 rounded-xl hover:bg-indigo-700 transition-all shadow-lg shadow-indigo-200 dark:shadow-none">
                        <i class="fas fa-check mr-1"></i> เลือก
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Academic Year & Semester Global Setting Modal (saves to DB) -->
    <div id="academicYearGlobalModal" style="display:none" class="fixed inset-0 z-[9999] items-center justify-center bg-black/50 backdrop-blur-sm" onclick="if(event.target===this)this.style.display='none'">
        <div class="bg-white dark:bg-[#242526] rounded-2xl shadow-2xl w-full max-w-lg mx-4 overflow-hidden">
            <div class="p-6 border-b border-gray-100 dark:border-[#3a3b3c]">
                <div class="flex items-center justify-between">
                    <div class="flex items-center gap-3">
                        <div class="w-10 h-10 rounded-xl bg-indigo-50 dark:bg-indigo-900/30 flex items-center justify-center">
                            <i class="fas fa-graduation-cap text-indigo-600 dark:text-indigo-400"></i>
                        </div>
                        <div>
                            <h3 class="font-bold text-lg text-gray-900 dark:text-white">ภาคเรียน & ปีการศึกษา</h3>
                            <p class="text-xs text-gray-500 dark:text-gray-400">ตั้งค่าภาคเรียนและปีการศึกษาปัจจุบันของระบบ</p>
                        </div>
                    </div>
                    <button onclick="document.getElementById('academicYearGlobalModal').style.display='none'" class="transition-colors hover:opacity-80" style="border:0;background:none;">
                        <i class="fas fa-times text-sm text-rose-400"></i>
                    </button>
                </div>
            </div>
            <form method="POST" action="{{ route('admin.academic-years.set-current-global') }}">
                @csrf
                <div class="p-4">
                    @php
                        $globalSetting = \App\Models\CurrentAcademicSetting::first();
                    @endphp

                    <!-- Semester Selection -->
                    <label class="block text-xs font-bold text-gray-500 dark:text-gray-400 uppercase tracking-wider mb-2">
                        <i class="fas fa-book-open mr-1"></i> ภาคเรียน
                    </label>
                    <div class="flex gap-2 mb-4">
                        @forelse($semesters as $sem)
                        <label class="flex-1 flex items-center justify-center gap-2 p-3 rounded-xl transition-all duration-200 cursor-pointer border-2 {{ ($globalSetting && $globalSetting->semester_id == $sem->id) ? 'bg-emerald-50 dark:bg-emerald-900/30 border-emerald-300 dark:border-emerald-700' : 'bg-gray-50 dark:bg-[#18191a] hover:bg-gray-100 dark:hover:bg-[#3a3b3c] border-gray-200 dark:border-[#3a3b3c]' }}">
                            <input type="radio" name="semester_id" value="{{ $sem->id }}" class="text-emerald-600 focus:ring-emerald-500" {{ ($globalSetting && $globalSetting->semester_id == $sem->id) ? 'checked' : '' }}>
                            <span class="font-bold text-sm text-gray-900 dark:text-white">ภาคเรียนที่ {{ $sem->semester_number }}</span>
                        </label>
                        @empty
                        <div class="w-full text-center py-4 text-gray-400">
                            <p class="text-sm font-medium">ไม่มีภาคเรียนที่เปิดใช้งาน</p>
                        </div>
                        @endforelse
                    </div>

                    <!-- Academic Year Selection -->
                    <label class="block text-xs font-bold text-gray-500 dark:text-gray-400 uppercase tracking-wider mb-2">
                        <i class="fas fa-calendar-alt mr-1"></i> ปีการศึกษา
                    </label>
                    <div class="max-h-48 overflow-y-auto mb-2 space-y-1">
                        @forelse($academicYears as $ay)
                        <label class="w-full flex items-center justify-between p-3 rounded-xl transition-all duration-200 cursor-pointer border-2 {{ ($globalSetting && $globalSetting->academic_year_id == $ay->id) ? 'bg-indigo-50 dark:bg-indigo-900/30 border-indigo-300 dark:border-indigo-700' : 'bg-gray-50 dark:bg-[#18191a] hover:bg-gray-100 dark:hover:bg-[#3a3b3c] border-gray-200 dark:border-[#3a3b3c]' }}">
                            <div class="flex items-center gap-3">
                                <input type="radio" name="academic_year_id" value="{{ $ay->id }}" class="text-indigo-600 focus:ring-indigo-500" {{ ($globalSetting && $globalSetting->academic_year_id == $ay->id) ? 'checked' : '' }}>
                                <div class="w-10 h-10 rounded-xl {{ ($globalSetting && $globalSetting->academic_year_id == $ay->id) ? 'bg-indigo-600 text-white' : 'bg-gray-100 dark:bg-zinc-700 text-gray-500 dark:text-gray-400' }} flex items-center justify-center font-bold text-sm">
                                    {{ $ay->year }}
                                </div>
                                <span class="font-bold text-sm text-gray-900 dark:text-white">ปีการศึกษา {{ $ay->year }}</span>
                            </div>
                            @if($globalSetting && $globalSetting->academic_year_id == $ay->id)
                            <i class="fas fa-check-circle text-indigo-600 dark:text-indigo-400"></i>
                            @endif
                        </label>
                        @empty
                        <div class="text-center py-6 text-gray-400">
                            <i class="fas fa-calendar-times text-2xl mb-2"></i>
                            <p class="text-sm font-medium">ไม่มีปีการศึกษาที่เปิดใช้งาน</p>
                        </div>
                        @endforelse
                    </div>
                </div>

                <div class="p-4 border-t border-gray-100 dark:border-[#3a3b3c] flex justify-end gap-2">
                    <button type="button" onclick="document.getElementById('academicYearGlobalModal').style.display='none'" class="px-4 py-2 text-sm font-bold text-gray-600 dark:text-gray-400 bg-gray-100 dark:bg-[#3a3b3c] rounded-xl hover:bg-gray-200 dark:hover:bg-[#4a4b4c] transition-all">
                        ยกเลิก
                    </button>
                    <button type="submit" class="px-6 py-2 text-sm font-bold text-white bg-indigo-600 rounded-xl hover:bg-indigo-700 transition-all shadow-lg shadow-indigo-200 dark:shadow-none">
                        <i class="fas fa-save mr-1"></i> บันทึก
                    </button>
                </div>
            </form>
        </div>
    </div>
    @endhasanyrole
    @endauth
</body>
</html>
