@php
    $isEdit = isset($permissionType);
    $actionUrl = $isEdit ? route('admin.permission-types.update', $permissionType->permissionType_id) : route('admin.permission-types.store');

    $title = $isEdit ? __('Edit Category') : __('Add New Permission Category');
    $subtitle = $isEdit ? __('Update classification details') : __('Permission Types Setup');
    $cardTitle = $isEdit ? __('Modify Classification') : __('Type Definition');
    $cardDesc = $isEdit
        ? __('You are updating category #:id. Ensure the name remains clear and meaningful.', ['id' => $permissionType->permissionType_id])
        : __('Define a new classification for your permissions to maintain organized system access control.');

    $btnText = $isEdit ? __('Save Changes') : __('Create Permission Category');

    $existingImage = $isEdit && $permissionType->permissionType_image_path ? $permissionType->permissionType_image_path : null;
@endphp

<x-layouts.admin :header="$title" :subheader="$subtitle">
    <x-slot name="actions">
        <x-button variant="secondary" icon="arrow-left" :href="route('admin.permission-types')">{{ __('Back to List') }}</x-button>
    </x-slot>

    <div class="max-w-xl mx-auto">
        <x-card :title="$cardTitle" :description="$cardDesc">
            <form action="{{ $actionUrl }}" method="POST" enctype="multipart/form-data" class="space-y-6" id="permissionTypeForm">
                @csrf
                @if($isEdit)
                    @method('PUT')
                @endif

                <div>
                    <label for="permissionType_name" class="form-label">{{ __('Classification Name') }}</label>
                    <input
                        type="text"
                        id="permissionType_name"
                        name="permissionType_name"
                        class="form-input @error('permissionType_name') border-red-300 @enderror"
                        placeholder="{{ __('e.g. Content Management') }}"
                        value="{{ old('permissionType_name', $isEdit ? $permissionType->permissionType_name : '') }}"
                        required
                        autofocus
                    />
                    @error('permissionType_name')
                        <p class="form-error">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label class="form-label">{{ __('Category Image (200x200)') }}</label>
                    <div onclick="document.getElementById('imageInput').click()" class="relative group cursor-pointer">
                        <div id="uploadPlaceholder" class="{{ $existingImage ? 'hidden' : '' }} flex flex-col items-center justify-center w-full py-10 bg-slate-50 border-2 border-dashed border-slate-200 rounded-xl hover:border-brand-300 hover:bg-brand-50/40 transition-all duration-200">
                            <div class="w-14 h-14 rounded-xl bg-white shadow-sm border border-slate-100 flex items-center justify-center text-slate-400 group-hover:text-brand-500 transition-colors mb-3">
                                <x-icon name="upload" class="h-6 w-6" />
                            </div>
                            <p class="text-sm font-semibold text-slate-600">{{ __('Click to browse image') }}</p>
                            <p class="text-xs text-slate-400 mt-1">{{ __('Automatically resized to 200x200') }}</p>
                        </div>

                        <div id="previewContainer" class="{{ $existingImage ? '' : 'hidden' }} relative w-[200px] h-[200px] mx-auto rounded-2xl overflow-hidden border-4 border-white shadow-card transition-all duration-300">
                            <img id="imagePreview" src="{{ $existingImage ?? '#' }}" class="w-full h-full object-cover">
                            <div class="absolute inset-0 bg-slate-900/40 opacity-0 group-hover:opacity-100 transition-opacity flex items-center justify-center">
                                <span class="px-3 py-1.5 bg-white rounded-lg text-[11px] font-semibold text-slate-800 shadow">
                                    {{ __('Change') }}
                                </span>
                            </div>
                            <div class="absolute top-2 right-2 px-2 py-1 bg-brand-600 text-white text-[10px] font-semibold uppercase tracking-widest rounded-full shadow">
                                200x200
                            </div>
                        </div>

                        <input type="file" id="imageInput" class="hidden" accept="image/*">
                        <input type="hidden" name="image_base64" id="imageBase64">
                        <canvas id="resizeCanvas" style="display:none;"></canvas>
                    </div>
                </div>

                <div class="flex items-center gap-3 pt-2">
                    <button type="submit" class="btn-primary">
                        <x-icon name="check" class="h-4 w-4" />
                        {{ $btnText }}
                    </button>
                    <a href="{{ route('admin.permission-types') }}" class="btn-secondary">
                        {{ $isEdit ? __('Cancel') : __('Dismiss') }}
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

                            document.getElementById('imagePreview').src = base64Data;
                            document.getElementById('uploadPlaceholder').classList.add('hidden');
                            document.getElementById('previewContainer').classList.remove('hidden');
                        };
                        img.src = event.target.result;
                    };
                    reader.readAsDataURL(file);
                });
            </script>
        </x-card>

        <div class="mt-4 flex items-start gap-3 p-4 bg-brand-50 rounded-xl border border-brand-100">
            <div class="shrink-0 h-8 w-8 rounded-full bg-white flex items-center justify-center border border-brand-100 text-brand-500">
                <x-icon name="layers" class="h-4 w-4" />
            </div>
            <div>
                <h4 class="text-xs font-semibold text-slate-700 uppercase mb-1">{{ __('System Help') }}</h4>
                <p class="text-xs text-slate-500 leading-relaxed">
                    {{ __('Classification names must be unique and descriptive. They help group functional permissions into logical modules for easier management.') }}
                </p>
            </div>
        </div>
    </div>
</x-layouts.admin>
