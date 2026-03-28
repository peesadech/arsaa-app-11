@extends('layouts.app')

@php
    $dayMeta = [
        1 => ['th' => 'จันทร์',   'short' => 'จ',   'en' => 'Mon'],
        2 => ['th' => 'อังคาร',   'short' => 'อ',   'en' => 'Tue'],
        3 => ['th' => 'พุธ',      'short' => 'พ',   'en' => 'Wed'],
        4 => ['th' => 'พฤหัส',   'short' => 'พฤ',  'en' => 'Thu'],
        5 => ['th' => 'ศุกร์',    'short' => 'ศ',   'en' => 'Fri'],
        6 => ['th' => 'เสาร์',    'short' => 'ส',   'en' => 'Sat'],
        7 => ['th' => 'อาทิตย์',  'short' => 'อา',  'en' => 'Sun'],
    ];
    $savedConfigs = $schedule->day_configs ?? [];
@endphp

@push('styles')
<style>
    .schedule-table { border-collapse: separate; border-spacing: 0; }
    .schedule-table td { vertical-align: middle; }
    .col-label { min-width: 64px; width: 64px; }
    .day-col { min-width: 112px; }
    .break-row td { height: 34px; }
    @keyframes fadeInUp {
        from { opacity:0; transform:translateY(12px); }
        to   { opacity:1; transform:translateY(0); }
    }
</style>
@endpush

@section('content')
<div class="min-h-screen bg-gray-50 dark:bg-[#18191a] py-10 px-4 sm:px-6 lg:px-8 transition-colors duration-300">
    <div class="max-w-6xl mx-auto">

        {{-- Header --}}
        <div class="flex items-center space-x-4 mb-8">
            <a href="{{ route('admin.yearly-schedule.index') }}"
               class="group flex items-center justify-center w-10 h-10 rounded-full bg-white dark:bg-[#242526] shadow-sm border border-gray-200 dark:border-[#3a3b3c] text-gray-500 dark:text-gray-400 hover:text-indigo-600 dark:hover:text-indigo-400 hover:border-indigo-300 transition-all duration-200">
                <i class="fas fa-arrow-left group-hover:-translate-x-0.5 transition-transform"></i>
            </a>
            <div>
                <h1 class="text-2xl font-extrabold text-gray-900 dark:text-white tracking-tight">{{ $educationLevel->name_th }}</h1>
                <p class="text-sm text-gray-500 dark:text-gray-400 font-medium px-1">
                    {{ $educationLevel->name_en }} — ปีการศึกษา {{ $academicYear->year }} / ภาคเรียนที่ {{ $semester->semester_number }}
                </p>
            </div>
        </div>

        @if($errors->any())
        <div class="mb-6 p-4 bg-rose-50 dark:bg-rose-900/20 border-l-4 border-rose-500 rounded-r-2xl text-sm text-rose-700 dark:text-rose-400 font-medium">
            <i class="fas fa-exclamation-triangle mr-2"></i>
            @foreach($errors->all() as $e)<div>{{ $e }}</div>@endforeach
        </div>
        @endif

        <form action="{{ route('admin.yearly-schedule.update', [$academicYear->id, $semester->id, $educationLevel->id]) }}" method="POST" id="scheduleForm">
            @csrf @method('PUT')
            <input type="hidden" name="day_configs" id="dayConfigsInput">
            <div id="teachingDaysInputs"></div>

            {{-- Global Settings --}}
            <div class="bg-white dark:bg-[#242526] rounded-[2rem] shadow-sm border border-gray-100 dark:border-[#3a3b3c] p-6 mb-5">
                <h3 class="text-xs font-bold text-gray-400 dark:text-gray-500 uppercase tracking-widest mb-4">ตั้งค่าทั่วไป</h3>
                <div class="flex flex-wrap gap-6">
                    <div class="space-y-2">
                        <label class="block text-xs font-bold text-gray-400 dark:text-gray-500 uppercase tracking-widest px-1">เวลาเริ่มสอน</label>
                        <div class="relative">
                            <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none text-gray-400"><i class="fas fa-clock text-sm"></i></div>
                            <input type="time" id="start_time" name="start_time"
                                value="{{ old('start_time', $schedule->start_time ? \Carbon\Carbon::parse($schedule->start_time)->format('H:i') : '08:00') }}"
                                class="pl-10 pr-4 py-3 bg-gray-50 dark:bg-[#3a3b3c] border-2 border-transparent rounded-2xl text-gray-900 dark:text-white focus:outline-none focus:border-indigo-500 transition-all w-40"
                                oninput="renderTable()" required>
                        </div>
                    </div>
                    <div class="space-y-2">
                        <label class="block text-xs font-bold text-gray-400 dark:text-gray-500 uppercase tracking-widest px-1">ความยาวต่อคาบ</label>
                        <div class="relative">
                            <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none text-gray-400"><i class="fas fa-hourglass-half text-sm"></i></div>
                            <input type="number" id="period_duration" name="period_duration" min="1" max="240"
                                value="{{ old('period_duration', $schedule->period_duration ?? 50) }}"
                                class="pl-10 pr-14 py-3 bg-gray-50 dark:bg-[#3a3b3c] border-2 border-transparent rounded-2xl text-gray-900 dark:text-white focus:outline-none focus:border-indigo-500 transition-all w-40"
                                oninput="renderTable()" required>
                            <div class="absolute inset-y-0 right-0 pr-4 flex items-center pointer-events-none text-gray-400 text-xs font-bold">นาที</div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Schedule Table --}}
            <div class="bg-white dark:bg-[#242526] rounded-[2rem] shadow-sm border border-gray-100 dark:border-[#3a3b3c] overflow-hidden mb-5">
                <div class="px-6 py-3 border-b border-gray-100 dark:border-[#3a3b3c] flex items-center justify-between">
                    <h3 class="text-xs font-bold text-gray-400 dark:text-gray-500 uppercase tracking-widest">ตารางเวลา</h3>
                    <span class="text-[10px] text-amber-500 font-bold">คลิกแถวพักเพื่อเปิด/ปิด | [-/+] เพิ่ม-ลดคาบของแต่ละวัน</span>
                </div>
                <div class="overflow-x-auto p-4">
                    <table id="scheduleTable" class="schedule-table">
                        <tbody id="scheduleBody"></tbody>
                    </table>
                </div>
            </div>

            {{-- Save --}}
            <button type="submit" onclick="prepareSubmit()"
                class="w-full group relative flex items-center justify-center px-8 py-4 bg-indigo-600 text-white hover:bg-indigo-700 font-bold rounded-2xl active:scale-95 transition-all duration-200 shadow-lg shadow-indigo-200 dark:shadow-none overflow-hidden">
                <span class="relative z-10 flex items-center">
                    <i class="fas fa-save mr-2 opacity-50 group-hover:opacity-100 transition-opacity"></i>
                    บันทึกกำหนดการ
                </span>
                <div class="absolute inset-0 bg-gradient-to-r from-transparent via-white/10 to-transparent -translate-x-full group-hover:translate-x-full transition-transform duration-1000 ease-in-out"></div>
            </button>
        </form>
    </div>
</div>

@push('scripts')
<script>
const DAY_META = @json($dayMeta);
const ALL_DAYS = ['1','2','3','4','5','6','7'];

let state = {
    teaching_days: ALL_DAYS,
    day_configs:   {},
};

(() => {
    const raw = @json($savedConfigs);
    for (const [d, cfg] of Object.entries(raw || {})) {
        state.day_configs[String(d)] = {
            periods:    cfg.periods ?? 8,
            start_time: cfg.start_time || null,
            breaks:     Object.fromEntries(
                Object.entries(cfg.breaks || {}).map(([k,v]) => [String(k), Number(v)])
            ),
        };
    }
    ALL_DAYS.forEach(d => {
        if (!state.day_configs[d]) state.day_configs[d] = { periods: d === '7' ? 0 : 8, start_time: null, breaks: {} };
    });
})();

function fmt(min) {
    const h = Math.floor(min / 60) % 24, m = min % 60;
    return String(h).padStart(2,'0') + ':' + String(m).padStart(2,'0');
}

function globalStartTime() {
    return document.getElementById('start_time').value || '08:00';
}

function periodStart(dayNum, p) {
    const cfg     = state.day_configs[dayNum] || {};
    const timeStr = cfg.start_time || globalStartTime();
    const [hh, mm] = timeStr.split(':').map(Number);
    const dur = Number(document.getElementById('period_duration').value) || 50;
    let t = hh * 60 + mm;
    for (let i = 1; i < p; i++) {
        t += dur;
        const b = (cfg.breaks || {})[String(i)];
        if (b) t += b;
    }
    return t;
}

function isDark() {
    return document.documentElement.classList.contains('dark');
}

function renderTable() {
    const days = state.teaching_days;
    const dur  = Number(document.getElementById('period_duration').value) || 50;
    const maxP = days.reduce((m, d) => Math.max(m, state.day_configs[d]?.periods ?? 0), 0);
    const dark = isDark();

    const C = {
        bg:        dark ? '#242526' : '#ffffff',
        bgAlt:     dark ? '#18191a' : '#f9fafb',
        border:    dark ? '#3a3b3c' : '#f0f0f0',
        text:      dark ? '#e4e6eb' : '#1f2937',
        muted:     dark ? '#6b7280' : '#9ca3af',
        indigo:    '#6366f1',
        amber:     '#f59e0b',
        amberBg:   dark ? 'rgba(245,158,11,0.12)' : 'rgba(254,243,199,0.8)',
        amberBdr:  '#f59e0b',
    };

    const td = (content, style='') =>
        `<td style="padding:6px 8px;border:1px solid ${C.border};${style}">${content}</td>`;

    let rows = '';

    rows += '<tr>';
    rows += `<td class="col-label" style="padding:8px;border-right:2px solid ${C.border}"></td>`;
    days.forEach(d => {
        const meta    = DAY_META[d];
        const cfg     = state.day_configs[d] || {};
        const p       = cfg.periods ?? 0;
        const isOff   = p === 0;
        const dayTime = cfg.start_time || globalStartTime();
        rows += `
        <td class="day-col" style="padding:10px 8px;text-align:center;background:${C.bgAlt};border:1px solid ${C.border};border-bottom:2px solid ${isOff ? C.border : C.indigo};opacity:${isOff ? '.5' : '1'}">
            <div style="font-size:13px;font-weight:800;color:${isOff ? C.muted : C.indigo}">${meta.th}</div>
            <div style="font-size:9px;color:${C.muted};text-transform:uppercase;margin-bottom:4px">${meta.en}</div>
            <div style="margin-bottom:8px">
                <input type="time" value="${dayTime}"
                    onchange="setDayStartTime('${d}',this.value)"
                    style="width:90px;padding:3px 6px;border-radius:8px;border:1.5px solid ${cfg.start_time ? C.indigo : C.border};background:${C.bg};color:${C.text};font-size:11px;font-weight:700;text-align:center;outline:none;cursor:pointer"
                    onfocus="this.style.borderColor='${C.indigo}'"
                    onblur="this.style.borderColor='${cfg.start_time ? C.indigo : C.border}'">
            </div>
            <div style="display:flex;align-items:center;justify-content:center;gap:5px">
                <button type="button" onclick="changePeriods('${d}',-1)" ${isOff ? 'disabled style="opacity:.3;cursor:default;"' : ''}
                    style="width:22px;height:22px;border-radius:7px;border:2px solid ${C.border};background:${C.bg};color:${C.muted};font-weight:900;${isOff?'':'cursor:pointer;'}font-size:14px;display:flex;align-items:center;justify-content:center;transition:all .15s"
                    ${isOff ? '' : `onmouseover="this.style.borderColor='${C.indigo}';this.style.color='${C.indigo}'" onmouseout="this.style.borderColor='${C.border}';this.style.color='${C.muted}'"`}>&#8722;</button>
                <span style="font-size:14px;font-weight:800;color:${isOff ? C.muted : C.text};min-width:22px;text-align:center">${p}</span>
                <button type="button" onclick="changePeriods('${d}',1)"
                    style="width:22px;height:22px;border-radius:7px;border:2px solid ${C.border};background:${C.bg};color:${C.muted};font-weight:900;cursor:pointer;font-size:14px;display:flex;align-items:center;justify-content:center;transition:all .15s"
                    onmouseover="this.style.borderColor='${C.indigo}';this.style.color='${C.indigo}'"
                    onmouseout="this.style.borderColor='${C.border}';this.style.color='${C.muted}'">+</button>
            </div>
            <div style="font-size:9px;color:${C.muted};margin-top:2px">${isOff ? 'หยุด' : 'คาบ'}</div>
        </td>`;
    });
    rows += '</tr>';

    for (let p = 1; p <= maxP; p++) {
        rows += '<tr>';
        rows += `<td class="col-label" style="padding:8px;border-right:2px solid ${C.border};white-space:nowrap">
            <span style="font-size:11px;font-weight:800;color:${C.indigo}">คาบ ${p}</span>
        </td>`;
        days.forEach(d => {
            const cfg = state.day_configs[d] || {};
            if (p <= (cfg.periods ?? 0)) {
                const s = periodStart(d, p);
                rows += td(`
                    <div style="text-align:center">
                        <div style="font-size:12px;font-weight:700;color:${C.text}">${fmt(s)}</div>
                        <div style="font-size:10px;color:${C.muted}">– ${fmt(s+dur)}</div>
                    </div>`, `background:${C.bg}`);
            } else {
                rows += td(`<div style="text-align:center;font-size:11px;color:${C.muted}">—</div>`,
                    `background:${C.bgAlt};opacity:.3`);
            }
        });
        rows += '</tr>';

        if (p < maxP) {
            rows += `<tr class="break-row">`;
            rows += `<td class="col-label" style="padding:3px 8px;border-right:2px solid ${C.border}">
                <span style="font-size:10px;color:${C.amber};font-weight:700">พัก</span>
            </td>`;
            days.forEach(d => {
                const cfg    = state.day_configs[d] || { periods: 0, breaks: {} };
                const active = p < (cfg.periods ?? 0);
                if (!active) {
                    rows += td('', `background:${C.bgAlt};opacity:.2`);
                    return;
                }
                const has  = cfg.breaks[String(p)] !== undefined;
                const bDur = cfg.breaks[String(p)] || 10;
                rows += `<td style="padding:3px 6px;border:1px solid ${has ? C.amberBdr : C.border};background:${has ? C.amberBg : C.bgAlt};cursor:pointer;transition:all .2s;text-align:center"
                    onclick="toggleBreak('${d}',${p})"
                    title="${has ? 'คลิกเพื่อลบช่วงพัก' : 'คลิกเพื่อเพิ่มช่วงพัก'}">
                    ${has ? `
                    <div style="display:flex;align-items:center;justify-content:center;gap:4px">
                        <input type="number" min="1" max="120" value="${bDur}"
                            onclick="event.stopPropagation()"
                            onchange="setBreakDur('${d}',${p},this.value)"
                            style="width:40px;border:1px solid ${C.amberBdr};border-radius:6px;padding:1px 4px;font-size:11px;font-weight:700;text-align:center;background:transparent;color:#d97706;outline:none">
                        <span style="font-size:9px;color:#d97706;font-weight:700">น.</span>
                    </div>` : `<span style="font-size:11px;color:${C.muted}">—</span>`}
                </td>`;
            });
            rows += '</tr>';
        }
    }

    document.getElementById('scheduleBody').innerHTML = rows;
}

function changePeriods(d, delta) {
    const cfg = state.day_configs[d] || { periods: 0, breaks: {} };
    cfg.periods = Math.max(0, Math.min(20, (cfg.periods ?? 0) + delta));
    Object.keys(cfg.breaks).forEach(p => { if (Number(p) >= cfg.periods) delete cfg.breaks[p]; });
    state.day_configs[d] = cfg;
    renderTable();
}

function setDayStartTime(d, val) {
    if (!state.day_configs[d]) state.day_configs[d] = { periods: 0, start_time: null, breaks: {} };
    state.day_configs[d].start_time = val || null;
    renderTable();
}

function toggleBreak(d, p) {
    const cfg = state.day_configs[d] || { periods: 1, breaks: {} };
    const key = String(p);
    if (cfg.breaks[key] !== undefined) delete cfg.breaks[key];
    else cfg.breaks[key] = 10;
    state.day_configs[d] = cfg;
    renderTable();
}

function setBreakDur(d, p, val) {
    const cfg = state.day_configs[d] || { periods: 1, breaks: {} };
    cfg.breaks[String(p)] = Math.max(1, Math.min(120, Number(val) || 10));
    state.day_configs[d] = cfg;
    renderTable();
}

function prepareSubmit() {
    document.getElementById('dayConfigsInput').value = JSON.stringify(state.day_configs);
    document.getElementById('teachingDaysInputs').innerHTML = ALL_DAYS
        .map(d => `<input type="hidden" name="teaching_days[]" value="${d}">`).join('');
}

new MutationObserver(renderTable).observe(document.documentElement, { attributes: true, attributeFilter: ['class'] });
document.getElementById('start_time').addEventListener('change', renderTable);
document.getElementById('period_duration').addEventListener('input', renderTable);

renderTable();
</script>
@endpush
@endsection
