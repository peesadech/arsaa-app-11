@extends('layouts.app')

@section('content')
<div class="min-h-screen bg-gray-50 dark:bg-[#18191a] py-10 px-4 sm:px-6 lg:px-8 transition-colors duration-300">
    <div class="max-w-4xl mx-auto">

        {{-- Header --}}
        <div class="flex items-center space-x-4 mb-8">
            <a href="{{ route('admin.teacher-term-status.index') }}"
               class="group flex items-center justify-center w-10 h-10 rounded-full bg-white dark:bg-[#242526] shadow-sm border border-gray-200 dark:border-[#3a3b3c] text-gray-500 dark:text-gray-400 hover:text-indigo-600 dark:hover:text-indigo-400 hover:border-indigo-300 transition-all duration-200">
                <i class="fas fa-arrow-left group-hover:-translate-x-0.5 transition-transform"></i>
            </a>
            <div>
                <h1 class="text-2xl font-extrabold text-gray-900 dark:text-white tracking-tight">{{ __('Teacher Term Status') }}</h1>
                <p class="text-sm text-gray-500 dark:text-gray-400 font-medium px-1">
                    {{ $teacher->name }} — {{ __('Academic Year') }} {{ $academicYear->year ?? '?' }} / {{ __('Semester') }} {{ $semester->semester_number ?? '?' }}
                </p>
            </div>
        </div>

        {{-- Flash --}}
        @if(session('status'))
        <div class="mb-6 p-4 bg-emerald-50 dark:bg-emerald-900/20 border border-emerald-200 dark:border-emerald-800 rounded-2xl text-emerald-700 dark:text-emerald-300 text-sm">
            {{ session('status') }}
        </div>
        @endif

        {{-- Teacher Info --}}
        <div class="bg-white dark:bg-[#242526] rounded-[2rem] shadow-sm border border-gray-100 dark:border-[#3a3b3c] p-6 mb-6">
            <div class="flex items-center gap-4">
                <div class="w-14 h-14 rounded-2xl overflow-hidden border-2 border-white shadow-sm">
                    <img src="{{ $teacher->image_path ? asset($teacher->image_path) : 'https://ui-avatars.com/api/?name=' . urlencode($teacher->name) . '&color=7F9CF5&background=EBF4FF' }}" class="w-full h-full object-cover" alt="">
                </div>
                <div>
                    <h3 class="text-lg font-bold text-gray-800 dark:text-white">{{ $teacher->name }}</h3>
                    <p class="text-xs text-gray-400">{{ $teacher->email }}</p>
                    @php $masterColor = $teacher->status == 1 ? 'bg-emerald-50 text-emerald-600' : 'bg-rose-50 text-rose-600'; @endphp
                    <span class="inline-flex mt-1 px-2 py-0.5 rounded-lg {{ $masterColor }} text-[10px] font-bold uppercase">
                        {{ __('Master') }}: {{ $teacher->status == 1 ? __('Active') : __('Not Active') }}
                    </span>
                </div>
            </div>
        </div>

        {{-- Form --}}
        <form id="termForm" action="{{ route('admin.teacher-term-status.update', $teacher->id) }}" method="POST">
            @csrf
            @method('PUT')
            <input type="hidden" name="unavailable_periods" id="unavailablePeriodsInput" value="">
            <div class="bg-white dark:bg-[#242526] rounded-[2rem] shadow-sm border border-gray-100 dark:border-[#3a3b3c] p-6 space-y-5">

                {{-- Status --}}
                <div>
                    <label class="block text-sm font-medium text-gray-600 dark:text-gray-400 mb-1">{{ __('Term Status') }}</label>
                    <select name="status" id="termStatusSelect"
                            class="w-full px-4 py-3 bg-gray-50 dark:bg-[#3a3b3c] border-2 border-transparent rounded-2xl text-gray-800 dark:text-white focus:border-indigo-500 focus:outline-none transition-all">
                        @foreach(\App\Models\TeacherTermStatus::STATUSES as $s)
                        <option value="{{ $s }}" {{ $termStatus->status === $s ? 'selected' : '' }}>
                            {{ __(ucfirst(str_replace('_', ' ', $s))) }}
                        </option>
                        @endforeach
                    </select>
                </div>

                {{-- Can Be Scheduled --}}
                <div>
                    <label class="flex items-center gap-3 cursor-pointer">
                        <input type="hidden" name="can_be_scheduled" value="0">
                        <input type="checkbox" name="can_be_scheduled" value="1" id="canBeScheduled"
                               class="w-5 h-5 rounded border-gray-300 text-indigo-600 focus:ring-indigo-500"
                               {{ $termStatus->can_be_scheduled ? 'checked' : '' }}>
                        <span class="text-sm font-medium text-gray-700 dark:text-gray-300">{{ __('Can be scheduled') }}</span>
                    </label>
                </div>

                {{-- Max Periods --}}
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-600 dark:text-gray-400 mb-1">{{ __('Max periods per day') }}</label>
                        <input type="number" name="max_periods_per_day" value="{{ $termStatus->max_periods_per_day }}" min="1" max="20" placeholder="-"
                               class="w-full px-4 py-3 bg-gray-50 dark:bg-[#3a3b3c] border-2 border-transparent rounded-2xl text-gray-800 dark:text-white focus:border-indigo-500 focus:outline-none transition-all">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-600 dark:text-gray-400 mb-1">{{ __('Max periods per week') }}</label>
                        <input type="number" name="max_periods_per_week" value="{{ $termStatus->max_periods_per_week }}" min="1" max="100" placeholder="-"
                               class="w-full px-4 py-3 bg-gray-50 dark:bg-[#3a3b3c] border-2 border-transparent rounded-2xl text-gray-800 dark:text-white focus:border-indigo-500 focus:outline-none transition-all">
                    </div>
                </div>

                {{-- Effective Dates --}}
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-600 dark:text-gray-400 mb-1">{{ __('Effective from') }}</label>
                        <input type="date" name="effective_from" value="{{ $termStatus->effective_from?->format('Y-m-d') }}"
                               class="w-full px-4 py-3 bg-gray-50 dark:bg-[#3a3b3c] border-2 border-transparent rounded-2xl text-gray-800 dark:text-white focus:border-indigo-500 focus:outline-none transition-all">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-600 dark:text-gray-400 mb-1">{{ __('Effective until') }}</label>
                        <input type="date" name="effective_until" value="{{ $termStatus->effective_until?->format('Y-m-d') }}"
                               class="w-full px-4 py-3 bg-gray-50 dark:bg-[#3a3b3c] border-2 border-transparent rounded-2xl text-gray-800 dark:text-white focus:border-indigo-500 focus:outline-none transition-all">
                    </div>
                </div>

                {{-- Assigned Courses --}}
                <div class="space-y-3">
                    <label class="block text-xs font-bold text-gray-400 dark:text-gray-500 uppercase tracking-widest px-1">
                        {{ __('Assigned Courses') }}
                        @if(!$hasTermCourses)
                        <span class="text-[10px] text-amber-500 font-normal normal-case ml-1">({{ __('Using global courses - save to set term-specific') }})</span>
                        @endif
                    </label>
                    <div id="selectedCoursesContainer" class="flex flex-wrap gap-2 min-h-[40px] p-3 bg-gray-50 dark:bg-[#3a3b3c] border-2 border-transparent rounded-2xl transition-all">
                    </div>
                    <button type="button" onclick="openCourseModal()"
                        class="inline-flex items-center px-4 py-2.5 bg-white dark:bg-[#242526] border-2 border-gray-100 dark:border-[#3a3b3c] rounded-xl text-sm font-bold text-gray-600 dark:text-gray-400 hover:border-indigo-500 hover:text-indigo-600 transition-all duration-200">
                        <i class="fas fa-plus-circle mr-2 text-xs"></i>
                        {{ __('Select Courses') }}
                    </button>
                </div>

                {{-- Unavailable Periods Schedule --}}
                <div id="scheduleSection" class="space-y-3" style="display:none">
                    <label class="block text-xs font-bold text-gray-400 dark:text-gray-500 uppercase tracking-widest px-1">
                        <i class="fas fa-calendar-times mr-1 text-rose-400"></i> {{ __('Unavailable Teaching Periods') }}
                    </label>
                    <p class="text-[10px] text-gray-400 dark:text-gray-500 font-medium px-1">{{ __('Click to select periods you do not want to teach (shown by Education Level of selected courses)') }}</p>
                    <div id="scheduleGridContainer"></div>
                </div>

                {{-- Notes --}}
                <div>
                    <label class="block text-sm font-medium text-gray-600 dark:text-gray-400 mb-1">{{ __('Notes') }}</label>
                    <textarea name="notes" rows="2"
                              class="w-full px-4 py-3 bg-gray-50 dark:bg-[#3a3b3c] border-2 border-transparent rounded-2xl text-gray-800 dark:text-white focus:border-indigo-500 focus:outline-none transition-all resize-none">{{ $termStatus->notes }}</textarea>
                </div>

                {{-- Reason for change --}}
                <div>
                    <label class="block text-sm font-medium text-gray-600 dark:text-gray-400 mb-1">{{ __('Reason for change') }}</label>
                    <input type="text" name="reason" placeholder="{{ __('Optional: reason for this change') }}"
                           class="w-full px-4 py-3 bg-gray-50 dark:bg-[#3a3b3c] border-2 border-transparent rounded-2xl text-gray-800 dark:text-white focus:border-indigo-500 focus:outline-none transition-all">
                </div>

                {{-- Submit --}}
                <div class="flex justify-end pt-2">
                    <button type="button" onclick="handleSubmit()" class="btn-app px-8">
                        <i class="fas fa-save text-[10px]"></i> {{ __('Save Changes') }}
                    </button>
                </div>
            </div>
        </form>

        {{-- Status Change History --}}
        @if($logs->isNotEmpty())
        <div class="bg-white dark:bg-[#242526] rounded-[2rem] shadow-sm border border-gray-100 dark:border-[#3a3b3c] p-6 mt-6">
            <h3 class="text-sm font-bold text-gray-700 dark:text-gray-300 uppercase tracking-wide mb-4">{{ __('Change History') }}</h3>
            <div class="space-y-3">
                @foreach($logs as $log)
                <div class="flex items-start gap-3 p-3 bg-gray-50 dark:bg-[#3a3b3c]/50 rounded-xl">
                    <div class="w-8 h-8 rounded-full bg-indigo-50 dark:bg-indigo-900/30 flex items-center justify-center shrink-0 mt-0.5">
                        <i class="fas fa-history text-indigo-500 text-xs"></i>
                    </div>
                    <div class="flex-1 min-w-0">
                        <div class="text-sm text-gray-700 dark:text-gray-300">
                            <span class="font-bold">{{ $log->old_status ?? '-' }}</span>
                            <i class="fas fa-arrow-right text-[8px] mx-1 text-gray-400"></i>
                            <span class="font-bold">{{ $log->new_status }}</span>
                            @if($log->old_can_be_scheduled !== $log->new_can_be_scheduled)
                                <span class="text-xs text-gray-400 ml-2">({{ __('Can Schedule') }}: {{ $log->new_can_be_scheduled ? __('Yes') : __('No') }})</span>
                            @endif
                        </div>
                        @if($log->reason)
                        <p class="text-xs text-gray-500 dark:text-gray-400 mt-0.5">{{ $log->reason }}</p>
                        @endif
                        <p class="text-[10px] text-gray-400 mt-1">
                            {{ $log->changedByUser->name ?? '-' }} — {{ $log->changed_at->format('d/m/Y H:i') }}
                        </p>
                    </div>
                </div>
                @endforeach
            </div>
        </div>
        @endif
    </div>
</div>

{{-- Course Selection Modal --}}
<div id="courseModal" class="fixed inset-0 z-50 overflow-y-auto hidden" aria-modal="true">
    <div class="flex items-center justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:p-0">
        <div class="fixed inset-0 bg-gray-500/75 dark:bg-black/75 backdrop-blur-sm transition-opacity" onclick="closeCourseModal()"></div>
        <div class="inline-block align-bottom bg-white dark:bg-[#242526] rounded-[2.5rem] text-left overflow-hidden shadow-2xl transform transition-all sm:my-8 sm:align-middle sm:max-w-2xl sm:w-full border border-gray-100 dark:border-[#3a3b3c]">
            <div class="px-6 pt-6 pb-4 border-b border-gray-100 dark:border-[#3a3b3c]">
                <div class="flex items-center justify-between mb-4">
                    <h3 class="text-lg font-extrabold text-gray-900 dark:text-white tracking-tight">
                        <i class="fas fa-book mr-2 text-indigo-500"></i>{{ __('Select Courses') }}
                    </h3>
                    <button onclick="closeCourseModal()" class="w-8 h-8 rounded-lg bg-gray-100 dark:bg-[#3a3b3c] flex items-center justify-center text-gray-400 hover:text-gray-600 transition-colors">
                        <i class="fas fa-times text-sm"></i>
                    </button>
                </div>
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                    <div class="relative">
                        <select id="modalEducationLevelFilter" class="appearance-none block w-full pl-4 pr-10 py-2.5 bg-gray-50 dark:bg-[#3a3b3c] border-2 border-gray-100 dark:border-[#4a4b4c] rounded-xl text-xs font-bold text-gray-600 dark:text-gray-400 focus:outline-none focus:border-indigo-500 transition-all cursor-pointer">
                            <option value="">{{ __('All Education Levels') }}</option>
                            @foreach($educationLevels as $el)
                                <option value="{{ $el->id }}">{{ $el->name_th }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="relative">
                        <select id="modalSubjectGroupFilter" class="appearance-none block w-full pl-4 pr-10 py-2.5 bg-gray-50 dark:bg-[#3a3b3c] border-2 border-gray-100 dark:border-[#4a4b4c] rounded-xl text-xs font-bold text-gray-600 dark:text-gray-400 focus:outline-none focus:border-indigo-500 transition-all cursor-pointer">
                            <option value="">{{ __('All Subject Groups') }}</option>
                            @foreach($subjectGroups as $sg)
                                <option value="{{ $sg->id }}">{{ $sg->name_th }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="relative">
                        <select id="modalSemesterFilter" class="appearance-none block w-full pl-4 pr-10 py-2.5 bg-gray-50 dark:bg-[#3a3b3c] border-2 border-gray-100 dark:border-[#4a4b4c] rounded-xl text-xs font-bold text-gray-600 dark:text-gray-400 focus:outline-none focus:border-indigo-500 transition-all cursor-pointer">
                            <option value="">{{ __('All Semesters') }}</option>
                            @foreach($semesters as $sem)
                                <option value="{{ $sem->id }}">{{ __('Semester') }} {{ $sem->semester_number }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="relative">
                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-gray-400">
                            <i class="fas fa-search text-xs"></i>
                        </div>
                        <input type="text" id="modalSearchInput" placeholder="{{ __('Search course name...') }}"
                            class="block w-full pl-9 pr-4 py-2.5 bg-gray-50 dark:bg-[#3a3b3c] border-2 border-gray-100 dark:border-[#4a4b4c] rounded-xl text-xs font-bold text-gray-600 dark:text-gray-400 placeholder-gray-400 focus:outline-none focus:border-indigo-500 transition-all">
                    </div>
                </div>
            </div>
            <div class="px-6 py-4 max-h-[400px] overflow-y-auto" id="courseListContainer">
                <div class="text-center py-8 text-gray-400">
                    <i class="fas fa-spinner fa-spin text-2xl mb-2"></i>
                    <p class="text-xs font-bold">{{ __('Loading courses...') }}</p>
                </div>
            </div>
            <div class="px-6 py-4 border-t border-gray-100 dark:border-[#3a3b3c] bg-gray-50/50 dark:bg-[#18191a]/30 flex items-center justify-between">
                <span class="text-xs font-bold text-gray-400"><span id="selectedCount">0</span> {{ __('courses selected') }}</span>
                <button onclick="closeCourseModal()" class="px-6 py-2.5 bg-indigo-600 text-white font-bold rounded-xl text-xs hover:bg-indigo-700 transition-all active:scale-95 uppercase tracking-wider">
                    {{ __('Done') }}
                </button>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
const LANG_NO_COURSES_SELECTED = @json(__('No courses selected. Click "Select Courses" to add.'));
const LANG_LOADING = @json(__('Loading...'));
const LANG_NO_COURSES_FOUND = @json(__('No courses found'));
const LANG_LOADING_SCHEDULE = @json(__('Loading schedule...'));
const LANG_SCHEDULE_NOT_CONFIGURED = @json(__('Schedule not yet configured'));
const LANG_CLICK_TO_DESELECT = @json(__('Click to deselect'));
const LANG_CLICK_TO_MARK_UNAVAILABLE = @json(__('Click to mark as unavailable'));
const LANG_NOT_TEACHING = @json(__('Not teaching'));
const LANG_PERIOD = @json(__('Period'));
const LANG_BREAK = @json(__('Break'));
const LANG_MINUTES_SHORT = @json(__('min.'));
const LANG_PERIODS_UNIT = @json(__('periods'));
const LANG_CLICK_UNAVAILABLE_PERIODS = @json(__('Click periods you do not want to teach'));

const DAY_META = {
    1: {th: @json(__('Monday')), en: 'Mon'},
    2: {th: @json(__('Tuesday')), en: 'Tue'},
    3: {th: @json(__('Wednesday')), en: 'Wed'},
    4: {th: @json(__('Thursday')), en: 'Thu'},
    5: {th: @json(__('Friday')), en: 'Fri'},
    6: {th: @json(__('Saturday')), en: 'Sat'},
    7: {th: @json(__('Sunday')), en: 'Sun'},
};

// ---- State ----
let selectedCourses = {};
let unavailablePeriods = {};
let scheduleConfigs = {};

// Load existing term courses
@foreach($selectedCourses as $course)
selectedCourses[{{ $course->id }}] = {
    id: {{ $course->id }},
    name: @json($course->name),
    grade: @json($course->grade ? ($course->grade->name_th . ' / ' . $course->grade->name_en) : '-'),
    semester: @json($course->semester ? $course->semester->semester_number : '-'),
    subject_group: @json($course->subjectGroup ? $course->subjectGroup->name_th : '-'),
    education_level_id: @json($course->grade?->education_level_id),
    education_level_name: @json($course->grade?->educationLevel?->name_th)
};
@endforeach

// Load term unavailable periods
unavailablePeriods = @json($termUnavailable ?? []);
if (Array.isArray(unavailablePeriods)) {
    // Convert legacy array format to object
    const obj = {};
    unavailablePeriods.forEach(function(entry) {
        const elId = String(entry.education_level_id || '');
        const day = String(entry.day || '');
        if (!elId || !day) return;
        if (!obj[elId]) obj[elId] = {};
        if (!obj[elId][day]) obj[elId][day] = [];
        for (let p = (entry.start_period || 1); p <= (entry.end_period || 1); p++) {
            if (obj[elId][day].indexOf(p) === -1) obj[elId][day].push(p);
        }
    });
    unavailablePeriods = obj;
}

// ---- Status auto-toggle ----
document.getElementById('termStatusSelect').addEventListener('change', function() {
    const schedulableStatuses = ['available', 'partial'];
    document.getElementById('canBeScheduled').checked = schedulableStatuses.includes(this.value);
});

// ---- Submit ----
function handleSubmit() {
    document.getElementById('unavailablePeriodsInput').value = JSON.stringify(unavailablePeriods);
    document.getElementById('termForm').submit();
}

// ---- Course selection (same as teacher save) ----
function renderSelectedCourses() {
    const container = document.getElementById('selectedCoursesContainer');
    const ids = Object.keys(selectedCourses);
    if (ids.length === 0) {
        container.innerHTML = '<span class="text-gray-400 text-xs italic">' + LANG_NO_COURSES_SELECTED + '</span>';
        return;
    }
    let html = '';
    ids.forEach(function(id) {
        const course = selectedCourses[id];
        html += '<div class="inline-flex items-center px-3 py-1.5 rounded-xl bg-indigo-50 dark:bg-indigo-900/30 border border-indigo-100 dark:border-indigo-800 text-indigo-700 dark:text-indigo-300 text-xs font-bold gap-2">';
        html += '<span>' + course.name + '</span>';
        html += '<button type="button" onclick="removeCourse(' + id + ')" class="w-4 h-4 rounded-full bg-indigo-200 dark:bg-indigo-700 flex items-center justify-center text-indigo-600 dark:text-indigo-300 hover:bg-rose-300 hover:text-rose-700 transition-colors">';
        html += '<i class="fas fa-times" style="font-size:8px"></i></button>';
        html += '<input type="hidden" name="course_ids[]" value="' + id + '">';
        html += '</div>';
    });
    container.innerHTML = html;
    updateSelectedCount();
}

function removeCourse(id) {
    delete selectedCourses[id];
    renderSelectedCourses();
    const cb = document.getElementById('course-cb-' + id);
    if (cb) cb.checked = false;
    loadScheduleData();
}

function updateSelectedCount() {
    const el = document.getElementById('selectedCount');
    if (el) el.textContent = Object.keys(selectedCourses).length;
}

// ---- Course modal ----
let searchTimer = null;

function openCourseModal() {
    document.getElementById('courseModal').classList.remove('hidden');
    document.body.style.overflow = 'hidden';
    fetchCourses();
}

function closeCourseModal() {
    document.getElementById('courseModal').classList.add('hidden');
    document.body.style.overflow = 'auto';
    renderSelectedCourses();
    loadScheduleData();
}

function fetchCourses() {
    const params = new URLSearchParams();
    const elVal = document.getElementById('modalEducationLevelFilter').value;
    const sgVal = document.getElementById('modalSubjectGroupFilter').value;
    const semVal = document.getElementById('modalSemesterFilter').value;
    const search = document.getElementById('modalSearchInput').value;
    if (elVal) params.append('education_level_id', elVal);
    if (sgVal) params.append('subject_group_id', sgVal);
    if (semVal) params.append('semester_id', semVal);
    if (search) params.append('search', search);

    const container = document.getElementById('courseListContainer');
    container.innerHTML = '<div class="text-center py-8 text-gray-400"><i class="fas fa-spinner fa-spin text-2xl mb-2"></i><p class="text-xs font-bold">' + LANG_LOADING + '</p></div>';

    fetch("{{ route('admin.teachers.search-courses') }}?" + params.toString())
        .then(r => r.json())
        .then(courses => {
            if (courses.length === 0) {
                container.innerHTML = '<div class="text-center py-8 text-gray-400"><i class="fas fa-inbox text-2xl mb-2"></i><p class="text-xs font-bold">' + LANG_NO_COURSES_FOUND + '</p></div>';
                return;
            }
            let html = '<div class="space-y-2">';
            courses.forEach(course => {
                const isChecked = selectedCourses[course.id] ? 'checked' : '';
                html += '<label class="flex items-center p-3 rounded-xl border-2 border-gray-100 dark:border-[#3a3b3c] hover:border-indigo-300 dark:hover:border-indigo-700 transition-all cursor-pointer group ' + (isChecked ? 'bg-indigo-50/50 dark:bg-indigo-900/20 border-indigo-200 dark:border-indigo-800' : '') + '">';
                html += '<input type="checkbox" id="course-cb-' + course.id + '" class="hidden course-checkbox" value="' + course.id + '" data-name="' + course.name.replace(/"/g, '&quot;') + '" data-grade="' + course.grade.replace(/"/g, '&quot;') + '" data-semester="' + course.semester + '" data-subject-group="' + course.subject_group.replace(/"/g, '&quot;') + '" data-education-level-id="' + (course.education_level_id || '') + '" data-education-level-name="' + (course.education_level_name || '').replace(/"/g, '&quot;') + '" onchange="toggleCourse(this)" ' + isChecked + '>';
                html += '<div class="w-5 h-5 rounded-lg border-2 border-gray-200 dark:border-[#4a4b4c] flex items-center justify-center mr-3 transition-all ' + (isChecked ? 'bg-indigo-500 border-indigo-500' : 'group-hover:border-indigo-400') + '">';
                html += isChecked ? '<i class="fas fa-check text-white text-[10px]"></i>' : '';
                html += '</div><div class="flex-1 min-w-0">';
                html += '<div class="text-sm font-bold text-gray-800 dark:text-gray-200 truncate">' + course.name + '</div>';
                html += '<div class="flex flex-wrap gap-2 mt-1">';
                html += '<span class="text-[10px] font-bold text-gray-400"><i class="fas fa-layer-group mr-1"></i>' + course.grade + '</span>';
                html += '<span class="text-[10px] font-bold text-gray-400"><i class="fas fa-calendar mr-1"></i>Sem ' + course.semester + '</span>';
                if (course.subject_group !== '-') html += '<span class="text-[10px] font-bold px-1.5 py-0.5 rounded bg-indigo-50 dark:bg-indigo-900/30 text-indigo-500">' + course.subject_group + '</span>';
                html += '</div></div></label>';
            });
            html += '</div>';
            container.innerHTML = html;
            updateSelectedCount();
        });
}

function toggleCourse(cb) {
    const id = parseInt(cb.value);
    const label = cb.closest('label');
    const icon = cb.nextElementSibling;
    if (cb.checked) {
        selectedCourses[id] = { id, name: cb.dataset.name, grade: cb.dataset.grade, semester: cb.dataset.semester, subject_group: cb.dataset.subjectGroup, education_level_id: cb.dataset.educationLevelId || null, education_level_name: cb.dataset.educationLevelName || null };
        label.classList.add('bg-indigo-50/50','dark:bg-indigo-900/20','border-indigo-200','dark:border-indigo-800');
        icon.classList.add('bg-indigo-500','border-indigo-500');
        icon.innerHTML = '<i class="fas fa-check text-white text-[10px]"></i>';
    } else {
        delete selectedCourses[id];
        label.classList.remove('bg-indigo-50/50','dark:bg-indigo-900/20','border-indigo-200','dark:border-indigo-800');
        icon.classList.remove('bg-indigo-500','border-indigo-500');
        icon.innerHTML = '';
    }
    updateSelectedCount();
}

document.getElementById('modalEducationLevelFilter').addEventListener('change', fetchCourses);
document.getElementById('modalSubjectGroupFilter').addEventListener('change', fetchCourses);
document.getElementById('modalSemesterFilter').addEventListener('change', fetchCourses);
document.getElementById('modalSearchInput').addEventListener('input', function() { clearTimeout(searchTimer); searchTimer = setTimeout(fetchCourses, 300); });

// ---- Schedule Grid (same as teacher save) ----
function getEducationLevelIds() {
    const ids = new Set();
    Object.values(selectedCourses).forEach(c => { if (c.education_level_id) ids.add(String(c.education_level_id)); });
    return Array.from(ids);
}

function loadScheduleData() {
    const elIds = getEducationLevelIds();
    const section = document.getElementById('scheduleSection');
    const container = document.getElementById('scheduleGridContainer');
    if (elIds.length === 0) { section.style.display = 'none'; container.innerHTML = ''; return; }
    section.style.display = '';
    const missing = elIds.filter(id => !scheduleConfigs[id]);
    if (missing.length === 0) { renderAllScheduleGrids(); return; }
    container.innerHTML = '<div class="text-center py-6 text-gray-400"><i class="fas fa-spinner fa-spin text-xl mb-2"></i><p class="text-xs font-bold">' + LANG_LOADING_SCHEDULE + '</p></div>';
    const params = new URLSearchParams();
    elIds.forEach(id => params.append('education_level_ids[]', id));
    fetch("{{ route('admin.teachers.schedule-data') }}?" + params.toString())
        .then(r => r.json())
        .then(data => { data.forEach(s => { scheduleConfigs[String(s.education_level_id)] = s; }); renderAllScheduleGrids(); });
}

function renderAllScheduleGrids() {
    const elIds = getEducationLevelIds();
    const container = document.getElementById('scheduleGridContainer');
    if (elIds.length === 0) { container.innerHTML = ''; document.getElementById('scheduleSection').style.display = 'none'; return; }
    let html = '';
    elIds.forEach(elId => { const config = scheduleConfigs[elId]; if (config) html += renderScheduleGrid(elId, config); });
    container.innerHTML = html;
}

function fmtTime(min) { const h = Math.floor(min/60)%24, m = min%60; return String(h).padStart(2,'0')+':'+String(m).padStart(2,'0'); }
function calcPeriodStart(cfg, dayNum, p) {
    const dc = cfg.day_configs[String(dayNum)] || {};
    const ts = dc.start_time || cfg.start_time || '08:00';
    const parts = ts.split(':').map(Number);
    const dur = cfg.period_duration || 50;
    let t = parts[0]*60+parts[1];
    for (let i = 1; i < p; i++) { t += dur; const b = (dc.breaks||{})[String(i)]; if (b) t += b; }
    return t;
}
function isDark() { return document.documentElement.classList.contains('dark') || document.body.classList.contains('dark'); }
function isUnavailable(elId, day, period) { const el = unavailablePeriods[String(elId)]; if (!el) return false; const d = el[String(day)]; if (!d) return false; return d.indexOf(period) !== -1; }
function toggleUnavailable(elId, day, period) {
    const ek = String(elId), dk = String(day);
    if (!unavailablePeriods[ek]) unavailablePeriods[ek] = {};
    if (!unavailablePeriods[ek][dk]) unavailablePeriods[ek][dk] = [];
    const arr = unavailablePeriods[ek][dk], idx = arr.indexOf(period);
    if (idx === -1) arr.push(period); else arr.splice(idx, 1);
    if (arr.length === 0) delete unavailablePeriods[ek][dk];
    if (Object.keys(unavailablePeriods[ek]).length === 0) delete unavailablePeriods[ek];
    renderAllScheduleGrids();
}

function renderScheduleGrid(elId, config) {
    const dark = isDark();
    const C = { bg: dark?'#242526':'#ffffff', bgAlt: dark?'#18191a':'#f9fafb', border: dark?'#3a3b3c':'#f0f0f0', text: dark?'#e4e6eb':'#1f2937', muted: dark?'#6b7280':'#9ca3af', indigo: '#6366f1', rose: '#f43f5e', roseBg: dark?'rgba(244,63,94,0.15)':'rgba(255,228,230,0.8)', roseBdr: '#f43f5e' };
    const dayConfigs = config.day_configs || {}, dur = config.period_duration || 50;
    const activeDays = [];
    for (let d = 1; d <= 7; d++) { const dc = dayConfigs[String(d)]; if (dc && dc.periods > 0) activeDays.push(d); }
    if (activeDays.length === 0) return '<div class="p-4 mb-3 bg-gray-50 dark:bg-[#18191a]/30 rounded-2xl border border-gray-100 dark:border-[#3a3b3c]/50"><div class="text-xs font-bold text-gray-400">' + config.education_level_name + ' — ' + LANG_SCHEDULE_NOT_CONFIGURED + '</div></div>';
    const maxP = activeDays.reduce((m,d) => Math.max(m, (dayConfigs[String(d)]||{}).periods||0), 0);
    let html = '<div class="mb-4 bg-white dark:bg-[#242526] rounded-2xl border border-gray-100 dark:border-[#3a3b3c] overflow-hidden">';
    html += '<div class="px-4 py-3 border-b border-gray-100 dark:border-[#3a3b3c] flex items-center justify-between">';
    html += '<span class="text-xs font-bold text-indigo-600 dark:text-indigo-400 uppercase tracking-wider"><i class="fas fa-school mr-1"></i> ' + config.education_level_name + '</span>';
    html += '<span class="text-[10px] text-rose-400 font-bold">' + LANG_CLICK_UNAVAILABLE_PERIODS + '</span></div>';
    html += '<div class="overflow-x-auto p-3"><table style="border-collapse:separate;border-spacing:0;width:100%"><tr>';
    html += '<td style="padding:8px;border-right:2px solid '+C.border+';min-width:56px;width:56px"></td>';
    activeDays.forEach(d => {
        const meta = DAY_META[d], dc = dayConfigs[String(d)]||{};
        html += '<td style="padding:8px 6px;text-align:center;background:'+C.bgAlt+';border:1px solid '+C.border+';border-bottom:2px solid '+C.indigo+';min-width:90px">';
        html += '<div style="font-size:12px;font-weight:800;color:'+C.indigo+'">'+meta.th+'</div>';
        html += '<div style="font-size:9px;color:'+C.muted+';text-transform:uppercase">'+meta.en+'</div>';
        html += '<div style="font-size:9px;color:'+C.muted+';margin-top:2px">'+dc.periods+' '+LANG_PERIODS_UNIT+'</div></td>';
    });
    html += '</tr>';
    for (let p = 1; p <= maxP; p++) {
        html += '<tr><td style="padding:6px 8px;border-right:2px solid '+C.border+';white-space:nowrap">';
        html += '<span style="font-size:11px;font-weight:800;color:'+C.indigo+'">'+LANG_PERIOD+' '+p+'</span></td>';
        activeDays.forEach(d => {
            const dc = dayConfigs[String(d)]||{};
            if (p <= (dc.periods||0)) {
                const s = calcPeriodStart(config, d, p), checked = isUnavailable(elId, d, p);
                const bg = checked?C.roseBg:C.bg, bdr = checked?C.roseBdr:C.border;
                html += '<td style="padding:5px 4px;border:1.5px solid '+bdr+';background:'+bg+';text-align:center;cursor:pointer;transition:all .15s;user-select:none" onclick="toggleUnavailable('+elId+','+d+','+p+')" title="'+(checked?LANG_CLICK_TO_DESELECT:LANG_CLICK_TO_MARK_UNAVAILABLE)+'">';
                if (checked) { html += '<div style="font-size:11px;font-weight:800;color:'+C.rose+'"><i class="fas fa-times-circle"></i></div><div style="font-size:9px;color:'+C.rose+';font-weight:700">'+LANG_NOT_TEACHING+'</div>'; }
                else { html += '<div style="font-size:11px;font-weight:700;color:'+C.text+'">'+fmtTime(s)+'</div><div style="font-size:9px;color:'+C.muted+'">– '+fmtTime(s+dur)+'</div>'; }
                html += '</td>';
            } else {
                html += '<td style="padding:5px 4px;border:1px solid '+C.border+';background:'+C.bgAlt+';opacity:.3;text-align:center"><span style="font-size:11px;color:'+C.muted+'">—</span></td>';
            }
        });
        html += '</tr>';
        if (p < maxP) {
            let hasBreak = false;
            activeDays.forEach(d => { const dc = dayConfigs[String(d)]||{}; if (p<(dc.periods||0) && dc.breaks && dc.breaks[String(p)]) hasBreak = true; });
            if (hasBreak) {
                html += '<tr><td style="padding:2px 8px;border-right:2px solid '+C.border+'"><span style="font-size:9px;color:#f59e0b;font-weight:700">☕ '+LANG_BREAK+'</span></td>';
                activeDays.forEach(d => {
                    const dc = dayConfigs[String(d)]||{}, bDur = (dc.breaks||{})[String(p)];
                    if (bDur && p<(dc.periods||0)) { html += '<td style="padding:2px 4px;border:1px solid #f59e0b;background:'+(dark?'rgba(245,158,11,0.12)':'rgba(254,243,199,0.8)')+';text-align:center"><span style="font-size:10px;color:#d97706;font-weight:700">☕ '+bDur+' '+LANG_MINUTES_SHORT+'</span></td>'; }
                    else { html += '<td style="padding:2px 4px;border:1px solid '+C.border+';background:'+C.bgAlt+';opacity:.2"></td>'; }
                });
                html += '</tr>';
            }
        }
    }
    html += '</table></div></div>';
    return html;
}

new MutationObserver(function() { renderAllScheduleGrids(); }).observe(document.documentElement, { attributes: true, attributeFilter: ['class'] });

renderSelectedCourses();
loadScheduleData();
</script>
@endpush
@endsection
