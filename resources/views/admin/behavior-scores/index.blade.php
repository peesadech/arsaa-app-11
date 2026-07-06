@php
    $isMerit = $type === 'merit';
    $title   = $isMerit ? __('Merit Scores') : __('Demerit Scores');
@endphp
<x-layouts.admin :header="$title" :subheader="__('Manage behavior score items — name and score value')">
    <x-slot name="actions">
        <x-button icon="plus" :href="route('admin.behavior-scores.create', $type)">{{ __('New Item') }}</x-button>
    </x-slot>

    <div x-data="{ deleteTarget: null, reorder: false }" x-on:open-delete.window="deleteTarget = $event.detail">
        {{-- Toolbar --}}
        <div class="flex justify-end mb-4" x-show="!reorder">
            <button type="button" class="btn-secondary" x-on:click="reorder = true">
                <x-icon name="layers" class="h-4 w-4" /> {{ __('Reorder') }}
            </button>
        </div>

        {{-- ===== List (data table) ===== --}}
        <div x-show="!reorder">
            <x-card padded="false">
                <x-data-table
                    :endpoint="route('admin.behavior-scores.index', $type)"
                    :columns="[
                        ['key' => 'name', 'label' => __('Name'), 'sortable' => true],
                        ['key' => 'score', 'label' => __('Score'), 'sortable' => true, 'align' => 'right'],
                        ['key' => null, 'label' => __('Status'), 'sortable' => false],
                        ['key' => null, 'label' => '', 'align' => 'right'],
                    ]"
                    :state="[
                        'search'     => request('search', ''),
                        'sort_by'    => request('sort_by', 'sort_order'),
                        'sort_order' => request('sort_order', 'asc'),
                        'per_page'   => (int) request('per_page', 10),
                        'page'       => (int) request('page', 1),
                        'is_active'  => request('is_active', ''),
                    ]"
                    :meta="[
                        'total'        => $items->total(),
                        'per_page'     => $items->perPage(),
                        'current_page' => $items->currentPage(),
                        'last_page'    => $items->lastPage(),
                        'from'         => $items->firstItem() ?? 0,
                        'to'           => $items->lastItem() ?? 0,
                    ]"
                    :show-search="false"
                >
                    <x-slot name="filters">
                        <select x-model="state.is_active" @change="changeFilter()"
                                class="form-select rounded-lg w-full sm:w-48">
                            <option value="">{{ __('All Status') }}</option>
                            <option value="1">{{ __('Active') }}</option>
                            <option value="0">{{ __('Inactive') }}</option>
                        </select>
                        <label class="relative block w-full sm:w-64">
                            <span class="absolute inset-y-0 left-0 flex items-center pl-3 text-slate-400 pointer-events-none">
                                <x-icon name="search" class="h-4 w-4" />
                            </span>
                            <input type="search" x-model="state.search" @input.debounce.350ms="changeFilter()"
                                   placeholder="{{ __('Search') }}..." class="form-input pl-9 rounded-lg w-full">
                        </label>
                    </x-slot>

                    <x-slot name="rows">
                        @include('admin.behavior-scores._rows', ['items' => $items, 'type' => $type])
                    </x-slot>
                </x-data-table>
            </x-card>
        </div>

        {{-- ===== Reorder mode (drag to sort) ===== --}}
        <div x-show="reorder" x-cloak>
            <x-card>
                <div class="flex flex-wrap items-center justify-between gap-3 mb-4">
                    <h2 class="text-xs font-semibold text-slate-500 uppercase tracking-wide">{{ __('Drag to reorder') }}</h2>
                    <div class="flex items-center gap-2">
                        <button type="button" class="btn-secondary" x-on:click="reorder = false">{{ __('Cancel') }}</button>
                        <button type="button" id="save-order-btn" class="btn-primary"><x-icon name="check" class="h-4 w-4" /> {{ __('Save order') }}</button>
                    </div>
                </div>

                @if($allItems->isEmpty())
                <p class="text-sm text-slate-400 py-6 text-center">{{ __('No records') }}</p>
                @else
                <div class="space-y-2" id="reorder-list">
                    @foreach($allItems as $it)
                    <div class="reorder-row flex items-center gap-3 p-3 rounded-xl border border-slate-100 bg-slate-50" data-id="{{ $it->id }}">
                        <span class="drag-handle cursor-grab active:cursor-grabbing text-slate-300 hover:text-slate-500 select-none" title="{{ __('Drag to reorder') }}">
                            <x-icon name="dots" class="h-4 w-4" />
                        </span>
                        <span class="flex-1 text-sm font-medium text-slate-800">{{ $it->name }}</span>
                        <span class="text-sm font-semibold {{ $it->score >= 0 ? 'text-emerald-600' : 'text-red-600' }}">{{ $it->score + 0 }}</span>
                        @unless($it->is_active)<x-badge color="gray">{{ __('Inactive') }}</x-badge>@endunless
                    </div>
                    @endforeach
                </div>
                @endif
            </x-card>
        </div>

        {{-- Delete confirm modal --}}
        <div x-show="deleteTarget !== null" x-cloak class="fixed inset-0 z-50 flex items-center justify-center p-4" x-transition.opacity>
            <div class="absolute inset-0 bg-slate-900/50" x-on:click="deleteTarget = null"></div>
            <div class="relative bg-white rounded-2xl shadow-2xl w-full max-w-md p-6">
                <div class="flex items-start gap-4">
                    <div class="h-12 w-12 rounded-full bg-red-50 text-red-600 flex items-center justify-center shrink-0"><x-icon name="trash" class="h-6 w-6" /></div>
                    <div>
                        <h3 class="text-lg font-semibold text-slate-900">{{ __('Confirm Deletion') }}</h3>
                        <p class="text-sm text-slate-500 mt-1">{{ __('Are you sure you want to permanently remove') }} <span class="font-medium text-slate-700" x-text="deleteTarget?.name"></span>?</p>
                    </div>
                </div>
                <div class="mt-6 flex justify-end gap-2">
                    <button type="button" class="btn-secondary" x-on:click="deleteTarget = null">{{ __('Cancel') }}</button>
                    <form method="POST" x-bind:action="`{{ url('admin/behavior-scores/'.$type) }}/${deleteTarget?.id}`">
                        @csrf @method('DELETE')
                        <button type="submit" class="btn-danger">{{ __('Confirm Delete') }}</button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    @push('scripts')
    <script>
    (function () {
        const list = document.getElementById('reorder-list');
        if (!list) return;
        const CSRF = '{{ csrf_token() }}';
        const REORDER_URL = '{{ route('admin.behavior-scores.reorder', $type) }}';

        let dragEl = null;
        list.querySelectorAll('.reorder-row').forEach(row => {
            const handle = row.querySelector('.drag-handle');
            if (!handle) return;
            handle.addEventListener('mousedown', () => row.setAttribute('draggable', 'true'));
            handle.addEventListener('mouseup', () => row.setAttribute('draggable', 'false'));
            row.addEventListener('dragstart', e => { dragEl = row; row.classList.add('opacity-40'); e.dataTransfer.effectAllowed = 'move'; });
            row.addEventListener('dragend', () => { row.classList.remove('opacity-40'); row.setAttribute('draggable', 'false'); dragEl = null; });
        });

        list.addEventListener('dragover', e => {
            e.preventDefault();
            if (!dragEl) return;
            const after = afterElement(e.clientY);
            if (after == null) list.appendChild(dragEl);
            else list.insertBefore(dragEl, after);
        });

        function afterElement(y) {
            const els = [...list.querySelectorAll('.reorder-row:not(.opacity-40)')];
            return els.reduce((closest, child) => {
                const box = child.getBoundingClientRect();
                const offset = y - box.top - box.height / 2;
                if (offset < 0 && offset > closest.offset) return { offset, element: child };
                return closest;
            }, { offset: -Infinity }).element;
        }

        const saveBtn = document.getElementById('save-order-btn');
        if (saveBtn) {
            saveBtn.addEventListener('click', () => {
                const order = [...list.querySelectorAll('.reorder-row')].map(r => r.dataset.id);
                saveBtn.disabled = true;
                fetch(REORDER_URL, {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': CSRF, 'Accept': 'application/json' },
                    body: JSON.stringify({ order }),
                })
                .then(r => r.ok ? r.json() : Promise.reject(r))
                .then(() => window.location.reload())
                .catch(() => { saveBtn.disabled = false; alert('{{ __('Save failed') }}'); });
            });
        }
    })();
    </script>
    @endpush
</x-layouts.admin>
