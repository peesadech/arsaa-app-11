<x-layouts.guest :title="__('Verify Your Email Address')">
    <div class="mb-8">
        <h1 class="text-2xl font-semibold text-slate-900">{{ __('Verify Your Email Address') }}</h1>
        <p class="mt-1 text-sm text-slate-500">{{ __('Before proceeding, please check your email for a verification link.') }}</p>
    </div>

    @if (session('resent'))
        <div class="mb-6 p-4 bg-emerald-50 border-l-4 border-emerald-500 rounded-r-lg" role="alert">
            <p class="text-sm font-medium text-emerald-800">{{ __('A fresh verification link has been sent to your email address.') }}</p>
        </div>
    @endif

    <div class="card">
        <div class="card-body text-sm text-slate-600">
            <p>{{ __('If you did not receive the email') }}:</p>
            @if (Route::has('verification.resend'))
                <form class="mt-4" method="POST" action="{{ route('verification.resend') }}">
                    @csrf
                    <button type="submit" class="btn-primary w-full py-2.5">{{ __('click here to request another') }}</button>
                </form>
            @endif
        </div>
    </div>
</x-layouts.guest>
