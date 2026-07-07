@php
    $driverLabels = [
        'mysql'   => 'MySQL',
        'mariadb' => 'MariaDB',
        'pgsql'   => 'PostgreSQL',
    ];
    $methodLabels = [
        'mysqldump' => 'mysqldump (' . __('native, fastest') . ')',
        'pg_dump'   => 'pg_dump (' . __('native, fastest') . ')',
        'php'       => __('Pure PHP (fallback)'),
    ];
@endphp

<x-layouts.admin :header="__('Backup Database')" :subheader="__('Download a full .sql backup (structure + data)')">
    <x-slot name="actions">
        <x-button variant="secondary" icon="arrow-left" :href="route('admin.dashboard')">{{ __('Back') }}</x-button>
    </x-slot>

    <div class="max-w-3xl mx-auto space-y-6">

        {{-- Error flash (redirect on failure) --}}
        @if (session('error'))
            <div class="rounded-2xl border border-red-100 bg-red-50 p-4">
                <div class="flex items-center gap-2 text-red-700">
                    <x-icon name="x" class="h-5 w-5 shrink-0" />
                    <p class="text-sm font-medium">{{ session('error') }}</p>
                </div>
            </div>
        @endif

        <x-card>
            {{-- Header --}}
            <div class="flex flex-col items-center text-center mb-8">
                <div class="w-16 h-16 rounded-2xl bg-brand-50 text-brand-600 flex items-center justify-center mb-4">
                    <x-icon name="database" class="h-8 w-8" />
                </div>
                <h2 class="text-lg font-semibold text-slate-900">{{ __('Database Backup') }}</h2>
                <p class="text-sm text-slate-500 max-w-md mt-1">
                    {{ __('Generate a complete SQL dump including all tables, structure and data. The file can be re-imported to restore the database.') }}
                </p>
            </div>

            {{-- Info grid --}}
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 mb-8">
                <div class="p-4 bg-slate-50 border border-slate-200 rounded-xl">
                    <p class="text-xs font-medium text-slate-400 uppercase tracking-wide">{{ __('Database Driver') }}</p>
                    <p class="text-sm font-semibold text-slate-800 mt-1">{{ $driverLabels[$info['driver']] ?? $info['driver'] }}</p>
                </div>
                <div class="p-4 bg-slate-50 border border-slate-200 rounded-xl">
                    <p class="text-xs font-medium text-slate-400 uppercase tracking-wide">{{ __('Database Name') }}</p>
                    <p class="text-sm font-semibold text-slate-800 mt-1">{{ $info['database'] }}</p>
                </div>
                <div class="p-4 bg-slate-50 border border-slate-200 rounded-xl">
                    <p class="text-xs font-medium text-slate-400 uppercase tracking-wide">{{ __('Tables') }}</p>
                    <p class="text-sm font-semibold text-slate-800 mt-1">{{ number_format($info['table_count']) }}</p>
                </div>
                <div class="p-4 bg-slate-50 border border-slate-200 rounded-xl">
                    <p class="text-xs font-medium text-slate-400 uppercase tracking-wide">{{ __('Database Size') }}</p>
                    <p class="text-sm font-semibold text-slate-800 mt-1">{{ $info['size_human'] ?? __('N/A') }}</p>
                </div>
                <div class="p-4 bg-slate-50 border border-slate-200 rounded-xl">
                    <p class="text-xs font-medium text-slate-400 uppercase tracking-wide">{{ __('Backup Method') }}</p>
                    <p class="text-sm font-semibold text-slate-800 mt-1">{{ $methodLabels[$info['method']] ?? $info['method'] }}</p>
                    <p class="text-xs text-slate-400 mt-0.5">{{ $info['method_reason'] }}</p>
                </div>
                <div class="p-4 bg-slate-50 border border-slate-200 rounded-xl">
                    <p class="text-xs font-medium text-slate-400 uppercase tracking-wide">{{ __('Last Backup') }}</p>
                    <p class="text-sm font-semibold text-slate-800 mt-1">{{ $info['last_backup_at'] ?? __('Never') }}</p>
                </div>
            </div>

            {{-- Download form --}}
            <form action="{{ route('admin.database-backup.download') }}" method="POST" id="backupForm">
                @csrf
                <button type="submit" class="btn-primary w-full" id="backupBtn">
                    <x-icon name="download" class="h-4 w-4" />
                    <span id="backupBtnText">{{ __('Download Backup') }}</span>
                </button>
            </form>

            {{-- Footer help --}}
            <div class="mt-8 pt-6 border-t border-slate-100 flex items-start gap-3">
                <div class="w-8 h-8 rounded-full bg-slate-50 flex items-center justify-center border border-slate-100 text-slate-500 shrink-0">
                    <i class="fas fa-info text-xs"></i>
                </div>
                <div>
                    <h4 class="text-xs font-semibold text-slate-700 uppercase mb-1">{{ __('Note') }}</h4>
                    <p class="text-xs text-slate-400 leading-relaxed">
                        {{ __('The backup runs on the server and may take a while for large databases. Please keep this page open until the download starts.') }}
                    </p>
                </div>
            </div>
        </x-card>
    </div>

    {{-- Loading overlay --}}
    <div id="backupOverlay" class="fixed inset-0 z-50 hidden items-center justify-center bg-slate-900/50">
        <div class="bg-white rounded-2xl shadow-xl px-8 py-6 flex flex-col items-center gap-3 max-w-xs text-center">
            <svg class="animate-spin h-8 w-8 text-brand-600" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 0 1 8-8V0C5.373 0 0 5.373 0 12h4z"></path>
            </svg>
            <p class="text-sm font-semibold text-slate-800">{{ __('Creating backup...') }}</p>
            <p class="text-xs text-slate-400">{{ __('This may take a moment. Do not close the window.') }}</p>
        </div>
    </div>

    @push('scripts')
    <script>
        (function () {
            const form    = document.getElementById('backupForm');
            const overlay = document.getElementById('backupOverlay');
            const btn     = document.getElementById('backupBtn');

            form.addEventListener('submit', function () {
                overlay.classList.remove('hidden');
                overlay.classList.add('flex');
                btn.disabled = true;
                btn.classList.add('opacity-70', 'cursor-not-allowed');

                // The response is a file download, so the page never navigates.
                // Re-enable the UI shortly after the browser starts the download.
                setTimeout(function () {
                    overlay.classList.add('hidden');
                    overlay.classList.remove('flex');
                    btn.disabled = false;
                    btn.classList.remove('opacity-70', 'cursor-not-allowed');
                }, 4000);
            });
        })();
    </script>
    @endpush
</x-layouts.admin>
