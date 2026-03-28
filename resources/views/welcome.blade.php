@extends('layouts.app')

@section('content')
<div class="relative flex items-top justify-center min-h-screen sm:items-center sm:pt-0 bg-gray-50/50">
    <div class="max-w-6xl mx-auto sm:px-6 lg:px-8">
        <div class="flex justify-center pt-8 sm:justify-start sm:pt-0">
            <div class="text-center w-full">
                @if($setting->app_logo)
                    <div class="relative inline-block group">
                        <div class="absolute inset-0 bg-indigo-500 blur-2xl opacity-20 group-hover:opacity-40 transition-opacity duration-1000 rounded-full"></div>
                        <img src="{{ asset('storage/' . $setting->app_logo) }}" alt="Logo" class="relative hover:-translate-y-2 transition-transform duration-500 drop-shadow-2xl" style="height: 120px; width: auto; display: block; margin: 0 auto 20px;">
                    </div>
                @endif
                <h1 class="text-6xl font-black tracking-tighter text-slate-800 mb-8 drop-shadow-sm">
                    {{ $setting->app_name }}
                </h1>
            </div>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mt-12">
            <a href="https://laravel.com/docs" class="block p-6 bg-white rounded-3xl shadow-xl shadow-gray-200/50 border border-gray-50 hover:border-indigo-100 group transition-all duration-300 hover:-translate-y-1">
                <div class="w-12 h-12 rounded-2xl bg-indigo-50 flex items-center justify-center text-indigo-600 mb-4 group-hover:scale-110 transition-transform">
                    <i class="fas fa-book text-xl"></i>
                </div>
                <h3 class="text-lg font-bold text-gray-800 mb-2">{{ __('Documentation') }}</h3>
                <p class="text-sm text-gray-500">{{ __('Comprehensive guides and documentation.') }}</p>
            </a>

            <a href="https://laracasts.com" class="block p-6 bg-white rounded-3xl shadow-xl shadow-gray-200/50 border border-gray-50 hover:border-sky-100 group transition-all duration-300 hover:-translate-y-1">
                <div class="w-12 h-12 rounded-2xl bg-sky-50 flex items-center justify-center text-sky-600 mb-4 group-hover:scale-110 transition-transform">
                    <i class="fas fa-video text-xl"></i>
                </div>
                <h3 class="text-lg font-bold text-gray-800 mb-2">{{ __('Laracasts') }}</h3>
                <p class="text-sm text-gray-500">{{ __('Amazing video tutorials for Laravel.') }}</p>
            </a>

            <a href="https://laravel-news.com" class="block p-6 bg-white rounded-3xl shadow-xl shadow-gray-200/50 border border-gray-50 hover:border-rose-100 group transition-all duration-300 hover:-translate-y-1">
                <div class="w-12 h-12 rounded-2xl bg-rose-50 flex items-center justify-center text-rose-600 mb-4 group-hover:scale-110 transition-transform">
                    <i class="fas fa-newspaper text-xl"></i>
                </div>
                <h3 class="text-lg font-bold text-gray-800 mb-2">{{ __('Laravel News') }}</h3>
                <p class="text-sm text-gray-500">{{ __('Latest news and ecosystem updates.') }}</p>
            </a>

            <a href="https://github.com/laravel/laravel" class="block p-6 bg-white rounded-3xl shadow-xl shadow-gray-200/50 border border-gray-50 hover:border-gray-200 group transition-all duration-300 hover:-translate-y-1">
                <div class="w-12 h-12 rounded-2xl bg-gray-50 flex items-center justify-center text-gray-600 mb-4 group-hover:scale-110 transition-transform">
                    <i class="fab fa-github text-xl"></i>
                </div>
                <h3 class="text-lg font-bold text-gray-800 mb-2">{{ __('GitHub') }}</h3>
                <p class="text-sm text-gray-500">{{ __('Contribute to the framework code.') }}</p>
            </a>
        </div>
        
        <div class="mt-12 text-center text-xs font-bold text-gray-400 uppercase tracking-widest">
            Laravel v{{ Illuminate\Foundation\Application::VERSION }} (PHP v{{ PHP_VERSION }})
        </div>
    </div>
</div>
@endsection
