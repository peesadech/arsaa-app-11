@extends('layouts.app')

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
                <h1 class="text-2xl font-extrabold text-gray-900 tracking-tight">{{ __('Account Settings') }}</h1>
                <p class="text-sm text-gray-500 font-medium px-1">{{ __('Manage your personal information') }}</p>
            </div>
        </div>

        <!-- Main Card -->
        <div class="bg-white rounded-3xl shadow-xl shadow-gray-200/50 border border-gray-100 overflow-hidden transform transition-all">
            <!-- Decorative Top Border -->
            <div class="h-2 bg-gradient-to-r from-indigo-500 via-purple-500 to-pink-500"></div>
            
            <div class="p-8 sm:p-10">
                <!-- Visual Identity Section -->
                <div class="flex flex-col items-center text-center mb-10">
                    <div class="relative group cursor-pointer" onclick="document.getElementById('imageInput').click()">
                        <div class="absolute inset-0 bg-indigo-500/20 blur-2xl rounded-full"></div>
                        <div id="previewContainer" class="relative w-32 h-32 mx-auto rounded-full overflow-hidden border-4 border-white shadow-2xl transition-all duration-300 transform group-hover:scale-105">
                            <img id="imagePreview" src="{{ $user->image_path ? asset($user->image_path) : 'https://ui-avatars.com/api/?name=' . urlencode($user->name) . '&background=6366f1&color=fff&size=200' }}" class="w-full h-full object-cover">
                            <div class="absolute inset-0 bg-black/40 opacity-0 group-hover:opacity-100 transition-opacity flex items-center justify-center backdrop-blur-sm">
                                <span class="px-3 py-1.5 bg-white rounded-xl text-[10px] font-bold text-gray-900 shadow-xl">
                                    <i class="fas fa-camera mr-1 text-indigo-500"></i> {{ __('Change') }}
                                </span>
                            </div>
                        </div>
                    </div>
                    <div class="mt-4">
                        <h2 class="text-xl font-bold text-gray-800">{{ $user->name }}</h2>
                        <p class="text-xs font-bold text-gray-400 uppercase tracking-widest mt-1">{{ $user->email }}</p>
                    </div>
                </div>

                <!-- Form -->
                <form action="{{ route('profile.update') }}" method="POST" class="space-y-8" id="profileForm">
                    @csrf
                    
                    <div class="space-y-2">
                        <label for="name" class="block text-xs font-bold text-gray-400 uppercase tracking-widest px-1">
                            {{ __('Full Name') }}
                        </label>
                        <div class="group relative">
                            <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none text-gray-400 group-focus-within:text-indigo-500 transition-colors">
                                <i class="fas fa-user text-sm"></i>
                            </div>
                            <input
                                type="text"
                                id="name"
                                name="name"
                                class="block w-full pl-10 pr-4 py-4 bg-gray-50 border-2 border-gray-100 rounded-2xl text-gray-900 placeholder-gray-400 focus:outline-none focus:ring-0 focus:border-indigo-500 focus:bg-white transition-all duration-200 @error('name') border-red-300 bg-red-50 @enderror"
                                placeholder="e.g. John Doe"
                                value="{{ old('name', $user->name) }}"
                                required
                            />
                        </div>
                        @error('name')
                            <p class="text-xs font-semibold text-red-500 mt-2 px-1 flex items-center">
                                <span class="w-1 h-1 bg-red-500 rounded-full mr-2"></span>
                                {{ $message }}
                            </p>
                        @enderror
                    </div>

                    <div class="hidden">
                        <input type="file" id="imageInput" accept="image/*">
                        <input type="hidden" name="image_base64" id="imageBase64">
                        <canvas id="resizeCanvas" style="display:none;"></canvas>
                    </div>

                    <!-- Actions -->
                    <div class="flex flex-col sm:flex-row space-y-3 sm:space-y-0 sm:space-x-4 pt-4">
                        <button 
                            type="submit"
                            class="flex-1 group relative flex items-center justify-center px-8 py-4 bg-indigo-600 text-white font-bold rounded-2xl active:scale-95 transition-all duration-200 shadow-lg shadow-indigo-200 overflow-hidden"
                        >
                            <span class="relative z-10 flex items-center">
                                <i class="fas fa-save mr-2 opacity-50 group-hover:opacity-100 transition-opacity"></i>
                                {{ __('Save Profile') }}
                            </span>
                            <div class="absolute inset-0 bg-gradient-to-r from-transparent via-white/10 to-transparent -translate-x-full group-hover:translate-x-full transition-transform duration-1000 ease-in-out"></div>
                        </button>
                        
                        <a href="{{ route('admin.dashboard') }}" 
                           class="flex-1 flex items-center justify-center px-8 py-4 bg-white text-gray-700 font-bold rounded-2xl border-2 border-gray-100 hover:border-gray-200 hover:bg-gray-50 active:scale-95 transition-all duration-200">
                            {{ __('Cancel') }}
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
                                
                                const sSize = Math.min(img.width, img.height);
                                const sx = (img.width - sSize) / 2;
                                const sy = (img.height - sSize) / 2;
                                
                                ctx.clearRect(0, 0, targetSize, targetSize);
                                ctx.imageSmoothingEnabled = true;
                                ctx.imageSmoothingQuality = 'high';
                                
                                ctx.fillStyle = '#FFFFFF';
                                ctx.fillRect(0, 0, targetSize, targetSize);
                                
                                ctx.drawImage(img, sx, sy, sSize, sSize, 0, 0, targetSize, targetSize);
                                
                                const base64Data = canvas.toDataURL('image/jpeg', 0.95);
                                document.getElementById('imageBase64').value = base64Data;
                                
                                // Show preview
                                document.getElementById('imagePreview').src = base64Data;
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
                        <i class="fas fa-shield-alt text-xs"></i>
                    </div>
                    <div>
                        <h4 class="text-xs font-bold text-gray-700 uppercase mb-1">{{ __('Privacy & Security') }}</h4>
                        <p class="text-xs text-gray-400 leading-relaxed">
                            {{ __('Your personal information is kept secure. Only your name and profile image are visible to other users if they have permission to see your profile.') }}
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
