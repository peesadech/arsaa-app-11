@props(['title' => null])

@php
    $appName    = $setting->app_name ?? config('app.name');
    $appLogoUrl = !empty($setting?->app_logo) ? asset('storage/' . $setting->app_logo) : null;
    $appInitial = mb_strtoupper(mb_substr($appName, 0, 1));
@endphp
<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ $title ?? $appName }}</title>
    @if($appLogoUrl)
        <link rel="icon" type="image/png" href="{{ $appLogoUrl }}?v={{ time() }}">
    @endif

    @include('layouts.partials.head-assets')

    @stack('styles')
</head>
<body class="antialiased min-h-screen bg-surface-100">
    <div class="min-h-screen grid lg:grid-cols-2">
        {{-- Left: brand panel --}}
        <div class="hidden lg:flex flex-col justify-between p-10 bg-gradient-to-br from-brand-700 via-brand-600 to-brand-500 text-white relative overflow-hidden">
            <div class="absolute inset-0 opacity-20" style="background-image: radial-gradient(circle at 20% 20%, white 1px, transparent 1px), radial-gradient(circle at 80% 60%, white 1px, transparent 1px); background-size: 40px 40px;"></div>
            <div class="relative flex items-center gap-3">
                @if($appLogoUrl)
                    <img src="{{ $appLogoUrl }}" alt="{{ $appName }}" class="h-11 w-11 rounded-xl object-contain bg-white/15 backdrop-blur border border-white/20">
                @else
                    <div class="h-11 w-11 rounded-xl bg-white/15 backdrop-blur flex items-center justify-center font-bold text-xl">{{ $appInitial }}</div>
                @endif
                <div>
                    <div class="font-semibold text-lg leading-tight">{{ $appName }}</div>
                    <div class="text-xs text-white/75">{{ __('Backoffice') }}</div>
                </div>
            </div>
            <div class="relative max-w-md">
                <h2 class="text-4xl font-semibold leading-tight">{{ __('School management, all in one place.') }}</h2>
                <p class="mt-4 text-white/80">{{ __('Manage academics, students, timetables, and grades from a single dashboard.') }}</p>
            </div>
            <div class="relative text-xs text-white/60">© {{ date('Y') }} {{ $appName }}</div>
        </div>

        {{-- Right: content panel --}}
        <div class="flex items-center justify-center p-6 sm:p-10">
            <div class="w-full max-w-md">
                <div class="lg:hidden mb-8 flex items-center gap-3">
                    @if($appLogoUrl)
                        <img src="{{ $appLogoUrl }}" alt="{{ $appName }}" class="h-11 w-11 rounded-xl object-contain border border-slate-200">
                    @else
                        <div class="h-11 w-11 rounded-xl bg-brand-600 text-white flex items-center justify-center font-bold text-xl">{{ $appInitial }}</div>
                    @endif
                    <div class="font-semibold text-slate-900 text-lg">{{ $appName }}</div>
                </div>
                {{ $slot }}
            </div>
        </div>
    </div>

    <x-flash />
    @include('layouts.partials.progress')
    @stack('scripts')
</body>
</html>
