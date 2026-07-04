@props([
    'endpoint',
    'columns' => [],
    'state' => [],
    'meta' => [],
    'searchPlaceholder' => 'Search...',
    'showSearch' => true,
    'perPageOptions' => [10, 25, 50, 100],
    'colspan' => null,
])

@php
    $defaultState = [
        'search'     => '',
        'sort_by'    => null,
        'sort_order' => 'desc',
        'per_page'   => 10,
        'page'       => 1,
    ];
    $initialState = array_merge($defaultState, $state);

    $defaultMeta = [
        'total'        => 0,
        'per_page'     => $initialState['per_page'],
        'current_page' => 1,
        'last_page'    => 1,
        'from'         => 0,
        'to'           => 0,
    ];
    $initialMeta = array_merge($defaultMeta, $meta);
@endphp

<div
    x-data="{
        endpoint: @js($endpoint),
        state: @js($initialState),
        meta: @js($initialMeta),
        loading: false,
        abortCtrl: null,
        sort(key) {
            if (!key) return;
            if (this.state.sort_by === key) {
                this.state.sort_order = this.state.sort_order === 'asc' ? 'desc' : 'asc';
            } else {
                this.state.sort_by = key;
                this.state.sort_order = 'asc';
            }
            this.state.page = 1;
            this.reload();
        },
        goto(page) {
            page = Number(page);
            if (!page || page < 1 || page > this.meta.last_page) return;
            this.state.page = page;
            this.reload();
        },
        changeFilter() {
            this.state.page = 1;
            this.reload();
        },
        async reload() {
            this.abortCtrl?.abort();
            this.abortCtrl = new AbortController();
            this.loading = true;
            const params = new URLSearchParams();
            Object.entries(this.state).forEach(([k, v]) => {
                if (v !== null && v !== undefined && v !== '') params.set(k, v);
            });
            try {
                const res = await fetch(`${this.endpoint}?${params.toString()}`, {
                    headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' },
                    signal: this.abortCtrl.signal,
                });
                if (!res.ok) throw new Error('Request failed: ' + res.status);
                const data = await res.json();
                this.$refs.tbody.innerHTML = data.html;
                if (window.Alpine) window.Alpine.initTree(this.$refs.tbody);
                this.meta = data.meta;
                const url = new URL(window.location);
                url.search = params.toString();
                window.history.replaceState({}, '', url);
            } catch (e) {
                if (e.name !== 'AbortError') console.error(e);
            } finally {
                this.loading = false;
            }
        }
    }"
    class="relative"
>
    <div class="px-6 py-4 border-b border-slate-100 flex flex-wrap items-center gap-3">
        @isset($filters)
            <div class="flex flex-wrap items-center gap-2 flex-1 min-w-0">
                {{ $filters }}
            </div>
        @endisset

        @if ($showSearch)
            <div class="ml-auto flex items-center gap-2">
                <label class="relative block">
                    <span class="absolute inset-y-0 left-0 flex items-center pl-3 text-slate-400 pointer-events-none">
                        <x-icon name="search" class="h-4 w-4" />
                    </span>
                    <input type="search"
                           x-model="state.search"
                           @input.debounce.350ms="changeFilter()"
                           placeholder="{{ $searchPlaceholder }}"
                           class="form-input pl-9 rounded-lg w-full sm:w-64">
                </label>
            </div>
        @endif
    </div>

    <div class="overflow-x-auto relative">
        <div x-show="loading" x-cloak class="absolute inset-0 bg-white/60 z-10 flex items-center justify-center">
            <div class="h-8 w-8 border-2 border-brand-500 border-t-transparent rounded-full animate-spin"></div>
        </div>
        <table class="w-full text-sm">
            <thead>
                <tr>
                    @foreach ($columns as $col)
                        @php
                            $key = $col['key'] ?? null;
                            $sortable = ($col['sortable'] ?? false) && $key;
                            $align = $col['align'] ?? 'left';
                        @endphp
                        <th class="px-5 py-3 text-{{ $align }} font-medium text-slate-500 uppercase tracking-wide text-xs bg-slate-50">
                            @if ($sortable)
                                <button type="button"
                                        @click="sort(@js($key))"
                                        class="inline-flex items-center gap-1 hover:text-slate-900 transition">
                                    <span>{{ $col['label'] }}</span>
                                    <span class="inline-flex flex-col leading-none">
                                        <svg :class="state.sort_by === @js($key) && state.sort_order === 'asc' ? 'text-brand-600' : 'text-slate-300'"
                                             class="h-2.5 w-2.5" fill="currentColor" viewBox="0 0 12 12"><path d="M6 3l4 5H2z"/></svg>
                                        <svg :class="state.sort_by === @js($key) && state.sort_order === 'desc' ? 'text-brand-600' : 'text-slate-300'"
                                             class="h-2.5 w-2.5 -mt-0.5" fill="currentColor" viewBox="0 0 12 12"><path d="M6 9L2 4h8z"/></svg>
                                    </span>
                                </button>
                            @else
                                {{ $col['label'] }}
                            @endif
                        </th>
                    @endforeach
                </tr>
            </thead>
            <tbody x-ref="tbody">{{ $rows ?? '' }}</tbody>
        </table>
    </div>

    <div class="px-5 py-3 border-t border-slate-100 bg-slate-50 flex flex-wrap items-center justify-between gap-3">
        <div class="flex flex-wrap items-center gap-2 text-sm text-slate-600">
            <label class="flex items-center gap-2">
                <span>{{ __('Rows per page') }}:</span>
                <select x-model.number="state.per_page"
                        @change="changeFilter()"
                        class="form-select py-1.5 pl-2 pr-7 text-sm rounded-lg border-slate-200 bg-white">
                    @foreach ($perPageOptions as $n)
                        <option value="{{ $n }}">{{ $n }}</option>
                    @endforeach
                </select>
            </label>
            <span class="ml-3" x-text="meta.total > 0 ? `{{ __('Showing') }} ${meta.from}–${meta.to} {{ __('of') }} ${meta.total}` : '{{ __('No records') }}'"></span>
        </div>

        <div class="flex items-center gap-1">
            <button type="button" @click="goto(1)" :disabled="state.page <= 1"
                    class="btn-ghost px-2.5 py-1.5 text-sm disabled:opacity-40 disabled:cursor-not-allowed" title="{{ __('First page') }}">&laquo;</button>
            <button type="button" @click="goto(state.page - 1)" :disabled="state.page <= 1"
                    class="btn-ghost px-2.5 py-1.5 text-sm disabled:opacity-40 disabled:cursor-not-allowed" title="{{ __('Previous') }}">&lsaquo;</button>
            <span class="px-3 text-sm text-slate-700">
                <span x-text="meta.current_page"></span> / <span x-text="meta.last_page"></span>
            </span>
            <button type="button" @click="goto(state.page + 1)" :disabled="state.page >= meta.last_page"
                    class="btn-ghost px-2.5 py-1.5 text-sm disabled:opacity-40 disabled:cursor-not-allowed" title="{{ __('Next') }}">&rsaquo;</button>
            <button type="button" @click="goto(meta.last_page)" :disabled="state.page >= meta.last_page"
                    class="btn-ghost px-2.5 py-1.5 text-sm disabled:opacity-40 disabled:cursor-not-allowed" title="{{ __('Last page') }}">&raquo;</button>
        </div>
    </div>
</div>
