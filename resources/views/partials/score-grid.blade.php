{{--
    ตารางกรอกคะแนนแบบ Excel + จัดการรายการคะแนน + Import/Export
    ตัวแปรที่ต้องส่งเข้ามา:
      $routePrefix, $openedCourse, $items, $enrollments, $matrix, $summaries, $gradeSettings, $categories
--}}
@php
    $activeItems = $items->where('is_active', true);
@endphp

@if(session('status'))
<div class="mb-6 flex items-center gap-2 p-4 bg-emerald-50 border border-emerald-200 rounded-xl text-emerald-700 text-sm">
    <x-icon name="check" class="h-4 w-4" />{{ session('status') }}
</div>
@endif

@if($errors->any())
<div class="mb-6 p-4 bg-red-50 border border-red-200 rounded-xl text-red-700 text-sm">
    {{ $errors->first() }}
</div>
@endif

{{-- Toolbar --}}
<div class="mb-4 flex flex-wrap items-center justify-between gap-3">
    <div class="flex flex-wrap items-center gap-2 text-xs text-slate-500">
        <span class="font-medium">{{ __('Grade Criteria') }}:</span>
        @foreach($gradeSettings as $gs)
        <x-badge :color="$gs->is_pass ? 'green' : 'red'">{{ $gs->grade }} = {{ $gs->min_score + 0 }}-{{ $gs->max_score + 0 }}</x-badge>
        @endforeach
    </div>
    <div class="flex items-center gap-2">
        <a href="{{ route($routePrefix . '.export', $openedCourse->id) }}" class="btn-secondary">
            <x-icon name="download" class="h-4 w-4" /> {{ __('Export') }}
        </a>
        <button type="button" class="btn-secondary" onclick="document.getElementById('score-import-input').click()">
            <x-icon name="upload" class="h-4 w-4" /> {{ __('Import') }}
        </button>
        <form id="score-import-form" action="{{ route($routePrefix . '.import', $openedCourse->id) }}" method="POST" enctype="multipart/form-data" class="hidden">
            @csrf
            <input id="score-import-input" type="file" name="file" accept=".csv,text/csv" onchange="document.getElementById('score-import-form').submit()">
        </form>
        <button type="button" class="btn-secondary" onclick="document.getElementById('manage-items-panel').classList.toggle('hidden')">
            <x-icon name="cog" class="h-4 w-4" /> {{ __('Manage Score Items') }}
        </button>
    </div>
</div>

{{-- Manage score items panel --}}
<div id="manage-items-panel" class="hidden mb-6">
    <x-card>
        @php
            $weightSum = $items->where('counts_toward_total', true)
                ->sum(fn($it) => $it->weight !== null ? (float) $it->weight : (float) $it->full_score);
            $weightSum = round($weightSum, 2);
        @endphp

        <div class="mb-4 flex items-center justify-between gap-3">
            <h2 class="text-xs font-semibold text-slate-500 uppercase tracking-wide">{{ __('Manage Score Items') }}</h2>
            <div class="flex items-center gap-2 text-sm" id="weight-sum-bar">
                <span class="text-slate-500">{{ __('Total (counts to grade)') }}:</span>
                <span id="weight-sum" class="font-bold">{{ $weightSum + 0 }}</span>
                <span class="text-slate-400">/ 100</span>
                <span id="weight-sum-badge"
                      class="inline-flex items-center px-2 py-0.5 rounded-lg text-xs font-semibold {{ abs($weightSum - 100) < 0.01 ? 'bg-emerald-100 text-emerald-700' : 'bg-red-100 text-red-700' }}">
                    {{ abs($weightSum - 100) < 0.01 ? '✓' : '≠ 100' }}
                </span>
            </div>
        </div>

        {{-- existing items (draggable to reorder) --}}
        <div class="space-y-2 mb-5" id="items-sortable">
            @foreach($items as $item)
            <form action="{{ route($routePrefix . '.items.update', [$openedCourse->id, $item->id]) }}" method="POST"
                  data-item-id="{{ $item->id }}"
                  class="item-row flex flex-wrap items-end gap-2 p-3 rounded-xl border border-slate-100 bg-slate-50">
                @csrf @method('PUT')
                <span class="drag-handle cursor-grab active:cursor-grabbing text-slate-300 hover:text-slate-500 pb-2 select-none" title="{{ __('Drag to reorder') }}">
                    <x-icon name="dots" class="h-4 w-4" />
                </span>
                <div class="w-36">
                    <label class="text-xs text-slate-400">{{ __('Category') }}</label>
                    <select name="category" class="form-input text-sm">
                        @foreach($categories as $key => $label)
                        <option value="{{ $key }}" @selected($item->category === $key)>{{ $label }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="flex-1 min-w-40">
                    <label class="text-xs text-slate-400">{{ __('Name') }}</label>
                    <input type="text" name="name" value="{{ $item->name }}" class="form-input text-sm" required>
                </div>
                <div class="w-24">
                    <label class="text-xs text-slate-400">{{ __('Full Score') }}</label>
                    <input type="number" name="full_score" value="{{ $item->full_score + 0 }}" step="0.01" min="0" class="form-input text-sm text-right" data-full required>
                </div>
                <div class="w-24">
                    <label class="text-xs text-slate-400">{{ __('Weight') }}</label>
                    <input type="number" name="weight" value="{{ $item->weight !== null ? $item->weight + 0 : '' }}" step="0.01" min="0" placeholder="{{ __('raw') }}" class="form-input text-sm text-right" data-weight>
                </div>
                <label class="flex items-center gap-1.5 text-xs text-slate-500 pb-2">
                    <input type="checkbox" name="counts_toward_total" value="1" @checked($item->counts_toward_total) class="rounded border-slate-300" data-counts>
                    {{ __('Counts to total') }}
                </label>
                <button type="submit" class="btn-secondary" title="{{ __('Save') }}"><x-icon name="check" class="h-4 w-4" /></button>
                <button type="submit" formaction="{{ route($routePrefix . '.items.destroy', [$openedCourse->id, $item->id]) }}"
                        formmethod="POST" class="btn-danger" title="{{ __('Delete') }}"
                        onclick="return confirm('{{ __('Delete this score item and its scores?') }}')">
                    <span class="hidden">delete</span><x-icon name="trash" class="h-4 w-4" />
                </button>
            </form>
            @endforeach
        </div>

        {{-- add new item --}}
        <form action="{{ route($routePrefix . '.items.store', $openedCourse->id) }}" method="POST"
              class="flex flex-wrap items-end gap-2 p-3 rounded-xl border-2 border-dashed border-brand-200 bg-brand-50/40">
            @csrf
            <div class="w-36">
                <label class="text-xs text-slate-400">{{ __('Category') }}</label>
                <select name="category" class="form-input text-sm">
                    @foreach($categories as $key => $label)
                    <option value="{{ $key }}">{{ $label }}</option>
                    @endforeach
                </select>
            </div>
            <div class="flex-1 min-w-40">
                <label class="text-xs text-slate-400">{{ __('Name') }}</label>
                <input type="text" name="name" class="form-input text-sm" placeholder="{{ __('e.g. Quiz 1') }}" required>
            </div>
            <div class="w-24">
                <label class="text-xs text-slate-400">{{ __('Full Score') }}</label>
                <input type="number" name="full_score" value="10" step="0.01" min="0" class="form-input text-sm text-right" required>
            </div>
            <div class="w-24">
                <label class="text-xs text-slate-400">{{ __('Weight') }}</label>
                <input type="number" name="weight" step="0.01" min="0" placeholder="{{ __('raw') }}" class="form-input text-sm text-right">
            </div>
            <label class="flex items-center gap-1.5 text-xs text-slate-500 pb-2">
                <input type="checkbox" name="counts_toward_total" value="1" checked class="rounded border-slate-300">
                {{ __('Counts to total') }}
            </label>
            <button type="submit" class="btn-primary"><x-icon name="plus" class="h-4 w-4" /> {{ __('Add') }}</button>
        </form>
        <p class="mt-3 text-xs text-slate-400">
            {{ __('Weight blank = use raw score. Set weight to scale an item into the total (e.g. full 20, weight 10). Drag the handle to reorder. Total that counts toward the grade should equal 100.') }}
        </p>
    </x-card>
</div>

@push('scripts')
<script>
(function () {
    const CSRF = '{{ csrf_token() }}';
    const REORDER_URL = '{{ route($routePrefix . '.items.reorder', $openedCourse->id) }}';
    const container = document.getElementById('items-sortable');
    if (!container) return;

    // ---- live sum of "counts to grade" (weight or full score) — must equal 100 ----
    const sumEl = document.getElementById('weight-sum');
    const badgeEl = document.getElementById('weight-sum-badge');
    function recalcSum() {
        let sum = 0;
        container.querySelectorAll('.item-row').forEach(row => {
            const cb = row.querySelector('[data-counts]');
            if (!cb || !cb.checked) return;
            const w = row.querySelector('[data-weight]');
            const f = row.querySelector('[data-full]');
            const wv = w && w.value !== '' ? parseFloat(w.value) : null;
            const fv = f && f.value !== '' ? parseFloat(f.value) : 0;
            const eff = wv !== null && !isNaN(wv) ? wv : (isNaN(fv) ? 0 : fv);
            sum += eff;
        });
        sum = Math.round(sum * 100) / 100;
        sumEl.textContent = sum;
        const ok = Math.abs(sum - 100) < 0.01;
        badgeEl.textContent = ok ? '✓' : '≠ 100';
        badgeEl.className = 'inline-flex items-center px-2 py-0.5 rounded-lg text-xs font-semibold '
            + (ok ? 'bg-emerald-100 text-emerald-700' : 'bg-red-100 text-red-700');
    }
    container.addEventListener('input', recalcSum);
    container.addEventListener('change', recalcSum);

    // ---- drag & drop reorder (initiated from handle, follows mouse) ----
    let dragEl = null;
    container.querySelectorAll('.item-row').forEach(row => {
        const handle = row.querySelector('.drag-handle');
        if (!handle) return;
        handle.addEventListener('mousedown', () => row.setAttribute('draggable', 'true'));
        handle.addEventListener('mouseup', () => row.setAttribute('draggable', 'false'));
        row.addEventListener('dragstart', e => {
            dragEl = row;
            row.classList.add('opacity-40');
            e.dataTransfer.effectAllowed = 'move';
        });
        row.addEventListener('dragend', () => {
            row.classList.remove('opacity-40');
            row.setAttribute('draggable', 'false');
            dragEl = null;
            persistOrder();
        });
    });

    container.addEventListener('dragover', e => {
        e.preventDefault();
        if (!dragEl) return;
        const after = afterElement(e.clientY);
        if (after == null) container.appendChild(dragEl);
        else container.insertBefore(dragEl, after);
    });

    function afterElement(y) {
        const els = [...container.querySelectorAll('.item-row:not(.opacity-40)')];
        return els.reduce((closest, child) => {
            const box = child.getBoundingClientRect();
            const offset = y - box.top - box.height / 2;
            if (offset < 0 && offset > closest.offset) return { offset, element: child };
            return closest;
        }, { offset: -Infinity }).element;
    }

    function persistOrder() {
        const order = [...container.querySelectorAll('.item-row')].map(r => r.dataset.itemId);
        fetch(REORDER_URL, {
            method: 'POST',
            headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': CSRF, 'Accept': 'application/json' },
            body: JSON.stringify({ order }),
        }).catch(() => {});
    }
})();
</script>
@endpush

@if($enrollments->isEmpty())
<x-card>
    <div class="py-10 text-center text-slate-400">
        <x-icon name="user" class="h-8 w-8 mx-auto mb-3" />
        <p class="text-sm font-medium">{{ __('No students enrolled in this classroom yet') }}</p>
    </div>
</x-card>
@elseif($activeItems->isEmpty())
<x-card>
    <div class="py-10 text-center text-slate-400">
        <x-icon name="clipboard" class="h-8 w-8 mx-auto mb-3" />
        <p class="text-sm font-medium">{{ __('No score items yet — add one from Manage Score Items') }}</p>
    </div>
</x-card>
@else

<form id="score-form" action="{{ route($routePrefix . '.save', $openedCourse->id) }}" method="POST">
    @csrf
    <x-card padded="false">
        <div class="overflow-x-auto">
            <table class="w-full text-sm" style="min-width:{{ 420 + $activeItems->count() * 90 }}px">
                <thead>
                    <tr class="text-left text-xs font-medium text-slate-500 uppercase tracking-wide bg-slate-50 border-b border-slate-100">
                        <th class="px-3 py-3 w-10">#</th>
                        <th class="px-4 py-3 sticky left-0 bg-slate-50 z-10">{{ __('Student') }}</th>
                        @foreach($activeItems as $item)
                        <th class="px-2 py-3 text-right">
                            <div class="font-semibold text-slate-600 normal-case">{{ $item->name }}</div>
                            <div class="text-[10px] text-slate-400 normal-case">
                                {{ __('Full') }} {{ $item->full_score + 0 }}{{ $item->weight !== null ? ' · '.__('Wt').' '.($item->weight + 0) : '' }}
                                {{ $item->counts_toward_total ? '' : ' · '.__('excl.') }}
                            </div>
                        </th>
                        @endforeach
                        <th class="px-3 py-3 text-right">{{ __('Total') }}</th>
                        <th class="px-3 py-3 text-center">{{ __('Grade') }}</th>
                    </tr>
                </thead>
                <tbody>
                @foreach($enrollments as $i => $enrollment)
                    @php
                        $student = $enrollment->student;
                        $summary = $summaries->get($student->id);
                    @endphp
                    <tr class="border-b border-slate-100 hover:bg-slate-50" data-student="{{ $student->id }}">
                        <td class="px-3 py-2 text-xs text-slate-400">{{ $i + 1 }}</td>
                        <td class="px-4 py-2 sticky left-0 bg-white z-10">
                            <div class="text-sm font-medium text-slate-800">{{ $student->name_th }}</div>
                            <div class="text-xs text-slate-400">{{ $student->student_code }}</div>
                        </td>
                        @foreach($activeItems as $c => $item)
                        <td class="px-1.5 py-2 text-right">
                            <input type="number"
                                   name="scores[{{ $student->id }}][{{ $item->id }}]"
                                   value="{{ isset($matrix[$student->id][$item->id]) ? $matrix[$student->id][$item->id] + 0 : '' }}"
                                   step="0.01" min="0" max="{{ $item->full_score + 0 }}"
                                   data-cell data-row="{{ $i }}" data-col="{{ $c }}"
                                   data-item="{{ $item->id }}" data-full="{{ $item->full_score + 0 }}"
                                   data-counts="{{ $item->counts_toward_total ? ($item->weight !== null ? 'w:'.($item->weight+0) : 'raw') : 'no' }}"
                                   class="form-input w-[70px] text-right text-sm px-1.5 py-1">
                        </td>
                        @endforeach
                        <td class="px-3 py-2 text-right font-semibold text-sm text-slate-800" data-total>{{ $summary && $summary->total_score !== null ? $summary->total_score + 0 : '-' }}</td>
                        <td class="px-3 py-2 text-center font-semibold text-sm text-brand-600" data-grade>{{ $summary?->grade ?? '-' }}</td>
                    </tr>
                @endforeach
                </tbody>
            </table>
        </div>

        <div class="p-4 flex items-center justify-between border-t border-slate-100">
            <span id="autosave-status" class="text-xs text-slate-400"></span>
            <button type="submit" class="btn-primary">
                <x-icon name="check" class="h-4 w-4" /> {{ __('Save All') }}
            </button>
        </div>
    </x-card>
</form>

@push('scripts')
<script>
(function () {
    const CSRF = '{{ csrf_token() }}';
    const CELL_URL = '{{ route($routePrefix . '.cell', $openedCourse->id) }}';
    const GRADES = {!! json_encode($gradeSettings->map(fn($g) => ['grade' => $g->grade, 'min' => (float) $g->min_score, 'max' => (float) $g->max_score, 'pass' => (bool) $g->is_pass])->values()) !!};
    const statusEl = document.getElementById('autosave-status');

    // ---- clamp to full score ----
    document.querySelectorAll('[data-cell]').forEach(inp => {
        inp.addEventListener('input', () => {
            const full = parseFloat(inp.dataset.full);
            let v = parseFloat(inp.value);
            if (!isNaN(v) && !isNaN(full) && full > 0 && v > full) inp.value = full;
            if (!isNaN(v) && v < 0) inp.value = 0;
            recalcRow(inp.closest('tr'));
        });
    });

    // ---- live recalc total/grade for a row (client-side preview) ----
    function recalcRow(row) {
        let total = null;
        row.querySelectorAll('[data-cell]').forEach(inp => {
            const counts = inp.dataset.counts;
            if (counts === 'no' || inp.value === '') return;
            const val = parseFloat(inp.value);
            if (isNaN(val)) return;
            let contrib = val;
            if (counts.startsWith('w:')) {
                const weight = parseFloat(counts.slice(2));
                const full = parseFloat(inp.dataset.full);
                contrib = full > 0 ? val / full * weight : 0;
            }
            total = (total ?? 0) + contrib;
        });
        const totalCell = row.querySelector('[data-total]');
        const gradeCell = row.querySelector('[data-grade]');
        if (total === null) { totalCell.textContent = '-'; gradeCell.textContent = '-'; return; }
        total = Math.round(total * 100) / 100;
        totalCell.textContent = total;
        const g = GRADES.find(x => total >= x.min && total <= x.max);
        gradeCell.textContent = g ? g.grade : '-';
    }

    // ---- auto save on blur ----
    let dirty = new WeakSet();
    document.querySelectorAll('[data-cell]').forEach(inp => {
        inp.addEventListener('change', () => dirty.add(inp));
        inp.addEventListener('blur', () => {
            if (!dirty.has(inp)) return;
            dirty.delete(inp);
            autoSave(inp);
        });
    });

    function autoSave(inp) {
        const row = inp.closest('tr');
        statusEl.textContent = '{{ __('Saving...') }}';
        fetch(CELL_URL, {
            method: 'POST',
            headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': CSRF, 'Accept': 'application/json' },
            body: JSON.stringify({
                score_item_id: inp.dataset.item,
                student_id: row.dataset.student,
                score: inp.value === '' ? null : inp.value,
            }),
        })
        .then(r => r.ok ? r.json() : Promise.reject(r))
        .then(data => {
            if (data.score !== null && data.score !== undefined) inp.value = parseFloat(data.score);
            row.querySelector('[data-total]').textContent = data.total !== null ? data.total : '-';
            row.querySelector('[data-grade]').textContent = data.grade ?? '-';
            statusEl.textContent = '{{ __('Saved') }} ✓';
            setTimeout(() => { if (statusEl.textContent.includes('{{ __('Saved') }}')) statusEl.textContent = ''; }, 1500);
        })
        .catch(() => { statusEl.textContent = '{{ __('Save failed') }}'; });
    }

    // ---- Excel-like keyboard navigation ----
    const cells = Array.from(document.querySelectorAll('[data-cell]'));
    const grid = {};
    let maxRow = 0, maxCol = 0;
    cells.forEach(c => {
        const r = +c.dataset.row, col = +c.dataset.col;
        grid[r + ':' + col] = c;
        maxRow = Math.max(maxRow, r); maxCol = Math.max(maxCol, col);
    });
    function focusCell(r, c) {
        const el = grid[r + ':' + c];
        if (el) { el.focus(); el.select(); }
    }
    document.addEventListener('keydown', e => {
        const t = e.target;
        if (!t.matches || !t.matches('[data-cell]')) return;
        const r = +t.dataset.row, c = +t.dataset.col;
        let handled = true;
        if (e.key === 'ArrowUp') focusCell(Math.max(0, r - 1), c);
        else if (e.key === 'ArrowDown' || e.key === 'Enter') focusCell(Math.min(maxRow, r + 1), c);
        else if (e.key === 'ArrowLeft' && t.selectionStart === 0) focusCell(r, Math.max(0, c - 1));
        else if (e.key === 'ArrowRight' && t.selectionStart === t.value.length) focusCell(r, Math.min(maxCol, c + 1));
        else handled = false;
        if (handled) e.preventDefault();
    });
})();
</script>
@endpush
@endif
