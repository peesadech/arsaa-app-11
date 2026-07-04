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

    {{-- Tailwind (CDN) --}}
    <script src="https://cdn.tailwindcss.com?plugins=forms,typography,aspect-ratio"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    fontFamily: {
                        sans: ['Inter', 'ui-sans-serif', 'system-ui', 'sans-serif'],
                    },
                    colors: {
                        brand: {
                            50: '#eff6ff', 100: '#dbeafe', 200: '#bfdbfe', 300: '#93c5fd',
                            400: '#60a5fa', 500: '#3b82f6', 600: '#2563eb', 700: '#1d4ed8',
                            800: '#1e40af', 900: '#1e3a8a',
                        },
                        surface: {
                            50: '#f8fafc', 100: '#f1f5f9', 200: '#e2e8f0', 300: '#cbd5e1',
                        },
                    },
                    boxShadow: {
                        card: '0 1px 2px 0 rgb(0 0 0 / 0.04), 0 1px 3px 0 rgb(0 0 0 / 0.06)',
                        soft: '0 2px 6px 0 rgb(15 23 42 / 0.06)',
                    },
                    borderRadius: {
                        xl: '0.9rem',
                        '2xl': '1.1rem',
                    },
                },
            },
        };
    </script>
    <style type="text/tailwindcss">
        @layer base {
            html { font-family: 'Inter', ui-sans-serif, system-ui, sans-serif; }
            body { @apply bg-surface-100 text-slate-700 antialiased; }
            [x-cloak] { display: none !important; }
            a { text-decoration: none; }
        }
        @layer components {
            .btn { @apply inline-flex items-center justify-center gap-2 px-4 py-2 rounded-lg text-sm font-medium transition focus:outline-none focus:ring-2 focus:ring-offset-1; }
            .btn-primary { @apply btn bg-brand-600 text-white hover:bg-brand-700 focus:ring-brand-400; }
            .btn-secondary { @apply btn bg-white text-slate-700 border border-slate-200 hover:bg-slate-50 focus:ring-slate-300; }
            .btn-danger { @apply btn bg-red-600 text-white hover:bg-red-700 focus:ring-red-400; }
            .btn-ghost { @apply btn text-slate-600 hover:bg-slate-100; }

            .card { @apply bg-white rounded-2xl shadow-card border border-slate-100; }
            .card-body { @apply p-6; }

            .form-input, .form-textarea, .form-select, .form-multiselect {
                @apply w-full rounded-lg border-slate-200 bg-white shadow-sm focus:border-brand-400 focus:ring-brand-200 text-sm;
            }
            .form-label { @apply block text-sm font-medium text-slate-700 mb-1.5; }
            .form-help { @apply text-xs text-slate-500 mt-1; }
            .form-error { @apply text-xs text-red-600 mt-1; }

            .badge { @apply inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-xs font-medium; }
            .badge-gray { @apply badge bg-slate-100 text-slate-700; }
            .badge-blue { @apply badge bg-brand-50 text-brand-700; }
            .badge-green { @apply badge bg-emerald-50 text-emerald-700; }
            .badge-amber { @apply badge bg-amber-50 text-amber-700; }
            .badge-red { @apply badge bg-red-50 text-red-700; }

            .sidebar-link { @apply flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm font-medium text-slate-600 hover:bg-slate-100 hover:text-slate-900 transition; }
            .sidebar-link.active { @apply bg-brand-50 text-brand-700; }

            .table-wrap { @apply card overflow-hidden; }
            .table-wrap table { @apply w-full text-sm; }
            .table-wrap thead th { @apply px-5 py-3 text-left font-medium text-slate-500 uppercase tracking-wide text-xs bg-slate-50; }
            .table-wrap tbody td { @apply px-5 py-4 border-t border-slate-100 text-slate-700; }
            .table-wrap tbody tr:hover { @apply bg-slate-50; }

            .toggle { @apply relative inline-flex h-6 w-11 shrink-0 cursor-pointer rounded-full border-2 border-transparent bg-slate-300 transition-colors duration-200 ease-in-out focus:outline-none; }
            .toggle-dot { @apply pointer-events-none inline-block h-5 w-5 transform rounded-full bg-white shadow ring-0 transition duration-200 ease-in-out translate-x-0; }
            .peer:checked ~ .toggle { @apply bg-brand-600; }
            .peer:checked ~ .toggle .toggle-dot { @apply translate-x-5; }
        }
    </style>

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
