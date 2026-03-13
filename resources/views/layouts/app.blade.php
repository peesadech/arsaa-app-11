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
                <a href="javascript:void(0)" onclick="document.getElementById('academicYearModal').style.display='flex'" class="inline-flex items-center text-xs font-bold ml-2 gap-1.5 px-3 py-1.5 rounded-full border transition-all cursor-pointer {{ isset($currentAcademicYear) ? 'bg-indigo-50 text-indigo-600 border-indigo-200 hover:bg-indigo-100' : 'bg-amber-50 text-amber-600 border-amber-200 hover:bg-amber-100' }}">
                    <i class="fas fa-graduation-cap text-[10px]"></i>
                    {{ isset($currentAcademicYear) ? $currentAcademicYear->year : 'Select Year' }}
                    <i class="fas fa-chevron-down text-[8px] ml-0.5"></i>
                </a>
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
    <!-- Academic Year Selection Modal -->
    <div id="academicYearModal" style="display:none" class="fixed inset-0 z-[9999] items-center justify-center bg-black/50 backdrop-blur-sm" onclick="if(event.target===this)this.style.display='none'">
        <div class="bg-white dark:bg-[#242526] rounded-2xl shadow-2xl w-full max-w-md mx-4 overflow-hidden">
            <div class="p-6 border-b border-gray-100 dark:border-[#3a3b3c]">
                <div class="flex items-center justify-between">
                    <div class="flex items-center gap-3">
                        <div class="w-10 h-10 rounded-xl bg-indigo-50 dark:bg-indigo-900/30 flex items-center justify-center">
                            <i class="fas fa-graduation-cap text-indigo-600 dark:text-indigo-400"></i>
                        </div>
                        <div>
                            <h3 class="font-bold text-lg text-gray-900 dark:text-white">Academic Year</h3>
                            <p class="text-xs text-gray-500 dark:text-gray-400">Select current academic year</p>
                        </div>
                    </div>
                    <button onclick="document.getElementById('academicYearModal').style.display='none'" class="transition-colors hover:opacity-80" style="border:0;background:none;">
                        <i class="fas fa-times text-sm text-rose-400"></i>
                    </button>
                </div>
            </div>
            <div class="p-4 max-h-80 overflow-y-auto">
                @php
                    $academicYears = \App\Models\AcademicYear::where('status', 1)->orderBy('year', 'desc')->get();
                @endphp
                @forelse($academicYears as $ay)
                <form method="POST" action="{{ route('admin.academic-years.set-current') }}" class="mb-1">
                    @csrf
                    <input type="hidden" name="academic_year_id" value="{{ $ay->id }}">
                    <button type="submit" class="w-full flex items-center justify-between p-3 rounded-xl transition-all duration-200 {{ $ay->is_current_year ? 'bg-indigo-50 dark:bg-indigo-900/30 border-2 border-indigo-300 dark:border-indigo-700' : 'bg-gray-50 dark:bg-[#18191a] hover:bg-gray-100 dark:hover:bg-[#3a3b3c] border-2 border-gray-200 dark:border-[#3a3b3c]' }}">
                        <div class="flex items-center gap-3">
                            <div class="w-10 h-10 rounded-xl {{ $ay->is_current_year ? 'bg-indigo-600 text-white' : 'bg-gray-100 dark:bg-zinc-700 text-gray-500 dark:text-gray-400' }} flex items-center justify-center font-bold text-sm">
                                {{ $ay->year }}
                            </div>
                            <div class="text-left">
                                <span class="font-bold text-sm text-gray-900 dark:text-white">ปีการศึกษา {{ $ay->year }}</span>
                                @if($ay->is_current_year)
                                <span class="block text-[10px] font-bold text-indigo-600 dark:text-indigo-400 uppercase tracking-wider">Current Year</span>
                                @endif
                            </div>
                        </div>
                        @if($ay->is_current_year)
                        <i class="fas fa-check-circle text-indigo-600 dark:text-indigo-400"></i>
                        @endif
                    </button>
                </form>
                @empty
                <div class="text-center py-8 text-gray-400">
                    <i class="fas fa-calendar-times text-3xl mb-2"></i>
                    <p class="text-sm font-medium">No active academic years</p>
                </div>
                @endforelse
            </div>
        </div>
    </div>
    @endhasanyrole
    @endauth
</body>
</html>
