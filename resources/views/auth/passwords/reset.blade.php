<x-layouts.guest :title="__('Update Your Password')">
    <div class="mb-8">
        <h1 class="text-2xl font-semibold text-slate-900">{{ __('Update Your Password') }}</h1>
        <p class="mt-1 text-sm text-slate-500">{{ __('Please enter your new secure password below.') }}</p>
    </div>

    <form class="space-y-6" method="POST" action="{{ route('password.update') }}">
        @csrf

        <input type="hidden" name="token" value="{{ $token }}">

        <div>
            <label for="email" class="form-label">{{ __('E-Mail Address') }}</label>
            <input id="email" type="email" name="email" value="{{ $email ?? old('email') }}"
                   class="form-input @error('email') border-red-400 @enderror"
                   required autocomplete="email" autofocus placeholder="name@example.com">
            @error('email')<p class="form-error">{{ $message }}</p>@enderror
        </div>

        <div>
            <label for="password" class="form-label">{{ __('New Password') }}</label>
            <input id="password" type="password" name="password"
                   class="form-input @error('password') border-red-400 @enderror"
                   required autocomplete="new-password" placeholder="••••••••">
            @error('password')<p class="form-error">{{ $message }}</p>@enderror
        </div>

        <div>
            <label for="password-confirm" class="form-label">{{ __('Confirm New Password') }}</label>
            <input id="password-confirm" type="password" name="password_confirmation"
                   class="form-input" required autocomplete="new-password" placeholder="••••••••">
        </div>

        <button type="submit" class="btn-primary w-full py-2.5">{{ __('Update Password') }}</button>
    </form>
</x-layouts.guest>
