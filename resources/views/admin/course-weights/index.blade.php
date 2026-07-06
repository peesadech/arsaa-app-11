<x-layouts.admin :header="__('Subject Weights')"
    :subheader="__('Academic Year') . ' ' . ($academicYear->year ?? '?') . ' / ' . __('Semester') . ' ' . ($semester->semester_number ?? '?') . ' — ' . __('Set each subject proportion per grade (should total 100)')">
    <x-slot name="actions">
        <x-button variant="secondary" icon="arrow-left" :href="route('admin.opened-courses.index')">{{ __('Back') }}</x-button>
    </x-slot>

    @if(session('status'))
    <div class="mb-6 flex items-center gap-2 p-4 bg-emerald-50 border border-emerald-200 rounded-xl text-emerald-700 text-sm">
        <x-icon name="check" class="h-4 w-4" />{{ session('status') }}
    </div>
    @endif

    @if($errors->any())
    <div class="mb-6 p-4 bg-red-50 border border-red-200 rounded-xl text-red-700 text-sm">{{ $errors->first() }}</div>
    @endif

    @if(!$yearId || !$semesterId)
    <x-card class="text-center text-slate-400 py-10">
        <x-icon name="calendar" class="h-9 w-9 mx-auto mb-3" />
        <p class="text-sm font-medium">{{ __('Please select an academic year and semester first.') }}</p>
    </x-card>
    @else

    {{-- Academic year + semester selector --}}
    <form method="GET" action="{{ route('admin.course-weights.index') }}" class="mb-6 flex flex-wrap items-end gap-3">
        <div class="w-48">
            <label class="text-xs text-slate-400">{{ __('Academic Year') }}</label>
            <select name="academic_year_id" class="form-select text-sm" onchange="this.form.submit()">
                @foreach($academicYears as $ay)
                <option value="{{ $ay->id }}" @selected($yearId == $ay->id)>{{ $ay->year }}</option>
                @endforeach
            </select>
        </div>
        <div class="w-48">
            <label class="text-xs text-slate-400">{{ __('Semester') }}</label>
            <select name="semester_id" class="form-select text-sm" onchange="this.form.submit()">
                @foreach($semesters as $sm)
                <option value="{{ $sm->id }}" @selected($semesterId == $sm->id)>{{ __('Semester') }} {{ $sm->semester_number }}</option>
                @endforeach
            </select>
        </div>
        @if($selectedGradeId)<input type="hidden" name="grade_id" value="{{ $selectedGradeId }}">@endif
        <noscript><button type="submit" class="btn-secondary">{{ __('Apply') }}</button></noscript>
    </form>

    {{-- Grade selector --}}
    <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-6 gap-3 mb-6">
        @foreach($grades as $g)
        @php $isSelected = $selectedGradeId == $g->id; @endphp
        <a href="{{ route('admin.course-weights.index', ['academic_year_id' => $yearId, 'semester_id' => $semesterId, 'grade_id' => $g->id]) }}"
           class="block p-4 rounded-2xl border text-center transition {{ $isSelected ? 'bg-brand-50 border-brand-200' : 'bg-white border-slate-100 shadow-card hover:border-brand-200' }}">
            <div class="text-sm font-semibold {{ $isSelected ? 'text-brand-700' : 'text-slate-800' }}">{{ $g->name_th ?? '-' }}</div>
        </a>
        @endforeach
    </div>

    @if(!$selectedGradeId)
    <x-card class="text-center text-slate-400 py-10">
        <x-icon name="filter" class="h-9 w-9 mx-auto mb-3" />
        <p class="text-sm font-medium">{{ __('Select a grade above to set subject weights') }}</p>
    </x-card>
    @elseif($courses->isEmpty())
    <x-card class="text-center text-slate-400 py-10">
        <x-icon name="book" class="h-9 w-9 mx-auto mb-3" />
        <p class="text-sm font-medium">{{ __('No courses for this grade in the current semester') }}</p>
    </x-card>
    @else
    @php
        $weightSum = 0;
        foreach($courses as $c) { $weightSum += (float) ($weights[$c->id]->weight ?? 0); }
        $weightSum = round($weightSum, 2);
    @endphp

    <form action="{{ route('admin.course-weights.save') }}" method="POST">
        @csrf
        <input type="hidden" name="academic_year_id" value="{{ $yearId }}">
        <input type="hidden" name="semester_id" value="{{ $semesterId }}">
        <input type="hidden" name="grade_id" value="{{ $selectedGradeId }}">

        {{-- สัดส่วนช่วงคะแนน กลางภาค/ปลายภาค/เก็บ (期中/期末/平时) --}}
        <x-card class="mb-4">
            <h2 class="text-xs font-semibold text-slate-500 uppercase tracking-wide mb-3">{{ __('Section weights') }} (期中 / 期末 / 平时)</h2>
            <div class="flex flex-wrap items-end gap-3">
                <div class="w-32">
                    <label class="text-xs text-slate-400">{{ __('Midterm') }} (%)</label>
                    <input type="number" name="midterm_weight" value="{{ $sectionWeight->midterm_weight + 0 }}" step="0.01" min="0" max="100" class="form-input text-sm text-right">
                </div>
                <div class="w-32">
                    <label class="text-xs text-slate-400">{{ __('Final') }} (%)</label>
                    <input type="number" name="final_weight" value="{{ $sectionWeight->final_weight + 0 }}" step="0.01" min="0" max="100" class="form-input text-sm text-right">
                </div>
                <div class="w-32">
                    <label class="text-xs text-slate-400">{{ __('Collect') }} (%)</label>
                    <input type="number" name="collect_weight" value="{{ $sectionWeight->collect_weight + 0 }}" step="0.01" min="0" max="100" class="form-input text-sm text-right">
                </div>
                <span class="text-xs text-slate-400 pb-2">{{ __('Used to weight midterm/final/collect into the term score (should total 100).') }}</span>
            </div>
        </x-card>

        <x-card padded="false">
            <div class="p-4 flex flex-wrap items-center justify-between gap-3 border-b border-slate-100">
                <div class="flex items-center gap-3">
                    <h2 class="text-xs font-semibold text-slate-500 uppercase tracking-wide">{{ __('Subject weights') }}</h2>
                    @if($existingSources->isNotEmpty())
                    <button type="button" class="btn-secondary text-xs py-1.5" onclick="document.getElementById('copy-weights-modal').classList.remove('hidden')">
                        <x-icon name="layers" class="h-4 w-4" /> {{ __('Copy from another year') }}
                    </button>
                    @endif
                </div>
                <div class="flex items-center gap-2 text-sm">
                    <span class="text-slate-500">{{ __('Total') }}:</span>
                    <span id="weight-sum" class="font-bold">{{ $weightSum + 0 }}</span>
                    <span class="text-slate-400">/ 100</span>
                    <span id="weight-sum-badge"
                          class="inline-flex items-center px-2 py-0.5 rounded-lg text-xs font-semibold {{ abs($weightSum - 100) < 0.01 ? 'bg-emerald-100 text-emerald-700' : 'bg-red-100 text-red-700' }}">
                        {{ abs($weightSum - 100) < 0.01 ? '✓' : '≠ 100' }}
                    </span>
                </div>
            </div>
            <div class="overflow-x-auto">
                <table class="w-full text-sm" style="min-width:520px">
                    <thead>
                        <tr class="text-left text-xs font-medium text-slate-500 uppercase tracking-wide bg-slate-50 border-b border-slate-100">
                            <th class="px-5 py-3 w-10">#</th>
                            <th class="px-5 py-3">{{ __('Course') }}</th>
                            <th class="px-5 py-3">{{ __('Subject Group') }}</th>
                            <th class="px-5 py-3 text-right w-40">{{ __('Weight') }} (%)</th>
                        </tr>
                    </thead>
                    <tbody>
                    @foreach($courses as $i => $c)
                        <tr class="border-b border-slate-100 hover:bg-slate-50">
                            <td class="px-5 py-3 text-xs text-slate-400">{{ $i + 1 }}</td>
                            <td class="px-5 py-3 font-medium text-slate-800">{{ $c->name }}</td>
                            <td class="px-5 py-3 text-slate-500">{{ $c->subjectGroup->name_th ?? '-' }}</td>
                            <td class="px-5 py-3 text-right">
                                <input type="number" name="weights[{{ $c->id }}]"
                                       value="{{ old('weights.'.$c->id, isset($weights[$c->id]) ? $weights[$c->id]->weight + 0 : '') }}"
                                       step="0.01" min="0" max="100" data-weight data-course="{{ $c->id }}"
                                       class="form-input w-28 text-right text-sm">
                            </td>
                        </tr>
                    @endforeach
                    </tbody>
                </table>
            </div>
            <div class="p-4 flex items-center justify-end gap-3 border-t border-slate-100">
                <span id="weight-save-hint" class="text-xs text-red-600 {{ abs($weightSum - 100) < 0.01 ? 'hidden' : '' }}">
                    {{ __('Total weight must equal 100 before saving.') }}
                </span>
                <button type="submit" id="save-weights-btn"
                        class="btn-primary disabled:opacity-40 disabled:cursor-not-allowed"
                        {{ abs($weightSum - 100) < 0.01 ? '' : 'disabled' }}>
                    <x-icon name="check" class="h-4 w-4" /> {{ __('Save') }}
                </button>
            </div>
        </x-card>
    </form>

    {{-- Copy from another term modal --}}
    @if($existingSources->isNotEmpty())
    <div id="copy-weights-modal" class="fixed inset-0 z-50 hidden">
        <div class="absolute inset-0 bg-slate-900/50 backdrop-blur-sm" onclick="document.getElementById('copy-weights-modal').classList.add('hidden')"></div>
        <div class="relative flex items-center justify-center min-h-screen p-4">
            <div class="bg-white rounded-2xl shadow-2xl border border-slate-100 w-full max-w-md p-6">
                <h3 class="text-lg font-semibold text-slate-900 mb-1">{{ __('Copy from another year') }}</h3>
                <p class="text-sm text-slate-500 mb-4">{{ __('Fill this form with weights from the same semester in another academic year. Review then Save.') }}</p>
                <div class="space-y-2 max-h-96 overflow-y-auto">
                    @foreach($existingSources as $idx => $src)
                    <div class="rounded-xl border border-slate-100 bg-slate-50 overflow-hidden">
                        <div class="flex items-center gap-2 p-3">
                            <button type="button" class="flex-1 flex items-center gap-2 text-left" data-expand="src-{{ $idx }}">
                                <x-icon name="chevron-down" class="h-4 w-4 text-slate-400 transition-transform" data-expand-icon="src-{{ $idx }}" />
                                <span class="text-sm font-medium text-slate-800">{{ __('Academic Year') }} {{ $src['year'] }} · {{ __('Semester') }} {{ $src['semester_number'] }}</span>
                                <span class="text-xs {{ abs($src['total'] - 100) < 0.01 ? 'text-emerald-600' : 'text-red-500' }}">({{ __('Total') }} {{ $src['total'] + 0 }})</span>
                            </button>
                            <button type="button" class="btn-secondary text-xs py-1.5" data-copy-year="{{ $src['academic_year_id'] }}" data-copy-sem="{{ $src['semester_id'] }}">
                                <x-icon name="download" class="h-4 w-4" /> {{ __('Use') }}
                            </button>
                        </div>
                        <div class="hidden px-3 pb-3" data-expand-body="src-{{ $idx }}">
                            <table class="w-full text-xs">
                                <tbody>
                                @foreach($src['details'] as $d)
                                <tr class="border-t border-slate-100">
                                    <td class="py-1.5 pr-2 text-slate-600">{{ $d['name'] }}</td>
                                    <td class="py-1.5 text-right font-medium text-slate-800 w-16">{{ $d['weight'] !== null ? $d['weight'] + 0 : '-' }}</td>
                                </tr>
                                @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                    @endforeach
                </div>
                <div class="flex justify-end mt-4">
                    <button type="button" class="btn-secondary" onclick="document.getElementById('copy-weights-modal').classList.add('hidden')">{{ __('Cancel') }}</button>
                </div>
            </div>
        </div>
    </div>
    @endif

    @push('scripts')
    <script>
    (function () {
        const inputs = document.querySelectorAll('[data-weight]');
        const sumEl = document.getElementById('weight-sum');
        const badgeEl = document.getElementById('weight-sum-badge');
        const saveBtn = document.getElementById('save-weights-btn');
        const hintEl = document.getElementById('weight-save-hint');
        const form = saveBtn ? saveBtn.closest('form') : null;

        function recalc() {
            let sum = 0;
            inputs.forEach(i => { if (i.value !== '') sum += parseFloat(i.value) || 0; });
            sum = Math.round(sum * 100) / 100;
            sumEl.textContent = sum;
            const ok = Math.abs(sum - 100) < 0.01;
            badgeEl.textContent = ok ? '✓' : '≠ 100';
            badgeEl.className = 'inline-flex items-center px-2 py-0.5 rounded-lg text-xs font-semibold '
                + (ok ? 'bg-emerald-100 text-emerald-700' : 'bg-red-100 text-red-700');
            if (saveBtn) saveBtn.disabled = !ok;
            if (hintEl) hintEl.classList.toggle('hidden', ok);
        }
        inputs.forEach(i => i.addEventListener('input', recalc));
        recalc(); // initial state (รวมค่า old หลัง validation error)

        // กันบันทึกถ้ารวม != 100 (เผื่อ JS/attribute ถูกข้าม)
        if (form) {
            form.addEventListener('submit', e => {
                let sum = 0;
                inputs.forEach(i => { if (i.value !== '') sum += parseFloat(i.value) || 0; });
                if (Math.abs(Math.round(sum * 100) / 100 - 100) >= 0.01) {
                    e.preventDefault();
                    recalc();
                }
            });
        }

        // ---- คัดลอกน้ำหนักจากปี/เทอมอื่น ----
        const modal = document.getElementById('copy-weights-modal');
        const COPY_URL = '{{ route('admin.course-weights.copy-source') }}';
        const GRADE_ID = {{ (int) $selectedGradeId }};
        if (modal) {
            // expand/ยุบ preview ของแต่ละปี/เทอม
            modal.querySelectorAll('[data-expand]').forEach(btn => {
                btn.addEventListener('click', () => {
                    const key = btn.dataset.expand;
                    const body = modal.querySelector(`[data-expand-body="${key}"]`);
                    const icon = modal.querySelector(`[data-expand-icon="${key}"]`);
                    if (body) body.classList.toggle('hidden');
                    if (icon) icon.classList.toggle('rotate-180');
                });
            });

            modal.querySelectorAll('[data-copy-year]').forEach(btn => {
                btn.addEventListener('click', async () => {
                    const y = btn.dataset.copyYear, s = btn.dataset.copySem;
                    const res = await fetch(`${COPY_URL}?academic_year_id=${y}&semester_id=${s}&grade_id=${GRADE_ID}`, {
                        headers: { 'Accept': 'application/json' },
                    });
                    const data = await res.json();
                    const weights = data.weights || {};
                    // เติมเฉพาะวิชาที่มีในฟอร์มปัจจุบัน (match ด้วย course_id)
                    inputs.forEach(i => {
                        const cid = i.dataset.course;
                        i.value = (weights[cid] !== undefined && weights[cid] !== null) ? weights[cid] : '';
                    });
                    modal.classList.add('hidden');
                    recalc();
                });
            });
        }
    })();
    </script>
    @endpush
    @endif
    @endif
</x-layouts.admin>
