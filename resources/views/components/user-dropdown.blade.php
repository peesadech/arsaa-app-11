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
    .user-dropdown-menu.open {
        opacity: 1;
        visibility: visible;
        transform: translateY(0);
    }
    .user-dropdown-menu a:hover,
    .user-dropdown-menu button:hover {
        text-decoration: none !important;
    }
    .setting-submenu {
        max-height: 0;
        overflow: hidden;
        transition: max-height 0.25s ease-out;
    }
    .setting-submenu.open {
        max-height: 1000px;
        overflow: visible;
        transition: max-height 0.35s ease-in;
    }
</style>

<!-- User Profile Dropdown Component -->
<div class="relative user-dropdown-group">
    <div onclick="toggleUserDropdown(event)" class="flex items-center space-x-2 focus:outline-none cursor-pointer">
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
    <div class="user-dropdown-menu absolute right-0 mt-2 w-96 overflow-y-auto {{ $theme === 'dark' ? 'bg-[#242526] text-white' : 'bg-white text-slate-800' }} rounded-xl shadow-2xl ring-1 ring-black ring-opacity-10 z-50" style="max-height: 80vh;">
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
                <!-- Setting toggle button -->
                <a href="javascript:void(0)" onclick="document.getElementById('setting-submenu').classList.toggle('open'); this.querySelector('.setting-arrow').classList.toggle('rotate-180')" class="flex items-center justify-between p-2 rounded-lg {{ $theme === 'dark' ? 'hover:bg-[#3a3b3c]' : 'hover:bg-gray-50' }} transition-colors group cursor-pointer">
                    <div class="flex items-center space-x-3">
                        <div class="w-10 h-10 rounded-full {{ $theme === 'dark' ? 'bg-zinc-700' : 'bg-gray-100' }} flex items-center justify-center text-xl">
                            <i class="fas fa-cogs text-base"></i>
                        </div>
                        <span class="font-bold text-sm">Setting</span>
                    </div>
                    <i class="fas fa-chevron-down setting-arrow text-gray-400 text-xs transition-transform duration-200"></i>
                </a>

                <!-- Setting Sub-menu -->
                <div id="setting-submenu" class="setting-submenu ml-5 border-l-2 {{ $theme === 'dark' ? 'border-zinc-700' : 'border-gray-200' }} pl-2 space-y-0.5">
                    <a href="{{ route('admin.users.index') }}" class="flex items-center space-x-3 p-2 rounded-lg {{ $theme === 'dark' ? 'hover:bg-[#3a3b3c]' : 'hover:bg-gray-50' }} transition-colors">
                        <div class="w-8 h-8 rounded-full {{ $theme === 'dark' ? 'bg-zinc-700' : 'bg-gray-100' }} flex items-center justify-center">
                            <i class="fas fa-users text-xs text-gray-400"></i>
                        </div>
                        <span class="font-medium text-sm">User Management</span>
                    </a>
                    <a href="{{ route('admin.grades.index') }}" class="flex items-center space-x-3 p-2 rounded-lg {{ $theme === 'dark' ? 'hover:bg-[#3a3b3c]' : 'hover:bg-gray-50' }} transition-colors">
                        <div class="w-8 h-8 rounded-full {{ $theme === 'dark' ? 'bg-zinc-700' : 'bg-gray-100' }} flex items-center justify-center">
                            <i class="fas fa-layer-group text-xs text-gray-400"></i>
                        </div>
                        <span class="font-medium text-sm">Grade Management</span>
                    </a>
                    <a href="{{ route('admin.classrooms.index') }}" class="flex items-center space-x-3 p-2 rounded-lg {{ $theme === 'dark' ? 'hover:bg-[#3a3b3c]' : 'hover:bg-gray-50' }} transition-colors">
                        <div class="w-8 h-8 rounded-full {{ $theme === 'dark' ? 'bg-zinc-700' : 'bg-gray-100' }} flex items-center justify-center">
                            <i class="fas fa-chalkboard text-xs text-gray-400"></i>
                        </div>
                        <span class="font-medium text-sm">Classroom Management</span>
                    </a>
                    <a href="{{ route('admin.courses.index') }}" class="flex items-center space-x-3 p-2 rounded-lg {{ $theme === 'dark' ? 'hover:bg-[#3a3b3c]' : 'hover:bg-gray-50' }} transition-colors">
                        <div class="w-8 h-8 rounded-full {{ $theme === 'dark' ? 'bg-zinc-700' : 'bg-gray-100' }} flex items-center justify-center">
                            <i class="fas fa-book text-xs text-gray-400"></i>
                        </div>
                        <span class="font-medium text-sm">Course Management</span>
                    </a>
                    <a href="{{ route('admin.academic-years.index') }}" class="flex items-center space-x-3 p-2 rounded-lg {{ $theme === 'dark' ? 'hover:bg-[#3a3b3c]' : 'hover:bg-gray-50' }} transition-colors">
                        <div class="w-8 h-8 rounded-full {{ $theme === 'dark' ? 'bg-zinc-700' : 'bg-gray-100' }} flex items-center justify-center">
                            <i class="fas fa-calendar-alt text-xs text-gray-400"></i>
                        </div>
                        <span class="font-medium text-sm">Academic Year</span>
                    </a>
                    <a href="{{ route('admin.semesters.index') }}" class="flex items-center space-x-3 p-2 rounded-lg {{ $theme === 'dark' ? 'hover:bg-[#3a3b3c]' : 'hover:bg-gray-50' }} transition-colors">
                        <div class="w-8 h-8 rounded-full {{ $theme === 'dark' ? 'bg-zinc-700' : 'bg-gray-100' }} flex items-center justify-center">
                            <i class="fas fa-list-ol text-xs text-gray-400"></i>
                        </div>
                        <span class="font-medium text-sm">Semester</span>
                    </a>
                    <a href="{{ route('admin.roles-permissions') }}" class="flex items-center space-x-3 p-2 rounded-lg {{ $theme === 'dark' ? 'hover:bg-[#3a3b3c]' : 'hover:bg-gray-50' }} transition-colors">
                        <div class="w-8 h-8 rounded-full {{ $theme === 'dark' ? 'bg-zinc-700' : 'bg-gray-100' }} flex items-center justify-center">
                            <i class="fas fa-user-shield text-xs text-gray-400"></i>
                        </div>
                        <span class="font-medium text-sm">Role Management</span>
                    </a>
                    <a href="{{ route('admin.user-assignments') }}" class="flex items-center space-x-3 p-2 rounded-lg {{ $theme === 'dark' ? 'hover:bg-[#3a3b3c]' : 'hover:bg-gray-50' }} transition-colors">
                        <div class="w-8 h-8 rounded-full {{ $theme === 'dark' ? 'bg-zinc-700' : 'bg-gray-100' }} flex items-center justify-center">
                            <i class="fas fa-user-tag text-xs text-gray-400"></i>
                        </div>
                        <span class="font-medium text-sm">User Assignments</span>
                    </a>
                    <a href="{{ route('admin.permissions') }}" class="flex items-center space-x-3 p-2 rounded-lg {{ $theme === 'dark' ? 'hover:bg-[#3a3b3c]' : 'hover:bg-gray-50' }} transition-colors">
                        <div class="w-8 h-8 rounded-full {{ $theme === 'dark' ? 'bg-zinc-700' : 'bg-gray-100' }} flex items-center justify-center">
                            <i class="fas fa-key text-xs text-gray-400"></i>
                        </div>
                        <span class="font-medium text-sm">Permissions</span>
                    </a>
                    <a href="{{ route('admin.permission-types') }}" class="flex items-center space-x-3 p-2 rounded-lg {{ $theme === 'dark' ? 'hover:bg-[#3a3b3c]' : 'hover:bg-gray-50' }} transition-colors">
                        <div class="w-8 h-8 rounded-full {{ $theme === 'dark' ? 'bg-zinc-700' : 'bg-gray-100' }} flex items-center justify-center">
                            <i class="fas fa-cubes text-xs text-gray-400"></i>
                        </div>
                        <span class="font-medium text-sm">Permission Type</span>
                    </a>
                    <a href="javascript:void(0)" onclick="document.getElementById('academicYearGlobalModal').style.display='flex'" class="flex items-center space-x-3 p-2 rounded-lg {{ $theme === 'dark' ? 'hover:bg-[#3a3b3c]' : 'hover:bg-gray-50' }} transition-colors">
                        <div class="w-8 h-8 rounded-full {{ $theme === 'dark' ? 'bg-zinc-700' : 'bg-gray-100' }} flex items-center justify-center">
                            <i class="fas fa-graduation-cap text-xs text-gray-400"></i>
                        </div>
                        <span class="font-medium text-sm">Academic Year & Semester</span>
                    </a>
                    <a href="{{ route('admin.settings.index') }}" class="flex items-center space-x-3 p-2 rounded-lg {{ $theme === 'dark' ? 'hover:bg-[#3a3b3c]' : 'hover:bg-gray-50' }} transition-colors">
                        <div class="w-8 h-8 rounded-full {{ $theme === 'dark' ? 'bg-zinc-700' : 'bg-gray-100' }} flex items-center justify-center">
                            <i class="fas fa-cog text-xs text-gray-400"></i>
                        </div>
                        <span class="font-medium text-sm">General Settings</span>
                    </a>
                </div>
                @endhasanyrole

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

    function toggleUserDropdown(e) {
        e.stopPropagation();
        var menu = document.querySelector('.user-dropdown-menu');
        menu.classList.toggle('open');
    }

    // Close dropdown when clicking outside
    document.addEventListener('click', function(e) {
        var dropdown = document.querySelector('.user-dropdown-group');
        var menu = document.querySelector('.user-dropdown-menu');
        if (dropdown && menu && !dropdown.contains(e.target)) {
            menu.classList.remove('open');
        }
    });
</script>
