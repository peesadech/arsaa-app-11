@php
    $setting = \App\Models\Setting::first() ?? new \App\Models\Setting(['app_name' => 'Laravel', 'theme' => 'light']);
    $theme = $setting->theme ?? 'light';
@endphp

<style>
    .user-dropdown-menu {
        opacity: 0;
        visibility: hidden;
        transform: translateY(-10px);
        transition: all 0.2s cubic-bezier(0.16, 1, 0.3, 1);
    }
    .user-dropdown-group:hover .user-dropdown-menu {
        opacity: 1;
        visibility: visible;
        transform: translateY(0);
    }
    .user-dropdown-menu a:hover {
        text-decoration: none !important;
    }
</style>

<!-- User Profile Dropdown Component -->
<div class="relative user-dropdown-group">
    <div class="flex items-center space-x-2 focus:outline-none">
        <span class="hidden md:block text-sm font-bold {{ $theme === 'dark' ? 'text-slate-300' : 'text-slate-600' }} hover:text-indigo-600 transition-colors">{{ Auth::user()->name }}</span>
        <div class="relative">
            <div class="w-10 h-10 rounded-full bg-slate-200 border-2 border-white shadow-sm overflow-hidden">
                <img src="{{ Auth::user()->image_path ? asset(Auth::user()->image_path) : 'https://ui-avatars.com/api/?name=' . urlencode(Auth::user()->name) . '&background=6366f1&color=fff' }}" alt="User Avatar" class="w-full h-full object-cover">
            </div>
            <div class="absolute bottom-0 right-0 w-4 h-4 bg-[#242526] rounded-full flex items-center justify-center border-2 border-white">
                <i class="fas fa-chevron-down text-[8px] text-gray-400"></i>
            </div>
        </div>
    </div>

    <!-- Dropdown Menu -->
    <div class="user-dropdown-menu absolute right-0 mt-2 w-96 {{ $theme === 'dark' ? 'bg-[#242526] text-white' : 'bg-white text-slate-800' }} rounded-xl shadow-2xl ring-1 ring-black ring-opacity-10 overflow-hidden z-50">
        <div class="p-4">
            <!-- Profile Section Card -->
            <a href="{{ route('profile.index') }}" class="block p-2 rounded-xl {{ $theme === 'dark' ? 'hover:bg-[#3a3b3c]' : 'hover:bg-gray-50' }} transition-colors cursor-pointer shadow-lg mb-4 border {{ $theme === 'dark' ? 'border-gray-700/50' : 'border-gray-100' }}">
                <div class="flex items-center space-x-3">
                    <div class="w-12 h-12 rounded-full overflow-hidden border-2 border-white shadow-sm">
                        <img src="{{ Auth::user()->image_path ? asset(Auth::user()->image_path) : 'https://ui-avatars.com/api/?name=' . urlencode(Auth::user()->name) . '&background=6366f1&color=fff' }}" class="w-full h-full object-cover">
                    </div>
                    <div class="flex flex-col">
                        <span class="font-bold text-lg leading-tight">{{ Auth::user()->name }}</span>
                        <span class="text-xs text-indigo-400 font-medium">Update Profile</span>
                    </div>
                </div>
            </a>

            <!-- Management Section -->
            <div class="space-y-1">
                @hasanyrole('admin|SuperAdmin')
                <a href="{{ route('admin.users.index') }}" class="flex items-center justify-between p-2 rounded-lg {{ $theme === 'dark' ? 'hover:bg-[#3a3b3c]' : 'hover:bg-gray-50' }} transition-colors group">
                    <div class="flex items-center space-x-3">
                        <div class="w-10 h-10 rounded-full {{ $theme === 'dark' ? 'bg-zinc-700' : 'bg-gray-100' }} flex items-center justify-center text-xl">
                            <i class="fas fa-users text-base"></i>
                        </div>
                        <span class="font-bold text-sm">User Management</span>
                    </div>
                </a>

                <a href="{{ route('admin.grades.index') }}" class="flex items-center justify-between p-2 rounded-lg {{ $theme === 'dark' ? 'hover:bg-[#3a3b3c]' : 'hover:bg-gray-50' }} transition-colors group">
                    <div class="flex items-center space-x-3">
                        <div class="w-10 h-10 rounded-full {{ $theme === 'dark' ? 'bg-zinc-700' : 'bg-gray-100' }} flex items-center justify-center text-xl">
                            <i class="fas fa-layer-group text-base"></i>
                        </div>
                        <span class="font-bold text-sm">Grade Management</span>
                    </div>
                </a>

                <a href="{{ route('admin.classrooms.index') }}" class="flex items-center justify-between p-2 rounded-lg {{ $theme === 'dark' ? 'hover:bg-[#3a3b3c]' : 'hover:bg-gray-50' }} transition-colors group">
                    <div class="flex items-center space-x-3">
                        <div class="w-10 h-10 rounded-full {{ $theme === 'dark' ? 'bg-zinc-700' : 'bg-gray-100' }} flex items-center justify-center text-xl">
                            <i class="fas fa-chalkboard text-base"></i>
                        </div>
                        <span class="font-bold text-sm">Classroom Management</span>
                    </div>
                </a>

                <a href="{{ route('admin.courses.index') }}" class="flex items-center justify-between p-2 rounded-lg {{ $theme === 'dark' ? 'hover:bg-[#3a3b3c]' : 'hover:bg-gray-50' }} transition-colors group">
                    <div class="flex items-center space-x-3">
                        <div class="w-10 h-10 rounded-full {{ $theme === 'dark' ? 'bg-zinc-700' : 'bg-gray-100' }} flex items-center justify-center text-xl">
                            <i class="fas fa-book text-base"></i>
                        </div>
                        <span class="font-bold text-sm">Course Management</span>
                    </div>
                </a>

                <a href="{{ route('admin.academic-years.index') }}" class="flex items-center justify-between p-2 rounded-lg {{ $theme === 'dark' ? 'hover:bg-[#3a3b3c]' : 'hover:bg-gray-50' }} transition-colors group">
                    <div class="flex items-center space-x-3">
                        <div class="w-10 h-10 rounded-full {{ $theme === 'dark' ? 'bg-zinc-700' : 'bg-gray-100' }} flex items-center justify-center text-xl">
                            <i class="fas fa-calendar-alt text-base"></i>
                        </div>
                        <span class="font-bold text-sm">Academic Year</span>
                    </div>
                </a>

                <a href="{{ route('admin.semesters.index') }}" class="flex items-center justify-between p-2 rounded-lg {{ $theme === 'dark' ? 'hover:bg-[#3a3b3c]' : 'hover:bg-gray-50' }} transition-colors group">
                    <div class="flex items-center space-x-3">
                        <div class="w-10 h-10 rounded-full {{ $theme === 'dark' ? 'bg-zinc-700' : 'bg-gray-100' }} flex items-center justify-center text-xl">
                            <i class="fas fa-list-ol text-base"></i>
                        </div>
                        <span class="font-bold text-sm">Semester</span>
                    </div>
                </a>

                <a href="{{ route('admin.roles-permissions') }}" class="flex items-center justify-between p-2 rounded-lg {{ $theme === 'dark' ? 'hover:bg-[#3a3b3c]' : 'hover:bg-gray-50' }} transition-colors group">
                    <div class="flex items-center space-x-3">
                        <div class="w-10 h-10 rounded-full {{ $theme === 'dark' ? 'bg-zinc-700' : 'bg-gray-100' }} flex items-center justify-center text-xl">
                            <i class="fas fa-user-shield text-base"></i>
                        </div>
                        <span class="font-bold text-sm">Role Management</span>
                    </div>
                    <!-- <i class="fas fa-chevron-right text-gray-500 text-sm"></i> -->
                </a>

                <a href="{{ route('admin.permissions') }}" class="flex items-center justify-between p-2 rounded-lg {{ $theme === 'dark' ? 'hover:bg-[#3a3b3c]' : 'hover:bg-gray-50' }} transition-colors group">
                    <div class="flex items-center space-x-3">
                        <div class="w-10 h-10 rounded-full {{ $theme === 'dark' ? 'bg-zinc-700' : 'bg-gray-100' }} flex items-center justify-center text-xl">
                            <i class="fas fa-key text-base"></i>
                        </div>
                        <span class="font-bold text-sm">Permissions</span>
                    </div>
                    <!-- <i class="fas fa-chevron-right text-gray-500 text-sm"></i> -->
                </a>

                <a href="{{ route('admin.permission-types') }}" class="flex items-center justify-between p-2 rounded-lg {{ $theme === 'dark' ? 'hover:bg-[#3a3b3c]' : 'hover:bg-gray-50' }} transition-colors group">
                    <div class="flex items-center space-x-3">
                        <div class="w-10 h-10 rounded-full {{ $theme === 'dark' ? 'bg-zinc-700' : 'bg-gray-100' }} flex items-center justify-center text-xl">
                            <i class="fas fa-cubes text-base"></i>
                        </div>
                        <span class="font-bold text-sm">Permission Type</span>
                    </div>
                    <!-- <i class="fas fa-chevron-right text-gray-500 text-sm"></i> -->
                </a>
                @endhasanyrole

                @role('SuperAdmin')
                <a href="{{ route('admin.settings.index') }}" class="flex items-center justify-between p-2 rounded-lg {{ $theme === 'dark' ? 'hover:bg-[#3a3b3c]' : 'hover:bg-gray-50' }} transition-colors group">
                    <div class="flex items-center space-x-3">
                        <div class="w-10 h-10 rounded-full {{ $theme === 'dark' ? 'bg-zinc-700' : 'bg-gray-100' }} flex items-center justify-center text-xl">
                            <i class="fas fa-cog text-base"></i>
                        </div>
                        <span class="font-bold text-sm">App Settings</span>
                    </div>
                </a>
                @endrole

                <a 
                   href=""
                   onclick="event.preventDefault(); document.getElementById('logout-form-dropdown').submit();"
                   class="flex items-center justify-between p-2 rounded-lg {{ $theme === 'dark' ? 'hover:bg-[#3a3b3c]' : 'hover:bg-gray-50' }} transition-colors group cursor-pointer"> 
                    <div class="flex items-center space-x-3">
                        <div class="w-10 h-10 rounded-full {{ $theme === 'dark' ? 'bg-zinc-700' : 'bg-gray-100' }} flex items-center justify-center text-xl">
                            <i class="fas fa-sign-out-alt text-base"></i>
                        </div>
                        <span class="font-bold text-sm">Log Out</span>
                    </div>
                </a>

                <form id="logout-form-dropdown" action="{{ route('logout') }}" method="POST" style="display: none;">
                    @csrf
                </form>
            </div>
        </div>

        <!-- Meta Footer Section -->
        <div class="p-4 text-[11px] text-gray-500 pt-0">
            <div class="flex flex-wrap gap-x-2 gap-y-1 px-2">
                <a href="#" class="hover:underline">Privacy</a> · 
                <a href="#" class="hover:underline">Terms</a> · 
                <a href="#" class="hover:underline">Advertising</a> · 
                <a href="#" class="hover:underline">Ad Choices</a> · 
                <a href="#" class="hover:underline">Cookies</a> · 
                <a href="#" class="hover:underline">More</a> · 
                <span class="cursor-default">{{ $setting->app_name }} &copy; {{ date('Y') }}</span>
            </div>
        </div>
    </div>
</div>

<script>
    document.getElementById('logout-form-dropdown').addEventListener('submit', function() {
        localStorage.removeItem('access_token');
    });
</script>
