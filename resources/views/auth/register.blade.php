<x-layouts.guest :title="__('Create your account')">
    <div class="mb-8">
        <h1 class="text-2xl font-semibold text-slate-900">{{ __('Create your account') }}</h1>
        <p class="mt-1 text-sm text-slate-500">{{ __('Register to start using the system') }}</p>
    </div>

    <form class="space-y-6" method="POST" action="{{ route('register') }}">
        @csrf

        <div>
            <label for="name" class="form-label">{{ __('Name') }}</label>
            <input id="name" type="text" name="name" value="{{ old('name') }}"
                   class="form-input @error('name') border-red-400 @enderror"
                   required autocomplete="name" autofocus>
            @error('name')<p class="form-error">{{ $message }}</p>@enderror
        </div>

        <div>
            <label for="email" class="form-label">{{ __('E-Mail Address') }}</label>
            <input id="email" type="email" name="email" value="{{ old('email') }}"
                   class="form-input @error('email') border-red-400 @enderror"
                   required autocomplete="email">
            @error('email')<p class="form-error">{{ $message }}</p>@enderror
        </div>

        <div>
            <label for="password" class="form-label">{{ __('Password') }}</label>
            <input id="password" type="password" name="password"
                   class="form-input @error('password') border-red-400 @enderror"
                   required autocomplete="new-password">
            @error('password')<p class="form-error">{{ $message }}</p>@enderror
        </div>

        <div>
            <label for="password-confirm" class="form-label">{{ __('Confirm Password') }}</label>
            <input id="password-confirm" type="password" name="password_confirmation"
                   class="form-input" required autocomplete="new-password">
        </div>

        <button type="submit" class="btn-primary w-full py-2.5">{{ __('Register') }}</button>

        @if(app(\App\Models\Setting::class)->first() && app(\App\Models\Setting::class)->first()->google_login_enabled)
        <div class="relative my-6">
            <div class="absolute inset-0 flex items-center"><div class="w-full border-t border-slate-200"></div></div>
            <div class="relative flex justify-center text-sm">
                <span class="px-2 bg-surface-100 text-slate-500 font-medium">{{ __('Or continue with') }}</span>
            </div>
        </div>

        <div class="space-y-3">
            <a href="{{ route('auth.google') }}"
               class="btn-secondary w-full py-2.5 group">
                <svg class="w-5 h-5 mr-1 group-hover:scale-110 transition-transform duration-200" viewBox="0 0 24 24">
                    <path fill="#EA4335" d="M24 12.27c0-.85-.07-1.71-.22-2.54H12v4.81h6.74c-.29 1.58-1.18 2.91-2.52 3.81v3.17h4.08C22.71 19.55 24 16.19 24 12.27z"/>
                    <path fill="#4285F4" d="M12.48 24c3.24 0 5.96-1.07 7.95-2.91l-4.08-3.17c-1.13.76-2.58 1.21-4.01 1.21-3.12 0-5.76-2.11-6.71-4.94H1.47v3.29C3.48 21.43 7.68 24 12.48 24z"/>
                    <path fill="#FBBC05" d="M5.77 14.19c-.24-.72-.37-1.48-.37-2.19s.13-1.47.37-2.19V6.52H1.47C.53 8.32 0 10.32 0 12.4s.53 4.08 1.47 5.88l4.3-3.09z"/>
                    <path fill="#34A853" d="M12.48 4.97c1.76 0 3.34.61 4.58 1.8l3.43-3.43C18.39 1.21 15.68 0 12.48 0 7.68 0 3.48 2.57 1.47 6.52l4.3 3.29c.95-2.83 3.59-4.84 6.71-4.84z"/>
                </svg>
                {{ __('Continue with Google') }}
            </a>
            @endif

            @if(app(\App\Models\Setting::class)->first() && app(\App\Models\Setting::class)->first()->facebook_login_enabled)
            @if(!(app(\App\Models\Setting::class)->first() && app(\App\Models\Setting::class)->first()->google_login_enabled))
            <div class="relative my-6">
                <div class="absolute inset-0 flex items-center"><div class="w-full border-t border-slate-200"></div></div>
                <div class="relative flex justify-center text-sm">
                    <span class="px-2 bg-surface-100 text-slate-500 font-medium">{{ __('Or continue with') }}</span>
                </div>
            </div>
            @endif
            <a href="{{ route('auth.facebook') }}"
               class="btn-secondary w-full py-2.5 group">
                <i class="fab fa-facebook text-[#1877F2] text-xl mr-1 group-hover:scale-110 transition-transform duration-200"></i>
                {{ __('Continue with Facebook') }}
            </a>
            @endif
        </div>
    </form>

    <p class="mt-8 text-center text-sm text-slate-500">
        {{ __('Already have an account?') }}
        <a href="{{ route('login') }}" class="font-medium text-brand-600 hover:text-brand-700">{{ __('Login') }}</a>
    </p>
</x-layouts.guest>
