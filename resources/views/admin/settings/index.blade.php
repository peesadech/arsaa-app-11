@extends('layouts.app')

@php
    $title = 'General Settings';
    $subtitle = 'Application Configuration';
    $gradientClass = 'bg-gradient-to-r from-slate-700 via-gray-700 to-zinc-700';
    $blurClass = 'bg-slate-500/20';
    $iconBgClass = 'bg-slate-50 border-slate-100';
    $iconClass = 'fa-cogs text-slate-600 rotate-3';
    $cardTitle = 'App Identity';
    $cardDesc = 'Manage key application details including name and branding logo.';
    
    $focusRing = 'focus:border-slate-400';
    $focusText = 'group-focus-within:text-slate-500';
    $hoverBorder = 'group-hover:border-slate-300';
    $hoverBg = 'group-hover:bg-slate-50/30';
    $hoverText = 'group-hover:text-slate-500';
    $changeIconColor = 'text-slate-500';
    $checkBg = 'bg-slate-600';
    
    $btnClass = 'bg-slate-700 text-white hover:bg-slate-800 shadow-slate-200';
    $btnText = 'Save Configuration';
    $btnIcon = 'fa-save';

    $logoUrl = $setting->app_logo ? asset('storage/' . $setting->app_logo) : null;
@endphp

@section('content')
<div class="min-h-screen bg-gray-50 py-12 px-4 sm:px-6 lg:px-8">
    <div class="max-w-xl mx-auto">
        <!-- Breadcrumb / Header -->
        <div class="flex items-center space-x-4 mb-8">
            <a href="{{ route('admin.dashboard') }}" 
               class="group flex items-center justify-center w-10 h-10 rounded-full bg-white shadow-sm border border-gray-200 text-gray-500 hover:text-indigo-600 hover:border-indigo-300 transition-all duration-200">
                <i class="fas fa-arrow-left group-hover:-translate-x-0.5 transition-transform"></i>
            </a>
            <div>
                <h1 class="text-2xl font-extrabold text-gray-900 tracking-tight">{{ $title }}</h1>
                <p class="text-sm text-gray-500 font-medium px-1">{{ $subtitle }}</p>
            </div>
        </div>

        @if ($errors->any())
            <div class="mb-8 p-4 bg-red-50 border-l-4 border-red-500 rounded-r-2xl shadow-md transform animate-fade-in">
                <div class="flex items-center mb-2">
                    <i class="fas fa-exclamation-triangle text-red-500 mr-2"></i>
                    <h3 class="text-sm font-bold text-red-800">Please correct the following errors:</h3>
                </div>
                <ul class="list-disc list-inside space-y-1">
                    @foreach ($errors->all() as $error)
                        <li class="text-xs text-red-700 font-medium">{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <!-- Main Card -->
        <div class="bg-white rounded-3xl shadow-xl shadow-gray-200/50 border border-gray-100 overflow-hidden transform transition-all">
            <!-- Decorative Top Border -->
            <div class="h-2 {{ $gradientClass }}"></div>
            
            <div class="p-8 sm:p-10">
                <!-- Visual Identity Section -->
                <div class="flex flex-col items-center text-center mb-10">
                    <div class="relative">
                        <div class="absolute inset-0 {{ $blurClass }} blur-2xl rounded-full"></div>
                        <div class="relative w-20 h-20 rounded-2xl flex items-center justify-center mb-4 transform border {{ $iconBgClass }}">
                            <i class="fas {{ $iconClass }} text-3xl"></i>
                        </div>
                    </div>
                    <h2 class="text-xl font-bold text-gray-800 mb-2">{{ $cardTitle }}</h2>
                    <p class="text-sm text-gray-500 max-w-xs mx-auto text-pretty">
                        {{ $cardDesc }}
                    </p>
                </div>

                <!-- Form -->
                <form action="{{ route('admin.settings.update') }}" method="POST" enctype="multipart/form-data" class="space-y-8" id="settingsForm">
                    @csrf
                    
                    <div class="space-y-2">
                        <label for="app_name" class="block text-xs font-bold text-gray-400 uppercase tracking-widest px-1">
                            Application Name
                        </label>
                        <div class="group relative">
                            <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none text-gray-400 {{ $focusText }} transition-colors">
                                <i class="fas fa-font text-sm"></i>
                            </div>
                            <input
                                type="text"
                                id="app_name"
                                name="app_name"
                                class="block w-full pl-10 pr-4 py-4 bg-gray-50 border-2 border-gray-100 rounded-2xl text-gray-900 placeholder-gray-400 focus:outline-none focus:ring-0 {{ $focusRing }} focus:bg-white transition-all duration-200 @error('app_name') border-red-300 bg-red-50 @enderror"
                                placeholder="My Amazing App"
                                value="{{ old('app_name', $setting->app_name) }}"
                                required
                                autofocus
                            />
                        </div>
                        @error('app_name')
                            <p class="text-xs font-semibold text-red-500 mt-2 px-1 flex items-center">
                                <span class="w-1 h-1 bg-red-500 rounded-full mr-2"></span>
                                {{ $message }}
                            </p>
                        @enderror
                    </div>

                    <div class="space-y-4">
                        <label class="block text-xs font-bold text-gray-400 uppercase tracking-widest px-1">
                            Application Theme
                        </label>
                        <div class="grid grid-cols-2 gap-4">
                            <!-- Light Theme Option -->
                            <label class="relative cursor-pointer group">
                                <input type="radio" name="theme" value="light" class="peer sr-only" {{ old('theme', $setting->theme) == 'light' ? 'checked' : '' }}>
                                <div class="p-4 bg-white border-2 border-gray-100 rounded-2xl flex flex-col items-center transition-all duration-200 peer-checked:border-slate-600 peer-checked:ring-4 peer-checked:ring-slate-600/10 hover:border-gray-200 shadow-sm">
                                    <div class="w-12 h-12 rounded-full bg-orange-50 flex items-center justify-center text-orange-500 mb-3 group-hover:scale-110 transition-transform">
                                        <i class="fas fa-sun text-xl"></i>
                                    </div>
                                    <span class="text-xs font-black text-gray-700 uppercase tracking-widest">Light Mode</span>
                                </div>
                                <div class="absolute -top-2 -right-2 hidden peer-checked:block">
                                    <div class="bg-slate-600 text-white w-6 h-6 rounded-full flex items-center justify-center shadow-lg border-2 border-white ring-2 ring-slate-600/20">
                                        <i class="fas fa-check text-[10px]"></i>
                                    </div>
                                </div>
                            </label>

                            <!-- Dark Theme Option -->
                            <label class="relative cursor-pointer group">
                                <input type="radio" name="theme" value="dark" class="peer sr-only" {{ old('theme', $setting->theme) == 'dark' ? 'checked' : '' }}>
                                <div class="p-4 bg-slate-800 border-2 border-slate-700/50 rounded-2xl flex flex-col items-center transition-all duration-200 peer-checked:border-slate-400 peer-checked:ring-4 peer-checked:ring-slate-400/20 hover:border-slate-600 shadow-xl shadow-slate-900/10">
                                    <div class="w-12 h-12 rounded-full bg-slate-700 flex items-center justify-center text-slate-300 mb-3 group-hover:scale-110 transition-transform">
                                        <i class="fas fa-moon text-xl"></i>
                                    </div>
                                    <span class="text-xs font-black text-slate-100 uppercase tracking-widest">Dark Mode</span>
                                </div>
                                <div class="absolute -top-2 -right-2 hidden peer-checked:block">
                                    <div class="bg-indigo-500 text-white w-6 h-6 rounded-full flex items-center justify-center shadow-lg border-2 border-white ring-2 ring-indigo-500/20">
                                        <i class="fas fa-check text-[10px]"></i>
                                    </div>
                                </div>
                            </label>
                        </div>
                        @error('theme')
                            <p class="text-xs font-semibold text-red-500 mt-2 px-1">
                                {{ $message }}
                            </p>
                        @enderror
                    </div>

                    <!-- Registration & Google Login Toggles -->
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <!-- Registration Toggle -->
                        <div class="space-y-4">
                            <label class="block text-xs font-bold text-gray-400 uppercase tracking-widest px-1">
                                User Access
                            </label>
                            <div class="p-5 bg-gray-50 border-2 border-gray-100 rounded-3xl flex items-center justify-between group hover:border-slate-200 transition-all duration-300 h-full">
                                <div class="flex items-center space-x-4">
                                    <div class="w-12 h-12 rounded-2xl bg-white shadow-sm border border-gray-100 flex items-center justify-center text-slate-500 group-hover:scale-110 transition-transform">
                                        <i class="fas fa-user-plus text-lg"></i>
                                    </div>
                                    <div>
                                        <h4 class="text-xs font-black text-gray-800 uppercase tracking-wider">Public Register</h4>
                                        <p class="text-[9px] text-gray-400 font-bold uppercase tracking-tight mt-0.5">Register accounts</p>
                                    </div>
                                </div>
                                <label class="relative inline-flex items-center cursor-pointer">
                                    <input type="checkbox" name="registration_enabled" value="1" class="sr-only peer" {{ old('registration_enabled', $setting->registration_enabled) ? 'checked' : '' }}>
                                    <div class="w-12 h-7 bg-gray-200 peer-focus:outline-none rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[4px] after:left-[4px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-[20px] after:w-[20px] after:transition-all peer-checked:bg-slate-700 shadow-inner"></div>
                                </label>
                            </div>
                        </div>

                        <!-- Google Login Toggle -->
                        <div class="space-y-4">
                            <label class="block text-xs font-bold text-gray-400 uppercase tracking-widest px-1">
                                Google Auth
                            </label>
                            <div class="p-5 bg-gray-50 border-2 border-gray-100 rounded-3xl flex items-center justify-between group hover:border-slate-200 transition-all duration-300 h-full">
                                <div class="flex items-center space-x-4">
                                    <div class="w-12 h-12 rounded-2xl bg-white shadow-sm border border-gray-100 flex items-center justify-center text-orange-500 group-hover:scale-110 transition-transform">
                                        <i class="fab fa-google text-lg"></i>
                                    </div>
                                    <div>
                                        <h4 class="text-xs font-black text-gray-800 uppercase tracking-wider">Google Login</h4>
                                        <p class="text-[9px] text-gray-400 font-bold uppercase tracking-tight mt-0.5">OAuth 2.0 Auth</p>
                                    </div>
                                </div>
                                <label class="relative inline-flex items-center cursor-pointer">
                                    <input type="checkbox" id="google_login_enabled" name="google_login_enabled" value="1" class="sr-only peer" {{ old('google_login_enabled', $setting->google_login_enabled) ? 'checked' : '' }}>
                                    <div class="w-12 h-7 bg-gray-200 peer-focus:outline-none rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[4px] after:left-[4px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-[20px] after:w-[20px] after:transition-all peer-checked:bg-slate-700 shadow-inner"></div>
                                </label>
                            </div>
                        </div>

                        <!-- Facebook Login Toggle -->
                        <div class="space-y-4 pt-4 md:pt-0">
                            <label class="block text-xs font-bold text-gray-400 uppercase tracking-widest px-1">
                                Facebook Auth
                            </label>
                            <div class="p-5 bg-gray-50 border-2 border-gray-100 rounded-3xl flex items-center justify-between group hover:border-slate-200 transition-all duration-300 h-full">
                                <div class="flex items-center space-x-4">
                                    <div class="w-12 h-12 rounded-2xl bg-white shadow-sm border border-gray-100 flex items-center justify-center text-blue-600 group-hover:scale-110 transition-transform">
                                        <i class="fab fa-facebook text-lg"></i>
                                    </div>
                                    <div>
                                        <h4 class="text-xs font-black text-gray-800 uppercase tracking-wider">Facebook Login</h4>
                                        <p class="text-[9px] text-gray-400 font-bold uppercase tracking-tight mt-0.5">OAuth 2.0 Auth</p>
                                    </div>
                                </div>
                                <label class="relative inline-flex items-center cursor-pointer">
                                    <input type="checkbox" id="facebook_login_enabled" name="facebook_login_enabled" value="1" class="sr-only peer" {{ old('facebook_login_enabled', $setting->facebook_login_enabled) ? 'checked' : '' }}>
                                    <div class="w-12 h-7 bg-gray-200 peer-focus:outline-none rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[4px] after:left-[4px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-[20px] after:w-[20px] after:transition-all peer-checked:bg-slate-700 shadow-inner"></div>
                                </label>
                            </div>
                        </div>
                    </div>

                    <div class="space-y-2">
                        <label class="block text-xs font-bold text-gray-400 uppercase tracking-widest px-1">
                            App Logo (PNG/JPG)
                        </label>
                        <div 
                            onclick="document.getElementById('app_logo').click()"
                            class="relative group cursor-pointer"
                        >
                            <div id="uploadPlaceholder" class="{{ $logoUrl ? 'hidden' : '' }} flex flex-col items-center justify-center w-full py-12 bg-gray-50 border-2 border-dashed border-gray-200 rounded-2xl {{ $hoverBorder }} {{ $hoverBg }} transition-all duration-200">
                                <div class="w-16 h-16 rounded-2xl bg-white shadow-sm border border-gray-100 flex items-center justify-center text-gray-400 {{ $hoverText }} transition-colors mb-4">
                                    <i class="fas fa-image text-2xl"></i>
                                </div>
                                <p class="text-sm font-bold text-gray-500">Upload Logo</p>
                                <p class="text-xs text-gray-400 mt-1">Click to browse</p>
                            </div>
                            
                            <div id="previewContainer" class="{{ $logoUrl ? '' : 'hidden' }} relative w-[200px] h-[200px] mx-auto rounded-[32px] overflow-hidden border-4 border-white shadow-xl transition-all duration-300 transform group-hover:scale-105">
                                <img id="imagePreview" src="{{ $logoUrl ?? '#' }}" class="w-full h-full object-contain bg-gray-100">
                                <div class="absolute inset-0 bg-black/40 opacity-0 group-hover:opacity-100 transition-opacity flex items-center justify-center backdrop-blur-sm">
                                    <span class="px-4 py-2 bg-white rounded-xl text-[10px] font-bold text-gray-900 shadow-xl">
                                        <i class="fas fa-sync-alt mr-1 {{ $changeIconColor }}"></i> Change
                                    </span>
                                </div>
                            </div>

                            <input type="file" id="app_logo" name="app_logo" class="hidden" accept="image/*">
                        </div>
                        @error('app_logo')
                            <p class="text-xs font-semibold text-red-500 mt-2 px-1 flex items-center">
                                <span class="w-1 h-1 bg-red-500 rounded-full mr-2"></span>
                                {{ $message }}
                            </p>
                        @enderror
                        @if($logoUrl)
                        <div class="text-center mt-2">
                             <a href="#" onclick="removeLogo()" class="text-xs font-bold text-rose-500 hover:text-rose-600 transition-colors">
                                <i class="fas fa-trash-alt mr-1"></i> Remove Logo
                            </a>
                             <input type="hidden" name="remove_logo" id="remove_logo" value="0">
                        </div>
                        @endif
                    </div>

                    <!-- Google OAuth Config Section -->
                    <div id="googleConfigSection" class="{{ old('google_login_enabled', $setting->google_login_enabled) ? '' : 'hidden' }} pt-6 border-t border-gray-100 space-y-6 animate-fade-in">
                        <div class="flex items-center space-x-3 mb-2">
                            <div class="w-8 h-8 rounded-xl bg-orange-50 flex items-center justify-center text-orange-500">
                                <i class="fab fa-google text-sm"></i>
                            </div>
                            <h3 class="text-sm font-black text-gray-800 uppercase tracking-wider">Google OAuth Credentials</h3>
                        </div>

                        <!-- Google Client ID -->
                        <div class="space-y-2">
                            <label for="google_client_id" class="block text-[10px] font-bold text-gray-400 uppercase tracking-widest px-1">
                                Google Client ID
                            </label>
                            <input
                                type="text"
                                id="google_client_id"
                                name="google_client_id"
                                class="block w-full px-4 py-3 bg-gray-50 border-2 border-gray-100 rounded-xl text-gray-900 text-sm focus:outline-none focus:ring-0 {{ $focusRing }} focus:bg-white transition-all duration-200"
                                placeholder="443741823356-xxx.apps.googleusercontent.com"
                                value="{{ old('google_client_id', $setting->google_client_id) }}"
                            />
                        </div>

                        <!-- Google Client Secret -->
                        <div class="space-y-2">
                            <label for="google_client_secret" class="block text-[10px] font-bold text-gray-400 uppercase tracking-widest px-1">
                                Google Client Secret
                            </label>
                            <input
                                type="password"
                                id="google_client_secret"
                                name="google_client_secret"
                                class="block w-full px-4 py-3 bg-gray-50 border-2 border-gray-100 rounded-xl text-gray-900 text-sm focus:outline-none focus:ring-0 {{ $focusRing }} focus:bg-white transition-all duration-200"
                                placeholder="GOCSPX-SAhxxx"
                                value="{{ old('google_client_secret', $setting->google_client_secret) }}"
                            />
                        </div>

                        <!-- Google Redirect URL -->
                        <div class="space-y-2">
                            <label for="google_redirect_url" class="block text-[10px] font-bold text-gray-400 uppercase tracking-widest px-1">
                                Redirect URL (Callback)
                            </label>
                            <input
                                type="text"
                                id="google_redirect_url"
                                name="google_redirect_url"
                                class="block w-full px-4 py-3 bg-gray-50 border-2 border-gray-100 rounded-xl text-gray-900 text-sm focus:outline-none focus:ring-0 {{ $focusRing }} focus:bg-white transition-all duration-200 bg-gray-100"
                                placeholder="{{ url('/auth/google/callback') }}"
                                value="{{ old('google_redirect_url', $setting->google_redirect_url) }}"
                            />
                            <p class="text-[9px] text-gray-400 font-medium px-1">Recommended: {{ url('/auth/google/callback') }}</p>
                        </div>
                    </div>

                    <!-- Facebook OAuth Config Section -->
                    <div id="facebookConfigSection" class="{{ old('facebook_login_enabled', $setting->facebook_login_enabled) ? '' : 'hidden' }} pt-6 border-t border-gray-100 space-y-6 animate-fade-in">
                        <div class="flex items-center space-x-3 mb-2">
                            <div class="w-8 h-8 rounded-xl bg-blue-50 flex items-center justify-center text-blue-600">
                                <i class="fab fa-facebook text-sm"></i>
                            </div>
                            <h3 class="text-sm font-black text-gray-800 uppercase tracking-wider">Facebook OAuth Credentials</h3>
                        </div>

                        <!-- Facebook App ID -->
                        <div class="space-y-2">
                            <label for="facebook_client_id" class="block text-[10px] font-bold text-gray-400 uppercase tracking-widest px-1">
                                Facebook App ID
                            </label>
                            <input
                                type="text"
                                id="facebook_client_id"
                                name="facebook_client_id"
                                class="block w-full px-4 py-3 bg-gray-50 border-2 border-gray-100 rounded-xl text-gray-900 text-sm focus:outline-none focus:ring-0 {{ $focusRing }} focus:bg-white transition-all duration-200"
                                placeholder="1234567890"
                                value="{{ old('facebook_client_id', $setting->facebook_client_id) }}"
                            />
                        </div>

                        <!-- Facebook App Secret -->
                        <div class="space-y-2">
                            <label for="facebook_client_secret" class="block text-[10px] font-bold text-gray-400 uppercase tracking-widest px-1">
                                Facebook App Secret
                            </label>
                            <input
                                type="password"
                                id="facebook_client_secret"
                                name="facebook_client_secret"
                                class="block w-full px-4 py-3 bg-gray-50 border-2 border-gray-100 rounded-xl text-gray-900 text-sm focus:outline-none focus:ring-0 {{ $focusRing }} focus:bg-white transition-all duration-200"
                                placeholder="••••••••••••••••"
                                value="{{ old('facebook_client_secret', $setting->facebook_client_secret) }}"
                            />
                        </div>

                        <!-- Facebook Redirect URL -->
                        <div class="space-y-2">
                            <label for="facebook_redirect_url" class="block text-[10px] font-bold text-gray-400 uppercase tracking-widest px-1">
                                Redirect URL (Callback)
                            </label>
                            <input
                                type="text"
                                id="facebook_redirect_url"
                                name="facebook_redirect_url"
                                class="block w-full px-4 py-3 bg-gray-50 border-2 border-gray-100 rounded-xl text-gray-900 text-sm focus:outline-none focus:ring-0 {{ $focusRing }} focus:bg-white transition-all duration-200 bg-gray-100"
                                placeholder="{{ url('/auth/facebook/callback') }}"
                                value="{{ old('facebook_redirect_url', $setting->facebook_redirect_url) }}"
                            />
                            <p class="text-[9px] text-gray-400 font-medium px-1">Recommended: {{ url('/auth/facebook/callback') }}</p>
                        </div>
                    </div>

                    <!-- Actions -->
                    <div class="pt-4">
                        <button 
                            type="submit"
                            class="w-full group relative flex items-center justify-center px-8 py-4 {{ $btnClass }} font-bold rounded-2xl active:scale-95 transition-all duration-200 shadow-lg overflow-hidden"
                        >
                            <span class="relative z-10 flex items-center">
                                <i class="fas {{ $btnIcon }} mr-2 opacity-50 group-hover:opacity-100 transition-opacity"></i>
                                {{ $btnText }}
                            </span>
                            <div class="absolute inset-0 bg-gradient-to-r from-transparent via-white/10 to-transparent -translate-x-full group-hover:translate-x-full transition-transform duration-1000 ease-in-out"></div>
                        </button>
                    </div>
                </form>

                <script>
                    document.getElementById('app_logo').addEventListener('change', function(e) {
                        const file = e.target.files[0];
                        if (!file) return;

                        const reader = new FileReader();
                        reader.onload = function(event) {
                             document.getElementById('imagePreview').src = event.target.result;
                             document.getElementById('uploadPlaceholder').classList.add('hidden');
                             document.getElementById('previewContainer').classList.remove('hidden');
                             // Reset remove flag
                             const removeInput = document.getElementById('remove_logo');
                             if(removeInput) removeInput.value = "0";
                        };
                        reader.readAsDataURL(file);
                    });

                    function removeLogo() {
                        document.getElementById('imagePreview').src = '';
                        document.getElementById('uploadPlaceholder').classList.remove('hidden');
                        document.getElementById('previewContainer').classList.add('hidden');
                        document.getElementById('app_logo').value = ''; // clear input
                        const removeInput = document.getElementById('remove_logo');
                        if(removeInput) removeInput.value = "1";
                    }

                    // Toggle OAuth Sections
                    function setupToggle(toggleId, sectionId) {
                        const toggle = document.getElementById(toggleId);
                        const section = document.getElementById(sectionId);
                        if (toggle && section) {
                            toggle.addEventListener('change', function() {
                                if (this.checked) {
                                    section.classList.remove('hidden');
                                    section.classList.add('animate-fade-in');
                                } else {
                                    section.classList.add('hidden');
                                }
                            });
                        }
                    }

                    setupToggle('google_login_enabled', 'googleConfigSection');
                    setupToggle('facebook_login_enabled', 'facebookConfigSection');

                    // Auto-dismiss alert
                    const alert = document.getElementById('statusAlert');
                    if (alert) {
                        setTimeout(() => {
                            alert.classList.remove('opacity-100');
                            alert.classList.add('opacity-0', 'translate-y-4');
                            setTimeout(() => {
                                alert.remove();
                            }, 500);
                        }, 2000);
                    }
                </script>
                <style>
                    @keyframes fadeIn {
                        from { opacity: 0; transform: translateY(-10px); }
                        to { opacity: 1; transform: translateY(0); }
                    }
                    .animate-fade-in {
                        animation: fadeIn 0.4s ease-out forwards;
                    }
                </style>
            </div>

            <!-- Footer Help -->
            <div class="bg-gray-50/50 p-6 border-t border-gray-100">
                 <div class="flex items-start space-x-3">
                    <div class="flex-shrink-0 w-8 h-8 rounded-full bg-white flex items-center justify-center border border-gray-200 shadow-sm text-slate-500">
                        <i class="fas fa-info text-xs"></i>
                    </div>
                    <div>
                        <h4 class="text-xs font-bold text-gray-700 uppercase mb-1">Impact</h4>
                        <p class="text-xs text-gray-400 leading-relaxed">
                            These settings affect the global branding of the application, including the header and login screens.
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
