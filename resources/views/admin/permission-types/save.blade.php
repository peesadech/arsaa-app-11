@extends('layouts.app')

@php
    $isEdit = isset($permissionType);
    $actionUrl = $isEdit ? route('admin.permission-types.update', $permissionType->permissionType_id) : route('admin.permission-types.store');
    
    $title = $isEdit ? 'Edit Category' : 'Add New Permission Category';
    $subtitle = $isEdit ? 'Update classification details' : 'Permission Types Setup';
    
    // Theme Configuration
    $gradientClass = $isEdit ? 'bg-gradient-to-r from-amber-500 via-orange-500 to-rose-500' : 'bg-gradient-to-r from-indigo-500 via-purple-500 to-pink-500';
    $blurClass = $isEdit ? 'bg-amber-500/20' : 'bg-indigo-500/20';
    $iconBgClass = $isEdit ? 'bg-amber-50 border-amber-100' : 'bg-indigo-50 border-indigo-100 shadow-inner';
    $iconClass = $isEdit ? 'fa-pen-nib text-amber-600 rotate-3' : 'fa-layer-group text-indigo-600 -rotate-3';
    $cardTitle = $isEdit ? 'Modify Classification' : 'Type Definition';
    $cardDesc = $isEdit 
        ? "You are updating category #{$permissionType->permissionType_id}. Ensure the name remains clear and meaningful."
        : 'Define a new classification for your permissions to maintain organized system access control.';
    
    $focusRing = $isEdit ? 'focus:border-amber-400' : 'focus:border-indigo-500';
    $focusText = $isEdit ? 'group-focus-within:text-amber-500' : 'group-focus-within:text-indigo-500';
    $hoverBorder = $isEdit ? 'group-hover:border-amber-300' : 'group-hover:border-indigo-300';
    $hoverBg = $isEdit ? 'group-hover:bg-amber-50/30' : 'group-hover:bg-indigo-50/30';
    $hoverText = $isEdit ? 'group-hover:text-amber-500' : 'group-hover:text-indigo-500';
    $changeIconColor = $isEdit ? 'text-amber-500' : 'text-indigo-500';
    $checkBg = $isEdit ? 'bg-amber-500' : 'bg-emerald-500';
    
    $btnClass = $isEdit 
        ? 'bg-amber-500 text-white hover:bg-amber-600 shadow-amber-200'
        : 'bg-indigo-600 text-white hover:bg-indigo-700 shadow-indigo-200';
    
    $btnText = $isEdit ? 'Save Changes' : 'Create Permission Category';
    $btnIcon = $isEdit ? 'fa-save' : 'fa-check-circle';

    $existingImage = $isEdit && $permissionType->permissionType_image_path ? $permissionType->permissionType_image_path : null;
@endphp

@section('content')
<div class="min-h-screen bg-gray-50 py-12 px-4 sm:px-6 lg:px-8">
    <div class="max-w-xl mx-auto">
        <!-- Breadcrumb / Header -->
        <div class="flex items-center space-x-4 mb-8">
            <a href="{{ route('admin.permission-types') }}" 
               class="group flex items-center justify-center w-10 h-10 rounded-full bg-white shadow-sm border border-gray-200 text-gray-500 hover:text-indigo-600 hover:border-indigo-300 transition-all duration-200">
                <i class="fas fa-arrow-left group-hover:-translate-x-0.5 transition-transform"></i>
            </a>
            <div>
                <h1 class="text-2xl font-extrabold text-gray-900 tracking-tight">{{ $title }}</h1>
                <p class="text-sm text-gray-500 font-medium px-1">{{ $subtitle }}</p>
            </div>
        </div>

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
                <form action="{{ $actionUrl }}" method="POST" enctype="multipart/form-data" class="space-y-8" id="permissionTypeForm">
                    @csrf
                    @if($isEdit)
                        @method('PUT')
                    @endif
                    
                    <div class="space-y-2">
                        <label for="permissionType_name" class="block text-xs font-bold text-gray-400 uppercase tracking-widest px-1">
                            Classification Name
                        </label>
                        <div class="group relative">
                            <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none text-gray-400 {{ $focusText }} transition-colors">
                                <i class="fas fa-terminal text-sm"></i>
                            </div>
                            <input
                                type="text"
                                id="permissionType_name"
                                name="permissionType_name"
                                class="block w-full pl-10 pr-4 py-4 bg-gray-50 border-2 border-gray-100 rounded-2xl text-gray-900 placeholder-gray-400 focus:outline-none focus:ring-0 {{ $focusRing }} focus:bg-white transition-all duration-200 @error('permissionType_name') border-red-300 bg-red-50 @enderror"
                                placeholder="e.g. Content Management"
                                value="{{ old('permissionType_name', $isEdit ? $permissionType->permissionType_name : '') }}"
                                required
                                autofocus
                            />
                            @if(!$isEdit && $errors->has('permissionType_name'))
                                <div class="absolute inset-y-0 right-0 pr-4 flex items-center pointer-events-none text-red-500 animate-pulse">
                                    <i class="fas fa-exclamation-circle text-sm"></i>
                                </div>
                            @endif
                        </div>
                        @error('permissionType_name')
                            <p class="text-xs font-semibold text-red-500 mt-2 px-1 flex items-center">
                                <span class="w-1 h-1 bg-red-500 rounded-full mr-2"></span>
                                {{ $message }}
                            </p>
                        @enderror
                    </div>

                    <div class="space-y-2">
                        <label class="block text-xs font-bold text-gray-400 uppercase tracking-widest px-1">
                            Category Image (200x200)
                        </label>
                        <div 
                            onclick="document.getElementById('imageInput').click()"
                            class="relative group cursor-pointer"
                        >
                            <div id="uploadPlaceholder" class="{{ $existingImage ? 'hidden' : '' }} flex flex-col items-center justify-center w-full py-12 bg-gray-50 border-2 border-dashed border-gray-200 rounded-2xl {{ $hoverBorder }} {{ $hoverBg }} transition-all duration-200">
                                <div class="w-16 h-16 rounded-2xl bg-white shadow-sm border border-gray-100 flex items-center justify-center text-gray-400 {{ $hoverText }} transition-colors mb-4">
                                    <i class="fas fa-cloud-upload-alt text-2xl"></i>
                                </div>
                                <p class="text-sm font-bold text-gray-500">Click to browse image</p>
                                <p class="text-xs text-gray-400 mt-1">Automatically resized to 200x200</p>
                            </div>
                            
                            <div id="previewContainer" class="{{ $existingImage ? '' : 'hidden' }} relative w-[200px] h-[200px] mx-auto rounded-[32px] overflow-hidden border-4 border-white shadow-2xl transition-all duration-300 transform hover:scale-105">
                                <img id="imagePreview" src="{{ $existingImage ?? '#' }}" class="w-full h-full object-cover">
                                <div class="absolute inset-0 bg-black/40 opacity-0 group-hover:opacity-100 transition-opacity flex items-center justify-center backdrop-blur-sm">
                                    <span class="px-4 py-2 bg-white rounded-xl text-[10px] font-bold text-gray-900 shadow-xl">
                                        <i class="fas fa-sync-alt mr-1 {{ $changeIconColor }}"></i> Change
                                    </span>
                                </div>
                                <div class="absolute top-2 right-2 px-2 py-1 {{ $checkBg }} text-white text-[10px] font-bold uppercase tracking-widest rounded-full shadow-lg">
                                    <i class="fas fa-check-circle mr-1"></i> 200x200
                                </div>
                            </div>

                            <input type="file" id="imageInput" class="hidden" accept="image/*">
                            <input type="hidden" name="image_base64" id="imageBase64">
                            <canvas id="resizeCanvas" style="display:none;"></canvas>
                        </div>
                    </div>

                    <!-- Actions -->
                    <div class="flex flex-col sm:flex-row space-y-3 sm:space-y-0 sm:space-x-4 pt-4">
                        <button 
                            type="submit"
                            class="flex-1 group relative flex items-center justify-center px-8 py-4 {{ $btnClass }} font-bold rounded-2xl active:scale-95 transition-all duration-200 shadow-lg overflow-hidden"
                        >
                            <span class="relative z-10 flex items-center">
                                <i class="fas {{ $btnIcon }} mr-2 opacity-50 group-hover:opacity-100 transition-opacity"></i>
                                {{ $btnText }}
                            </span>
                            <div class="absolute inset-0 bg-gradient-to-r from-transparent via-white/10 to-transparent -translate-x-full group-hover:translate-x-full transition-transform duration-1000 ease-in-out"></div>
                        </button>
                        
                        <a href="{{ route('admin.permission-types') }}" 
                           class="flex-1 flex items-center justify-center px-8 py-4 bg-white text-gray-700 font-bold rounded-2xl border-2 border-gray-100 hover:border-gray-200 hover:bg-gray-50 active:scale-95 transition-all duration-200">
                            {{ $isEdit ? 'Cancel' : 'Dismiss' }}
                        </a>
                    </div>
                </form>

                <script>
                    document.getElementById('imageInput').addEventListener('change', function(e) {
                        const file = e.target.files[0];
                        if (!file) return;

                        const reader = new FileReader();
                        reader.onload = function(event) {
                            const img = new Image();
                            img.onload = function() {
                                const canvas = document.getElementById('resizeCanvas');
                                const ctx = canvas.getContext('2d');
                                
                                const targetSize = 200;
                                canvas.width = targetSize;
                                canvas.height = targetSize;
                                
                                // Precise center crop math
                                // 1. Determine the square area from the source
                                const sSize = Math.min(img.width, img.height);
                                const sx = (img.width - sSize) / 2;
                                const sy = (img.height - sSize) / 2;
                                
                                // 2. Clear canvas and set smoothing
                                ctx.clearRect(0, 0, targetSize, targetSize);
                                ctx.imageSmoothingEnabled = true;
                                ctx.imageSmoothingQuality = 'high';
                                
                                // 3. Fill background (white for transparency handling)
                                ctx.fillStyle = '#FFFFFF';
                                ctx.fillRect(0, 0, targetSize, targetSize);
                                
                                // 4. Draw the cropped square into the 200x200 destination
                                ctx.drawImage(img, sx, sy, sSize, sSize, 0, 0, targetSize, targetSize);
                                
                                const base64Data = canvas.toDataURL('image/jpeg', 0.95);
                                document.getElementById('imageBase64').value = base64Data;
                                
                                // Show preview
                                document.getElementById('imagePreview').src = base64Data;
                                document.getElementById('uploadPlaceholder').classList.add('hidden');
                                document.getElementById('previewContainer').classList.remove('hidden');
                            };
                            img.src = event.target.result;
                        };
                        reader.readAsDataURL(file);
                    });
                </script>
            </div>

            <!-- Footer Help -->
            <div class="bg-gray-50/50 p-6 border-t border-gray-100">
                <div class="flex items-start space-x-3">
                    <div class="flex-shrink-0 w-8 h-8 rounded-full bg-white flex items-center justify-center border border-gray-200 shadow-sm text-indigo-500">
                        <i class="fas fa-lightbulb text-xs"></i>
                    </div>
                    <div>
                        <h4 class="text-xs font-bold text-gray-700 uppercase mb-1">System Help</h4>
                        <p class="text-xs text-gray-400 leading-relaxed">
                            Classification names must be unique and descriptive. They help group functional permissions into logical modules for easier management.
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
