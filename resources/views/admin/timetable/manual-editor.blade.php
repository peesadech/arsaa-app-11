@extends('layouts.app')

@section('content')
<div class="min-h-screen bg-gray-50 dark:bg-[#18191a] py-6 px-4 sm:px-6 lg:px-8 transition-colors duration-300">
    <div class="max-w-[98vw] mx-auto">

        {{-- Header --}}
        <div class="flex items-center justify-between mb-4">
            <div class="flex items-center space-x-4">
                <a href="{{ route('admin.timetable.manual.select') }}"
                   class="group flex items-center justify-center w-10 h-10 rounded-full bg-white dark:bg-[#242526] shadow-sm border border-gray-200 dark:border-[#3a3b3c] text-gray-500 dark:text-gray-400 hover:text-indigo-600 dark:hover:text-indigo-400 hover:border-indigo-300 transition-all duration-200">
                    <i class="fas fa-arrow-left group-hover:-translate-x-0.5 transition-transform"></i>
                </a>
                <div>
                    <h1 class="text-xl font-extrabold text-gray-900 dark:text-white tracking-tight">
                        {{ __('Schedule') }} {{ $grade->name_th }} / {{ $classroom->name }}
                    </h1>
                    <p class="text-xs text-gray-500 dark:text-gray-400">{{ __('Click empty cell to place | Drag to move | Right-click to remove') }}</p>
                </div>
            </div>
            <div class="flex items-center gap-2">
                <button onclick="checkAllConflicts()" class="btn-app text-sm">
                    <i class="fas fa-search text-[10px]"></i> {{ __('Check Conflicts') }}
                </button>
            </div>
        </div>

        <div class="flex gap-4">
            {{-- Sidebar: Course List --}}
            <div class="w-72 shrink-0">
                <div class="bg-white dark:bg-[#242526] rounded-2xl shadow-sm border border-gray-100 dark:border-[#3a3b3c] p-4 sticky top-4">
                    <h3 class="text-sm font-bold text-gray-700 dark:text-gray-300 uppercase tracking-wide mb-3">{{ __('Courses to Schedule') }}</h3>
                    <div class="space-y-2 max-h-[70vh] overflow-y-auto" id="course-list">
                        @foreach($openedCourses as $oc)
                        @php
                            $colorIdx = ($oc->course->subject_group_id ?? 0) % 8;
                            $placed = $entries->where('opened_course_id', $oc->id)->count();
                            $needed = $oc->course->periods_per_week ?? 1;
                            $remaining = max(0, $needed - $placed);

                            // Use term courses if available, otherwise fallback to global
                            $termTeachersForCourse = $termCourseTeachers[$oc->course_id] ?? null;
                            if ($termTeachersForCourse && $termTeachersForCourse->isNotEmpty()) {
                                $teachersJson = $termTeachersForCourse->map(fn($tc) => [
                                    'id' => $tc->teacher_id,
                                    'name' => $tc->teacher->name,
                                    'schedulable' => in_array($tc->teacher_id, $schedulableTeacherIds),
                                ])->values();
                            } else {
                                $teachersJson = $oc->course->teachers->map(fn($t) => [
                                    'id' => $t->id,
                                    'name' => $t->name,
                                    'schedulable' => in_array($t->id, $schedulableTeacherIds),
                                ])->values();
                            }
                        @endphp
                        <div class="course-item p-3 rounded-xl border cursor-pointer transition-all
                                    {{ $remaining === 0 ? 'bg-gray-50 dark:bg-[#3a3b3c]/50 border-gray-200 dark:border-[#3a3b3c] opacity-60' : 'bg-white dark:bg-[#3a3b3c] border-gray-200 dark:border-[#4a4b4c] hover:border-indigo-300 dark:hover:border-indigo-600 hover:shadow-sm' }}"
                             data-oc-id="{{ $oc->id }}"
                             data-course-id="{{ $oc->course_id }}"
                             data-course-name="{{ $oc->course->name }}"
                             data-subject-group="{{ $oc->course->subjectGroup->name_th ?? '' }}"
                             data-subject-group-id="{{ $oc->course->subject_group_id ?? 0 }}"
                             data-periods-needed="{{ $needed }}"
                             data-periods-per-session="{{ $oc->course->periods_per_session ?? 1 }}"
                             data-color-idx="{{ $colorIdx }}"
                             data-teachers='@json($teachersJson)'
                             data-rooms='@json($oc->course->rooms->map(function($r) { return ["id" => $r->id, "label" => $r->room_number . ($r->building ? " (".$r->building->name_th.")" : "")]; }))'
                             data-preferred-days='@json($oc->course->preferred_days ?? [])'
                             onclick="selectCourse(this)">
                            <div class="flex items-center justify-between">
                                <span class="text-sm font-bold text-gray-800 dark:text-white truncate">{{ $oc->course->name }}</span>
                                <span class="text-[10px] font-bold {{ $remaining === 0 ? 'text-emerald-600' : 'text-indigo-600' }} whitespace-nowrap ml-2"
                                      id="remaining-{{ $oc->id }}">{{ $placed }}/{{ $needed }}</span>
                            </div>
                            <div class="text-[10px] text-gray-400 dark:text-gray-500 mt-0.5">
                                {{ $oc->course->subjectGroup->name_th ?? '-' }}
                                @if(($oc->course->periods_per_session ?? 1) > 1)
                                    <span class="text-indigo-500 dark:text-indigo-400 ml-1">| {{ $oc->course->periods_per_session }} {{ __('periods/session') }}</span>
                                @endif
                            </div>
                            @if(!empty($oc->course->preferred_days))
                            @php
                                $dNames = [1=>__('Mon'), 2=>__('Tue'), 3=>__('Wed'), 4=>__('Thu'), 5=>__('Fri'), 6=>__('Sat'), 7=>__('Sun')];
                                $pDays = collect($oc->course->preferred_days)->map(fn($d) => $dNames[$d] ?? $d)->join(' ');
                            @endphp
                            <div class="text-[10px] text-emerald-500 dark:text-emerald-400 mt-0.5">
                                <i class="fas fa-calendar-check text-[8px]"></i> {{ __('Available') }}: {{ $pDays }}
                            </div>
                            @endif
                            <div class="text-[10px] text-gray-400 dark:text-gray-500">
                                {{ __('Teacher') }}:
                                @php $displayTeachers = $teachersJson; @endphp
                                @forelse($displayTeachers as $t)
                                    @if($t['schedulable'])
                                        <span>{{ $t['name'] }}</span>{{ !$loop->last ? ',' : '' }}
                                    @else
                                        <span class="line-through text-rose-400" title="{{ __('Cannot schedule this term') }}">{{ $t['name'] }}</span>{{ !$loop->last ? ',' : '' }}
                                    @endif
                                @empty
                                    -
                                @endforelse
                            </div>
                        </div>
                        @endforeach
                    </div>
                </div>
            </div>

            {{-- Grid --}}
            <div class="flex-1 min-w-0">
                <div class="bg-white dark:bg-[#242526] rounded-2xl shadow-sm border border-gray-100 dark:border-[#3a3b3c] p-4 overflow-x-auto">
                    <div id="grid-container">
                        {{-- Rendered by JS --}}
                    </div>
                </div>

                {{-- Conflict panel --}}
                <div id="conflict-panel" class="mt-4 hidden">
                    <div class="bg-white dark:bg-[#242526] rounded-2xl shadow-sm border border-gray-100 dark:border-[#3a3b3c] p-4">
                        <h3 class="text-sm font-bold text-gray-700 dark:text-gray-300 mb-2">{{ __('Conflict Check Results') }}</h3>
                        <div id="conflict-list" class="space-y-2"></div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

{{-- Place Course Modal --}}
<div id="placeModal" style="display:none" class="fixed inset-0 z-[9999] flex items-center justify-center bg-black/50 backdrop-blur-sm" onclick="if(event.target===this)closePlaceModal()">
    <div class="bg-white dark:bg-[#242526] rounded-2xl shadow-2xl w-full max-w-md mx-4 p-6">
        <h3 class="text-lg font-bold text-gray-800 dark:text-white mb-4" id="modal-title">{{ __('Place Course') }}</h3>
        <div class="space-y-4">
            <div>
                <label class="block text-sm font-medium text-gray-600 dark:text-gray-400 mb-1">{{ __('Course') }}</label>
                <div id="modal-course-name" class="px-4 py-2 bg-gray-50 dark:bg-[#3a3b3c] rounded-xl text-sm text-gray-800 dark:text-white font-medium"></div>
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-600 dark:text-gray-400 mb-1">{{ __('Teacher') }}</label>
                <select id="modal-teacher" class="w-full px-4 py-2.5 bg-gray-50 dark:bg-[#3a3b3c] border-2 border-transparent rounded-xl text-sm text-gray-800 dark:text-white focus:border-indigo-500 focus:outline-none">
                </select>
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-600 dark:text-gray-400 mb-1">{{ __('Room / Lab') }}</label>
                <select id="modal-room" class="w-full px-4 py-2.5 bg-gray-50 dark:bg-[#3a3b3c] border-2 border-transparent rounded-xl text-sm text-gray-800 dark:text-white focus:border-indigo-500 focus:outline-none">
                    <option value="">{{ __('Not specified') }}</option>
                </select>
            </div>
            <div id="modal-conflicts" class="hidden p-3 bg-rose-50 dark:bg-rose-900/20 border border-rose-200 dark:border-rose-800 rounded-xl text-sm text-rose-700 dark:text-rose-300"></div>
        </div>
        <div class="flex gap-3 mt-6">
            <button onclick="closePlaceModal()" class="flex-1 py-2.5 bg-gray-100 dark:bg-[#3a3b3c] hover:bg-gray-200 dark:hover:bg-[#4a4b4c] text-gray-700 dark:text-gray-300 rounded-xl text-sm font-semibold transition-colors">{{ __('Cancel') }}</button>
            <button onclick="confirmPlace()" id="btn-confirm" class="flex-1 py-2.5 bg-indigo-600 hover:bg-indigo-700 text-white rounded-xl text-sm font-semibold transition-colors">{{ __('Place Course') }}</button>
        </div>
    </div>
</div>

{{-- Alert Modal --}}
<div id="alertModal" style="display:none" class="fixed inset-0 z-[10000] flex items-center justify-center bg-black/50 backdrop-blur-sm" onclick="if(event.target===this)closeAlertModal()">
    <div class="bg-white dark:bg-[#242526] rounded-2xl shadow-2xl w-full max-w-sm mx-4 p-6 text-center">
        <div id="alert-icon" class="mx-auto w-12 h-12 rounded-full flex items-center justify-center mb-4"></div>
        <h3 id="alert-title" class="text-lg font-bold text-gray-800 dark:text-white mb-2"></h3>
        <p id="alert-message" class="text-sm text-gray-600 dark:text-gray-400 mb-6 whitespace-pre-line"></p>
        <button onclick="closeAlertModal()" class="w-full py-2.5 bg-indigo-600 hover:bg-indigo-700 text-white rounded-xl text-sm font-semibold transition-colors">{{ __('OK') }}</button>
    </div>
</div>

{{-- Confirm Modal --}}
<div id="confirmModal" style="display:none" class="fixed inset-0 z-[10000] flex items-center justify-center bg-black/50 backdrop-blur-sm" onclick="if(event.target===this)closeConfirmModal(false)">
    <div class="bg-white dark:bg-[#242526] rounded-2xl shadow-2xl w-full max-w-sm mx-4 p-6 text-center">
        <div class="mx-auto w-12 h-12 rounded-full bg-amber-100 dark:bg-amber-900/30 flex items-center justify-center mb-4">
            <i class="fas fa-question text-amber-600 dark:text-amber-400 text-xl"></i>
        </div>
        <h3 id="confirm-title" class="text-lg font-bold text-gray-800 dark:text-white mb-2"></h3>
        <p id="confirm-message" class="text-sm text-gray-600 dark:text-gray-400 mb-6 whitespace-pre-line"></p>
        <div class="flex gap-3">
            <button onclick="closeConfirmModal(false)" class="flex-1 py-2.5 bg-gray-100 dark:bg-[#3a3b3c] hover:bg-gray-200 dark:hover:bg-[#4a4b4c] text-gray-700 dark:text-gray-300 rounded-xl text-sm font-semibold transition-colors">{{ __('Cancel') }}</button>
            <button onclick="closeConfirmModal(true)" id="confirm-yes-btn" class="flex-1 py-2.5 bg-rose-600 hover:bg-rose-700 text-white rounded-xl text-sm font-semibold transition-colors">{{ __('Confirm') }}</button>
        </div>
    </div>
</div>

<script>
// ==================== Modal Alert / Confirm ====================
let confirmResolve = null;

const __t = {
    warning: @json(__('Warning')),
    error: @json(__('Cannot proceed')),
    success: @json(__('Success')),
    info: @json(__('Information')),
    period: @json(__('Period')),
    day: @json(__('Day')),
    breakMin: @json(__('Break :min minutes')),
    placed: @json(__('placed')),
    selectCourseFirst: @json(__('Please select a course from the list first')),
    courseFullyScheduled: @json(__('This course is fully scheduled')),
    cannotPlaceOnDay: @json(__('This course cannot be placed on')),
    availableDays: @json(__('Available days')),
    courseAlreadyOnDay: @json(__('already scheduled on')),
    cannotDuplicateDay: @json(__('Cannot place the same course on the same day')),
    needConsecutivePeriods: @json(__('This course requires :pps consecutive periods but not enough periods remain')),
    periodRange: @json(__('Period :start-:end exceeds periods for this day')),
    needConsecutiveButOccupied: @json(__('This course requires :pps consecutive periods (Period :start-:end)')),
    butPeriodOccupied: @json(__('but period :p has course \":name\" already')),
    consecutiveLabel: @json(__(':pps consecutive: Period :start-:end')),
    placeCourseTitle: @json(__('Place Course — :day Period :period')),
    noTeacherAssigned: @json(__('No teacher assigned')),
    noSchedulableTeacher: @json(__('No schedulable teacher for this term')),
    unavailableThisTerm: @json(__('Unavailable this term')),
    courseRooms: @json(__('Rooms assigned to course')),
    otherRooms: @json(__('Other rooms')),
    notSpecified: @json(__('Not specified')),
    hardErrors: @json(__('Errors (cannot place)')),
    softWarnings: @json(__('Warnings (can place but not recommended)')),
    pleaseSelectTeacher: @json(__('Please select a teacher')),
    cannotPlacePeriod: @json(__('Cannot place period :p')),
    cannotMove: @json(__('Cannot move')),
    cannotDeleteLocked: @json(__('Cannot delete because period :periods is locked')),
    confirmDelete: @json(__('Confirm Delete')),
    deleteCourseDayPeriods: @json(__('Delete \":name\" :day Period :periods (:count periods)')),
    deleteCourseDayPeriod: @json(__('Delete \":name\" :day Period :period')),
    noConflictsFound: @json(__('No conflicts found. All correct!')),
    viewAllDetails: @json(__('View all details')),
};

function showAlert(message, type = 'warning') {
    const icons = {
        warning: '<i class="fas fa-exclamation-triangle text-amber-600 dark:text-amber-400 text-xl"></i>',
        error: '<i class="fas fa-times-circle text-rose-600 dark:text-rose-400 text-xl"></i>',
        success: '<i class="fas fa-check-circle text-emerald-600 dark:text-emerald-400 text-xl"></i>',
        info: '<i class="fas fa-info-circle text-indigo-600 dark:text-indigo-400 text-xl"></i>',
    };
    const bgColors = {
        warning: 'bg-amber-100 dark:bg-amber-900/30',
        error: 'bg-rose-100 dark:bg-rose-900/30',
        success: 'bg-emerald-100 dark:bg-emerald-900/30',
        info: 'bg-indigo-100 dark:bg-indigo-900/30',
    };
    const titles = { warning: __t.warning, error: __t.error, success: __t.success, info: __t.info };

    document.getElementById('alert-icon').className = `mx-auto w-12 h-12 rounded-full flex items-center justify-center mb-4 ${bgColors[type] || bgColors.warning}`;
    document.getElementById('alert-icon').innerHTML = icons[type] || icons.warning;
    document.getElementById('alert-title').textContent = titles[type] || __t.warning;
    document.getElementById('alert-message').textContent = message;
    document.getElementById('alertModal').style.display = 'flex';
}

function closeAlertModal() {
    document.getElementById('alertModal').style.display = 'none';
}

function showConfirm(title, message) {
    return new Promise(resolve => {
        confirmResolve = resolve;
        document.getElementById('confirm-title').textContent = title;
        document.getElementById('confirm-message').textContent = message;
        document.getElementById('confirmModal').style.display = 'flex';
    });
}

function closeConfirmModal(result) {
    document.getElementById('confirmModal').style.display = 'none';
    if (confirmResolve) { confirmResolve(result); confirmResolve = null; }
}

// ==================== Main ====================
const solutionId = {{ $solution->id }};
const csrfToken = '{{ csrf_token() }}';
const gradeId = {{ $grade->id }};
const classroomId = {{ $classroom->id }};

const schedule = @json($schedule);
const allRooms = @json($allRoomsJson);
const schedulableTeacherIds = new Set(@json($schedulableTeacherIds));
const dayConfigs = schedule.day_configs || {};
// Only show days that have periods > 0 (exclude holidays/non-teaching days)
const teachingDays = (schedule.teaching_days || []).filter(d => {
    const dc = dayConfigs[String(d)];
    return dc && (dc.periods || 0) > 0;
});
const periodDuration = schedule.period_duration || 50;
const globalStart = schedule.start_time || '08:00';
const dayNames = {1:@json(__('Monday')), 2:@json(__('Tuesday')), 3:@json(__('Wednesday')), 4:@json(__('Thursday')), 5:@json(__('Friday')), 6:@json(__('Saturday')), 7:@json(__('Sunday'))};

const subjectColors = [
    'bg-indigo-100 dark:bg-indigo-900/30 border-indigo-300 dark:border-indigo-700 text-indigo-800 dark:text-indigo-200',
    'bg-emerald-100 dark:bg-emerald-900/30 border-emerald-300 dark:border-emerald-700 text-emerald-800 dark:text-emerald-200',
    'bg-amber-100 dark:bg-amber-900/30 border-amber-300 dark:border-amber-700 text-amber-800 dark:text-amber-200',
    'bg-rose-100 dark:bg-rose-900/30 border-rose-300 dark:border-rose-700 text-rose-800 dark:text-rose-200',
    'bg-purple-100 dark:bg-purple-900/30 border-purple-300 dark:border-purple-700 text-purple-800 dark:text-purple-200',
    'bg-teal-100 dark:bg-teal-900/30 border-teal-300 dark:border-teal-700 text-teal-800 dark:text-teal-200',
    'bg-pink-100 dark:bg-pink-900/30 border-pink-300 dark:border-pink-700 text-pink-800 dark:text-pink-200',
    'bg-cyan-100 dark:bg-cyan-900/30 border-cyan-300 dark:border-cyan-700 text-cyan-800 dark:text-cyan-200',
];

// State
let entries = @json($entriesJson);

let selectedCourse = null;
let pendingDay = null;
let pendingPeriod = null;

// Time helpers
function timeToMin(t) { const p = t.split(':'); return parseInt(p[0])*60 + parseInt(p[1]||0); }
function minToTime(m) { return String(Math.floor(m/60)).padStart(2,'0') + ':' + String(m%60).padStart(2,'0'); }

function calcTimes(dayStr) {
    const dc = dayConfigs[dayStr] || {};
    const st = dc.start_time || globalStart;
    const breaks = dc.breaks || {};
    let min = timeToMin(st);
    const times = [];
    for (let p = 1; p <= (dc.periods || 0); p++) {
        times.push({start: minToTime(min), end: minToTime(min + periodDuration)});
        min += periodDuration;
        if (breaks[String(p)]) min += parseInt(breaks[String(p)]);
    }
    return times;
}

// Select course from sidebar
function selectCourse(el) {
    document.querySelectorAll('.course-item').forEach(e => e.classList.remove('ring-2', 'ring-indigo-500'));
    el.classList.add('ring-2', 'ring-indigo-500');
    selectedCourse = el;
    renderGrid();
}

// Render grid
function renderGrid() {
    let maxPeriods = 0;
    teachingDays.forEach(d => { const dc = dayConfigs[d]; if (dc && dc.periods > maxPeriods) maxPeriods = dc.periods; });

    let html = '<table class="w-full border-collapse" style="min-width:600px">';
    html += `<thead><tr><th class="p-2 text-xs font-semibold text-gray-500 dark:text-gray-400 border border-gray-200 dark:border-[#3a3b3c] bg-gray-50 dark:bg-[#3a3b3c] w-24">${__t.period}</th>`;
    teachingDays.forEach(d => {
        html += `<th class="p-2 text-xs font-semibold text-gray-700 dark:text-gray-300 border border-gray-200 dark:border-[#3a3b3c] bg-gray-50 dark:bg-[#3a3b3c]">${dayNames[parseInt(d)] || __t.day+' '+d}</th>`;
    });
    html += '</tr></thead><tbody>';

    for (let p = 1; p <= maxPeriods; p++) {
        html += '<tr>';
        const times = calcTimes(teachingDays[0]);
        const tl = times[p-1] ? `<br><span class="text-[10px] text-gray-400">${times[p-1].start}-${times[p-1].end}</span>` : '';
        html += `<td class="p-2 text-center text-xs font-medium text-gray-600 dark:text-gray-400 border border-gray-200 dark:border-[#3a3b3c] bg-gray-50 dark:bg-[#3a3b3c]">${__t.period} ${p}${tl}</td>`;

        teachingDays.forEach(d => {
            const dayInt = parseInt(d);
            const dc = dayConfigs[d];
            if (!dc || p > dc.periods) {
                html += '<td class="border border-gray-200 dark:border-[#3a3b3c] bg-gray-100 dark:bg-[#1a1b1c]"></td>';
                return;
            }

            const entry = entries.find(e => e.day === dayInt && e.period === p);
            if (entry) {
                const ci = (entry.subject_group_id || 0) % subjectColors.length;
                const color = subjectColors[ci];
                const lockIcon = entry.is_locked
                    ? '<i class="fas fa-lock text-amber-500 text-[9px]"></i>'
                    : '<i class="fas fa-lock-open text-gray-300 dark:text-gray-600 text-[9px] cursor-pointer hover:text-amber-500" onclick="event.stopPropagation();toggleLock('+entry.id+')"></i>';

                html += `<td class="border border-gray-200 dark:border-[#3a3b3c] p-1" data-day="${dayInt}" data-period="${p}"
                             draggable="true" ondragstart="dragStart(event, ${entry.id})"
                             oncontextmenu="event.preventDefault();removeEntry(${entry.id})">
                    <div class="p-2 rounded-xl border ${color} text-xs space-y-0.5 cursor-move" id="entry-${entry.id}">
                        <div class="flex items-center justify-between">
                            <span class="font-bold truncate">${entry.course_name}</span>
                            ${lockIcon}
                        </div>
                        <div class="text-[10px] opacity-75 truncate">${entry.teacher_name || '-'}</div>
                        <div class="text-[10px] opacity-60 truncate">${entry.room_number || '-'}</div>
                    </div>
                </td>`;
            } else {
                let cellBg = 'hover:bg-indigo-50 dark:hover:bg-indigo-900/10';
                let hint = '<span class="text-gray-200 dark:text-gray-700 text-lg">+</span>';
                let clickable = true;

                if (selectedCourse) {
                    const ocIdSel = parseInt(selectedCourse.dataset.ocId);
                    const prefDays = JSON.parse(selectedCourse.dataset.preferredDays || '[]').map(Number);
                    const pps = parseInt(selectedCourse.dataset.periodsPerSession) || 1;

                    if (prefDays.length > 0 && !prefDays.includes(dayInt)) {
                        cellBg = 'bg-gray-100 dark:bg-[#1e1f20] opacity-50';
                        hint = '<span class="text-gray-300 dark:text-gray-600 text-[10px]"><i class="fas fa-ban text-[8px]"></i></span>';
                        clickable = false;
                    }
                    else {
                        const sameCourseOnDay = entries.filter(e => e.opened_course_id === ocIdSel && e.day === dayInt);
                        if (sameCourseOnDay.length >= pps) {
                            cellBg = 'bg-gray-100 dark:bg-[#1e1f20] opacity-40';
                            hint = `<span class="text-gray-300 dark:text-gray-600 text-[10px]">${__t.placed}</span>`;
                            clickable = false;
                        } else {
                            cellBg = 'bg-emerald-50 dark:bg-emerald-900/10 hover:bg-emerald-100 dark:hover:bg-emerald-900/20 ring-1 ring-inset ring-emerald-200 dark:ring-emerald-800';
                            hint = '<span class="text-emerald-400 dark:text-emerald-600 text-lg">+</span>';
                        }
                    }
                }

                if (clickable) {
                    html += `<td class="border border-gray-200 dark:border-[#3a3b3c] p-1 cursor-pointer ${cellBg} transition-colors"
                                 data-day="${dayInt}" data-period="${p}"
                                 onclick="cellClick(${dayInt}, ${p})"
                                 ondragover="event.preventDefault()" ondrop="drop(event, ${dayInt}, ${p})">
                        <div class="h-14 flex items-center justify-center">${hint}</div>
                    </td>`;
                } else {
                    html += `<td class="border border-gray-200 dark:border-[#3a3b3c] p-1 ${cellBg}" data-day="${dayInt}" data-period="${p}">
                        <div class="h-14 flex items-center justify-center">${hint}</div>
                    </td>`;
                }
            }
        });
        html += '</tr>';

        // Breaks
        const breaks = dayConfigs[teachingDays[0]]?.breaks || {};
        if (breaks[String(p)]) {
            html += `<tr><td colspan="${teachingDays.length + 1}" class="py-1 text-center text-[10px] text-amber-600 dark:text-amber-400 bg-amber-50 dark:bg-amber-900/10 border border-gray-200 dark:border-[#3a3b3c]">${__t.breakMin.replace(':min', breaks[String(p)])}</td></tr>`;
        }
    }

    html += '</tbody></table>';
    document.getElementById('grid-container').innerHTML = html;

    updateSidebarCounts();
}

function updateSidebarCounts() {
    document.querySelectorAll('.course-item').forEach(el => {
        const ocId = parseInt(el.dataset.ocId);
        const needed = parseInt(el.dataset.periodsNeeded);
        const placed = entries.filter(e => e.opened_course_id === ocId).length;
        const remaining = Math.max(0, needed - placed);
        const span = document.getElementById('remaining-' + ocId);
        if (span) {
            span.textContent = `${placed}/${needed}`;
            span.className = remaining === 0
                ? 'text-[10px] font-bold text-emerald-600 whitespace-nowrap ml-2'
                : 'text-[10px] font-bold text-indigo-600 whitespace-nowrap ml-2';
        }
        el.classList.toggle('opacity-60', remaining === 0);
    });
}

// Cell click: open modal to place course
function cellClick(day, period) {
    if (!selectedCourse) {
        showAlert(__t.selectCourseFirst, 'info');
        return;
    }

    const ocId = parseInt(selectedCourse.dataset.ocId);
    const needed = parseInt(selectedCourse.dataset.periodsNeeded);
    const placed = entries.filter(e => e.opened_course_id === ocId).length;
    if (placed >= needed) {
        showAlert(__t.courseFullyScheduled, 'warning');
        return;
    }

    const prefDays = JSON.parse(selectedCourse.dataset.preferredDays || '[]').map(Number);
    if (prefDays.length > 0 && !prefDays.includes(day)) {
        showAlert(__t.cannotPlaceOnDay + dayNames[day] + '\n' + __t.availableDays + ': ' + prefDays.map(d => dayNames[d]).join(', '), 'error');
        return;
    }

    const alreadyOnDay = entries.some(e => e.opened_course_id === ocId && e.day === day);
    if (alreadyOnDay) {
        showAlert(selectedCourse.dataset.courseName + ' ' + __t.courseAlreadyOnDay + dayNames[day] + '\n' + __t.cannotDuplicateDay, 'error');
        return;
    }

    const pps = parseInt(selectedCourse.dataset.periodsPerSession) || 1;
    if (pps > 1) {
        const dc = dayConfigs[String(day)];
        const maxP = dc ? dc.periods : 0;
        for (let i = 0; i < pps; i++) {
            const checkP = period + i;
            if (checkP > maxP) {
                showAlert(__t.needConsecutivePeriods.replace(':pps', pps) + '\n' + __t.periodRange.replace(':start', period).replace(':end', period+pps-1), 'error');
                return;
            }
            const occupied = entries.find(e => e.day === day && e.period === checkP);
            if (occupied) {
                showAlert(__t.needConsecutiveButOccupied.replace(':pps', pps).replace(':start', period).replace(':end', period+pps-1) + '\n' + __t.butPeriodOccupied.replace(':p', checkP).replace(':name', occupied.course_name), 'error');
                return;
            }
        }
    }

    pendingDay = day;
    pendingPeriod = period;

    const sessionLabel = pps > 1 ? ` (${__t.consecutiveLabel.replace(':pps', pps).replace(':start', period).replace(':end', period+pps-1)})` : '';
    document.getElementById('modal-course-name').textContent = selectedCourse.dataset.courseName + sessionLabel;
    document.getElementById('modal-title').textContent = __t.placeCourseTitle.replace(':day', dayNames[day]).replace(':period', pps > 1 ? period+'-'+(period+pps-1) : period);

    // Populate teachers (only schedulable for this term)
    const allCourseTeachers = JSON.parse(selectedCourse.dataset.teachers);
    const teachers = allCourseTeachers.filter(t => schedulableTeacherIds.has(t.id));
    const unavailableTeachers = allCourseTeachers.filter(t => !schedulableTeacherIds.has(t.id));
    const tSelect = document.getElementById('modal-teacher');
    let teacherHtml = '';
    if (teachers.length === 0 && unavailableTeachers.length === 0) {
        teacherHtml = `<option value="">${__t.noTeacherAssigned}</option>`;
    } else if (teachers.length === 0) {
        teacherHtml = `<option value="">${__t.noSchedulableTeacher}</option>`;
    } else {
        teacherHtml = teachers.map(t => `<option value="${t.id}">${t.name}</option>`).join('');
    }
    if (unavailableTeachers.length > 0) {
        teacherHtml += `<optgroup label="${__t.unavailableThisTerm}">`;
        teacherHtml += unavailableTeachers.map(t => `<option value="${t.id}" disabled class="text-gray-400">${t.name}</option>`).join('');
        teacherHtml += '</optgroup>';
    }
    tSelect.innerHTML = teacherHtml;

    // Populate rooms
    const courseRooms = JSON.parse(selectedCourse.dataset.rooms);
    const courseRoomIds = courseRooms.map(r => r.id);
    const otherRooms = allRooms.filter(r => !courseRoomIds.includes(r.id));
    const rSelect = document.getElementById('modal-room');
    let roomHtml = `<option value="">${__t.notSpecified}</option>`;
    if (courseRooms.length > 0) {
        roomHtml += `<optgroup label="${__t.courseRooms}">`;
        roomHtml += courseRooms.map(r => `<option value="${r.id}">${r.label}</option>`).join('');
        roomHtml += '</optgroup>';
    }
    if (otherRooms.length > 0) {
        roomHtml += `<optgroup label="${__t.otherRooms}">`;
        roomHtml += otherRooms.map(r => `<option value="${r.id}">${r.label}</option>`).join('');
        roomHtml += '</optgroup>';
    }
    rSelect.innerHTML = roomHtml;

    document.getElementById('modal-conflicts').classList.add('hidden');
    document.getElementById('placeModal').style.display = 'flex';

    autoCheckConflict();
}

function closePlaceModal() {
    document.getElementById('placeModal').style.display = 'none';
}

function autoCheckConflict() {
    const teacherId = document.getElementById('modal-teacher').value;
    const roomId = document.getElementById('modal-room').value;
    const ocId = parseInt(selectedCourse.dataset.ocId);

    if (!teacherId) return;

    fetch(`/admin/timetable/api/check-conflicts?solution_id=${solutionId}&opened_course_id=${ocId}&teacher_id=${teacherId}&room_id=${roomId || 0}&day=${pendingDay}&period=${pendingPeriod}`)
        .then(r => r.json())
        .then(data => {
            const panel = document.getElementById('modal-conflicts');
            const violations = data.violations || [];
            const hardViolations = violations.filter(v => v.severity === 'hard');
            const softViolations = violations.filter(v => v.severity === 'soft');

            if (violations.length > 0) {
                let html = '';
                if (hardViolations.length > 0) {
                    html += `<div class="font-bold mb-1 text-rose-700 dark:text-rose-300"><i class="fas fa-times-circle mr-1"></i>${__t.hardErrors}:</div>`;
                    html += hardViolations.map(v => `<div class="text-xs mt-1 text-rose-600 dark:text-rose-400">- ${v.message}</div>`).join('');
                }
                if (softViolations.length > 0) {
                    html += `<div class="font-bold mb-1 mt-2 text-amber-700 dark:text-amber-300"><i class="fas fa-exclamation-triangle mr-1"></i>${__t.softWarnings}:</div>`;
                    html += softViolations.map(v => `<div class="text-xs mt-1 text-amber-600 dark:text-amber-400">- ${v.message}</div>`).join('');
                }
                panel.innerHTML = html;
                panel.className = hardViolations.length > 0
                    ? 'p-3 bg-rose-50 dark:bg-rose-900/20 border border-rose-200 dark:border-rose-800 rounded-xl text-sm'
                    : 'p-3 bg-amber-50 dark:bg-amber-900/20 border border-amber-200 dark:border-amber-800 rounded-xl text-sm';
                panel.classList.remove('hidden');
            } else {
                panel.classList.add('hidden');
            }
        });
}

// Listen for teacher/room change to re-check
document.getElementById('modal-teacher').addEventListener('change', autoCheckConflict);
document.getElementById('modal-room').addEventListener('change', autoCheckConflict);

async function confirmPlace() {
    const teacherId = document.getElementById('modal-teacher').value;
    const roomId = document.getElementById('modal-room').value;
    const ocId = parseInt(selectedCourse.dataset.ocId);
    const pps = parseInt(selectedCourse.dataset.periodsPerSession) || 1;

    if (!teacherId) {
        showAlert(__t.pleaseSelectTeacher, 'warning');
        return;
    }

    const periodsToPlace = [];
    for (let i = 0; i < pps; i++) {
        periodsToPlace.push(pendingPeriod + i);
    }

    let allSuccess = true;
    for (const p of periodsToPlace) {
        const res = await fetch('/admin/timetable/api/entries', {
            method: 'POST',
            headers: {'X-CSRF-TOKEN': csrfToken, 'Content-Type': 'application/json'},
            body: JSON.stringify({
                solution_id: solutionId,
                opened_course_id: ocId,
                teacher_id: parseInt(teacherId),
                room_id: roomId ? parseInt(roomId) : null,
                day: pendingDay,
                period: p,
            }),
        });
        const data = await res.json();

        if (data.success) {
            entries.push({
                id: data.entry_id,
                opened_course_id: ocId,
                course_name: selectedCourse.dataset.courseName,
                subject_group_id: parseInt(selectedCourse.dataset.subjectGroupId),
                teacher_name: document.getElementById('modal-teacher').selectedOptions[0]?.text || '',
                teacher_id: parseInt(teacherId),
                room_number: document.getElementById('modal-room').selectedOptions[0]?.text || '',
                room_id: roomId ? parseInt(roomId) : null,
                day: pendingDay,
                period: p,
                is_locked: false,
            });
        } else {
            const msgs = (data.violations || []).map(v => v.message).join('\n');
            showAlert(__t.cannotPlacePeriod.replace(':p', p) + '\n' + msgs, 'error');
            allSuccess = false;
            break;
        }
    }

    closePlaceModal();
    renderGrid();
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

    fetch(`/admin/timetable/api/entries/${dragEntryId}/move`, {
        method: 'POST',
        headers: {'X-CSRF-TOKEN': csrfToken, 'Content-Type': 'application/json'},
        body: JSON.stringify({day, period}),
    }).then(r => r.json()).then(data => {
        if (data.success) {
            const entry = entries.find(e => e.id === dragEntryId);
            if (entry) { entry.day = day; entry.period = period; }
            renderGrid();
        } else {
            const msgs = (data.violations || []).map(v => v.message).join('\n');
            showAlert(__t.cannotMove + '\n' + msgs, 'error');
        }
    });
    dragEntryId = null;
}

// Toggle lock
function toggleLock(entryId) {
    fetch(`/admin/timetable/api/entries/${entryId}/lock`, {
        method: 'POST',
        headers: {'X-CSRF-TOKEN': csrfToken, 'Content-Type': 'application/json'},
    }).then(r => r.json()).then(data => {
        if (data.success) {
            const entry = entries.find(e => e.id === entryId);
            if (entry) entry.is_locked = data.is_locked;
            renderGrid();
        }
    });
}

// Remove entry (right-click)
async function removeEntry(entryId) {
    const entry = entries.find(e => e.id === entryId);
    if (!entry) return;

    const sessionEntries = entries.filter(e =>
        e.opened_course_id === entry.opened_course_id && e.day === entry.day
    ).sort((a, b) => a.period - b.period);

    const lockedInSession = sessionEntries.filter(e => e.is_locked);
    if (lockedInSession.length > 0) {
        const lockedPeriods = lockedInSession.map(e => e.period).join(', ');
        showAlert(__t.cannotDeleteLocked.replace(':periods', lockedPeriods), 'warning');
        return;
    }

    const periodList = sessionEntries.map(e => e.period).join(', ');
    const msg = sessionEntries.length > 1
        ? __t.deleteCourseDayPeriods.replace(':name', entry.course_name).replace(':day', dayNames[entry.day]).replace(':periods', periodList).replace(':count', sessionEntries.length)
        : __t.deleteCourseDayPeriod.replace(':name', entry.course_name).replace(':day', dayNames[entry.day]).replace(':period', entry.period);

    const ok = await showConfirm(__t.confirmDelete, msg);
    if (!ok) return;

    for (const se of sessionEntries) {
        const res = await fetch(`/admin/timetable/api/entries/${se.id}`, {
            method: 'DELETE',
            headers: {'X-CSRF-TOKEN': csrfToken},
        });
        const data = await res.json();
        if (data.success) {
            entries = entries.filter(e => e.id !== se.id);
        }
    }
    renderGrid();
}

// Check all conflicts
function checkAllConflicts() {
    fetch(`/admin/timetable/api/solutions/${solutionId}/fitness`)
        .then(r => r.json())
        .then(data => {
            const panel = document.getElementById('conflict-panel');
            const list = document.getElementById('conflict-list');
            panel.classList.remove('hidden');

            if (data.total_conflicts === 0) {
                list.innerHTML = `<div class="text-emerald-600 dark:text-emerald-400 text-center py-4 font-bold"><i class="fas fa-check-circle mr-1"></i> ${__t.noConflictsFound}</div>`;
            } else {
                list.innerHTML = `<div class="text-sm text-gray-700 dark:text-gray-300 mb-2">
                    <span class="font-bold text-rose-600">${data.hard_violations}</span> Hard |
                    <span class="font-bold text-amber-600">${data.soft_violations}</span> Soft
                </div>
                <a href="/admin/timetable/conflicts/${solutionId}" class="text-indigo-600 dark:text-indigo-400 text-sm hover:underline">${__t.viewAllDetails}</a>`;
            }
        });
}

// Init
renderGrid();
</script>
@endsection
