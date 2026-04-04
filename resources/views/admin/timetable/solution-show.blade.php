@extends('layouts.app')

@php
    $theme = (\App\Models\Setting::first()->theme ?? 'light');
    $isDark = $theme === 'dark';
@endphp

@section('content')
<div class="min-h-screen bg-gray-50 dark:bg-[#18191a] py-10 px-4 sm:px-6 lg:px-8 transition-colors duration-300">
    <div class="max-w-[95vw] mx-auto">

        {{-- Header --}}
        <div class="flex items-center justify-between mb-6">
            <div class="flex items-center space-x-4">
                <a href="{{ route('admin.timetable.generations.show', $solution->generation_id) }}"
                   class="group flex items-center justify-center w-10 h-10 rounded-full bg-white dark:bg-[#242526] shadow-sm border border-gray-200 dark:border-[#3a3b3c] text-gray-500 dark:text-gray-400 hover:text-indigo-600 dark:hover:text-indigo-400 hover:border-indigo-300 transition-all duration-200">
                    <i class="fas fa-arrow-left group-hover:-translate-x-0.5 transition-transform"></i>
                </a>
                <div>
                    <h1 class="text-2xl font-extrabold text-gray-900 dark:text-white tracking-tight">{{ __('Timetable') }} — {{ __('Solution') }} #{{ $solution->rank }}</h1>
                    <p class="text-sm text-gray-500 dark:text-gray-400 font-medium px-1">
                        {{ __('Fitness') }}: {{ number_format($solution->fitness_score, 0) }} |
                        {{ __('Hard') }}: {{ $solution->hard_violations }} | {{ __('Soft') }}: {{ $solution->soft_violations }}
                    </p>
                </div>
            </div>
            <div class="flex items-center space-x-2">
                <button onclick="doExport('pdf')" class="btn-app text-sm">
                    <i class="fas fa-file-pdf text-[10px]"></i>Export PDF
                </button>
                <button onclick="doExport('excel')" class="btn-app text-sm">
                    <i class="fas fa-file-excel text-[10px]"></i>Export Excel
                </button>
                <button onclick="doExport('word')" class="btn-app text-sm">
                    <i class="fas fa-file-word text-[10px]"></i>Export Word
                </button>
                <button onclick="recalcFitness()" class="btn-app text-sm" title="{{ __('Recalculate Fitness') }}">
                    <i class="fas fa-sync-alt text-[10px]"></i> {{ __('Recalculate') }}
                </button>
            </div>
        </div>

        {{-- Filter Tabs --}}
        <div class="bg-white dark:bg-[#242526] rounded-[2rem] shadow-sm border border-gray-100 dark:border-[#3a3b3c] p-4 mb-6">
            <div class="flex flex-wrap items-center gap-3">
                <label class="text-sm font-medium text-gray-600 dark:text-gray-400">{{ __('View') }}:</label>
                <select id="viewMode" onchange="changeView()" class="px-4 py-2 bg-gray-50 dark:bg-[#3a3b3c] border-2 border-transparent rounded-xl text-sm text-gray-800 dark:text-white focus:border-indigo-500 focus:outline-none">
                    <option value="classroom">{{ __('By Classroom') }}</option>
                    <option value="teacher">{{ __('By Teacher') }}</option>
                    <option value="room">{{ __('By Room') }}</option>
                </select>

                <select id="filterEntity" onchange="renderGrid()" class="px-4 py-2 bg-gray-50 dark:bg-[#3a3b3c] border-2 border-transparent rounded-xl text-sm text-gray-800 dark:text-white focus:border-indigo-500 focus:outline-none">
                    <option value="">-- {{ __('Select') }} --</option>
                </select>

                {{-- Layout toggle --}}
                <div class="flex items-center gap-2 ml-auto">
                    <button id="layout-btn-normal" onclick="setLayout('normal')" class="btn-app text-sm btn-layout-active">
                        <i class="fas fa-columns text-[10px]"></i> {{ __('Day = Column') }}
                    </button>
                    <button id="layout-btn-transposed" onclick="setLayout('transposed')" class="btn-app text-sm opacity-40">
                        <i class="fas fa-rows text-[10px]"></i> {{ __('Period = Column') }}
                    </button>
                </div>
            </div>
        </div>

        {{-- Grid Container --}}
        <div id="grid-container" class="bg-white dark:bg-[#242526] rounded-[2rem] shadow-sm border border-gray-100 dark:border-[#3a3b3c] p-4 overflow-x-auto">
            <p class="text-center text-gray-400 dark:text-gray-500 py-12">{{ __('Select view and entity to display timetable') }}</p>
        </div>
    </div>
</div>

<script>
const solutionId = {{ $solution->id }};
const csrfToken = '{{ csrf_token() }}';
let allEntries = [];
let yearlySchedules = @json($yearlySchedules);
let openedCourses = @json($openedCoursesJson);
let classrooms = @json($classroomsJson);
let teachers = @json($teachersJson);
let rooms = @json($roomsJson);
let currentLayout = 'normal'; // 'normal' or 'transposed'

const __t = {
    period: @json(__('Period')),
    day: @json(__('Day')),
    select: '-- ' + @json(__('Select')) + ' --',
    selectViewPrompt: @json(__('Select view to display timetable')),
    noScheduleData: @json(__('No schedule data found for this level')),
    breakMin: @json(__('Break :min minutes')),
    cannotMove: @json(__('Cannot move')),
    recalcDone: @json(__('Recalculation done')),
};

const subjectColors = [
    'bg-indigo-100 dark:bg-indigo-900/30 border-indigo-300 dark:border-indigo-700 text-indigo-800 dark:text-indigo-300',
    'bg-emerald-100 dark:bg-emerald-900/30 border-emerald-300 dark:border-emerald-700 text-emerald-800 dark:text-emerald-300',
    'bg-amber-100 dark:bg-amber-900/30 border-amber-300 dark:border-amber-700 text-amber-800 dark:text-amber-300',
    'bg-rose-100 dark:bg-rose-900/30 border-rose-300 dark:border-rose-700 text-rose-800 dark:text-rose-300',
    'bg-purple-100 dark:bg-purple-900/30 border-purple-300 dark:border-purple-700 text-purple-800 dark:text-purple-300',
    'bg-teal-100 dark:bg-teal-900/30 border-teal-300 dark:border-teal-700 text-teal-800 dark:text-teal-300',
    'bg-pink-100 dark:bg-pink-900/30 border-pink-300 dark:border-pink-700 text-pink-800 dark:text-pink-300',
    'bg-cyan-100 dark:bg-cyan-900/30 border-cyan-300 dark:border-cyan-700 text-cyan-800 dark:text-cyan-300',
];

const dayNames = {1:@json(__('Monday')), 2:@json(__('Tuesday')), 3:@json(__('Wednesday')), 4:@json(__('Thursday')), 5:@json(__('Friday')), 6:@json(__('Saturday')), 7:@json(__('Sunday'))};

// Load entries
fetch(`/admin/timetable/api/solutions/${solutionId}/entries`)
    .then(r => r.json())
    .then(data => { allEntries = data; changeView(); });

// Layout toggle
function setLayout(layout) {
    currentLayout = layout;
    const btnNormal = document.getElementById('layout-btn-normal');
    const btnTransposed = document.getElementById('layout-btn-transposed');
    btnNormal.classList.toggle('opacity-40', layout !== 'normal');
    btnTransposed.classList.toggle('opacity-40', layout !== 'transposed');
    renderGrid();
}

function changeView() {
    const mode = document.getElementById('viewMode').value;
    const select = document.getElementById('filterEntity');
    select.innerHTML = `<option value="">${__t.select}</option>`;

    if (mode === 'classroom') {
        classrooms.forEach(c => {
            select.innerHTML += `<option value="${c.grade_id}_${c.classroom_id}" data-edu="${c.education_level_id}">${c.label}</option>`;
        });
    } else if (mode === 'teacher') {
        teachers.forEach(t => {
            select.innerHTML += `<option value="${t.id}">${t.name}</option>`;
        });
    } else if (mode === 'room') {
        rooms.forEach(r => {
            select.innerHTML += `<option value="${r.id}">${r.label}</option>`;
        });
    }
}

// Merge multiple schedules — union teaching_days, merge day_configs (use max periods)
function mergeSchedules(scheduleList) {
    const allTeachingDays = new Set();
    const mergedDayConfigs = {};
    let periodDuration = 50;
    let globalStart = '08:00';

    scheduleList.forEach(sch => {
        periodDuration = sch.period_duration || periodDuration;
        globalStart = sch.start_time || globalStart;
        (sch.teaching_days || []).forEach(d => {
            const dk = String(d);
            allTeachingDays.add(dk);
            const dc = (sch.day_configs || {})[dk];
            if (dc) {
                if (!mergedDayConfigs[dk] || (dc.periods || 0) > (mergedDayConfigs[dk].periods || 0)) {
                    mergedDayConfigs[dk] = dc;
                }
            }
        });
    });

    // Only keep days that actually have periods > 0
    const teachingDays = [...allTeachingDays]
        .filter(d => mergedDayConfigs[d] && (mergedDayConfigs[d].periods || 0) > 0)
        .sort((a, b) => parseInt(a) - parseInt(b));
    return { teachingDays, dayConfigs: mergedDayConfigs, periodDuration, globalStart };
}

// Shared: resolve filtered entries + schedule
function resolveGridData() {
    const mode = document.getElementById('viewMode').value;
    const value = document.getElementById('filterEntity').value;
    if (!value) return null;

    let filtered = [];
    let scheduleData = null;

    if (mode === 'classroom') {
        const [gId, cId] = value.split('_').map(Number);
        filtered = allEntries.filter(e => e.grade_id === gId && e.classroom_id === cId);
        const opt = document.getElementById('filterEntity').selectedOptions[0];
        const eduLevelId = parseInt(opt.dataset.edu);
        const schedule = yearlySchedules[eduLevelId];
        if (!schedule) return { error: 'noSchedule' };
        scheduleData = {
            teachingDays: (schedule.teaching_days || []).map(String),
            dayConfigs: schedule.day_configs || {},
            periodDuration: schedule.period_duration || 50,
            globalStart: schedule.start_time || '08:00',
        };
    } else {
        // Teacher or Room — collect all education levels involved
        if (mode === 'teacher') {
            filtered = allEntries.filter(e => e.teacher_id === parseInt(value));
        } else {
            filtered = allEntries.filter(e => e.room_id === parseInt(value));
        }

        // Find all unique education_level_ids from filtered entries
        const eduIds = new Set();
        filtered.forEach(e => {
            const oc = openedCourses.find(o => o.id === e.opened_course_id);
            if (oc?.education_level_id) eduIds.add(oc.education_level_id);
        });

        const relevantSchedules = [...eduIds]
            .map(id => yearlySchedules[id])
            .filter(Boolean);

        if (relevantSchedules.length === 0) return { error: 'noSchedule' };

        scheduleData = mergeSchedules(relevantSchedules);
    }

    let { teachingDays, dayConfigs, periodDuration, globalStart } = scheduleData;

    // Safety: exclude days with 0 periods (fixes legacy data that incorrectly includes non-teaching days)
    teachingDays = teachingDays.filter(d => {
        const dc = dayConfigs[d];
        return dc && (dc.periods || 0) > 0;
    });

    let maxPeriods = 0;
    teachingDays.forEach(d => {
        const dc = dayConfigs[d];
        if (dc && dc.periods > maxPeriods) maxPeriods = dc.periods;
    });

    return { filtered, mode, teachingDays, dayConfigs, periodDuration, globalStart, maxPeriods };
}

function calcTimes(dayStr, dayConfigs, globalStart, periodDuration) {
    const dc = dayConfigs[dayStr] || {};
    const startTime = dc.start_time || globalStart;
    const breaks = dc.breaks || {};
    let min = timeToMin(startTime);
    const times = [];
    for (let p = 1; p <= (dc.periods || 0); p++) {
        times.push({start: minToTime(min), end: minToTime(min + periodDuration)});
        min += periodDuration;
        if (breaks[String(p)]) min += parseInt(breaks[String(p)]);
    }
    return times;
}

function renderGrid() {
    const container = document.getElementById('grid-container');
    const value = document.getElementById('filterEntity').value;

    if (!value) {
        container.innerHTML = `<p class="text-center text-gray-400 dark:text-gray-500 py-12">${__t.selectViewPrompt}</p>`;
        return;
    }

    const data = resolveGridData();
    if (!data || data.error === 'noSchedule') {
        container.innerHTML = `<p class="text-center text-gray-400 py-12">${__t.noScheduleData}</p>`;
        return;
    }

    if (currentLayout === 'transposed') {
        container.innerHTML = buildTransposedGrid(data);
    } else {
        container.innerHTML = buildNormalGrid(data);
    }
}

function renderEntryCell(entry, mode) {
    const colorIdx = (entry.subject_group_id || 0) % subjectColors.length;
    const color = subjectColors[colorIdx];
    const lockIcon = entry.is_locked
        ? '<i class="fas fa-lock text-amber-500 text-[9px]"></i>'
        : '<i class="fas fa-lock-open text-gray-300 text-[9px] cursor-pointer hover:text-amber-500" onclick="toggleLock('+entry.id+')"></i>';

    return `<td class="border border-gray-200 dark:border-[#3a3b3c] p-1"
                 draggable="true" ondragstart="dragStart(event, ${entry.id})"
                 data-day="${entry.day}" data-period="${entry.period}">
        <div class="p-2 rounded-xl border ${color} text-xs space-y-0.5 cursor-move" id="entry-${entry.id}">
            <div class="flex items-center justify-between">
                <span class="font-bold truncate">${entry.course_name}</span>
                ${lockIcon}
            </div>
            <div class="text-[10px] opacity-75 truncate">${entry.teacher_name || '-'}</div>
            <div class="text-[10px] opacity-60 truncate">${entry.room_number || '-'}${mode === 'teacher' || mode === 'room' ? ' | ' + (entry.classroom || '') : ''}</div>
        </div>
    </td>`;
}

function renderEmptyCell(dayInt, p) {
    return `<td class="border border-gray-200 dark:border-[#3a3b3c] p-1 hover:bg-indigo-50 dark:hover:bg-indigo-900/10 transition-colors"
                 ondragover="event.preventDefault()" ondrop="drop(event, ${dayInt}, ${p})"
                 data-day="${dayInt}" data-period="${p}">
        <div class="h-12 flex items-center justify-center text-gray-300 dark:text-gray-600"></div>
    </td>`;
}

// ==================== Normal layout: columns=days, rows=periods ====================
function buildNormalGrid({filtered, mode, teachingDays, dayConfigs, periodDuration, globalStart, maxPeriods}) {
    const times = calcTimes(teachingDays[0], dayConfigs, globalStart, periodDuration);

    let html = '<table class="w-full border-collapse" style="min-width:600px">';
    html += `<thead><tr><th class="p-2 text-xs font-semibold text-gray-500 dark:text-gray-400 border border-gray-200 dark:border-[#3a3b3c] bg-gray-50 dark:bg-[#3a3b3c] w-20">${__t.period}</th>`;
    teachingDays.forEach(d => {
        html += `<th class="p-2 text-xs font-semibold text-gray-700 dark:text-gray-300 border border-gray-200 dark:border-[#3a3b3c] bg-gray-50 dark:bg-[#3a3b3c]">${dayNames[parseInt(d)] || __t.day+' '+d}</th>`;
    });
    html += '</tr></thead><tbody>';

    for (let p = 1; p <= maxPeriods; p++) {
        html += '<tr>';
        const timeLabel = times[p-1] ? `<br><span class="text-[10px] text-gray-400">${times[p-1].start}-${times[p-1].end}</span>` : '';
        html += `<td class="p-2 text-center text-xs font-medium text-gray-600 dark:text-gray-400 border border-gray-200 dark:border-[#3a3b3c] bg-gray-50 dark:bg-[#3a3b3c]">${__t.period} ${p}${timeLabel}</td>`;

        teachingDays.forEach(d => {
            const dayInt = parseInt(d);
            const dc = dayConfigs[d];
            if (!dc || p > dc.periods) {
                html += '<td class="border border-gray-200 dark:border-[#3a3b3c] bg-gray-100 dark:bg-[#1a1b1c]"></td>';
                return;
            }
            const entry = filtered.find(e => e.day === dayInt && e.period === p);
            html += entry ? renderEntryCell(entry, mode) : renderEmptyCell(dayInt, p);
        });
        html += '</tr>';

        const breaks = dayConfigs[teachingDays[0]]?.breaks || {};
        if (breaks[String(p)]) {
            html += `<tr><td colspan="${teachingDays.length + 1}" class="py-1 text-center text-[10px] text-amber-600 dark:text-amber-400 bg-amber-50 dark:bg-amber-900/10 border border-gray-200 dark:border-[#3a3b3c]">
                ${__t.breakMin.replace(':min', breaks[String(p)])}
            </td></tr>`;
        }
    }

    html += '</tbody></table>';
    return html;
}

// ==================== Transposed layout: columns=periods, rows=days ====================
function buildTransposedGrid({filtered, mode, teachingDays, dayConfigs, periodDuration, globalStart, maxPeriods}) {
    const times = calcTimes(teachingDays[0], dayConfigs, globalStart, periodDuration);

    let html = '<table class="w-full border-collapse" style="min-width:600px">';

    // Header: Day | Period 1 | Period 2 | ...
    html += `<thead><tr><th class="p-2 text-xs font-semibold text-gray-500 dark:text-gray-400 border border-gray-200 dark:border-[#3a3b3c] bg-gray-50 dark:bg-[#3a3b3c] w-24">${__t.day}</th>`;
    for (let p = 1; p <= maxPeriods; p++) {
        const timeLabel = times[p-1] ? `<br><span class="text-[10px] text-gray-400 font-normal">${times[p-1].start}-${times[p-1].end}</span>` : '';
        html += `<th class="p-2 text-xs font-semibold text-gray-700 dark:text-gray-300 border border-gray-200 dark:border-[#3a3b3c] bg-gray-50 dark:bg-[#3a3b3c]">${__t.period} ${p}${timeLabel}</th>`;
    }
    html += '</tr></thead><tbody>';

    // Rows: one per teaching day
    teachingDays.forEach(d => {
        const dayInt = parseInt(d);
        const dc = dayConfigs[d];
        html += '<tr>';
        html += `<td class="p-2 text-center text-xs font-semibold text-gray-700 dark:text-gray-300 border border-gray-200 dark:border-[#3a3b3c] bg-gray-50 dark:bg-[#3a3b3c]">${dayNames[dayInt] || __t.day+' '+d}</td>`;

        for (let p = 1; p <= maxPeriods; p++) {
            if (!dc || p > dc.periods) {
                html += '<td class="border border-gray-200 dark:border-[#3a3b3c] bg-gray-100 dark:bg-[#1a1b1c]"></td>';
                continue;
            }
            const entry = filtered.find(e => e.day === dayInt && e.period === p);
            html += entry ? renderEntryCell(entry, mode) : renderEmptyCell(dayInt, p);
        }
        html += '</tr>';
    });

    html += '</tbody></table>';
    return html;
}

// Drag & Drop
let dragEntryId = null;

function dragStart(e, entryId) {
    dragEntryId = entryId;
    e.dataTransfer.effectAllowed = 'move';
}

function drop(e, day, period) {
    e.preventDefault();
    if (!dragEntryId) return;

    const entry = allEntries.find(en => en.id === dragEntryId);
    if (!entry) return;

    fetch(`/admin/timetable/api/entries/${dragEntryId}/move`, {
        method: 'POST',
        headers: {'X-CSRF-TOKEN': csrfToken, 'Content-Type': 'application/json'},
        body: JSON.stringify({day, period}),
    }).then(r => r.json()).then(data => {
        if (data.success) {
            entry.day = day;
            entry.period = period;
            renderGrid();
        } else {
            const msgs = (data.violations || []).map(v => v.message).join('\n');
            alert(__t.cannotMove + ':\n' + msgs);
        }
    });
    dragEntryId = null;
}

function toggleLock(entryId) {
    fetch(`/admin/timetable/api/entries/${entryId}/lock`, {
        method: 'POST',
        headers: {'X-CSRF-TOKEN': csrfToken, 'Content-Type': 'application/json'},
    }).then(r => r.json()).then(data => {
        if (data.success) {
            const entry = allEntries.find(e => e.id === entryId);
            if (entry) entry.is_locked = data.is_locked;
            renderGrid();
        }
    });
}

function recalcFitness() {
    fetch(`/admin/timetable/api/solutions/${solutionId}/fitness`)
        .then(r => r.json())
        .then(data => {
            alert(`${__t.recalcDone}\nHard: ${data.hard_violations}\nSoft: ${data.soft_violations}\nTotal conflicts: ${data.total_conflicts}`);
            location.reload();
        });
}

function timeToMin(t) { const p = t.split(':'); return parseInt(p[0])*60 + parseInt(p[1]||0); }
function minToTime(m) { return String(Math.floor(m/60)).padStart(2,'0') + ':' + String(m%60).padStart(2,'0'); }

// ==================== Export ====================

function doExport(format) {
    const mode = document.getElementById('viewMode').value;
    const value = document.getElementById('filterEntity').value;

    if (!value) {
        alert(@json(__('Please select a view and entity before exporting')));
        return;
    }

    const formData = { solution_id: solutionId, layout: currentLayout };
    let url = '';

    if (format === 'pdf') {
        if (mode === 'classroom') {
            const [gId, cId] = value.split('_').map(Number);
            formData.grade_id = gId;
            formData.classroom_id = cId;
            url = '/admin/timetable/api/export/classroom-pdf';
        } else if (mode === 'teacher') {
            formData.teacher_id = parseInt(value);
            url = '/admin/timetable/api/export/teacher-pdf';
        } else if (mode === 'room') {
            formData.room_id = parseInt(value);
            url = '/admin/timetable/api/export/room-pdf';
        }
        postFormNewWindow(url, formData);
    } else if (format === 'excel') {
        formData.type = mode;
        if (mode === 'classroom') {
            const [gId, cId] = value.split('_').map(Number);
            formData.grade_id = gId;
            formData.classroom_id = cId;
        } else if (mode === 'teacher') {
            formData.teacher_id = parseInt(value);
        } else if (mode === 'room') {
            formData.room_id = parseInt(value);
        }
        url = '/admin/timetable/api/export/excel';
        postFormDownload(url, formData);
    } else if (format === 'word') {
        formData.type = mode;
        if (mode === 'classroom') {
            const [gId, cId] = value.split('_').map(Number);
            formData.grade_id = gId;
            formData.classroom_id = cId;
        } else if (mode === 'teacher') {
            formData.teacher_id = parseInt(value);
        } else if (mode === 'room') {
            formData.room_id = parseInt(value);
        }
        url = '/admin/timetable/api/export/word';
        postFormDownload(url, formData);
    }
}

function postFormNewWindow(url, data) {
    const form = document.createElement('form');
    form.method = 'POST';
    form.action = url;
    form.target = '_blank';
    form.innerHTML = `<input type="hidden" name="_token" value="${csrfToken}">`;
    for (const [k, v] of Object.entries(data)) {
        form.innerHTML += `<input type="hidden" name="${k}" value="${v}">`;
    }
    document.body.appendChild(form);
    form.submit();
    form.remove();
}

function postFormDownload(url, data) {
    const form = document.createElement('form');
    form.method = 'POST';
    form.action = url;
    form.innerHTML = `<input type="hidden" name="_token" value="${csrfToken}">`;
    for (const [k, v] of Object.entries(data)) {
        form.innerHTML += `<input type="hidden" name="${k}" value="${v}">`;
    }
    document.body.appendChild(form);
    form.submit();
    form.remove();
}
</script>
@endsection
