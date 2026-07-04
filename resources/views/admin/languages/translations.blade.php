<x-layouts.admin :header="__('Edit Translations')" :subheader="__('Manage translation strings for') . ' ' . $language->name . ' (' . $language->code . ')'">
    <x-slot name="actions">
        <span class="badge-gray">
            <span id="translationCount">{{ count($translations) }}</span> {{ __('Translations') }}
        </span>
        <x-button variant="secondary" icon="arrow-left" :href="route('admin.languages.index')">{{ __('Back to List') }}</x-button>
    </x-slot>

    <x-card padded="false">
        <div class="px-6 py-4 border-b border-slate-100 flex items-center gap-2">
            <span class="text-xl">{{ $language->flag }}</span>
            <h3 class="text-base font-semibold text-slate-900">{{ $language->native_name }}</h3>
        </div>

        <form action="{{ route('admin.languages.translations.update', $language->code) }}" method="POST">
            @csrf
            @method('PUT')

            {{-- Search --}}
            <div class="p-6 border-b border-slate-100">
                <label class="relative block">
                    <span class="absolute inset-y-0 left-0 flex items-center pl-3 text-slate-400 pointer-events-none">
                        <x-icon name="search" class="h-4 w-4" />
                    </span>
                    <input type="text" id="searchTranslations"
                        class="form-input pl-9"
                        placeholder="{{ __('Search translations...') }}" />
                </label>
            </div>

            {{-- Translations Table --}}
            <div class="divide-y divide-slate-100" id="translationsList">
                {{-- Table Header --}}
                <div class="grid grid-cols-12 gap-4 px-6 py-3 bg-slate-50">
                    <div class="col-span-5 text-xs font-medium text-slate-500 uppercase tracking-wide">
                        {{ __('Translation Key') }} (English)
                    </div>
                    <div class="col-span-7 text-xs font-medium text-slate-500 uppercase tracking-wide">
                        {{ __('Translation Value') }} ({{ $language->native_name }})
                    </div>
                </div>

                @foreach($translations as $key => $value)
                <div class="translation-row grid grid-cols-12 gap-4 px-6 py-3 items-center" data-key="{{ strtolower($key) }}" data-value="{{ strtolower($value) }}">
                    <div class="col-span-5">
                        <div class="text-sm font-medium text-slate-700 truncate" title="{{ $key }}">
                            {{ $key }}
                        </div>
                        @if($language->code !== 'en' && isset($enTranslations[$key]))
                        <div class="text-[10px] text-slate-400 truncate mt-0.5" title="{{ $enTranslations[$key] }}">
                            EN: {{ $enTranslations[$key] }}
                        </div>
                        @endif
                    </div>
                    <div class="col-span-7">
                        <input type="text" name="translations[{{ $key }}]" value="{{ $value }}"
                            class="form-input text-sm"
                            placeholder="{{ $key }}" />
                    </div>
                </div>
                @endforeach
            </div>

            {{-- Footer / Save --}}
            <div class="p-6 border-t border-slate-100 bg-slate-50 flex items-center justify-between">
                <a href="{{ route('admin.languages.index') }}" class="btn-secondary">
                    {{ __('Back to List') }}
                </a>
                <button type="submit" class="btn-primary">
                    <x-icon name="check" class="h-4 w-4" />
                    {{ __('Save Translations') }}
                </button>
            </div>
        </form>
    </x-card>

    @push('styles')
    <style>
        .translation-row:hover {
            background-color: rgba(37, 99, 235, 0.03);
        }
    </style>
    @endpush

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
</x-layouts.admin>
