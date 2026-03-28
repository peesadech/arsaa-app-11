@extends('layouts.app')

@push('styles')
<style>
    .translation-row:hover {
        background-color: rgba(99, 102, 241, 0.03);
    }
    .dark .translation-row:hover {
        background-color: rgba(99, 102, 241, 0.08);
    }
</style>
@endpush

@section('content')
<div class="min-h-screen bg-gray-50 dark:bg-[#18191a] py-8 px-4 sm:px-6 lg:px-8 transition-colors duration-300">
    <div class="max-w-5xl mx-auto">
        <!-- Header -->
        <div class="flex flex-col md:flex-row md:items-center md:justify-between mb-10 space-y-4 md:space-y-0">
            <div class="flex items-center space-x-4">
                <a href="{{ route('admin.languages.index') }}"
                   class="group flex items-center justify-center w-10 h-10 rounded-xl bg-white dark:bg-[#242526] shadow-sm border border-gray-200 dark:border-[#3a3b3c] text-gray-500 dark:text-gray-400 hover:text-indigo-600 dark:hover:text-indigo-400 hover:border-indigo-300 transition-all duration-200">
                    <i class="fas fa-arrow-left group-hover:-translate-x-0.5 transition-transform"></i>
                </a>
                <div>
                    <h1 class="text-2xl font-extrabold text-gray-900 dark:text-white tracking-tight">
                        {{ __('Edit Translations') }} — <span class="text-indigo-600">{{ $language->flag }} {{ $language->native_name }}</span>
                    </h1>
                    <p class="text-sm text-gray-500 dark:text-gray-400 font-medium px-1">
                        {{ __('Manage translation strings for') }} {{ $language->name }} ({{ $language->code }})
                    </p>
                </div>
            </div>
            <div class="flex items-center gap-3">
                <span class="text-xs font-bold text-gray-400 dark:text-gray-500">
                    <span id="translationCount">{{ count($translations) }}</span> {{ __('Translations') }}
                </span>
            </div>
        </div>

        <!-- Main Card -->
        <div class="bg-white dark:bg-[#242526] rounded-[2.5rem] shadow-xl shadow-gray-200/50 dark:shadow-none border border-gray-100 dark:border-[#3a3b3c] overflow-hidden">
            <div class="h-2 bg-gradient-to-r from-indigo-500 via-purple-500 to-pink-500"></div>

            <form action="{{ route('admin.languages.translations.update', $language->code) }}" method="POST">
                @csrf
                @method('PUT')

                <!-- Search -->
                <div class="p-6 border-b border-gray-100 dark:border-[#3a3b3c]">
                    <div class="relative">
                        <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none text-gray-400">
                            <i class="fas fa-search text-sm"></i>
                        </div>
                        <input type="text" id="searchTranslations"
                            class="block w-full pl-10 pr-4 py-4 bg-gray-50 dark:bg-[#3a3b3c] border-2 border-transparent rounded-2xl text-gray-900 dark:text-white placeholder-gray-400 focus:outline-none focus:ring-0 focus:border-indigo-500 transition-all duration-200"
                            placeholder="{{ __('Search translations...') }}" />
                    </div>
                </div>

                <!-- Translations Table -->
                <div class="divide-y divide-gray-50 dark:divide-[#3a3b3c]/50" id="translationsList">
                    <!-- Table Header -->
                    <div class="grid grid-cols-12 gap-4 px-6 py-4 bg-gray-50/50 dark:bg-[#18191a]/30">
                        <div class="col-span-5 text-xs font-bold text-gray-400 dark:text-gray-500 uppercase tracking-widest">
                            {{ __('Translation Key') }} (English)
                        </div>
                        <div class="col-span-7 text-xs font-bold text-gray-400 dark:text-gray-500 uppercase tracking-widest">
                            {{ __('Translation Value') }} ({{ $language->native_name }})
                        </div>
                    </div>

                    @foreach($translations as $key => $value)
                    <div class="translation-row grid grid-cols-12 gap-4 px-6 py-3 items-center" data-key="{{ strtolower($key) }}" data-value="{{ strtolower($value) }}">
                        <div class="col-span-5">
                            <div class="text-sm font-bold text-gray-700 dark:text-gray-300 truncate" title="{{ $key }}">
                                {{ $key }}
                            </div>
                            @if($language->code !== 'en' && isset($enTranslations[$key]))
                            <div class="text-[10px] text-gray-400 dark:text-gray-500 truncate mt-0.5" title="{{ $enTranslations[$key] }}">
                                EN: {{ $enTranslations[$key] }}
                            </div>
                            @endif
                        </div>
                        <div class="col-span-7">
                            <input type="text" name="translations[{{ $key }}]" value="{{ $value }}"
                                class="block w-full px-4 py-2.5 bg-gray-50 dark:bg-[#3a3b3c] border-2 border-transparent rounded-xl text-sm text-gray-900 dark:text-white placeholder-gray-400 focus:outline-none focus:ring-0 focus:border-indigo-500 focus:bg-white dark:focus:bg-[#3a3b3c] transition-all duration-200"
                                placeholder="{{ $key }}" />
                        </div>
                    </div>
                    @endforeach
                </div>

                <!-- Footer / Save -->
                <div class="p-6 border-t border-gray-100 dark:border-[#3a3b3c] bg-gray-50/50 dark:bg-[#18191a]/30 flex items-center justify-between">
                    <a href="{{ route('admin.languages.index') }}"
                       class="px-6 py-3 bg-white dark:bg-[#242526] text-gray-700 dark:text-gray-300 font-bold rounded-2xl border-2 border-gray-100 dark:border-[#3a3b3c] hover:border-gray-200 dark:hover:border-[#4a4b4c] hover:bg-gray-50 dark:hover:bg-[#3a3b3c] active:scale-95 transition-all duration-200">
                        {{ __('Back to List') }}
                    </a>
                    <button type="submit"
                        class="px-8 py-3 bg-indigo-600 text-white font-bold rounded-2xl hover:bg-indigo-700 active:scale-95 transition-all duration-200 shadow-lg shadow-indigo-200 dark:shadow-none">
                        <i class="fas fa-save mr-2 opacity-75"></i>
                        {{ __('Save Translations') }}
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

@push('scripts')
<script>
    document.getElementById('searchTranslations').addEventListener('input', function() {
        const search = this.value.toLowerCase();
        const rows = document.querySelectorAll('.translation-row');
        let visibleCount = 0;

        rows.forEach(function(row) {
            const key = row.dataset.key || '';
            const value = row.dataset.value || '';
            const match = key.includes(search) || value.includes(search);
            row.style.display = match ? '' : 'none';
            if (match) visibleCount++;
        });

        document.getElementById('translationCount').textContent = visibleCount;
    });
</script>
@endpush
@endsection
