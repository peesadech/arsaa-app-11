@extends('layouts.app')

@section('content')
<div class="min-h-screen flex items-center justify-center bg-gray-100 py-12 px-4 sm:px-6 lg:px-8">
    <div class="max-w-md w-full space-y-8">
        <div>
            <h2 class="mt-6 text-center text-3xl leading-9 font-extrabold text-gray-900">
                {{ __('Sign in to your account') }}
            </h2>
            <p class="mt-2 text-center text-sm leading-5 text-gray-600">
                {{ __('Use your email and password to login') }}
            </p>
            @if (session('error'))
                <div class="mt-4 p-4 bg-red-50 border-l-4 border-red-500 rounded-r-xl shadow-sm">
                    <p class="text-xs font-bold text-red-800">{{ session('error') }}</p>
                </div>
            @endif
        </div>
        <div class="bg-white py-8 px-6 shadow rounded-lg sm:px-10">
            <form class="space-y-6" method="POST" action="{{ route('login') }}">
                @csrf

                <div>
                    <label for="email" class="block text-sm font-medium text-gray-700">
                        {{ __('E-Mail Address') }}
                    </label>
                    <div class="mt-1">
                        <input id="email" type="email"
                               class="appearance-none block w-full px-3 py-2 border @error('email') border-red-500 @else border-gray-300 @enderror rounded-md shadow-sm placeholder-gray-400 focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm"
                               name="email" value="{{ old('email') }}" required autocomplete="email" autofocus>
                    </div>
                    @error('email')
                        <p class="mt-2 text-sm text-red-600" role="alert">
                            <strong>{{ $message }}</strong>
                        </p>
                    @enderror
                </div>

                <div>
                    <label for="password" class="block text-sm font-medium text-gray-700">
                        {{ __('Password') }}
                    </label>
                    <div class="mt-1 relative">
                        <input id="password" type="password"
                               class="appearance-none block w-full pl-3 pr-10 py-2 border @error('password') border-red-500 @else border-gray-300 @enderror rounded-md shadow-sm placeholder-gray-400 focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm"
                               name="password" required autocomplete="current-password">
                        <div class="absolute inset-y-0 right-0 pr-3 flex items-center cursor-pointer" onclick="togglePassword()">
                            <i id="toggleIcon" class="fas fa-eye text-gray-400 hover:text-gray-600"></i>
                        </div>
                    </div>
                    @error('password')
                        <p class="mt-2 text-sm text-red-600" role="alert">
                            <strong>{{ $message }}</strong>
                        </p>
                    @enderror
                </div>

                <div class="flex items-center justify-between">
                    <div class="flex items-center">
                        <input id="remember" type="checkbox" name="remember"
                               class="h-4 w-4 text-indigo-600 focus:ring-indigo-500 border-gray-300 rounded"
                               {{ old('remember') ? 'checked' : '' }}>
                        <label for="remember" class="ml-2 block text-sm text-gray-900">
                            {{ __('Remember Me') }}
                        </label>
                    </div>

                    @if (Route::has('password.request'))
                        <div class="text-sm">
                            <a class="font-medium text-indigo-600 hover:text-indigo-500" href="{{ route('password.request') }}">
                                {{ __('Forgot Your Password?') }}
                            </a>
                        </div>
                    @endif
                </div>

                <div>
                    <button type="submit"
                            class="w-full flex justify-center py-2.5 px-4 border border-transparent text-sm font-bold rounded-xl text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 transition-all duration-200">
                        {{ __('Login') }}
                    </button>
                </div>

                @if(app(\App\Models\Setting::class)->first() && app(\App\Models\Setting::class)->first()->google_login_enabled)
                <div class="relative my-6">
                    <div class="absolute inset-0 flex items-center">
                        <div class="w-full border-t border-gray-200"></div>
                    </div>
                    <div class="relative flex justify-center text-sm">
                        <span class="px-2 bg-white text-gray-500 font-medium">{{ __('Or continue with') }}</span>
                    </div>
                </div>

                <div class="space-y-3">
                    <a href="{{ route('auth.google') }}"
                       class="w-full flex items-center justify-center py-2.5 px-4 border border-gray-200 rounded-xl bg-white text-sm font-bold text-gray-700 hover:bg-gray-50 hover:border-gray-300 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-gray-200 transition-all duration-200 group no-underline">
                        <svg class="w-5 h-5 mr-3 group-hover:scale-110 transition-transform duration-200" viewBox="0 0 24 24">
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
                        <div class="absolute inset-0 flex items-center">
                            <div class="w-full border-t border-gray-200"></div>
                        </div>
                        <div class="relative flex justify-center text-sm">
                            <span class="px-2 bg-white text-gray-500 font-medium">{{ __('Or continue with') }}</span>
                        </div>
                    </div>
                    @endif
                    <a href="{{ route('auth.facebook') }}"
                       class="w-full flex items-center justify-center py-2.5 px-4 border border-blue-100 rounded-xl bg-white text-sm font-bold text-gray-700 hover:bg-blue-50/30 hover:border-blue-200 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-100 transition-all duration-200 group no-underline">
                        <i class="fab fa-facebook text-[#1877F2] text-xl mr-3 group-hover:scale-110 transition-transform duration-200"></i>
                        {{ __('Continue with Facebook') }}
                    </a>
                    @endif
                </div>
            </form>
        </div>
    </div>
</div>
@push('scripts')
<script>
function togglePassword() {
    const passwordInput = document.getElementById('password');
    const toggleIcon = document.getElementById('toggleIcon');
    if (passwordInput.type === 'password') {
        passwordInput.type = 'text';
        toggleIcon.classList.remove('fa-eye');
        toggleIcon.classList.add('fa-eye-slash');
    } else {
        passwordInput.type = 'password';
        toggleIcon.classList.remove('fa-eye-slash');
        toggleIcon.classList.add('fa-eye');
    }
}

$(document).ready(function() {
    $('form.space-y-6').on('submit', function(e) {
        e.preventDefault();
        const form = $(this);
        const email = $('#email').val();
        const password = $('#password').val();

        // First, try to login via API to get JWT
        axios.post('/api/auth/login', {
            email: email,
            password: password
        })
        .then(response => {
            if (response.data.access_token) {
                localStorage.setItem('access_token', response.data.access_token);
                // After getting the token, proceed with the standard session login
                form.off('submit').submit();
            }
        })
        .catch(error => {
            console.error('API Login failed', error);
            // Even if API fails, try regular login so the user gets validation errors
            form.off('submit').submit();
        });
    });
});
</script>
@endpush
@endsection
