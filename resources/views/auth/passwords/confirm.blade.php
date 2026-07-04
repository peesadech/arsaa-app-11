<x-layouts.guest :title="__('Confirm Access')">
    <div class="mb-8">
        <h1 class="text-2xl font-semibold text-slate-900">{{ __('Confirm Access') }}</h1>
        <p class="mt-1 text-sm text-slate-500">{{ __('This is a secure area. Please confirm your password.') }}</p>
    </div>

    <form class="space-y-6" method="POST" action="{{ route('password.confirm') }}">
        @csrf

        <div>
            <label for="password" class="form-label">{{ __('Password') }}</label>
            <input id="password" type="password" name="password"
                   class="form-input @error('password') border-red-400 @enderror"
                   required autocomplete="current-password">
            @error('password')<p class="form-error">{{ $message }}</p>@enderror
        </div>

        <button type="submit" class="btn-primary w-full py-2.5">{{ __('Confirm Password') }}</button>

        @if (Route::has('password.request'))
            <a class="block text-center text-sm font-medium text-brand-600 hover:text-brand-700" href="{{ route('password.request') }}">
                {{ __('Forgot Your Password?') }}
            </a>
        @endif
    </form>
</x-layouts.guest>
