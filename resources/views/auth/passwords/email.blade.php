@extends('layouts.app')

@section('content')
<div class="min-h-screen flex flex-col items-center justify-center bg-gray-50 dark:bg-[#18191a] py-12 px-4 sm:px-6 lg:px-8">
    <div class="max-w-md w-full space-y-8">
        <div>
            <div class="flex justify-center">
                <div class="w-20 h-20 rounded-3xl bg-indigo-600 flex items-center justify-center text-white shadow-lg shadow-indigo-200 dark:shadow-none mb-6 text-3xl">
                    <i class="fas fa-key"></i>
                </div>
            </div>
            <h2 class="text-center text-3xl leading-9 font-extrabold text-gray-900 dark:text-white tracking-tight">
                {{ __('Reset Password') }}
            </h2>
            <p class="mt-2 text-center text-sm leading-5 text-gray-600 dark:text-gray-400 font-medium">
                {{ __('We will send you a secure link to reset your password.') }}
            </p>
        </div>

        <div class="bg-white dark:bg-[#242526] py-10 px-8 shadow-xl shadow-gray-200/50 dark:shadow-none border border-gray-100 dark:border-[#3a3b3c] rounded-[2.5rem] sm:px-10 transform transition-all">
            <form class="space-y-6" method="POST" action="{{ route('password.email') }}">
                @csrf

                <div class="space-y-2">
                    <label for="email" class="block text-xs font-bold text-gray-400 dark:text-gray-500 uppercase tracking-widest px-1">
                        {{ __('E-Mail Address') }}
                    </label>
                    <div class="group relative">
                        <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none text-gray-400 group-focus-within:text-indigo-600 transition-colors">
                            <i class="fas fa-envelope text-sm"></i>
                        </div>
                        <input id="email" type="email"
                               class="appearance-none block w-full pl-11 pr-4 py-4 bg-gray-50 dark:bg-[#3a3b3c] border-2 border-transparent rounded-2xl text-gray-900 dark:text-white placeholder-gray-400 focus:outline-none focus:ring-0 focus:border-indigo-500 focus:bg-white dark:focus:bg-[#3a3b3c] transition-all duration-200 @error('email') border-rose-500 bg-rose-50 dark:bg-rose-900/20 @enderror"
                               name="email" value="{{ old('email') }}" required autocomplete="email" autofocus placeholder="name@example.com">
                    </div>
                    @error('email')
                        <p class="mt-2 text-xs font-semibold text-rose-500 px-1 flex items-center">
                            <span class="w-1 h-1 bg-rose-500 rounded-full mr-2"></span>
                            {{ $message }}
                        </p>
                    @enderror
                </div>

                <div>
                    <button type="submit"
                            class="w-full relative group flex justify-center py-4 px-4 border border-transparent text-sm font-bold rounded-2xl text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 transition-all duration-200 shadow-lg shadow-indigo-200 dark:shadow-none transform active:scale-95 overflow-hidden">
                        <span class="relative z-10 flex items-center">
                            {{ __('Send Password Reset Link') }}
                            <i class="fas fa-paper-plane ml-3 opacity-50 group-hover:opacity-100 transition-opacity"></i>
                        </span>
                        <div class="absolute inset-0 bg-gradient-to-r from-transparent via-white/10 to-transparent -translate-x-full group-hover:translate-x-full transition-transform duration-1000 ease-in-out"></div>
                    </button>
                </div>
            </form>

            <div class="mt-8 pt-6 border-t border-gray-100 dark:border-[#3a3b3c] text-center text-sm font-medium">
                <a href="{{ route('login') }}" class="text-indigo-600 dark:text-indigo-400 hover:text-indigo-500 transition-colors flex items-center justify-center">
                    <i class="fas fa-arrow-left mr-2 text-xs"></i>
                    {{ __('Back to Login') }}
                </a>
            </div>
        </div>
    </div>
</div>
@endsection
