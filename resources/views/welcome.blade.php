<x-layouts.guest :title="$setting->app_name">
    <div class="text-center">
        @if($setting->app_logo)
            <img src="{{ asset('storage/' . $setting->app_logo) }}" alt="{{ $setting->app_name }}"
                 class="mx-auto h-24 w-24 rounded-2xl object-contain border border-slate-200 shadow-soft mb-6">
        @else
            <div class="mx-auto h-24 w-24 rounded-2xl bg-brand-600 text-white flex items-center justify-center text-4xl font-bold shadow-soft mb-6">
                {{ mb_strtoupper(mb_substr($setting->app_name, 0, 1)) }}
            </div>
        @endif

        <h1 class="text-4xl font-bold tracking-tight text-slate-900">{{ $setting->app_name }}</h1>
        <p class="mt-3 text-slate-500">{{ __('School management, all in one place.') }}</p>

        <div class="mt-10 flex flex-col sm:flex-row items-center justify-center gap-3">
            <a href="{{ route('login') }}" class="btn-primary w-full sm:w-auto px-8 py-2.5">{{ __('Login') }}</a>
            @if (Route::has('register') && $setting->registration_enabled)
                <a href="{{ route('register') }}" class="btn-secondary w-full sm:w-auto px-8 py-2.5">{{ __('Register') }}</a>
            @endif
        </div>
    </div>
</x-layouts.guest>
