<x-layouts.admin :header="__('Account Settings')" :subheader="__('Manage your personal information')">
    <x-slot name="actions">
        <x-button variant="secondary" icon="arrow-left" :href="route('admin.dashboard')">{{ __('Back') }}</x-button>
    </x-slot>

    <div class="max-w-2xl mx-auto">
        <x-card>
            {{-- Avatar / Identity --}}
            <div class="flex flex-col items-center text-center mb-8">
                <div class="relative group cursor-pointer" onclick="document.getElementById('imageInput').click()">
                    <div id="previewContainer" class="relative w-28 h-28 mx-auto rounded-full overflow-hidden border border-slate-200 shadow-sm transition group-hover:opacity-90">
                        <img id="imagePreview" src="{{ $user->image_path ? asset($user->image_path) : 'https://ui-avatars.com/api/?name=' . urlencode($user->name) . '&background=2563eb&color=fff&size=200' }}" class="w-full h-full object-cover">
                        <div class="absolute inset-0 bg-slate-900/40 opacity-0 group-hover:opacity-100 transition flex items-center justify-center">
                            <span class="px-3 py-1.5 bg-white rounded-lg text-xs font-medium text-slate-900 shadow inline-flex items-center gap-1">
                                <x-icon name="image" class="h-3.5 w-3.5" /> {{ __('Change') }}
                            </span>
                        </div>
                    </div>
                </div>
                <div class="mt-4">
                    <h2 class="text-lg font-semibold text-slate-900">{{ $user->name }}</h2>
                    <p class="text-sm text-slate-500 mt-0.5">{{ $user->email }}</p>
                </div>
            </div>

            {{-- Form --}}
            <form action="{{ route('profile.update') }}" method="POST" class="space-y-6" id="profileForm">
                @csrf

                <div>
                    <label for="name" class="form-label">{{ __('Full Name') }}</label>
                    <input
                        type="text"
                        id="name"
                        name="name"
                        class="form-input @error('name') border-red-300 @enderror"
                        placeholder="e.g. John Doe"
                        value="{{ old('name', $user->name) }}"
                        required
                    />
                    @error('name')
                        <p class="form-error">{{ $message }}</p>
                    @enderror
                </div>

                <div class="hidden">
                    <input type="file" id="imageInput" accept="image/*">
                    <input type="hidden" name="image_base64" id="imageBase64">
                    <canvas id="resizeCanvas" style="display:none;"></canvas>
                </div>

                {{-- Actions --}}
                <div class="flex flex-col sm:flex-row gap-3 pt-2">
                    <button type="submit" class="btn-primary flex-1">
                        <x-icon name="check" class="h-4 w-4" />
                        {{ __('Save Profile') }}
                    </button>
                    <a href="{{ route('admin.dashboard') }}" class="btn-secondary flex-1">
                        {{ __('Cancel') }}
                    </a>
                </div>
            </form>

            {{-- Footer help --}}
            <div class="mt-8 pt-6 border-t border-slate-100 flex items-start gap-3">
                <div class="w-8 h-8 rounded-full bg-slate-50 flex items-center justify-center border border-slate-100 text-brand-600 shrink-0">
                    <x-icon name="shield" class="h-4 w-4" />
                </div>
                <div>
                    <h4 class="text-xs font-semibold text-slate-700 uppercase mb-1">{{ __('Privacy & Security') }}</h4>
                    <p class="text-xs text-slate-400 leading-relaxed">
                        {{ __('Your personal information is kept secure. Only your name and profile image are visible to other users if they have permission to see your profile.') }}
                    </p>
                </div>
            </div>
        </x-card>
    </div>

    @push('scripts')
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
    @endpush
</x-layouts.admin>
