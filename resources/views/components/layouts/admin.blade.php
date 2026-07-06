@props(['header' => null, 'subheader' => null, 'title' => null])

<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ $title ?? $header ?? ($setting->app_name ?? config('app.name')) }}</title>
    @if(!empty($setting?->app_logo))
        <link rel="icon" type="image/png" href="{{ asset('storage/' . $setting->app_logo) }}?v={{ time() }}">
    @endif

    {{-- Inter font (matches design system) --}}
    <link rel="preconnect" href="https://rsms.me/">
    <link rel="stylesheet" href="https://rsms.me/inter/inter.css">

    {{-- Icons (transition safety for shared partials still using FontAwesome) --}}
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">

    {{-- Tailwind (compiled via Vite — new design system) --}}
    @vite(['resources/css/admin.css'])

    {{-- Alpine.js --}}
    <script defer src="https://cdn.jsdelivr.net/npm/@alpinejs/collapse@3.x.x/dist/cdn.min.js"></script>
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>

    @stack('styles')
</head>
<body class="min-h-screen bg-surface-100">
    <div x-data="{ mobileMenu: false }" class="flex min-h-screen">
        @include('layouts.partials.sidebar')

        <div x-show="mobileMenu" x-cloak x-transition.opacity
             @click="mobileMenu = false"
             class="fixed inset-0 bg-slate-900/50 z-30 lg:hidden"></div>

        <div class="flex-1 min-w-0 flex flex-col">
            @include('layouts.partials.header')

            <main class="flex-1 p-4 sm:p-6">
                @if ($header)
                    <div class="mb-6 flex items-center justify-between gap-4 flex-wrap">
                        <div>
                            <h1 class="text-2xl font-semibold text-slate-900">{{ $header }}</h1>
                            @if ($subheader)
                                <p class="text-sm text-slate-500 mt-1">{{ $subheader }}</p>
                            @endif
                        </div>
                        @isset($actions)
                            <div class="flex items-center gap-2">{{ $actions }}</div>
                        @endisset
                    </div>
                @endif

                {{ $slot }}
            </main>
        </div>
    </div>

    <x-flash />

    @auth
        @include('layouts.partials.academic-modals')
    @endauth

    @include('layouts.partials.progress')

    @stack('scripts')
</body>
</html>
