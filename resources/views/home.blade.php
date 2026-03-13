@extends('layouts.app')

@push('styles')
<style>
    a.group.block:hover {
        text-decoration: none !important;
    }
</style>
@endpush

@section('content')
<div class="min-h-screen bg-gray-50/50 py-12 px-4 sm:px-6 lg:px-8">
    <div class="max-w-7xl mx-auto space-y-8">
        
        <!-- Welcome Header -->
        <div class="bg-white rounded-3xl shadow-xl shadow-gray-200/50 border border-gray-100 p-8 md:p-12 relative overflow-hidden">
            <div class="absolute top-0 right-0 -m-8 w-64 h-64 bg-indigo-50 rounded-full opacity-50 blur-3xl"></div>
            <div class="relative z-10 flex flex-col md:flex-row md:items-center md:justify-between gap-6">
                <div class="flex items-center space-x-6">
                    <div class="relative">
                        <div class="absolute inset-0 bg-indigo-500/20 blur-xl rounded-full"></div>
                        <div class="relative w-24 h-24 rounded-3xl overflow-hidden border-4 border-white shadow-xl">
                            <img src="{{ Auth::user()->image_path ? asset(Auth::user()->image_path) : 'https://ui-avatars.com/api/?name=' . urlencode(Auth::user()->name) . '&background=6366f1&color=fff&size=200' }}" class="w-full h-full object-cover">
                        </div>
                    </div>
                    <div class="space-y-2">
                        <h1 class="text-4xl font-extrabold text-gray-900 tracking-tight">
                            {{ __('Welcome back,') }} <span class="text-indigo-600">{{ Auth::user()->name }}!</span>
                        </h1>
                        <p class="text-lg text-gray-500 font-medium max-w-2xl leading-relaxed">
                            {{ __('Everything looks great today. Here is what is happening with your system.') }}
                        </p>
                    </div>
                </div>
                <div class="flex items-center space-x-4">
                    <div class="w-12 h-12 rounded-2xl bg-indigo-50 flex items-center justify-center text-indigo-600 border border-indigo-100">
                        <i class="fas fa-calendar-alt text-xl"></i>
                    </div>
                    <div>
                        <p class="text-xs font-bold text-gray-400 uppercase tracking-widest">{{ __('Current Date') }}</p>
                        <p class="font-bold text-gray-800 tracking-tight">{{ now()->format('F j, Y') }}</p>
                    </div>
                </div>
            </div>
        </div>

        @if (session('status'))
            <div class="flex items-center p-6 bg-emerald-50 border-l-4 border-emerald-500 rounded-r-2xl shadow-sm animate-fade-in-down">
                <div class="flex-shrink-0 mr-4">
                    <i class="fas fa-check-circle text-emerald-500 text-2xl"></i>
                </div>
                <div>
                    <h3 class="text-sm font-bold text-emerald-900">{{ __('Success Notification') }}</h3>
                    <p class="text-sm font-medium text-emerald-800">{{ session('status') }}</p>
                </div>
            </div>
        @endif

        <!-- Quick Access Grid -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
            
            <!-- Update Profile Card -->
            <a href="{{ route('profile.index') }}" class="group block">
                <div class="h-full bg-white rounded-3xl shadow-lg shadow-gray-200/40 border-2 border-transparent hover:border-amber-100 transition-all duration-300 transform hover:-translate-y-2 p-8 overflow-hidden relative">
                    <div class="absolute -right-4 -bottom-4 w-32 h-32 bg-amber-50 rounded-full group-hover:scale-150 transition-transform duration-500 opacity-50"></div>
                    <div class="relative z-10 space-y-4">
                        <div class="w-14 h-14 rounded-2xl bg-amber-500 shadow-lg shadow-amber-200 flex items-center justify-center text-white text-2xl group-hover:scale-110 transition-transform duration-300">
                            <i class="fas fa-user-cog"></i>
                        </div>
                        <div>
                            <h3 class="text-xl font-bold text-gray-900 tracking-tight">{{ __('Update Profile') }}</h3>
                            <p class="text-gray-500 text-sm mt-1 leading-relaxed">{{ __('Modify your personal information, name, and profile picture.') }}</p>
                        </div>
                        <div class="pt-4 flex items-center text-amber-600 text-sm font-bold">
                            <span>{{ __('Manage Account') }}</span>
                            <i class="fas fa-chevron-right ml-2 group-hover:translate-x-1 transition-transform"></i>
                        </div>
                    </div>
                </div>
            </a>

            @hasanyrole('admin|SuperAdmin')
            <!-- Users Card -->
            <a href="{{ route('admin.users.index') }}" class="group block">
                <div class="h-full bg-white rounded-3xl shadow-lg shadow-gray-200/40 border-2 border-transparent hover:border-violet-100 transition-all duration-300 transform hover:-translate-y-2 p-8 overflow-hidden relative">
                    <div class="absolute -right-4 -bottom-4 w-32 h-32 bg-violet-50 rounded-full group-hover:scale-150 transition-transform duration-500 opacity-50"></div>
                    <div class="relative z-10 space-y-4">
                        <div class="w-14 h-14 rounded-2xl bg-violet-600 shadow-lg shadow-violet-200 flex items-center justify-center text-white text-2xl group-hover:scale-110 transition-transform duration-300">
                            <i class="fas fa-users"></i>
                        </div>
                        <div>
                            <h3 class="text-xl font-bold text-gray-900 tracking-tight">{{ __('User Management') }}</h3>
                            <p class="text-gray-500 text-sm mt-1 leading-relaxed">{{ __('Manage system users, their accounts, passwords and assigned roles.') }}</p>
                        </div>
                        <div class="pt-4 flex items-center text-violet-600 text-sm font-bold">
                            <span>{{ __('View Users') }}</span>
                            <i class="fas fa-chevron-right ml-2 group-hover:translate-x-1 transition-transform"></i>
                        </div>
                    </div>
                </div>
            </a>

            <!-- Grade Card -->
            <a href="{{ route('admin.grades.index') }}" class="group block">
                <div class="h-full bg-white rounded-3xl shadow-lg shadow-gray-200/40 border-2 border-transparent hover:border-blue-100 transition-all duration-300 transform hover:-translate-y-2 p-8 overflow-hidden relative">
                    <div class="absolute -right-4 -bottom-4 w-32 h-32 bg-blue-50 rounded-full group-hover:scale-150 transition-transform duration-500 opacity-50"></div>
                    <div class="relative z-10 space-y-4">
                        <div class="w-14 h-14 rounded-2xl bg-blue-600 shadow-lg shadow-blue-200 flex items-center justify-center text-white text-2xl group-hover:scale-110 transition-transform duration-300">
                            <i class="fas fa-layer-group"></i>
                        </div>
                        <div>
                            <h3 class="text-xl font-bold text-gray-900 tracking-tight">{{ __('Grade Management') }}</h3>
                            <p class="text-gray-500 text-sm mt-1 leading-relaxed">{{ __('Manage system grades, levels, and educational categories.') }}</p>
                        </div>
                        <div class="pt-4 flex items-center text-blue-600 text-sm font-bold">
                            <span>{{ __('View Grades') }}</span>
                            <i class="fas fa-chevron-right ml-2 group-hover:translate-x-1 transition-transform"></i>
                        </div>
                    </div>
                </div>
            </a>

            <!-- Classroom Card -->
            <a href="{{ route('admin.classrooms.index') }}" class="group block">
                <div class="h-full bg-white rounded-3xl shadow-lg shadow-gray-200/40 border-2 border-transparent hover:border-blue-100 transition-all duration-300 transform hover:-translate-y-2 p-8 overflow-hidden relative">
                    <div class="absolute -right-4 -bottom-4 w-32 h-32 bg-blue-50 rounded-full group-hover:scale-150 transition-transform duration-500 opacity-50"></div>
                    <div class="relative z-10 space-y-4">
                        <div class="w-14 h-14 rounded-2xl bg-blue-600 shadow-lg shadow-blue-200 flex items-center justify-center text-white text-2xl group-hover:scale-110 transition-transform duration-300">
                            <i class="fas fa-chalkboard"></i>
                        </div>
                        <div>
                            <h3 class="text-xl font-bold text-gray-900 tracking-tight">{{ __('Classroom Management') }}</h3>
                            <p class="text-gray-500 text-sm mt-1 leading-relaxed">{{ __('Manage system classrooms.') }}</p>
                        </div>
                        <div class="pt-4 flex items-center text-blue-600 text-sm font-bold">
                            <span>{{ __('View Classrooms') }}</span>
                            <i class="fas fa-chevron-right ml-2 group-hover:translate-x-1 transition-transform"></i>
                        </div>
                    </div>
                </div>
            </a>

            <!-- Course Card -->
            <a href="{{ route('admin.courses.index') }}" class="group block">
                <div class="h-full bg-white rounded-3xl shadow-lg shadow-gray-200/40 border-2 border-transparent hover:border-yellow-100 transition-all duration-300 transform hover:-translate-y-2 p-8 overflow-hidden relative">
                    <div class="absolute -right-4 -bottom-4 w-32 h-32 bg-yellow-50 rounded-full group-hover:scale-150 transition-transform duration-500 opacity-50"></div>
                    <div class="relative z-10 space-y-4">
                        <div class="w-14 h-14 rounded-2xl bg-yellow-500 shadow-lg shadow-yellow-200 flex items-center justify-center text-white text-2xl group-hover:scale-110 transition-transform duration-300">
                            <i class="fas fa-book"></i>
                        </div>
                        <div>
                            <h3 class="text-xl font-bold text-gray-900 tracking-tight">{{ __('Course Management') }}</h3>
                            <p class="text-gray-500 text-sm mt-1 leading-relaxed">{{ __('Manage system courses.') }}</p>
                        </div>
                        <div class="pt-4 flex items-center text-yellow-600 text-sm font-bold">
                            <span>{{ __('View Courses') }}</span>
                            <i class="fas fa-chevron-right ml-2 group-hover:translate-x-1 transition-transform"></i>
                        </div>
                    </div>
                </div>
            </a>

            <!-- Academic Year Card -->
            <a href="{{ route('admin.academic-years.index') }}" class="group block">
                <div class="h-full bg-white rounded-3xl shadow-lg shadow-gray-200/40 border-2 border-transparent hover:border-emerald-100 transition-all duration-300 transform hover:-translate-y-2 p-8 overflow-hidden relative">
                    <div class="absolute -right-4 -bottom-4 w-32 h-32 bg-emerald-50 rounded-full group-hover:scale-150 transition-transform duration-500 opacity-50"></div>
                    <div class="relative z-10 space-y-4">
                        <div class="w-14 h-14 rounded-2xl bg-emerald-600 shadow-lg shadow-emerald-200 flex items-center justify-center text-white text-2xl group-hover:scale-110 transition-transform duration-300">
                            <i class="fas fa-calendar-check"></i>
                        </div>
                        <div>
                            <h3 class="text-xl font-bold text-gray-900 tracking-tight">{{ __('Academic Year') }}</h3>
                            <p class="text-gray-500 text-sm mt-1 leading-relaxed">{{ __('Manage system academic years, BE and AD conversions.') }}</p>
                        </div>
                        <div class="pt-4 flex items-center text-emerald-600 text-sm font-bold">
                            <span>{{ __('Manage Years') }}</span>
                            <i class="fas fa-chevron-right ml-2 group-hover:translate-x-1 transition-transform"></i>
                        </div>
                    </div>
                </div>
            </a>

            <!-- Semester Card -->
            <a href="{{ route('admin.semesters.index') }}" class="group block">
                <div class="h-full bg-white rounded-3xl shadow-lg shadow-gray-200/40 border-2 border-transparent hover:border-indigo-100 transition-all duration-300 transform hover:-translate-y-2 p-8 overflow-hidden relative">
                    <div class="absolute -right-4 -bottom-4 w-32 h-32 bg-indigo-50 rounded-full group-hover:scale-150 transition-transform duration-500 opacity-50"></div>
                    <div class="relative z-10 space-y-4">
                        <div class="w-14 h-14 rounded-2xl bg-indigo-600 shadow-lg shadow-indigo-200 flex items-center justify-center text-white text-2xl group-hover:scale-110 transition-transform duration-300">
                            <i class="fas fa-list-ol"></i>
                        </div>
                        <div>
                            <h3 class="text-xl font-bold text-gray-900 tracking-tight">{{ __('Semester Management') }}</h3>
                            <p class="text-gray-500 text-sm mt-1 leading-relaxed">{{ __('Manage system semesters and academic terms.') }}</p>
                        </div>
                        <div class="pt-4 flex items-center text-indigo-600 text-sm font-bold">
                            <span>{{ __('View Semesters') }}</span>
                            <i class="fas fa-chevron-right ml-2 group-hover:translate-x-1 transition-transform"></i>
                        </div>
                    </div>
                </div>
            </a>

            <!-- Roles Card -->
            <a href="{{ route('admin.roles-permissions') }}" class="group block">
                <div class="h-full bg-white rounded-3xl shadow-lg shadow-gray-200/40 border-2 border-transparent hover:border-indigo-100 transition-all duration-300 transform hover:-translate-y-2 p-8 overflow-hidden relative">
                    <div class="absolute -right-4 -bottom-4 w-32 h-32 bg-indigo-50 rounded-full group-hover:scale-150 transition-transform duration-500 opacity-50"></div>
                    <div class="relative z-10 space-y-4">
                        <div class="w-14 h-14 rounded-2xl bg-indigo-600 shadow-lg shadow-indigo-200 flex items-center justify-center text-white text-2xl group-hover:scale-110 transition-transform duration-300">
                            <i class="fas fa-user-shield"></i>
                        </div>
                        <div>
                            <h3 class="text-xl font-bold text-gray-900 tracking-tight">{{ __('Roles Management') }}</h3>
                            <p class="text-gray-500 text-sm mt-1 leading-relaxed">{{ __('Control system roles, user permissions, and security groupings.') }}</p>
                        </div>
                        <div class="pt-4 flex items-center text-indigo-600 text-sm font-bold">
                            <span>{{ __('Manage Roles') }}</span>
                            <i class="fas fa-chevron-right ml-2 group-hover:translate-x-1 transition-transform"></i>
                        </div>
                    </div>
                </div>
            </a>

            <!-- Permissions Card -->
            <a href="{{ route('admin.permissions') }}" class="group block">
                <div class="h-full bg-white rounded-3xl shadow-lg shadow-gray-200/40 border-2 border-transparent hover:border-emerald-100 transition-all duration-300 transform hover:-translate-y-2 p-8 overflow-hidden relative">
                    <div class="absolute -right-4 -bottom-4 w-32 h-32 bg-emerald-50 rounded-full group-hover:scale-150 transition-transform duration-500 opacity-50"></div>
                    <div class="relative z-10 space-y-4">
                        <div class="w-14 h-14 rounded-2xl bg-emerald-600 shadow-lg shadow-emerald-200 flex items-center justify-center text-white text-2xl group-hover:scale-110 transition-transform duration-300">
                            <i class="fas fa-key"></i>
                        </div>
                        <div>
                            <h3 class="text-xl font-bold text-gray-900 tracking-tight">{{ __('Permissions Center') }}</h3>
                            <p class="text-gray-500 text-sm mt-1 leading-relaxed">{{ __('Define granular system capabilities and secure access points.') }}</p>
                        </div>
                        <div class="pt-4 flex items-center text-emerald-600 text-sm font-bold">
                            <span>{{ __('Configure Access') }}</span>
                            <i class="fas fa-chevron-right ml-2 group-hover:translate-x-1 transition-transform"></i>
                        </div>
                    </div>
                </div>
            </a>

            <!-- Permission Types Card -->
            <a href="{{ route('admin.permission-types') }}" class="group block">
                <div class="h-full bg-white rounded-3xl shadow-lg shadow-gray-200/40 border-2 border-transparent hover:border-teal-100 transition-all duration-300 transform hover:-translate-y-2 p-8 overflow-hidden relative">
                    <div class="absolute -right-4 -bottom-4 w-32 h-32 bg-teal-50 rounded-full group-hover:scale-150 transition-transform duration-500 opacity-50"></div>
                    <div class="relative z-10 space-y-4">
                        <div class="w-14 h-14 rounded-2xl bg-teal-600 shadow-lg shadow-teal-200 flex items-center justify-center text-white text-2xl group-hover:scale-110 transition-transform duration-300">
                            <i class="fas fa-cubes"></i>
                        </div>
                        <div>
                            <h3 class="text-xl font-bold text-gray-900 tracking-tight">{{ __('Permission  Category') }}</h3>
                            <p class="text-gray-500 text-sm mt-1 leading-relaxed">{{ __('Organize your permissions into logical categories and grouping types.') }}</p>
                        </div>
                        <div class="pt-4 flex items-center text-teal-600 text-sm font-bold">
                            <span>{{ __('View Categories') }}</span>
                            <i class="fas fa-chevron-right ml-2 group-hover:translate-x-1 transition-transform"></i>
                        </div>
                    </div>
                </div>
            </a>

            @endhasanyrole

            @role('SuperAdmin')
            <!-- System Settings Card -->
            <a href="{{ route('admin.settings.index') }}" class="group block">
                <div class="h-full bg-white rounded-3xl shadow-lg shadow-gray-200/40 border-2 border-transparent hover:border-slate-100 transition-all duration-300 transform hover:-translate-y-2 p-8 overflow-hidden relative">
                    <div class="absolute -right-4 -bottom-4 w-32 h-32 bg-slate-50 rounded-full group-hover:scale-150 transition-transform duration-500 opacity-50"></div>
                    <div class="relative z-10 space-y-4">
                        <div class="w-14 h-14 rounded-2xl bg-slate-600 shadow-lg shadow-slate-200 flex items-center justify-center text-white text-2xl group-hover:scale-110 transition-transform duration-300">
                            <i class="fas fa-cogs"></i>
                        </div>
                        <div>
                            <h3 class="text-xl font-bold text-gray-900 tracking-tight">{{ __('App Settings') }}</h3>
                            <p class="text-gray-500 text-sm mt-1 leading-relaxed">{{ __('Configure global application identity, name, and branding logo.') }}</p>
                        </div>
                        <div class="pt-4 flex items-center text-slate-600 text-sm font-bold">
                            <span>{{ __('Manage Settings') }}</span>
                            <i class="fas fa-chevron-right ml-2 group-hover:translate-x-1 transition-transform"></i>
                        </div>
                    </div>
                </div>
            </a>
            @endrole

            <!-- Logout Container -->
            <div class="lg:col-span-3 mt-12 flex justify-center">
                <form id="logout-form-home" action="{{ route('logout') }}" method="POST" class="relative group">
                    @csrf
                    <div class="absolute inset-0 bg-rose-200 rounded-2xl blur group-hover:blur-lg transition-all opacity-20"></div>
                    <button type="submit"
                            class="relative flex items-center px-12 py-4 bg-white border-2 border-rose-50 rounded-2xl text-rose-600 font-bold hover:bg-rose-50 hover:border-rose-100 transition-all duration-300 transform active:scale-95 shadow-lg shadow-gray-100">
                        <i class="fas fa-sign-out-alt mr-3"></i>
                        {{ __('Logout') }}
                    </button>
                </form>
            </div>

        </div>

        <!-- System Status Footer -->
        <div class="pt-12 border-t border-gray-100 flex flex-col md:flex-row items-center justify-between gap-4 text-xs font-bold text-gray-400 uppercase tracking-widest">
            <div class="flex items-center">
                <span class="w-2 h-2 rounded-full bg-emerald-500 mr-2 shadow-sm shadow-emerald-200 animate-pulse"></span>
                {{ __('All Systems Operational') }}
            </div>
            <div class="flex items-center space-x-6">
                <span class="hover:text-gray-600 cursor-default transition-colors">{{ __('Premium Admin Console') }}</span>
                <span class="w-1 h-1 rounded-full bg-gray-300"></span>
                <span class="hover:text-gray-600 cursor-default transition-colors">v2.0.4</span>
            </div>
        </div>

    </div>
</div>
@endsection
