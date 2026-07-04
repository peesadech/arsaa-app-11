@php
    $gradingScheme = $gradingScheme ?? null;
    $isEdit = $gradingScheme !== null;

    $existingDetails = old('details', $isEdit
        ? $gradingScheme->details->map(fn ($d) => [
            'result_th' => $d->result_th,
            'result_en' => $d->result_en,
            'result_cn' => $d->result_cn,
            'min_score' => $d->min_score + 0,
            'max_score' => $d->max_score + 0,
            'description' => $d->description,
        ])->values()
        : []);

    $currentType = old('result_type', $isEdit ? $gradingScheme->result_type : 'grade');
@endphp

@if($errors->any())
    <div class="mb-6 p-4 bg-red-50 border border-red-200 rounded-xl text-red-700 text-sm">
        <ul class="list-disc pl-4 space-y-0.5">
            @foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach
        </ul>
    </div>
@endif

{{-- Card: General --}}
<x-card :title="__('Grading Scheme')" :description="__('Define how scores are converted to results')">
    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
        {{-- Name --}}
        <div>
            <label for="name" class="form-label">{{ __('Scheme name') }} <span class="text-red-500">*</span></label>
            <input type="text" id="name" name="name" required
                class="form-input"
                placeholder="{{ __('e.g. เกรดปกติ A-F, ผ่าน/ไม่ผ่าน') }}"
                value="{{ old('name', $isEdit ? $gradingScheme->name : '') }}">
            @error('name')<p class="form-error">{{ $message }}</p>@enderror
        </div>

        {{-- Result Type --}}
        <div>
            <label class="form-label">{{ __('Result type') }}</label>
            <div class="flex flex-wrap gap-2">
                <label class="relative cursor-pointer">
                    <input type="radio" name="result_type" value="grade" class="peer sr-only" {{ $currentType === 'grade' ? 'checked' : '' }}>
                    <div class="px-4 py-2.5 rounded-xl border border-slate-200 bg-white text-sm font-medium text-slate-500 transition peer-checked:border-brand-500 peer-checked:bg-brand-50 peer-checked:text-brand-700">
                        {{ __('Grade (A-F)') }}
                    </div>
                </label>
                <label class="relative cursor-pointer">
                    <input type="radio" name="result_type" value="pass_fail" class="peer sr-only" {{ $currentType === 'pass_fail' ? 'checked' : '' }}>
                    <div class="px-4 py-2.5 rounded-xl border border-slate-200 bg-white text-sm font-medium text-slate-500 transition peer-checked:border-brand-500 peer-checked:bg-brand-50 peer-checked:text-brand-700">
                        {{ __('Pass / Fail') }}
                    </div>
                </label>
            </div>
            @error('result_type')<p class="form-error">{{ $message }}</p>@enderror
        </div>
    </div>

    {{-- Description (scheme-level) --}}
    <div class="mt-6">
        <label for="description" class="form-label">{{ __('Description') }}</label>
        <textarea id="description" name="description" rows="2" class="form-textarea"
            placeholder="{{ __('Additional details...') }}">{{ old('description', $isEdit ? $gradingScheme->description : '') }}</textarea>
    </div>

    {{-- Status --}}
    <div class="mt-6">
        <label class="form-label">{{ __('Status') }}</label>
        <div class="flex flex-wrap gap-2">
            <label class="relative cursor-pointer">
                <input type="radio" name="status" value="1" class="peer sr-only" {{ old('status', $isEdit ? $gradingScheme->status : 1) == 1 ? 'checked' : '' }}>
                <div class="px-4 py-2.5 rounded-xl border border-slate-200 bg-white text-sm font-medium text-slate-500 transition peer-checked:border-green-500 peer-checked:bg-green-50 peer-checked:text-green-700">
                    {{ __('Active') }}
                </div>
            </label>
            <label class="relative cursor-pointer">
                <input type="radio" name="status" value="2" class="peer sr-only" {{ old('status', $isEdit ? $gradingScheme->status : 1) == 2 ? 'checked' : '' }}>
                <div class="px-4 py-2.5 rounded-xl border border-slate-200 bg-white text-sm font-medium text-slate-500 transition peer-checked:border-red-500 peer-checked:bg-red-50 peer-checked:text-red-700">
                    {{ __('Not Active') }}
                </div>
            </label>
        </div>
    </div>
</x-card>

{{-- Card: Grade Criteria Table --}}
<x-card>
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 mb-5">
        <div>
            <h2 class="text-base font-semibold text-slate-900">{{ __('Grade Criteria') }}</h2>
            <p class="text-xs text-slate-500 mt-0.5">{{ __('Define score ranges and their result') }}</p>
        </div>
        <div class="flex flex-wrap gap-2">
            <button type="button" onclick="applyPreset('grade')" class="btn-secondary">{{ __('Use default A-F') }}</button>
            <button type="button" onclick="applyPreset('pass_fail')" class="btn-secondary">{{ __('Use default Pass/Fail') }}</button>
            <button type="button" onclick="addRow()" class="btn-secondary"><x-icon name="plus" class="h-4 w-4" /> {{ __('Add grade row') }}</button>
        </div>
    </div>

    {{-- Desktop: table --}}
    <div class="hidden md:block overflow-x-auto">
        <table class="w-full" style="min-width:760px">
            <thead>
                <tr class="text-left text-xs font-medium text-slate-500 uppercase tracking-wide border-b border-slate-100">
                    <th class="py-2 pr-2 w-12">{{ __('Order') }}</th>
                    <th class="py-2 pr-2">{{ __('Result') }} (TH) *</th>
                    <th class="py-2 pr-2">{{ __('Result') }} (EN)</th>
                    <th class="py-2 pr-2">{{ __('Result') }} (CN)</th>
                    <th class="py-2 pr-2 w-24">{{ __('Min score') }}</th>
                    <th class="py-2 pr-2 w-24">{{ __('Max score') }}</th>
                    <th class="py-2 pr-2">{{ __('Description') }}</th>
                    <th class="py-2 w-12 text-right">{{ __('Action') }}</th>
                </tr>
            </thead>
            <tbody id="rowsTableBody"></tbody>
        </table>
    </div>

    {{-- Mobile: cards --}}
    <div class="md:hidden space-y-3" id="rowsCardContainer"></div>

    <p id="rowsEmpty" class="hidden text-sm text-slate-400 text-center py-6">{{ __('No grade rows yet — add one or use a default preset') }}</p>
</x-card>

{{-- Card: Preview --}}
<x-card>
    <div class="flex items-center gap-2 mb-4">
        <x-icon name="chart" class="h-5 w-5 text-brand-500" />
        <h2 class="text-base font-semibold text-slate-900">{{ __('Preview') }}</h2>
        <span class="text-xs text-slate-400">{{ __('Enter a score to see the resulting grade') }}</span>
    </div>
    <div class="flex flex-wrap items-center gap-3 mb-4">
        <input type="number" id="previewScore" step="0.01" min="0" max="100" placeholder="85"
            class="form-input w-32 text-center">
        <x-icon name="arrow-left" class="h-4 w-4 text-slate-300 rotate-180" />
        <span id="previewResult" class="px-3 py-1.5 rounded-lg bg-slate-100 text-sm font-bold text-slate-400">—</span>
    </div>
    <div class="flex flex-wrap gap-2" id="previewSamples"></div>
</x-card>

<script>
(function () {
    const CELL = 'form-input w-full text-sm';
    const CELL_RESULT = 'form-input w-full text-sm font-bold text-brand-600';
    const CELL_NUM = 'form-input w-full text-sm text-center';

    const L = {
        result: @json(__('Result')),
        min: @json(__('Min score')),
        max: @json(__('Max score')),
        desc: @json(__('Description')),
        remove: @json(__('Remove')),
        noResult: @json(__('No matching result')),
    };

    const tableBody = document.getElementById('rowsTableBody');
    const cardBox = document.getElementById('rowsCardContainer');
    const emptyMsg = document.getElementById('rowsEmpty');
    let idx = 0;

    const esc = s => String(s ?? '').replace(/"/g, '&quot;');

    // แต่ละแถวเก็บ state เดียว sync ทั้ง desktop(table) และ mobile(card) ผ่าน name เดียวกัน
    // ใช้วิธี render สองชุดที่ผูก name[i] เหมือนกันไม่ได้ (จะซ้ำ) — จึงเลือกแสดงตามหน้าจอด้วย CSS
    // แนวทาง: input จริงอยู่ใน table (desktop) + card (mobile) ต้องใช้ id ต่างกัน → ใช้ JS mirror
    // เพื่อความเรียบง่าย: สร้าง "แถวข้อมูล" กลาง แล้ว render เป็น table row + card ที่ share ค่าโดย event

    const rows = []; // เก็บ id ของแต่ละแถว (table เป็น input ตัวจริงที่ submit, card mirror ค่า)

    function syncEmpty() {
        emptyMsg.classList.toggle('hidden', rows.length > 0);
        renderPreviewSamples();
    }

    function makeRow(data = {}) {
        const i = idx++;
        rows.push(i);

        // ----- Desktop table row (input จริง) -----
        const tr = document.createElement('tr');
        tr.className = 'border-b border-slate-100';
        tr.dataset.rowId = i;
        tr.innerHTML = `
            <td class="py-2 pr-2 text-xs text-slate-400 order-cell text-center"></td>
            <td class="py-2 pr-2"><input type="text" data-f="result_th" name="details[${i}][result_th]" value="${esc(data.result_th)}" maxlength="50" required placeholder="A" class="${CELL_RESULT}"></td>
            <td class="py-2 pr-2"><input type="text" data-f="result_en" name="details[${i}][result_en]" value="${esc(data.result_en)}" maxlength="50" placeholder="A" class="${CELL}"></td>
            <td class="py-2 pr-2"><input type="text" data-f="result_cn" name="details[${i}][result_cn]" value="${esc(data.result_cn)}" maxlength="50" placeholder="A" class="${CELL}"></td>
            <td class="py-2 pr-2"><input type="number" data-f="min_score" name="details[${i}][min_score]" value="${esc(data.min_score)}" step="0.01" min="0" max="100" required placeholder="80" class="${CELL_NUM}"></td>
            <td class="py-2 pr-2"><input type="number" data-f="max_score" name="details[${i}][max_score]" value="${esc(data.max_score)}" step="0.01" min="0" max="100" required placeholder="100" class="${CELL_NUM}"></td>
            <td class="py-2 pr-2"><input type="text" data-f="description" name="details[${i}][description]" value="${esc(data.description)}" maxlength="255" placeholder="${L.desc}" class="${CELL}"></td>
            <td class="py-2 text-right">
                <button type="button" class="row-remove btn-ghost p-2 text-red-500 hover:bg-red-50" title="${L.remove}"><svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="1.7" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M14.74 9l-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673A2.25 2.25 0 0 1 15.916 21.75H8.084A2.25 2.25 0 0 1 5.84 19.673L5.03 5.79m14.94 0a48.667 48.667 0 0 0-3.478-.397M4.772 5.79c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 0 1 3.478-.397m7.5 0v-.916c0-1.18-.91-2.164-2.09-2.201a51.964 51.964 0 0 0-3.32 0c-1.18.037-2.09 1.02-2.09 2.201v.916m7.5 0a48.667 48.667 0 0 0-7.5 0"/></svg></button>
            </td>`;
        tableBody.appendChild(tr);

        // ----- Mobile card (input จริงชุดที่สอง แต่ต้อง name ต่างเพื่อไม่ชน) -----
        // แก้ปัญหาชนกัน: card ใช้ index ต่างช่วง (10000+) แต่จะ submit ซ้ำ → เลี่ยงโดยให้ card ไม่มี name
        // แล้ว mirror ค่าจาก card → table ด้วย event 'input'
        const card = document.createElement('div');
        card.className = 'p-4 rounded-xl border border-slate-200 bg-slate-50 space-y-3';
        card.dataset.cardId = i;
        card.innerHTML = `
            <div class="flex items-center justify-between">
                <span class="text-xs font-bold text-slate-400 order-cell"></span>
                <button type="button" class="row-remove text-red-500 hover:text-red-600 text-xs font-bold">${L.remove}</button>
            </div>
            <div class="grid grid-cols-3 gap-2">
                <div><label class="block text-[10px] text-slate-400 mb-1">${L.result} TH *</label><input type="text" data-m="result_th" value="${esc(data.result_th)}" maxlength="50" class="${CELL_RESULT}"></div>
                <div><label class="block text-[10px] text-slate-400 mb-1">EN</label><input type="text" data-m="result_en" value="${esc(data.result_en)}" maxlength="50" class="${CELL}"></div>
                <div><label class="block text-[10px] text-slate-400 mb-1">CN</label><input type="text" data-m="result_cn" value="${esc(data.result_cn)}" maxlength="50" class="${CELL}"></div>
                <div><label class="block text-[10px] text-slate-400 mb-1">${L.min}</label><input type="number" data-m="min_score" value="${esc(data.min_score)}" step="0.01" min="0" max="100" placeholder="80" class="${CELL_NUM}"></div>
                <div><label class="block text-[10px] text-slate-400 mb-1">${L.max}</label><input type="number" data-m="max_score" value="${esc(data.max_score)}" step="0.01" min="0" max="100" placeholder="100" class="${CELL_NUM}"></div>
                <div><label class="block text-[10px] text-slate-400 mb-1">${L.desc}</label><input type="text" data-m="description" value="${esc(data.description)}" maxlength="255" class="${CELL}"></div>
            </div>`;
        cardBox.appendChild(card);

        // mirror card -> table (table เป็นตัว submit)
        card.querySelectorAll('[data-m]').forEach(mInput => {
            const field = mInput.dataset.m;
            const tInput = tr.querySelector(`[data-f="${field}"]`);
            mInput.addEventListener('input', () => { tInput.value = mInput.value; renderPreviewSamples(); });
            tInput.addEventListener('input', () => { mInput.value = tInput.value; renderPreviewSamples(); });
        });

        // remove (ทั้งสองปุ่มลบทั้งคู่)
        const removeRow = () => { tr.remove(); card.remove(); const p = rows.indexOf(i); if (p > -1) rows.splice(p, 1); renumber(); syncEmpty(); };
        tr.querySelector('.row-remove').addEventListener('click', removeRow);
        card.querySelector('.row-remove').addEventListener('click', removeRow);

        renumber();
        syncEmpty();
    }

    function renumber() {
        tableBody.querySelectorAll('tr').forEach((tr, n) => { tr.querySelector('.order-cell').textContent = n + 1; });
        cardBox.querySelectorAll('[data-card-id]').forEach((c, n) => { c.querySelector('.order-cell').textContent = '#' + (n + 1); });
    }

    // ===== Presets =====
    window.applyPreset = function (type) {
        tableBody.innerHTML = '';
        cardBox.innerHTML = '';
        rows.length = 0;
        // set result_type radio ให้สอดคล้อง
        const radio = document.querySelector(`input[name="result_type"][value="${type}"]`);
        if (radio) radio.checked = true;

        if (type === 'grade') {
            [['A',80,100],['B',70,79.99],['C',60,69.99],['D',50,59.99],['F',0,49.99]].forEach(([r,mn,mx]) =>
                makeRow({ result_th: r, result_en: r, result_cn: r, min_score: mn, max_score: mx }));
        } else {
            makeRow({ result_th: 'ผ่าน', result_en: 'Pass', result_cn: '通过', min_score: 50, max_score: 100 });
            makeRow({ result_th: 'ไม่ผ่าน', result_en: 'Fail', result_cn: '不通过', min_score: 0, max_score: 49.99 });
        }
    };

    window.addRow = function () { makeRow(); };

    // ===== Preview =====
    function currentRows() {
        return Array.from(tableBody.querySelectorAll('tr')).map(tr => ({
            result_th: tr.querySelector('[data-f="result_th"]').value,
            min: parseFloat(tr.querySelector('[data-f="min_score"]').value),
            max: parseFloat(tr.querySelector('[data-f="max_score"]').value),
        }));
    }
    function resultForScore(score) {
        const hit = currentRows().find(r => !isNaN(r.min) && !isNaN(r.max) && score >= r.min && score <= r.max);
        return hit ? (hit.result_th || '?') : null;
    }
    const previewInput = document.getElementById('previewScore');
    const previewResult = document.getElementById('previewResult');
    function runPreview() {
        const v = parseFloat(previewInput.value);
        if (isNaN(v)) { previewResult.textContent = '—'; previewResult.className = 'px-3 py-1.5 rounded-lg bg-slate-100 text-sm font-bold text-slate-400'; return; }
        const r = resultForScore(v);
        previewResult.textContent = r ?? L.noResult;
        previewResult.className = 'px-3 py-1.5 rounded-lg text-sm font-bold ' + (r ? 'bg-brand-50 text-brand-600' : 'bg-red-50 text-red-500');
    }
    previewInput.addEventListener('input', runPreview);

    function renderPreviewSamples() {
        const box = document.getElementById('previewSamples');
        const samples = [85, 72, 45];
        box.innerHTML = samples.map(s => {
            const r = resultForScore(s);
            return `<span class="px-2.5 py-1 rounded-lg bg-slate-50 text-xs text-slate-500">${@json(__('Score'))} ${s} <b class="text-brand-500 ml-1">${r ?? '—'}</b></span>`;
        }).join('');
        runPreview();
    }

    // ===== Init =====
    const EXISTING = @json($existingDetails);
    const list = Array.isArray(EXISTING) ? EXISTING : Object.values(EXISTING);
    if (list.length) {
        list.forEach(d => makeRow(d));
    } else {
        makeRow(); // เริ่มด้วย 1 แถวว่าง
    }

    // client-side guard: min <= max ก่อน submit
    document.getElementById('gradingSchemeForm').addEventListener('submit', function (e) {
        for (const r of currentRows()) {
            if (!isNaN(r.min) && !isNaN(r.max) && r.min > r.max) {
                e.preventDefault();
                alert(@json(__('Max score must not be less than min score')));
                return;
            }
        }
    });
})();
</script>
