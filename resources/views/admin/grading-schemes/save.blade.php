@extends('layouts.app')

@php
    $isEdit = isset($gradingScheme);
    $actionUrl = $isEdit ? route('admin.grading-schemes.update', $gradingScheme->id) : route('admin.grading-schemes.store');

    $title = $isEdit ? __('Edit Grading Scheme') : __('Create New Grading Scheme');
    $subtitle = $isEdit ? __('Update grading scheme details') : __('Grading Scheme Registration');

    $gradientClass = $isEdit ? 'bg-gradient-to-r from-amber-500 via-orange-500 to-rose-500' : 'bg-gradient-to-r from-indigo-500 via-purple-500 to-pink-500';
    $focusRing = $isEdit ? 'focus:border-amber-400' : 'focus:border-indigo-500';
    $focusText = $isEdit ? 'group-focus-within:text-amber-500' : 'group-focus-within:text-indigo-500';
    $btnClass = $isEdit
        ? 'bg-amber-500 text-white hover:bg-amber-600 shadow-amber-200'
        : 'bg-indigo-600 text-white hover:bg-indigo-700 shadow-indigo-200';
    $btnText = $isEdit ? __('Save Changes') : __('Create Grading Scheme');
    $btnIcon = $isEdit ? 'fa-save' : 'fa-check-circle';

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
@endphp

@section('content')
<div class="min-h-screen bg-gray-50 dark:bg-[#18191a] py-12 px-4 sm:px-6 lg:px-8 transition-colors duration-300">
    <div class="max-w-4xl mx-auto">
        <!-- Breadcrumb / Header -->
        <div class="flex items-center space-x-4 mb-8">
            <a href="{{ route('admin.grading-schemes.index') }}"
               class="group flex items-center justify-center w-10 h-10 rounded-xl bg-white dark:bg-[#242526] shadow-sm border border-gray-200 dark:border-[#3a3b3c] text-gray-500 dark:text-gray-400 hover:text-indigo-600 dark:hover:text-indigo-400 hover:border-indigo-300 transition-all duration-200">
                <i class="fas fa-arrow-left group-hover:-translate-x-0.5 transition-transform"></i>
            </a>
            <div>
                <h1 class="text-2xl font-extrabold text-gray-900 dark:text-white tracking-tight">{{ $title }}</h1>
                <p class="text-sm text-gray-500 dark:text-gray-400 font-medium px-1">{{ $subtitle }}</p>
            </div>
        </div>

        @if($errors->any())
        <div class="mb-6 p-4 bg-rose-50 dark:bg-rose-900/20 border border-rose-200 dark:border-rose-800 rounded-2xl text-rose-700 dark:text-rose-300 text-sm">
            <ul class="list-disc pl-4 space-y-0.5">
                @foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach
            </ul>
        </div>
        @endif

        <form action="{{ $actionUrl }}" method="POST" id="gradingSchemeForm">
            @csrf
            @if($isEdit) @method('PUT') @endif

            <!-- Card: General -->
            <div class="bg-white dark:bg-[#242526] rounded-[2.5rem] shadow-xl shadow-gray-200/50 dark:shadow-none border border-gray-100 dark:border-[#3a3b3c] overflow-hidden mb-6">
                <div class="h-2 {{ $gradientClass }}"></div>
                <div class="p-6 sm:p-8 space-y-6">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <!-- Name -->
                        <div class="space-y-2">
                            <label for="name" class="block text-xs font-bold text-gray-400 dark:text-gray-500 uppercase tracking-widest px-1">{{ __('Scheme name') }}</label>
                            <div class="group relative">
                                <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none text-gray-400 {{ $focusText }} transition-colors">
                                    <i class="fas fa-award text-sm"></i>
                                </div>
                                <input type="text" id="name" name="name" required
                                    class="block w-full pl-10 pr-4 py-4 bg-gray-50 dark:bg-[#3a3b3c] border-2 border-transparent rounded-2xl text-gray-900 dark:text-white placeholder-gray-400 focus:outline-none focus:ring-0 {{ $focusRing }} focus:bg-white dark:focus:bg-[#3a3b3c] transition-all duration-200 @error('name') border-rose-300 bg-rose-50 dark:bg-rose-900/20 @enderror"
                                    placeholder="{{ __('e.g. เกรดปกติ A-F, ผ่าน/ไม่ผ่าน') }}"
                                    value="{{ old('name', $isEdit ? $gradingScheme->name : '') }}">
                            </div>
                            @error('name')<p class="text-[10px] font-bold text-rose-500 mt-1 px-1">{{ $message }}</p>@enderror
                        </div>

                        <!-- Result Type -->
                        <div class="space-y-2">
                            <label class="block text-xs font-bold text-gray-400 dark:text-gray-500 uppercase tracking-widest px-1">{{ __('Result type') }}</label>
                            @php $currentType = old('result_type', $isEdit ? $gradingScheme->result_type : 'grade'); @endphp
                            <div class="flex flex-wrap gap-2 pt-1">
                                <label class="relative group cursor-pointer">
                                    <input type="radio" name="result_type" value="grade" class="peer hidden" {{ $currentType === 'grade' ? 'checked' : '' }}>
                                    <div class="px-4 py-3 rounded-xl border-2 border-gray-100 dark:border-[#3a3b3c] bg-white dark:bg-[#242526] text-sm font-bold text-gray-500 dark:text-gray-400 transition-all duration-200 peer-checked:border-indigo-500 peer-checked:bg-indigo-50 dark:peer-checked:bg-indigo-900/30 peer-checked:text-indigo-600 dark:peer-checked:text-indigo-400">
                                        <i class="fas fa-font mr-2 text-[10px] opacity-50"></i>{{ __('Grade (A-F)') }}
                                    </div>
                                </label>
                                <label class="relative group cursor-pointer">
                                    <input type="radio" name="result_type" value="pass_fail" class="peer hidden" {{ $currentType === 'pass_fail' ? 'checked' : '' }}>
                                    <div class="px-4 py-3 rounded-xl border-2 border-gray-100 dark:border-[#3a3b3c] bg-white dark:bg-[#242526] text-sm font-bold text-gray-500 dark:text-gray-400 transition-all duration-200 peer-checked:border-purple-500 peer-checked:bg-purple-50 dark:peer-checked:bg-purple-900/30 peer-checked:text-purple-600 dark:peer-checked:text-purple-400">
                                        <i class="fas fa-check-double mr-2 text-[10px] opacity-50"></i>{{ __('Pass / Fail') }}
                                    </div>
                                </label>
                            </div>
                            @error('result_type')<p class="text-[10px] font-bold text-rose-500 mt-1 px-1">{{ $message }}</p>@enderror
                        </div>
                    </div>

                    <!-- Description (scheme-level) -->
                    <div class="space-y-2">
                        <label for="description" class="block text-xs font-bold text-gray-400 dark:text-gray-500 uppercase tracking-widest px-1">{{ __('Description') }}</label>
                        <div class="group relative">
                            <div class="absolute top-4 left-4 flex items-start pointer-events-none text-gray-400 {{ $focusText }} transition-colors">
                                <i class="fas fa-align-left text-sm"></i>
                            </div>
                            <textarea id="description" name="description" rows="2"
                                class="block w-full pl-10 pr-4 py-4 bg-gray-50 dark:bg-[#3a3b3c] border-2 border-transparent rounded-2xl text-gray-900 dark:text-white placeholder-gray-400 focus:outline-none focus:ring-0 {{ $focusRing }} focus:bg-white dark:focus:bg-[#3a3b3c] transition-all duration-200"
                                placeholder="{{ __('Additional details...') }}">{{ old('description', $isEdit ? $gradingScheme->description : '') }}</textarea>
                        </div>
                    </div>

                    <!-- Status -->
                    <div class="space-y-3">
                        <label class="block text-xs font-bold text-gray-400 dark:text-gray-500 uppercase tracking-widest px-1">{{ __('Status') }}</label>
                        <div class="flex flex-wrap gap-2">
                            <label class="relative group cursor-pointer">
                                <input type="radio" name="status" value="1" class="peer hidden" {{ old('status', $isEdit ? $gradingScheme->status : 1) == 1 ? 'checked' : '' }}>
                                <div class="px-4 py-2.5 rounded-xl border-2 border-gray-100 dark:border-[#3a3b3c] bg-white dark:bg-[#242526] text-sm font-bold text-gray-500 dark:text-gray-400 transition-all duration-200 peer-checked:border-emerald-500 peer-checked:bg-emerald-50 dark:peer-checked:bg-emerald-900/30 peer-checked:text-emerald-600 dark:peer-checked:text-emerald-400">
                                    <i class="fas fa-check-circle mr-2 text-[10px] opacity-50"></i>{{ __('Active') }}
                                </div>
                            </label>
                            <label class="relative group cursor-pointer">
                                <input type="radio" name="status" value="2" class="peer hidden" {{ old('status', $isEdit ? $gradingScheme->status : 1) == 2 ? 'checked' : '' }}>
                                <div class="px-4 py-2.5 rounded-xl border-2 border-gray-100 dark:border-[#3a3b3c] bg-white dark:bg-[#242526] text-sm font-bold text-gray-500 dark:text-gray-400 transition-all duration-200 peer-checked:border-rose-500 peer-checked:bg-rose-50 dark:peer-checked:bg-rose-900/30 peer-checked:text-rose-600 dark:peer-checked:text-rose-400">
                                    <i class="fas fa-times-circle mr-2 text-[10px] opacity-50"></i>{{ __('Not Active') }}
                                </div>
                            </label>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Card: Grade Criteria Table -->
            <div class="bg-white dark:bg-[#242526] rounded-[2.5rem] shadow-xl shadow-gray-200/50 dark:shadow-none border border-gray-100 dark:border-[#3a3b3c] overflow-hidden mb-6">
                <div class="p-6 sm:p-8">
                    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 mb-5">
                        <div>
                            <h2 class="text-lg font-bold text-gray-800 dark:text-white">{{ __('Grade Criteria') }}</h2>
                            <p class="text-xs text-gray-400 mt-0.5">{{ __('Define score ranges and their result') }}</p>
                        </div>
                        <div class="flex flex-wrap gap-2">
                            <button type="button" onclick="applyPreset('grade')" class="btn-app"><i class="fas fa-font text-[10px]"></i> {{ __('Use default A-F') }}</button>
                            <button type="button" onclick="applyPreset('pass_fail')" class="btn-app"><i class="fas fa-check-double text-[10px]"></i> {{ __('Use default Pass/Fail') }}</button>
                            <button type="button" onclick="addRow()" class="btn-app"><i class="fas fa-plus text-[10px]"></i> {{ __('Add grade row') }}</button>
                        </div>
                    </div>

                    <!-- Desktop: table -->
                    <div class="hidden md:block overflow-x-auto">
                        <table class="w-full" style="min-width:760px">
                            <thead>
                                <tr class="text-left text-[10px] font-bold text-gray-400 uppercase tracking-widest border-b border-gray-100 dark:border-[#3a3b3c]">
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

                    <!-- Mobile: cards -->
                    <div class="md:hidden space-y-3" id="rowsCardContainer"></div>

                    <p id="rowsEmpty" class="hidden text-sm text-gray-400 text-center py-6">{{ __('No grade rows yet — add one or use a default preset') }}</p>
                </div>
            </div>

            <!-- Card: Preview -->
            <div class="bg-white dark:bg-[#242526] rounded-[2.5rem] shadow-xl shadow-gray-200/50 dark:shadow-none border border-gray-100 dark:border-[#3a3b3c] overflow-hidden mb-6">
                <div class="p-6 sm:p-8">
                    <div class="flex items-center gap-2 mb-4">
                        <i class="fas fa-flask text-indigo-500"></i>
                        <h2 class="text-lg font-bold text-gray-800 dark:text-white">{{ __('Preview') }}</h2>
                        <span class="text-xs text-gray-400">{{ __('Enter a score to see the resulting grade') }}</span>
                    </div>
                    <div class="flex flex-wrap items-center gap-3 mb-4">
                        <input type="number" id="previewScore" step="0.01" min="0" max="100" placeholder="85"
                            class="w-32 px-4 py-2.5 bg-gray-50 dark:bg-[#3a3b3c] border-2 border-transparent rounded-xl text-sm text-center text-gray-800 dark:text-white focus:border-indigo-500 focus:outline-none transition-all">
                        <i class="fas fa-arrow-right text-gray-300"></i>
                        <span id="previewResult" class="px-3 py-1.5 rounded-lg bg-gray-100 dark:bg-[#3a3b3c] text-sm font-bold text-gray-400">—</span>
                    </div>
                    <div class="flex flex-wrap gap-2" id="previewSamples"></div>
                </div>
            </div>

            <!-- Actions -->
            <div class="flex flex-col sm:flex-row space-y-3 sm:space-y-0 sm:space-x-4">
                <button type="submit"
                    class="flex-1 group relative flex items-center justify-center px-8 py-4 {{ $btnClass }} font-bold rounded-2xl active:scale-95 transition-all duration-200 shadow-lg overflow-hidden">
                    <span class="relative z-10 flex items-center">
                        <i class="fas {{ $btnIcon }} mr-2 opacity-50 group-hover:opacity-100 transition-opacity"></i>{{ $btnText }}
                    </span>
                </button>
                <a href="{{ route('admin.grading-schemes.index') }}"
                    class="flex-1 flex items-center justify-center px-8 py-4 bg-white dark:bg-[#242526] text-gray-700 dark:text-gray-300 font-bold rounded-2xl border-2 border-gray-100 dark:border-[#3a3b3c] hover:border-gray-200 dark:hover:border-[#4a4b4c] hover:bg-gray-50 dark:hover:bg-[#3a3b3c] active:scale-95 transition-all duration-200">
                    {{ $isEdit ? __('Cancel') : __('Back to List') }}
                </a>
            </div>
        </form>
    </div>
</div>

<script>
(function () {
    const CELL = 'w-full px-3 py-2 bg-gray-50 dark:bg-[#3a3b3c] border-2 border-transparent rounded-xl text-sm text-gray-800 dark:text-white focus:border-indigo-500 focus:outline-none transition-all';
    const CELL_RESULT = CELL + ' font-bold text-indigo-600 dark:text-indigo-400';
    const CELL_NUM = 'w-full px-2 py-2 bg-gray-50 dark:bg-[#3a3b3c] border-2 border-transparent rounded-xl text-sm text-center text-gray-800 dark:text-white focus:border-indigo-500 focus:outline-none transition-all';

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
        tr.className = 'border-b border-gray-50 dark:border-[#3a3b3c]/50';
        tr.dataset.rowId = i;
        tr.innerHTML = `
            <td class="py-2 pr-2 text-xs text-gray-400 order-cell text-center"></td>
            <td class="py-2 pr-2"><input type="text" data-f="result_th" name="details[${i}][result_th]" value="${esc(data.result_th)}" maxlength="50" required placeholder="A" class="${CELL_RESULT}"></td>
            <td class="py-2 pr-2"><input type="text" data-f="result_en" name="details[${i}][result_en]" value="${esc(data.result_en)}" maxlength="50" placeholder="A" class="${CELL}"></td>
            <td class="py-2 pr-2"><input type="text" data-f="result_cn" name="details[${i}][result_cn]" value="${esc(data.result_cn)}" maxlength="50" placeholder="A" class="${CELL}"></td>
            <td class="py-2 pr-2"><input type="number" data-f="min_score" name="details[${i}][min_score]" value="${esc(data.min_score)}" step="0.01" min="0" max="100" required placeholder="80" class="${CELL_NUM}"></td>
            <td class="py-2 pr-2"><input type="number" data-f="max_score" name="details[${i}][max_score]" value="${esc(data.max_score)}" step="0.01" min="0" max="100" required placeholder="100" class="${CELL_NUM}"></td>
            <td class="py-2 pr-2"><input type="text" data-f="description" name="details[${i}][description]" value="${esc(data.description)}" maxlength="255" placeholder="${L.desc}" class="${CELL}"></td>
            <td class="py-2 text-right">
                <button type="button" class="row-remove inline-flex items-center justify-center w-8 h-8 rounded-lg bg-white dark:bg-[#242526] border border-gray-100 dark:border-[#3a3b3c] text-rose-500 hover:bg-rose-50 transition-all shadow-sm" title="${L.remove}"><i class="fas fa-trash-alt text-xs"></i></button>
            </td>`;
        tableBody.appendChild(tr);

        // ----- Mobile card (input จริงชุดที่สอง แต่ต้อง name ต่างเพื่อไม่ชน) -----
        // แก้ปัญหาชนกัน: card ใช้ index ต่างช่วง (10000+) แต่จะ submit ซ้ำ → เลี่ยงโดยให้ card ไม่มี name
        // แล้ว mirror ค่าจาก card → table ด้วย event 'input'
        const card = document.createElement('div');
        card.className = 'p-4 rounded-2xl border border-gray-100 dark:border-[#3a3b3c] bg-gray-50/50 dark:bg-[#3a3b3c]/30 space-y-3';
        card.dataset.cardId = i;
        card.innerHTML = `
            <div class="flex items-center justify-between">
                <span class="text-xs font-bold text-gray-400 order-cell"></span>
                <button type="button" class="row-remove text-rose-400 hover:text-rose-600 text-xs font-bold"><i class="fas fa-trash-alt mr-1"></i>${L.remove}</button>
            </div>
            <div class="grid grid-cols-3 gap-2">
                <div><label class="block text-[10px] text-gray-400 mb-1">${L.result} TH *</label><input type="text" data-m="result_th" value="${esc(data.result_th)}" maxlength="50" class="${CELL_RESULT}"></div>
                <div><label class="block text-[10px] text-gray-400 mb-1">EN</label><input type="text" data-m="result_en" value="${esc(data.result_en)}" maxlength="50" class="${CELL}"></div>
                <div><label class="block text-[10px] text-gray-400 mb-1">CN</label><input type="text" data-m="result_cn" value="${esc(data.result_cn)}" maxlength="50" class="${CELL}"></div>
                <div><label class="block text-[10px] text-gray-400 mb-1">${L.min}</label><input type="number" data-m="min_score" value="${esc(data.min_score)}" step="0.01" min="0" max="100" placeholder="80" class="${CELL_NUM}"></div>
                <div><label class="block text-[10px] text-gray-400 mb-1">${L.max}</label><input type="number" data-m="max_score" value="${esc(data.max_score)}" step="0.01" min="0" max="100" placeholder="100" class="${CELL_NUM}"></div>
                <div><label class="block text-[10px] text-gray-400 mb-1">${L.desc}</label><input type="text" data-m="description" value="${esc(data.description)}" maxlength="255" class="${CELL}"></div>
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
        if (isNaN(v)) { previewResult.textContent = '—'; previewResult.className = 'px-3 py-1.5 rounded-lg bg-gray-100 dark:bg-[#3a3b3c] text-sm font-bold text-gray-400'; return; }
        const r = resultForScore(v);
        previewResult.textContent = r ?? L.noResult;
        previewResult.className = 'px-3 py-1.5 rounded-lg text-sm font-bold ' + (r ? 'bg-indigo-50 text-indigo-600 dark:bg-indigo-900/30 dark:text-indigo-400' : 'bg-rose-50 text-rose-500');
    }
    previewInput.addEventListener('input', runPreview);

    function renderPreviewSamples() {
        const box = document.getElementById('previewSamples');
        const samples = [85, 72, 45];
        box.innerHTML = samples.map(s => {
            const r = resultForScore(s);
            return `<span class="px-2.5 py-1 rounded-lg bg-gray-50 dark:bg-[#3a3b3c] text-xs text-gray-500 dark:text-gray-400">${@json(__('Score'))} ${s} <i class="fas fa-arrow-right text-[9px] text-gray-300 mx-1"></i> <b class="text-indigo-500">${r ?? '—'}</b></span>`;
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
@endsection
