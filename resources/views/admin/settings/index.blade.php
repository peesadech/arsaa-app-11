@php
    $logoUrl = $setting->app_logo ? asset('storage/' . $setting->app_logo) : null;
@endphp

<x-layouts.admin :header="__('General Settings')" :subheader="__('Application Configuration')">
    <x-slot name="actions">
        <x-button variant="secondary" icon="arrow-left" :href="route('admin.dashboard')">{{ __('Back') }}</x-button>
    </x-slot>

    <div class="max-w-2xl mx-auto">
        @if ($errors->any())
            <div class="mb-6 rounded-2xl border border-red-100 bg-red-50 p-4">
                <div class="flex items-center gap-2 mb-2 text-red-700">
                    <x-icon name="x" class="h-5 w-5" />
                    <h3 class="text-sm font-semibold">{{ __('Please correct the following errors:') }}</h3>
                </div>
                <ul class="list-disc list-inside space-y-1">
                    @foreach ($errors->all() as $error)
                        <li class="text-xs text-red-600">{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <x-card>
            {{-- Identity header --}}
            <div class="flex flex-col items-center text-center mb-8">
                <div class="w-16 h-16 rounded-2xl bg-brand-50 text-brand-600 flex items-center justify-center mb-4">
                    <x-icon name="cog" class="h-8 w-8" />
                </div>
                <h2 class="text-lg font-semibold text-slate-900">{{ __('App Identity') }}</h2>
                <p class="text-sm text-slate-500 max-w-sm mt-1">
                    {{ __('Manage key application details including name and branding logo.') }}
                </p>
            </div>

            {{-- Form --}}
            <form action="{{ route('admin.settings.update') }}" method="POST" enctype="multipart/form-data" class="space-y-8" id="settingsForm">
                @csrf

                <div>
                    <label for="app_name" class="form-label">{{ __('Application Name') }}</label>
                    <input
                        type="text"
                        id="app_name"
                        name="app_name"
                        class="form-input @error('app_name') border-red-300 @enderror"
                        placeholder="My Amazing App"
                        value="{{ old('app_name', $setting->app_name) }}"
                        required
                        autofocus
                    />
                    @error('app_name')
                        <p class="form-error">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label class="form-label">{{ __('Application Theme') }}</label>
                    <div class="grid grid-cols-2 gap-4">
                        {{-- Light Theme Option --}}
                        <label class="relative cursor-pointer">
                            <input type="radio" name="theme" value="light" class="peer sr-only" {{ old('theme', $setting->theme) == 'light' ? 'checked' : '' }}>
                            <div class="p-4 bg-white border border-slate-200 rounded-xl flex flex-col items-center transition peer-checked:border-brand-500 peer-checked:ring-2 peer-checked:ring-brand-200 hover:border-slate-300">
                                <div class="w-11 h-11 rounded-full bg-amber-50 flex items-center justify-center text-amber-500 mb-3">
                                    <i class="fas fa-sun text-lg"></i>
                                </div>
                                <span class="text-xs font-semibold text-slate-700 uppercase tracking-wide">{{ __('Light Mode') }}</span>
                            </div>
                            <div class="absolute -top-2 -right-2 hidden peer-checked:block">
                                <div class="bg-brand-600 text-white w-5 h-5 rounded-full flex items-center justify-center shadow border-2 border-white">
                                    <x-icon name="check" class="h-3 w-3" />
                                </div>
                            </div>
                        </label>

                        {{-- Dark Theme Option (setting preserved for legacy public layout) --}}
                        <label class="relative cursor-pointer">
                            <input type="radio" name="theme" value="dark" class="peer sr-only" {{ old('theme', $setting->theme) == 'dark' ? 'checked' : '' }}>
                            <div class="p-4 bg-white border border-slate-200 rounded-xl flex flex-col items-center transition peer-checked:border-brand-500 peer-checked:ring-2 peer-checked:ring-brand-200 hover:border-slate-300">
                                <div class="w-11 h-11 rounded-full bg-slate-100 flex items-center justify-center text-slate-600 mb-3">
                                    <i class="fas fa-moon text-lg"></i>
                                </div>
                                <span class="text-xs font-semibold text-slate-700 uppercase tracking-wide">{{ __('Dark Mode') }}</span>
                            </div>
                            <div class="absolute -top-2 -right-2 hidden peer-checked:block">
                                <div class="bg-brand-600 text-white w-5 h-5 rounded-full flex items-center justify-center shadow border-2 border-white">
                                    <x-icon name="check" class="h-3 w-3" />
                                </div>
                            </div>
                        </label>
                    </div>
                    @error('theme')
                        <p class="form-error">{{ $message }}</p>
                    @enderror
                </div>

                {{-- Access & Social login toggles --}}
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    {{-- Registration Toggle --}}
                    <div class="p-4 bg-slate-50 border border-slate-200 rounded-xl flex items-center justify-between gap-3">
                        <div class="flex items-center gap-3">
                            <div class="w-10 h-10 rounded-lg bg-white border border-slate-200 flex items-center justify-center text-brand-600 shrink-0">
                                <x-icon name="users" class="h-5 w-5" />
                            </div>
                            <div>
                                <h4 class="text-sm font-semibold text-slate-800">{{ __('Public Register') }}</h4>
                                <p class="text-xs text-slate-400">{{ __('Register accounts') }}</p>
                            </div>
                        </div>
                        <label class="relative inline-flex items-center cursor-pointer">
                            <input type="checkbox" name="registration_enabled" value="1" class="sr-only peer" {{ old('registration_enabled', $setting->registration_enabled) ? 'checked' : '' }}>
                            <div class="w-11 h-6 bg-slate-300 rounded-full peer peer-checked:after:translate-x-full after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border after:border-slate-300 after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-brand-600"></div>
                        </label>
                    </div>

                    {{-- Google Login Toggle --}}
                    <div class="p-4 bg-slate-50 border border-slate-200 rounded-xl flex items-center justify-between gap-3">
                        <div class="flex items-center gap-3">
                            <div class="w-10 h-10 rounded-lg bg-white border border-slate-200 flex items-center justify-center text-red-500 shrink-0">
                                <i class="fab fa-google"></i>
                            </div>
                            <div>
                                <h4 class="text-sm font-semibold text-slate-800">{{ __('Google Login') }}</h4>
                                <p class="text-xs text-slate-400">{{ __('OAuth 2.0 Auth') }}</p>
                            </div>
                        </div>
                        <label class="relative inline-flex items-center cursor-pointer">
                            <input type="checkbox" id="google_login_enabled" name="google_login_enabled" value="1" class="sr-only peer" {{ old('google_login_enabled', $setting->google_login_enabled) ? 'checked' : '' }}>
                            <div class="w-11 h-6 bg-slate-300 rounded-full peer peer-checked:after:translate-x-full after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border after:border-slate-300 after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-brand-600"></div>
                        </label>
                    </div>

                    {{-- Facebook Login Toggle --}}
                    <div class="p-4 bg-slate-50 border border-slate-200 rounded-xl flex items-center justify-between gap-3">
                        <div class="flex items-center gap-3">
                            <div class="w-10 h-10 rounded-lg bg-white border border-slate-200 flex items-center justify-center text-blue-600 shrink-0">
                                <i class="fab fa-facebook"></i>
                            </div>
                            <div>
                                <h4 class="text-sm font-semibold text-slate-800">{{ __('Facebook Login') }}</h4>
                                <p class="text-xs text-slate-400">{{ __('OAuth 2.0 Auth') }}</p>
                            </div>
                        </div>
                        <label class="relative inline-flex items-center cursor-pointer">
                            <input type="checkbox" id="facebook_login_enabled" name="facebook_login_enabled" value="1" class="sr-only peer" {{ old('facebook_login_enabled', $setting->facebook_login_enabled) ? 'checked' : '' }}>
                            <div class="w-11 h-6 bg-slate-300 rounded-full peer peer-checked:after:translate-x-full after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border after:border-slate-300 after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-brand-600"></div>
                        </label>
                    </div>
                </div>

                {{-- Logo Upload --}}
                <div>
                    <label class="form-label">{{ __('App Logo') }} (PNG/JPG)</label>
                    <div onclick="document.getElementById('app_logo').click()" class="relative group cursor-pointer">
                        <div id="uploadPlaceholder" class="{{ $logoUrl ? 'hidden' : '' }} flex flex-col items-center justify-center w-full py-10 bg-slate-50 border-2 border-dashed border-slate-200 rounded-xl hover:border-brand-300 transition">
                            <div class="w-14 h-14 rounded-xl bg-white shadow-sm border border-slate-100 flex items-center justify-center text-slate-400 mb-3">
                                <x-icon name="image" class="h-6 w-6" />
                            </div>
                            <p class="text-sm font-medium text-slate-600">{{ __('Upload Logo') }}</p>
                            <p class="text-xs text-slate-400 mt-1">{{ __('Click to browse') }}</p>
                        </div>

                        <div id="previewContainer" class="{{ $logoUrl ? '' : 'hidden' }} relative w-[200px] h-[200px] mx-auto rounded-2xl overflow-hidden border border-slate-200 shadow-sm transition group-hover:opacity-90">
                            <img id="imagePreview" src="{{ $logoUrl ?? '#' }}" class="w-full h-full object-contain bg-slate-100">
                            <div class="absolute inset-0 bg-slate-900/40 opacity-0 group-hover:opacity-100 transition flex items-center justify-center">
                                <span class="px-3 py-1.5 bg-white rounded-lg text-xs font-medium text-slate-900 shadow inline-flex items-center gap-1">
                                    <x-icon name="upload" class="h-3.5 w-3.5" /> {{ __('Change') }}
                                </span>
                            </div>
                        </div>

                        <input type="file" id="app_logo" name="app_logo" class="hidden" accept="image/*">
                    </div>
                    @error('app_logo')
                        <p class="form-error">{{ $message }}</p>
                    @enderror
                    @if($logoUrl)
                    <div class="text-center mt-2">
                        <a href="#" onclick="removeLogo()" class="text-xs font-medium text-red-500 hover:text-red-600 transition inline-flex items-center gap-1">
                            <x-icon name="trash" class="h-3.5 w-3.5" /> {{ __('Remove Logo') }}
                        </a>
                        <input type="hidden" name="remove_logo" id="remove_logo" value="0">
                    </div>
                    @endif
                </div>

                {{-- Google OAuth Config Section --}}
                <div id="googleConfigSection" class="{{ old('google_login_enabled', $setting->google_login_enabled) ? '' : 'hidden' }} pt-6 border-t border-slate-100 space-y-5 animate-fade-in">
                    <div class="flex items-center gap-3">
                        <div class="w-8 h-8 rounded-lg bg-red-50 flex items-center justify-center text-red-500">
                            <i class="fab fa-google text-sm"></i>
                        </div>
                        <h3 class="text-sm font-semibold text-slate-800">{{ __('Google OAuth Credentials') }}</h3>
                    </div>

                    <div>
                        <label for="google_client_id" class="form-label">{{ __('Google Client ID') }}</label>
                        <input
                            type="text"
                            id="google_client_id"
                            name="google_client_id"
                            class="form-input"
                            placeholder="443741823356-xxx.apps.googleusercontent.com"
                            value="{{ old('google_client_id', $setting->google_client_id) }}"
                        />
                    </div>

                    <div>
                        <label for="google_client_secret" class="form-label">{{ __('Google Client Secret') }}</label>
                        <input
                            type="password"
                            id="google_client_secret"
                            name="google_client_secret"
                            class="form-input"
                            placeholder="GOCSPX-SAhxxx"
                            value="{{ old('google_client_secret', $setting->google_client_secret) }}"
                        />
                    </div>

                    <div>
                        <label for="google_redirect_url" class="form-label">{{ __('Redirect URL (Callback)') }}</label>
                        <input
                            type="text"
                            id="google_redirect_url"
                            name="google_redirect_url"
                            class="form-input"
                            placeholder="{{ url('/auth/google/callback') }}"
                            value="{{ old('google_redirect_url', $setting->google_redirect_url) }}"
                        />
                        <p class="form-help">{{ __('Recommended:') }} {{ url('/auth/google/callback') }}</p>
                    </div>
                </div>

                {{-- Facebook OAuth Config Section --}}
                <div id="facebookConfigSection" class="{{ old('facebook_login_enabled', $setting->facebook_login_enabled) ? '' : 'hidden' }} pt-6 border-t border-slate-100 space-y-5 animate-fade-in">
                    <div class="flex items-center gap-3">
                        <div class="w-8 h-8 rounded-lg bg-blue-50 flex items-center justify-center text-blue-600">
                            <i class="fab fa-facebook text-sm"></i>
                        </div>
                        <h3 class="text-sm font-semibold text-slate-800">{{ __('Facebook OAuth Credentials') }}</h3>
                    </div>

                    <div>
                        <label for="facebook_client_id" class="form-label">{{ __('Facebook App ID') }}</label>
                        <input
                            type="text"
                            id="facebook_client_id"
                            name="facebook_client_id"
                            class="form-input"
                            placeholder="1234567890"
                            value="{{ old('facebook_client_id', $setting->facebook_client_id) }}"
                        />
                    </div>

                    <div>
                        <label for="facebook_client_secret" class="form-label">{{ __('Facebook App Secret') }}</label>
                        <input
                            type="password"
                            id="facebook_client_secret"
                            name="facebook_client_secret"
                            class="form-input"
                            placeholder="••••••••••••••••"
                            value="{{ old('facebook_client_secret', $setting->facebook_client_secret) }}"
                        />
                    </div>

                    <div>
                        <label for="facebook_redirect_url" class="form-label">{{ __('Redirect URL (Callback)') }}</label>
                        <input
                            type="text"
                            id="facebook_redirect_url"
                            name="facebook_redirect_url"
                            class="form-input"
                            placeholder="{{ url('/auth/facebook/callback') }}"
                            value="{{ old('facebook_redirect_url', $setting->facebook_redirect_url) }}"
                        />
                        <p class="form-help">{{ __('Recommended:') }} {{ url('/auth/facebook/callback') }}</p>
                    </div>
                </div>

                {{-- Actions --}}
                <div class="pt-2">
                    <button type="submit" class="btn-primary w-full">
                        <x-icon name="check" class="h-4 w-4" />
                        {{ __('Save Configuration') }}
                    </button>
                </div>
            </form>

            {{-- Footer help --}}
            <div class="mt-8 pt-6 border-t border-slate-100 flex items-start gap-3">
                <div class="w-8 h-8 rounded-full bg-slate-50 flex items-center justify-center border border-slate-100 text-slate-500 shrink-0">
                    <i class="fas fa-info text-xs"></i>
                </div>
                <div>
                    <h4 class="text-xs font-semibold text-slate-700 uppercase mb-1">{{ __('Impact') }}</h4>
                    <p class="text-xs text-slate-400 leading-relaxed">
                        {{ __('These settings affect the global branding of the application.') }}
                    </p>
                </div>
            </div>
        </x-card>
    </div>

    @push('styles')
    <style>
        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(-10px); }
            to { opacity: 1; transform: translateY(0); }
        }
        .animate-fade-in {
            animation: fadeIn 0.4s ease-out forwards;
        }
    </style>
    @endpush

    @push('scripts')
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
    @endpush
</x-layouts.admin>
