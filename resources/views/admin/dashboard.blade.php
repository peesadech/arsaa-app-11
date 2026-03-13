@extends('layouts.app')

@push('styles')
<style>
    a.group.block:hover {
        text-decoration: none !important;
    }
</style>
@endpush

@section('content')
<div class="min-h-screen bg-gray-50/50 dark:bg-[#18191a] py-8 px-4 sm:px-6 lg:px-8 transition-colors duration-300">
    <div class="max-w-7xl mx-auto space-y-8">

        <!-- Header -->
        <div class="flex flex-col md:flex-row md:items-center md:justify-between mb-2">
            <div class="flex items-center space-x-6">
                <a href="{{ route('home') }}"
                   class="group flex items-center justify-center w-10 h-10 rounded-full bg-white dark:bg-[#242526] shadow-sm border border-gray-200 dark:border-[#3a3b3c] text-gray-500 dark:text-gray-400 hover:text-indigo-600 dark:hover:text-indigo-400 hover:border-indigo-300 transition-all duration-200">
                    <i class="fas fa-arrow-left group-hover:-translate-x-0.5 transition-transform"></i>
                </a>
                <div>
                    <h1 class="text-3xl font-extrabold text-gray-900 dark:text-white tracking-tight">{{ __('Setting') }}</h1>
                    <p class="text-sm text-gray-500 dark:text-gray-400 font-medium mt-1 px-1">{{ __('Manage all system settings and configurations') }}</p>
                </div>
            </div>
        </div>

        <!-- Admin Cards Grid -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">

            <!-- Users Card -->
            <a href="{{ route('admin.users.index') }}" class="group block">
                <div class="h-full bg-white dark:bg-[#242526] rounded-3xl shadow-lg shadow-gray-200/40 dark:shadow-none border-2 border-transparent hover:border-violet-100 dark:hover:border-violet-900/50 transition-all duration-300 transform hover:-translate-y-2 p-8 overflow-hidden relative">
                    <div class="absolute -right-4 -bottom-4 w-32 h-32 bg-violet-50 dark:bg-violet-900/20 rounded-full group-hover:scale-150 transition-transform duration-500 opacity-50"></div>
                    <div class="relative z-10 space-y-4">
                        <div class="w-14 h-14 rounded-2xl bg-violet-600 shadow-lg shadow-violet-200 dark:shadow-none flex items-center justify-center text-white text-2xl group-hover:scale-110 transition-transform duration-300">
                            <i class="fas fa-users"></i>
                        </div>
                        <div>
                            <h3 class="text-xl font-bold text-gray-900 dark:text-white tracking-tight">{{ __('User Management') }}</h3>
                            <p class="text-gray-500 dark:text-gray-400 text-sm mt-1 leading-relaxed">{{ __('Manage system users, their accounts, passwords and assigned roles.') }}</p>
                        </div>
                        <div class="pt-4 flex items-center text-violet-600 dark:text-violet-400 text-sm font-bold">
                            <span>{{ __('View Users') }}</span>
                            <i class="fas fa-chevron-right ml-2 group-hover:translate-x-1 transition-transform"></i>
                        </div>
                    </div>
                </div>
            </a>

            <!-- Grade Card -->
            <a href="{{ route('admin.grades.index') }}" class="group block">
                <div class="h-full bg-white dark:bg-[#242526] rounded-3xl shadow-lg shadow-gray-200/40 dark:shadow-none border-2 border-transparent hover:border-blue-100 dark:hover:border-blue-900/50 transition-all duration-300 transform hover:-translate-y-2 p-8 overflow-hidden relative">
                    <div class="absolute -right-4 -bottom-4 w-32 h-32 bg-blue-50 dark:bg-blue-900/20 rounded-full group-hover:scale-150 transition-transform duration-500 opacity-50"></div>
                    <div class="relative z-10 space-y-4">
                        <div class="w-14 h-14 rounded-2xl bg-blue-600 shadow-lg shadow-blue-200 dark:shadow-none flex items-center justify-center text-white text-2xl group-hover:scale-110 transition-transform duration-300">
                            <i class="fas fa-layer-group"></i>
                        </div>
                        <div>
                            <h3 class="text-xl font-bold text-gray-900 dark:text-white tracking-tight">{{ __('Grade Management') }}</h3>
                            <p class="text-gray-500 dark:text-gray-400 text-sm mt-1 leading-relaxed">{{ __('Manage system grades, levels, and educational categories.') }}</p>
                        </div>
                        <div class="pt-4 flex items-center text-blue-600 dark:text-blue-400 text-sm font-bold">
                            <span>{{ __('View Grades') }}</span>
                            <i class="fas fa-chevron-right ml-2 group-hover:translate-x-1 transition-transform"></i>
                        </div>
                    </div>
                </div>
            </a>

            <!-- Classroom Card -->
            <a href="{{ route('admin.classrooms.index') }}" class="group block">
                <div class="h-full bg-white dark:bg-[#242526] rounded-3xl shadow-lg shadow-gray-200/40 dark:shadow-none border-2 border-transparent hover:border-blue-100 dark:hover:border-blue-900/50 transition-all duration-300 transform hover:-translate-y-2 p-8 overflow-hidden relative">
                    <div class="absolute -right-4 -bottom-4 w-32 h-32 bg-blue-50 dark:bg-blue-900/20 rounded-full group-hover:scale-150 transition-transform duration-500 opacity-50"></div>
                    <div class="relative z-10 space-y-4">
                        <div class="w-14 h-14 rounded-2xl bg-blue-600 shadow-lg shadow-blue-200 dark:shadow-none flex items-center justify-center text-white text-2xl group-hover:scale-110 transition-transform duration-300">
                            <i class="fas fa-chalkboard"></i>
                        </div>
                        <div>
                            <h3 class="text-xl font-bold text-gray-900 dark:text-white tracking-tight">{{ __('Classroom Management') }}</h3>
                            <p class="text-gray-500 dark:text-gray-400 text-sm mt-1 leading-relaxed">{{ __('Manage system classrooms.') }}</p>
                        </div>
                        <div class="pt-4 flex items-center text-blue-600 dark:text-blue-400 text-sm font-bold">
                            <span>{{ __('View Classrooms') }}</span>
                            <i class="fas fa-chevron-right ml-2 group-hover:translate-x-1 transition-transform"></i>
                        </div>
                    </div>
                </div>
            </a>

            <!-- Course Card -->
            <a href="{{ route('admin.courses.index') }}" class="group block">
                <div class="h-full bg-white dark:bg-[#242526] rounded-3xl shadow-lg shadow-gray-200/40 dark:shadow-none border-2 border-transparent hover:border-yellow-100 dark:hover:border-yellow-900/50 transition-all duration-300 transform hover:-translate-y-2 p-8 overflow-hidden relative">
                    <div class="absolute -right-4 -bottom-4 w-32 h-32 bg-yellow-50 dark:bg-yellow-900/20 rounded-full group-hover:scale-150 transition-transform duration-500 opacity-50"></div>
                    <div class="relative z-10 space-y-4">
                        <div class="w-14 h-14 rounded-2xl bg-yellow-500 shadow-lg shadow-yellow-200 dark:shadow-none flex items-center justify-center text-white text-2xl group-hover:scale-110 transition-transform duration-300">
                            <i class="fas fa-book"></i>
                        </div>
                        <div>
                            <h3 class="text-xl font-bold text-gray-900 dark:text-white tracking-tight">{{ __('Course Management') }}</h3>
                            <p class="text-gray-500 dark:text-gray-400 text-sm mt-1 leading-relaxed">{{ __('Manage system courses.') }}</p>
                        </div>
                        <div class="pt-4 flex items-center text-yellow-600 dark:text-yellow-400 text-sm font-bold">
                            <span>{{ __('View Courses') }}</span>
                            <i class="fas fa-chevron-right ml-2 group-hover:translate-x-1 transition-transform"></i>
                        </div>
                    </div>
                </div>
            </a>

            <!-- Academic Year Card -->
            <a href="{{ route('admin.academic-years.index') }}" class="group block">
                <div class="h-full bg-white dark:bg-[#242526] rounded-3xl shadow-lg shadow-gray-200/40 dark:shadow-none border-2 border-transparent hover:border-emerald-100 dark:hover:border-emerald-900/50 transition-all duration-300 transform hover:-translate-y-2 p-8 overflow-hidden relative">
                    <div class="absolute -right-4 -bottom-4 w-32 h-32 bg-emerald-50 dark:bg-emerald-900/20 rounded-full group-hover:scale-150 transition-transform duration-500 opacity-50"></div>
                    <div class="relative z-10 space-y-4">
                        <div class="w-14 h-14 rounded-2xl bg-emerald-600 shadow-lg shadow-emerald-200 dark:shadow-none flex items-center justify-center text-white text-2xl group-hover:scale-110 transition-transform duration-300">
                            <i class="fas fa-calendar-check"></i>
                        </div>
                        <div>
                            <h3 class="text-xl font-bold text-gray-900 dark:text-white tracking-tight">{{ __('Academic Year') }}</h3>
                            <p class="text-gray-500 dark:text-gray-400 text-sm mt-1 leading-relaxed">{{ __('Manage system academic years, BE and AD conversions.') }}</p>
                        </div>
                        <div class="pt-4 flex items-center text-emerald-600 dark:text-emerald-400 text-sm font-bold">
                            <span>{{ __('Manage Years') }}</span>
                            <i class="fas fa-chevron-right ml-2 group-hover:translate-x-1 transition-transform"></i>
                        </div>
                    </div>
                </div>
            </a>

            <!-- Semester Card -->
            <a href="{{ route('admin.semesters.index') }}" class="group block">
                <div class="h-full bg-white dark:bg-[#242526] rounded-3xl shadow-lg shadow-gray-200/40 dark:shadow-none border-2 border-transparent hover:border-indigo-100 dark:hover:border-indigo-900/50 transition-all duration-300 transform hover:-translate-y-2 p-8 overflow-hidden relative">
                    <div class="absolute -right-4 -bottom-4 w-32 h-32 bg-indigo-50 dark:bg-indigo-900/20 rounded-full group-hover:scale-150 transition-transform duration-500 opacity-50"></div>
                    <div class="relative z-10 space-y-4">
                        <div class="w-14 h-14 rounded-2xl bg-indigo-600 shadow-lg shadow-indigo-200 dark:shadow-none flex items-center justify-center text-white text-2xl group-hover:scale-110 transition-transform duration-300">
                            <i class="fas fa-list-ol"></i>
                        </div>
                        <div>
                            <h3 class="text-xl font-bold text-gray-900 dark:text-white tracking-tight">{{ __('Semester Management') }}</h3>
                            <p class="text-gray-500 dark:text-gray-400 text-sm mt-1 leading-relaxed">{{ __('Manage system semesters and academic terms.') }}</p>
                        </div>
                        <div class="pt-4 flex items-center text-indigo-600 dark:text-indigo-400 text-sm font-bold">
                            <span>{{ __('View Semesters') }}</span>
                            <i class="fas fa-chevron-right ml-2 group-hover:translate-x-1 transition-transform"></i>
                        </div>
                    </div>
                </div>
            </a>

            <!-- Roles Card -->
            <a href="{{ route('admin.roles-permissions') }}" class="group block">
                <div class="h-full bg-white dark:bg-[#242526] rounded-3xl shadow-lg shadow-gray-200/40 dark:shadow-none border-2 border-transparent hover:border-indigo-100 dark:hover:border-indigo-900/50 transition-all duration-300 transform hover:-translate-y-2 p-8 overflow-hidden relative">
                    <div class="absolute -right-4 -bottom-4 w-32 h-32 bg-indigo-50 dark:bg-indigo-900/20 rounded-full group-hover:scale-150 transition-transform duration-500 opacity-50"></div>
                    <div class="relative z-10 space-y-4">
                        <div class="w-14 h-14 rounded-2xl bg-indigo-600 shadow-lg shadow-indigo-200 dark:shadow-none flex items-center justify-center text-white text-2xl group-hover:scale-110 transition-transform duration-300">
                            <i class="fas fa-user-shield"></i>
                        </div>
                        <div>
                            <h3 class="text-xl font-bold text-gray-900 dark:text-white tracking-tight">{{ __('Roles Management') }}</h3>
                            <p class="text-gray-500 dark:text-gray-400 text-sm mt-1 leading-relaxed">{{ __('Control system roles, user permissions, and security groupings.') }}</p>
                        </div>
                        <div class="pt-4 flex items-center text-indigo-600 dark:text-indigo-400 text-sm font-bold">
                            <span>{{ __('Manage Roles') }}</span>
                            <i class="fas fa-chevron-right ml-2 group-hover:translate-x-1 transition-transform"></i>
                        </div>
                    </div>
                </div>
            </a>

            <!-- Permissions Card -->
            <a href="{{ route('admin.permissions') }}" class="group block">
                <div class="h-full bg-white dark:bg-[#242526] rounded-3xl shadow-lg shadow-gray-200/40 dark:shadow-none border-2 border-transparent hover:border-emerald-100 dark:hover:border-emerald-900/50 transition-all duration-300 transform hover:-translate-y-2 p-8 overflow-hidden relative">
                    <div class="absolute -right-4 -bottom-4 w-32 h-32 bg-emerald-50 dark:bg-emerald-900/20 rounded-full group-hover:scale-150 transition-transform duration-500 opacity-50"></div>
                    <div class="relative z-10 space-y-4">
                        <div class="w-14 h-14 rounded-2xl bg-emerald-600 shadow-lg shadow-emerald-200 dark:shadow-none flex items-center justify-center text-white text-2xl group-hover:scale-110 transition-transform duration-300">
                            <i class="fas fa-key"></i>
                        </div>
                        <div>
                            <h3 class="text-xl font-bold text-gray-900 dark:text-white tracking-tight">{{ __('Permissions Center') }}</h3>
                            <p class="text-gray-500 dark:text-gray-400 text-sm mt-1 leading-relaxed">{{ __('Define granular system capabilities and secure access points.') }}</p>
                        </div>
                        <div class="pt-4 flex items-center text-emerald-600 dark:text-emerald-400 text-sm font-bold">
                            <span>{{ __('Configure Access') }}</span>
                            <i class="fas fa-chevron-right ml-2 group-hover:translate-x-1 transition-transform"></i>
                        </div>
                    </div>
                </div>
            </a>

            <!-- Permission Types Card -->
            <a href="{{ route('admin.permission-types') }}" class="group block">
                <div class="h-full bg-white dark:bg-[#242526] rounded-3xl shadow-lg shadow-gray-200/40 dark:shadow-none border-2 border-transparent hover:border-teal-100 dark:hover:border-teal-900/50 transition-all duration-300 transform hover:-translate-y-2 p-8 overflow-hidden relative">
                    <div class="absolute -right-4 -bottom-4 w-32 h-32 bg-teal-50 dark:bg-teal-900/20 rounded-full group-hover:scale-150 transition-transform duration-500 opacity-50"></div>
                    <div class="relative z-10 space-y-4">
                        <div class="w-14 h-14 rounded-2xl bg-teal-600 shadow-lg shadow-teal-200 dark:shadow-none flex items-center justify-center text-white text-2xl group-hover:scale-110 transition-transform duration-300">
                            <i class="fas fa-cubes"></i>
                        </div>
                        <div>
                            <h3 class="text-xl font-bold text-gray-900 dark:text-white tracking-tight">{{ __('Permission Category') }}</h3>
                            <p class="text-gray-500 dark:text-gray-400 text-sm mt-1 leading-relaxed">{{ __('Organize your permissions into logical categories and grouping types.') }}</p>
                        </div>
                        <div class="pt-4 flex items-center text-teal-600 dark:text-teal-400 text-sm font-bold">
                            <span>{{ __('View Categories') }}</span>
                            <i class="fas fa-chevron-right ml-2 group-hover:translate-x-1 transition-transform"></i>
                        </div>
                    </div>
                </div>
            </a>

            @role('SuperAdmin')
            <!-- System Settings Card -->
            <a href="{{ route('admin.settings.index') }}" class="group block">
                <div class="h-full bg-white dark:bg-[#242526] rounded-3xl shadow-lg shadow-gray-200/40 dark:shadow-none border-2 border-transparent hover:border-slate-100 dark:hover:border-slate-700 transition-all duration-300 transform hover:-translate-y-2 p-8 overflow-hidden relative">
                    <div class="absolute -right-4 -bottom-4 w-32 h-32 bg-slate-50 dark:bg-slate-900/20 rounded-full group-hover:scale-150 transition-transform duration-500 opacity-50"></div>
                    <div class="relative z-10 space-y-4">
                        <div class="w-14 h-14 rounded-2xl bg-slate-600 shadow-lg shadow-slate-200 dark:shadow-none flex items-center justify-center text-white text-2xl group-hover:scale-110 transition-transform duration-300">
                            <i class="fas fa-cogs"></i>
                        </div>
                        <div>
                            <h3 class="text-xl font-bold text-gray-900 dark:text-white tracking-tight">{{ __('General Settings') }}</h3>
                            <p class="text-gray-500 dark:text-gray-400 text-sm mt-1 leading-relaxed">{{ __('Configure global application identity, name, and branding logo.') }}</p>
                        </div>
                        <div class="pt-4 flex items-center text-slate-600 dark:text-slate-400 text-sm font-bold">
                            <span>{{ __('Manage Settings') }}</span>
                            <i class="fas fa-chevron-right ml-2 group-hover:translate-x-1 transition-transform"></i>
                        </div>
                    </div>
                </div>
            </a>
            @endrole

        </div>

        <!-- Footer -->
        <div class="pt-12 border-t border-gray-100 dark:border-[#3a3b3c] flex flex-col md:flex-row items-center justify-between gap-4 text-xs font-bold text-gray-400 dark:text-gray-500 uppercase tracking-widest">
            <div class="flex items-center">
                <span class="w-2 h-2 rounded-full bg-emerald-500 mr-2 shadow-sm shadow-emerald-200 animate-pulse"></span>
                {{ __('All Systems Operational') }}
            </div>
            <div class="flex items-center space-x-6">
                <span>{{ __('Setting') }}</span>
                <span class="w-1 h-1 rounded-full bg-gray-300"></span>
                <span>v2.0.4</span>
            </div>
        </div>

    </div>
</div>
@endsection
