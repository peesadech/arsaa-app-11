@extends('layouts.app')

@php
    $isEdit = isset($user);
    $actionUrl = $isEdit ? route('admin.users.update', $user->id) : route('admin.users.store');
    
    $title = $isEdit ? 'Edit User' : 'Create New User';
    $subtitle = $isEdit ? 'Update account details' : 'User Registration';
    
    // Theme Configuration
    $gradientClass = $isEdit ? 'bg-gradient-to-r from-amber-500 via-orange-500 to-rose-500' : 'bg-gradient-to-r from-indigo-500 via-purple-500 to-pink-500';
    $blurClass = $isEdit ? 'bg-amber-500/20' : 'bg-indigo-500/20';
    $iconBgClass = $isEdit ? 'bg-amber-50 border-amber-100' : 'bg-indigo-50 border-indigo-100 shadow-inner';
    $iconClass = $isEdit ? 'fa-user-edit text-amber-600 rotate-3' : 'fa-user-plus text-indigo-600 -rotate-3';
    $cardTitle = $isEdit ? 'Modify Account' : 'Account Details';
    $cardDesc = $isEdit 
        ? "You are updating account #{$user->id}. Ensure all details are correct."
        : 'Define a new system user and assign appropriate access roles.';
    
    $focusRing = $isEdit ? 'focus:border-amber-400' : 'focus:border-indigo-500';
    $focusText = $isEdit ? 'group-focus-within:text-amber-500' : 'group-focus-within:text-indigo-500';
    
    $btnClass = $isEdit 
        ? 'bg-amber-500 text-white hover:bg-amber-600 shadow-amber-200'
        : 'bg-indigo-600 text-white hover:bg-indigo-700 shadow-indigo-200';
    
    $btnText = $isEdit ? 'Save Changes' : 'Create User';
    $btnIcon = $isEdit ? 'fa-save' : 'fa-check-circle';

    $existingImage = $isEdit && $user->image_path ? $user->image_path : null;
@endphp

@section('content')
<div class="min-h-screen bg-gray-50 dark:bg-[#18191a] py-12 px-4 sm:px-6 lg:px-8 transition-colors duration-300">
    <div class="max-w-2xl mx-auto">
        <!-- Breadcrumb / Header -->
        <div class="flex items-center space-x-4 mb-8">
            <a href="{{ route('admin.users.index') }}" 
               class="group flex items-center justify-center w-10 h-10 rounded-xl bg-white dark:bg-[#242526] shadow-sm border border-gray-200 dark:border-[#3a3b3c] text-gray-500 dark:text-gray-400 hover:text-indigo-600 dark:hover:text-indigo-400 hover:border-indigo-300 transition-all duration-200">
                <i class="fas fa-arrow-left group-hover:-translate-x-0.5 transition-transform"></i>
            </a>
            <div>
                <h1 class="text-2xl font-extrabold text-gray-900 dark:text-white tracking-tight">{{ $title }}</h1>
                <p class="text-sm text-gray-500 dark:text-gray-400 font-medium px-1">{{ $subtitle }}</p>
            </div>
        </div>

        <!-- Main Card -->
        <div class="bg-white dark:bg-[#242526] rounded-[2.5rem] shadow-xl shadow-gray-200/50 dark:shadow-none border border-gray-100 dark:border-[#3a3b3c] overflow-hidden transform transition-all">
            <!-- Decorative Top Border -->
            <div class="h-2 {{ $gradientClass }}"></div>
            
            <div class="p-8 sm:p-10">
                <!-- Visual Identity Section -->
                <div class="flex flex-col items-center text-center mb-10">
                    <div class="relative">
                        <div class="absolute inset-0 {{ $blurClass }} blur-2xl rounded-full"></div>
                        <div id="image-preview-container" class="relative w-24 h-24 rounded-3xl flex items-center justify-center mb-4 transform border-2 border-white dark:border-[#3a3b3c] shadow-xl overflow-hidden {{ $iconBgClass }}">
                            @if($existingImage)
                                <img src="{{ asset($existingImage) }}" id="preview-img" class="w-full h-full object-cover">
                            @else
                                <i class="fas {{ $iconClass }} text-3xl"></i>
                            @endif
                        </div>
                        <label for="image_input" class="absolute -right-2 -bottom-2 w-8 h-8 rounded-xl bg-white dark:bg-[#3a3b3c] shadow-lg flex items-center justify-center text-gray-500 hover:text-indigo-600 cursor-pointer transition-all hover:scale-110 active:scale-90 border border-gray-100 dark:border-[#4a4b4c]">
                            <i class="fas fa-camera text-xs"></i>
                        </label>
                        <input type="file" id="image_input" class="hidden" accept="image/*">
                    </div>
                    <h2 class="text-xl font-bold text-gray-800 dark:text-white mb-2">{{ $cardTitle }}</h2>
                    <p class="text-sm text-gray-500 dark:text-gray-400 max-w-xs mx-auto">
                        {{ $cardDesc }}
                    </p>
                </div>

                <!-- Form -->
                <form action="{{ $actionUrl }}" method="POST" class="space-y-6" id="userForm">
                    @csrf
                    @if($isEdit)
                        @method('PUT')
                    @endif
                    
                    <input type="hidden" name="image_base64" id="image_base64">
                    
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <!-- Name -->
                        <div class="space-y-2">
                            <label for="name" class="block text-xs font-bold text-gray-400 dark:text-gray-500 uppercase tracking-widest px-1">
                                Full Name
                            </label>
                            <div class="group relative">
                                <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none text-gray-400 {{ $focusText }} transition-colors">
                                    <i class="fas fa-user text-sm"></i>
                                </div>
                                <input
                                    type="text"
                                    id="name"
                                    name="name"
                                    class="block w-full pl-10 pr-4 py-4 bg-gray-50 dark:bg-[#3a3b3c] border-2 border-transparent rounded-2xl text-gray-900 dark:text-white placeholder-gray-400 focus:outline-none focus:ring-0 {{ $focusRing }} focus:bg-white dark:focus:bg-[#3a3b3c] transition-all duration-200 @error('name') border-rose-300 bg-rose-50 dark:bg-rose-900/20 @enderror"
                                    placeholder="Enter full name"
                                    value="{{ old('name', $isEdit ? $user->name : '') }}"
                                    required
                                />
                            </div>
                            @error('name')
                                <p class="text-[10px] font-bold text-rose-500 mt-1 px-1">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Email -->
                        <div class="space-y-2">
                            <label for="email" class="block text-xs font-bold text-gray-400 dark:text-gray-500 uppercase tracking-widest px-1">
                                Email Address
                            </label>
                            <div class="group relative">
                                <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none text-gray-400 {{ $focusText }} transition-colors">
                                    <i class="fas fa-envelope text-sm"></i>
                                </div>
                                <input
                                    type="email"
                                    id="email"
                                    name="email"
                                    class="block w-full pl-10 pr-4 py-4 bg-gray-50 dark:bg-[#3a3b3c] border-2 border-transparent rounded-2xl text-gray-900 dark:text-white placeholder-gray-400 focus:outline-none focus:ring-0 {{ $focusRing }} focus:bg-white dark:focus:bg-[#3a3b3c] transition-all duration-200 @error('email') border-rose-300 bg-rose-50 dark:bg-rose-900/20 @enderror"
                                    placeholder="email@example.com"
                                    value="{{ old('email', $isEdit ? $user->email : '') }}"
                                    required
                                />
                            </div>
                            @error('email')
                                <p class="text-[10px] font-bold text-rose-500 mt-1 px-1">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <!-- Password -->
                        <div class="space-y-2">
                            <label for="password" class="block text-xs font-bold text-gray-400 dark:text-gray-500 uppercase tracking-widest px-1">
                                {{ $isEdit ? 'Change Password' : 'Password' }}
                            </label>
                            <div class="group relative">
                                <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none text-gray-400 {{ $focusText }} transition-colors">
                                    <i class="fas fa-lock text-sm"></i>
                                </div>
                                <input
                                    type="password"
                                    id="password"
                                    name="password"
                                    class="block w-full pl-10 pr-4 py-4 bg-gray-50 dark:bg-[#3a3b3c] border-2 border-transparent rounded-2xl text-gray-900 dark:text-white placeholder-gray-400 focus:outline-none focus:ring-0 {{ $focusRing }} focus:bg-white dark:focus:bg-[#3a3b3c] transition-all duration-200 @error('password') border-rose-300 bg-rose-50 dark:bg-rose-900/20 @enderror"
                                    placeholder="{{ $isEdit ? 'Leave blank to keep current' : 'Enter secure password' }}"
                                    {{ $isEdit ? '' : 'required' }}
                                />
                            </div>
                            @error('password')
                                <p class="text-[10px] font-bold text-rose-500 mt-1 px-1">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Confirm Password -->
                        <div class="space-y-2">
                            <label for="password_confirmation" class="block text-xs font-bold text-gray-400 dark:text-gray-500 uppercase tracking-widest px-1">
                                Confirm Password
                            </label>
                            <div class="group relative">
                                <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none text-gray-400 {{ $focusText }} transition-colors">
                                    <i class="fas fa-shield-alt text-sm"></i>
                                </div>
                                <input
                                    type="password"
                                    id="password_confirmation"
                                    name="password_confirmation"
                                    class="block w-full pl-10 pr-4 py-4 bg-gray-50 dark:bg-[#3a3b3c] border-2 border-transparent rounded-2xl text-gray-900 dark:text-white placeholder-gray-400 focus:outline-none focus:ring-0 {{ $focusRing }} focus:bg-white dark:focus:bg-[#3a3b3c] transition-all duration-200"
                                    placeholder="Confirm password"
                                    {{ $isEdit ? '' : 'required' }}
                                />
                            </div>
                        </div>
                    </div>

                    <!-- Roles -->
                    <div class="space-y-3">
                        <label class="block text-xs font-bold text-gray-400 dark:text-gray-500 uppercase tracking-widest px-1">
                            Assign Roles
                        </label>
                        <div class="flex flex-wrap gap-2">
                            @foreach($roles as $role)
                                <label class="relative group cursor-pointer">
                                    <input type="checkbox" name="roles[]" value="{{ $role->name }}" class="peer hidden" 
                                        {{ (isset($userRoles) && in_array($role->name, $userRoles)) || (old('roles') && in_array($role->name, old('roles'))) ? 'checked' : '' }}>
                                    <div class="px-4 py-2.5 rounded-xl border-2 border-gray-100 dark:border-[#3a3b3c] bg-white dark:bg-[#242526] text-sm font-bold text-gray-500 dark:text-gray-400 transition-all duration-200 peer-checked:border-indigo-500 peer-checked:bg-indigo-50 dark:peer-checked:bg-indigo-900/30 peer-checked:text-indigo-600 dark:peer-checked:text-indigo-400 group-hover:border-gray-200 dark:group-hover:border-[#4a4b4c]">
                                        <div class="flex items-center">
                                            <i class="fas fa-shield-alt mr-2 text-[10px] opacity-50"></i>
                                            {{ $role->name }}
                                        </div>
                                    </div>
                                    <div class="absolute -top-1 -right-1 w-4 h-4 bg-indigo-500 rounded-full flex items-center justify-center text-[8px] text-white opacity-0 peer-checked:opacity-100 transition-all scale-50 peer-checked:scale-100">
                                        <i class="fas fa-check"></i>
                                    </div>
                                </label>
                            @endforeach
                        </div>
                    </div>

                    <!-- Status -->
                    <div class="space-y-3">
                        <label class="block text-xs font-bold text-gray-400 dark:text-gray-500 uppercase tracking-widest px-1">
                            Account Status
                        </label>
                        <div class="flex flex-wrap gap-2">
                            <!-- Active Status -->
                            <label class="relative group cursor-pointer">
                                <input type="radio" name="status" value="1" class="peer hidden" {{ old('status', $isEdit ? $user->status : 1) == 1 ? 'checked' : '' }}>
                                <div class="px-4 py-2.5 rounded-xl border-2 border-gray-100 dark:border-[#3a3b3c] bg-white dark:bg-[#242526] text-sm font-bold text-gray-500 dark:text-gray-400 transition-all duration-200 peer-checked:border-emerald-500 peer-checked:bg-emerald-50 dark:peer-checked:bg-emerald-900/30 peer-checked:text-emerald-600 dark:peer-checked:text-emerald-400 group-hover:border-gray-200 dark:group-hover:border-[#4a4b4c]">
                                    <div class="flex items-center">
                                        <i class="fas fa-check-circle mr-2 text-[10px] opacity-50"></i>
                                        Active
                                    </div>
                                </div>
                                <div class="absolute -top-1 -right-1 w-4 h-4 bg-emerald-500 rounded-full flex items-center justify-center text-[8px] text-white opacity-0 peer-checked:opacity-100 transition-all scale-50 peer-checked:scale-100">
                                    <i class="fas fa-check"></i>
                                </div>
                            </label>
                            
                            <!-- Not Active Status -->
                            <label class="relative group cursor-pointer">
                                <input type="radio" name="status" value="2" class="peer hidden" {{ old('status', $isEdit ? $user->status : 1) == 2 ? 'checked' : '' }}>
                                <div class="px-4 py-2.5 rounded-xl border-2 border-gray-100 dark:border-[#3a3b3c] bg-white dark:bg-[#242526] text-sm font-bold text-gray-500 dark:text-gray-400 transition-all duration-200 peer-checked:border-rose-500 peer-checked:bg-rose-50 dark:peer-checked:bg-rose-900/30 peer-checked:text-rose-600 dark:peer-checked:text-rose-400 group-hover:border-gray-200 dark:group-hover:border-[#4a4b4c]">
                                    <div class="flex items-center">
                                        <i class="fas fa-times-circle mr-2 text-[10px] opacity-50"></i>
                                        Not Active
                                    </div>
                                </div>
                                <div class="absolute -top-1 -right-1 w-4 h-4 bg-rose-500 rounded-full flex items-center justify-center text-[8px] text-white opacity-0 peer-checked:opacity-100 transition-all scale-50 peer-checked:scale-100">
                                    <i class="fas fa-check"></i>
                                </div>
                            </label>
                        </div>
                        @error('status')
                            <p class="text-[10px] font-bold text-rose-500 mt-1 px-1">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Actions -->
                    <div class="flex flex-col sm:flex-row space-y-3 sm:space-y-0 sm:space-x-4 pt-6">
                        <button 
                            type="button"
                            onclick="handleSubmit()"
                            class="flex-1 group relative flex items-center justify-center px-8 py-4 {{ $btnClass }} font-bold rounded-2xl active:scale-95 transition-all duration-200 shadow-lg overflow-hidden"
                        >
                            <span class="relative z-10 flex items-center">
                                <i class="fas {{ $btnIcon }} mr-2 opacity-50 group-hover:opacity-100 transition-opacity"></i>
                                {{ $btnText }}
                            </span>
                            <div class="absolute inset-0 bg-gradient-to-r from-transparent via-white/10 to-transparent -translate-x-full group-hover:translate-x-full transition-transform duration-1000 ease-in-out"></div>
                        </button>
                        
                        <a href="{{ route('admin.users.index') }}" 
                           class="flex-1 flex items-center justify-center px-8 py-4 bg-white dark:bg-[#242526] text-gray-700 dark:text-gray-300 font-bold rounded-2xl border-2 border-gray-100 dark:border-[#3a3b3c] hover:border-gray-200 dark:hover:border-[#4a4b4c] hover:bg-gray-50 dark:hover:bg-[#3a3b3c] active:scale-95 transition-all duration-200">
                            {{ $isEdit ? 'Cancel' : 'Back to List' }}
                        </a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<!-- Image Processing Modal (Hidden) -->
<canvas id="canvas" class="hidden"></canvas>

@push('scripts')
<script shadow>
    const imageInput = document.getElementById('image_input');
    const imagePreview = document.getElementById('preview-img');
    const container = document.getElementById('image-preview-container');
    const base64Input = document.getElementById('image_base64');
    const canvas = document.getElementById('canvas');
    const ctx = canvas.getContext('2d');

    imageInput.addEventListener('change', function(e) {
        if (e.target.files && e.target.files[0]) {
            const reader = new FileReader();
            reader.onload = function(event) {
                const img = new Image();
                img.onload = function() {
                    // Resize to 400x400
                    canvas.width = 400;
                    canvas.height = 400;
                    
                    // Center crop logic
                    let sourceX = 0;
                    let sourceY = 0;
                    let sourceWidth = img.width;
                    let sourceHeight = img.height;
                    
                    if (img.width > img.height) {
                        sourceWidth = img.height;
                        sourceX = (img.width - img.height) / 2;
                    } else {
                        sourceHeight = img.width;
                        sourceY = (img.height - img.width) / 2;
                    }
                    
                    ctx.drawImage(img, sourceX, sourceY, sourceWidth, sourceHeight, 0, 0, 400, 400);
                    
                    const base64 = canvas.toDataURL('image/jpeg', 0.85);
                    base64Input.value = base64;
                    
                    // Update UI
                    if (imagePreview) {
                        imagePreview.src = base64;
                    } else {
                        container.innerHTML = `<img src="${base64}" id="preview-img" class="w-full h-full object-cover">`;
                    }
                };
                img.src = event.target.result;
            };
            reader.readAsDataURL(e.target.files[0]);
        }
    });

    function handleSubmit() {
        document.getElementById('userForm').submit();
    }
</script>
@endpush
@endsection
