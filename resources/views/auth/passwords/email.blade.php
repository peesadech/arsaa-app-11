<x-layouts.guest :title="__('Reset Password')">
    <div class="mb-8">
        <h1 class="text-2xl font-semibold text-slate-900">{{ __('Reset Password') }}</h1>
        <p class="mt-1 text-sm text-slate-500">{{ __('We will send you a secure link to reset your password.') }}</p>
    </div>

    <form class="space-y-6" method="POST" action="{{ route('password.email') }}">
        @csrf

        <div>
            <label for="email" class="form-label">{{ __('E-Mail Address') }}</label>
            <input id="email" type="email" name="email" value="{{ old('email') }}"
                   class="form-input @error('email') border-red-400 @enderror"
                   required autocomplete="email" autofocus placeholder="name@example.com">
            @error('email')<p class="form-error">{{ $message }}</p>@enderror
        </div>

        <button type="submit" class="btn-primary w-full py-2.5">{{ __('Send Password Reset Link') }}</button>
    </form>

    <div class="mt-8 pt-6 border-t border-slate-100 text-center text-sm">
        <a href="{{ route('login') }}" class="inline-flex items-center gap-2 font-medium text-brand-600 hover:text-brand-700">
            <i class="fas fa-arrow-left text-xs"></i>
            {{ __('Back to Login') }}
        </a>
    </div>
</x-layouts.guest>
