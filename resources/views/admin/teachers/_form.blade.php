@php
    $teacher = $teacher ?? null;
    $isEdit = $teacher !== null;
    $existingImage = $isEdit && $teacher->image_path ? $teacher->image_path : null;
@endphp

{{-- Hidden inputs populated by the JS widgets below --}}
<input type="hidden" name="image_base64" id="image_base64">
<input type="hidden" name="unavailable_periods" id="unavailablePeriodsInput">

<div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
    <div class="lg:col-span-2 space-y-6">
        <x-card :title="__('Teacher Details')">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <x-form.input :label="__('Full Name')" name="name" :value="$teacher->name ?? null"
                              placeholder="{{ __('Enter full name') }}" required />
                <x-form.input type="email" :label="__('Email Address')" name="email" :value="$teacher->email ?? null"
                              placeholder="email@example.com" required />
                <x-form.input type="password" :label="$isEdit ? __('Change Password') : __('Password')" name="password"
                              placeholder="{{ $isEdit ? __('Leave blank to keep current') : __('Enter secure password') }}"
                              :required="!$isEdit" />
                <x-form.input type="password" :label="__('Confirm Password')" name="password_confirmation"
                              placeholder="{{ __('Confirm password') }}" />
                <div class="md:col-span-2">
                    <x-form.input :label="__('Phone Number')" name="phone" :value="$teacher->phone ?? null"
                                  placeholder="{{ __('Phone number (optional)') }}" />
                </div>
            </div>
        </x-card>

        <x-card :title="__('Assigned Courses')">
            <div id="selectedCoursesContainer" class="flex flex-wrap gap-2 min-h-[44px] p-3 bg-slate-50 border border-slate-200 rounded-lg transition-all">
                {{-- Selected courses (with hidden courses[] inputs) rendered by JS --}}
            </div>
            <button type="button" onclick="openCourseModal()"
                    class="mt-3 inline-flex items-center gap-2 px-4 py-2 rounded-lg text-sm font-medium bg-white text-slate-700 border border-slate-200 hover:bg-slate-50 transition">
                <x-icon name="plus" class="h-4 w-4" />
                {{ __('Select Courses') }}
            </button>
            @error('courses')<p class="form-error">{{ $message }}</p>@enderror
        </x-card>

        <div id="scheduleSection" class="space-y-3" style="display:none">
            <x-card :title="__('Unavailable Teaching Periods')">
                <p class="form-help mb-3">{{ __('Click to select periods you do not want to teach (shown by Education Level of selected courses)') }}</p>
                <div id="scheduleGridContainer">
                    {{-- Schedule grids rendered per education level by JS --}}
                </div>
            </x-card>
        </div>
    </div>

    <div class="space-y-6">
        <x-card :title="__('Photo')">
            <div class="flex flex-col items-center text-center">
                <div id="image-preview-container" class="relative w-28 h-28 rounded-2xl flex items-center justify-center overflow-hidden border border-slate-200 bg-slate-50 text-slate-300">
                    @if($existingImage)
                        <img src="{{ asset($existingImage) }}" id="preview-img" class="w-full h-full object-cover">
                    @else
                        <x-icon name="user" class="h-10 w-10" />
                    @endif
                </div>
                <label for="image_input"
                       class="mt-3 inline-flex items-center gap-2 px-4 py-2 rounded-lg text-sm font-medium bg-white text-slate-700 border border-slate-200 hover:bg-slate-50 transition cursor-pointer">
                    <x-icon name="upload" class="h-4 w-4" />
                    {{ __('Upload Photo') }}
                </label>
                <input type="file" id="image_input" class="hidden" accept="image/*">
            </div>
        </x-card>

        <x-card :title="__('Account Status')">
            <x-form.select
                name="status"
                :options="[1 => __('Active'), 2 => __('Not Active')]"
                :selected="$teacher->status ?? 1" />
        </x-card>
    </div>
</div>

@push('scripts')
{{-- Course Selection Modal (rendered outside the form, at body end) --}}
<div id="courseModal" class="fixed inset-0 z-50 overflow-y-auto hidden" aria-modal="true">
    <div class="flex items-center justify-center min-h-screen p-4">
        <div class="fixed inset-0 bg-slate-900/50" onclick="closeCourseModal()"></div>
        <div class="relative bg-white rounded-2xl shadow-2xl w-full max-w-2xl">
            {{-- Modal Header --}}
            <div class="px-6 pt-6 pb-4 border-b border-slate-100">
                <div class="flex items-center justify-between mb-4">
                    <h3 class="text-lg font-semibold text-slate-900">{{ __('Select Courses') }}</h3>
                    <button type="button" onclick="closeCourseModal()" class="btn-ghost p-2">
                        <x-icon name="x" class="h-4 w-4" />
                    </button>
                </div>

                {{-- Filters --}}
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                    <select id="modalEducationLevelFilter" class="form-select rounded-lg text-sm">
                        <option value="">{{ __('All Education Levels') }}</option>
                        @foreach($educationLevels as $el)
                            <option value="{{ $el->id }}">{{ $el->name_th }}</option>
                        @endforeach
                    </select>
                    <select id="modalSubjectGroupFilter" class="form-select rounded-lg text-sm">
                        <option value="">{{ __('All Subject Groups') }}</option>
                        @foreach($subjectGroups as $sg)
                            <option value="{{ $sg->id }}">{{ $sg->name_th }}</option>
                        @endforeach
                    </select>
                    <select id="modalSemesterFilter" class="form-select rounded-lg text-sm">
                        <option value="">{{ __('All Semesters') }}</option>
                        @foreach($semesters as $sem)
                            <option value="{{ $sem->id }}">{{ __('Semester') }} {{ $sem->semester_number }}</option>
                        @endforeach
                    </select>
                    <label class="relative block">
                        <span class="absolute inset-y-0 left-0 flex items-center pl-3 text-slate-400 pointer-events-none">
                            <x-icon name="search" class="h-4 w-4" />
                        </span>
                        <input type="text" id="modalSearchInput" placeholder="{{ __('Search course name...') }}"
                               class="form-input pl-9 rounded-lg text-sm w-full">
                    </label>
                </div>
            </div>

            {{-- Course List --}}
            <div class="px-6 py-4 max-h-[400px] overflow-y-auto" id="courseListContainer">
                <div class="text-center py-8 text-slate-400">
                    <p class="text-sm font-medium">{{ __('Loading courses...') }}</p>
                </div>
            </div>

            {{-- Modal Footer --}}
            <div class="px-6 py-4 border-t border-slate-100 bg-slate-50 rounded-b-2xl flex items-center justify-between">
                <span class="text-sm text-slate-500"><span id="selectedCount">0</span> {{ __('courses selected') }}</span>
                <button type="button" onclick="closeCourseModal()" class="btn-primary px-6">{{ __('Done') }}</button>
            </div>
        </div>
    </div>
</div>

{{-- Image Processing Canvas --}}
<canvas id="canvas" class="hidden"></canvas>

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
    const LANG_ALL_SEMESTERS = @json(__('All Semesters'));

    // Image upload handling
    const imageInput = document.getElementById('image_input');
    let imagePreview = document.getElementById('preview-img');
    const container = document.getElementById('image-preview-container');
    const base64Input = document.getElementById('image_base64');
    const canvas = document.getElementById('canvas');
    const ctx = canvas.getContext('2d');

    imageInput.addEventListener('change', function(e) {
        if (e.target.files && e.target.files[0]) {
            const reader = new FileReader();
            reader.onload = function(event) {
                const img = new Image();
                img.onload = function() {
                    canvas.width = 400;
                    canvas.height = 400;
                    let sourceX = 0, sourceY = 0, sourceWidth = img.width, sourceHeight = img.height;
                    if (img.width > img.height) {
                        sourceWidth = img.height;
                        sourceX = (img.width - img.height) / 2;
                    } else {
                        sourceHeight = img.width;
                        sourceY = (img.height - img.width) / 2;
                    }
                    ctx.drawImage(img, sourceX, sourceY, sourceWidth, sourceHeight, 0, 0, 400, 400);
                    const base64 = canvas.toDataURL('image/jpeg', 0.85);
                    base64Input.value = base64;
                    imagePreview = document.getElementById('preview-img');
                    if (imagePreview) {
                        imagePreview.src = base64;
                    } else {
                        container.innerHTML = '<img src="' + base64 + '" id="preview-img" class="w-full h-full object-cover">';
                    }
                };
                img.src = event.target.result;
            };
            reader.readAsDataURL(e.target.files[0]);
        }
    });

    // Selected courses state
    let selectedCourses = {};
    // Schedule state: { education_level_id: { day: [period, ...] } }
    let unavailablePeriods = {};
    // Cached schedule configs from server
    let scheduleConfigs = {};

    const DAY_META = {
        1: {th: @json(__('Monday')), en: 'Mon'},
        2: {th: @json(__('Tuesday')), en: 'Tue'},
        3: {th: @json(__('Wednesday')), en: 'Wed'},
        4: {th: @json(__('Thursday')), en: 'Thu'},
        5: {th: @json(__('Friday')), en: 'Fri'},
        6: {th: @json(__('Saturday')), en: 'Sat'},
        7: {th: @json(__('Sunday')), en: 'Sun'},
    };

    @if($isEdit && isset($teacherCourseIds))
        @foreach($teacher->courses as $course)
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
        @if($teacher->unavailable_periods)
            unavailablePeriods = @json($teacher->unavailable_periods);
        @endif
    @endif

    function renderSelectedCourses() {
        const container = document.getElementById('selectedCoursesContainer');
        const ids = Object.keys(selectedCourses);

        if (ids.length === 0) {
            container.innerHTML = '<span class="text-slate-400 text-sm italic">' + LANG_NO_COURSES_SELECTED + '</span>';
            return;
        }

        let html = '';
        ids.forEach(function(id) {
            const course = selectedCourses[id];
            html += '<div class="inline-flex items-center gap-2 px-3 py-1.5 rounded-lg bg-brand-50 border border-brand-100 text-brand-700 text-xs font-medium">';
            html += '<span>' + course.name + '</span>';
            html += '<button type="button" onclick="removeCourse(' + id + ')" class="w-4 h-4 rounded-full bg-brand-100 flex items-center justify-center text-brand-600 hover:bg-red-100 hover:text-red-600 transition-colors">';
            html += '<svg class="w-2.5 h-2.5" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18 18 6M6 6l12 12"/></svg>';
            html += '</button>';
            html += '<input type="hidden" name="courses[]" value="' + id + '">';
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
        const countEl = document.getElementById('selectedCount');
        if (countEl) countEl.textContent = Object.keys(selectedCourses).length;
    }

    // Course modal
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
        const educationLevelId = document.getElementById('modalEducationLevelFilter').value;
        const subjectGroupId = document.getElementById('modalSubjectGroupFilter').value;
        const semesterId = document.getElementById('modalSemesterFilter').value;
        const search = document.getElementById('modalSearchInput').value;

        const params = new URLSearchParams();
        if (educationLevelId) params.append('education_level_id', educationLevelId);
        if (subjectGroupId) params.append('subject_group_id', subjectGroupId);
        if (semesterId) params.append('semester_id', semesterId);
        if (search) params.append('search', search);

        const container = document.getElementById('courseListContainer');
        container.innerHTML = '<div class="text-center py-8 text-slate-400"><p class="text-sm font-medium">' + LANG_LOADING + '</p></div>';

        fetch("{{ route('admin.teachers.search-courses') }}?" + params.toString())
            .then(function(r) { return r.json(); })
            .then(function(courses) {
                if (courses.length === 0) {
                    container.innerHTML = '<div class="text-center py-8 text-slate-400"><p class="text-sm font-medium">' + LANG_NO_COURSES_FOUND + '</p></div>';
                    return;
                }

                let html = '<div class="space-y-2">';
                courses.forEach(function(course) {
                    const isChecked = selectedCourses[course.id] ? 'checked' : '';
                    html += '<label class="flex items-center p-3 rounded-lg border border-slate-200 hover:border-brand-300 transition cursor-pointer group ' + (isChecked ? 'bg-brand-50 border-brand-200' : '') + '">';
                    html += '<input type="checkbox" id="course-cb-' + course.id + '" class="hidden course-checkbox" value="' + course.id + '" data-name="' + course.name.replace(/"/g, '&quot;') + '" data-grade="' + course.grade.replace(/"/g, '&quot;') + '" data-semester="' + course.semester + '" data-subject-group="' + course.subject_group.replace(/"/g, '&quot;') + '" data-education-level-id="' + (course.education_level_id || '') + '" data-education-level-name="' + (course.education_level_name || '').replace(/"/g, '&quot;') + '" onchange="toggleCourse(this)" ' + isChecked + '>';
                    html += '<div class="w-5 h-5 rounded border-2 border-slate-300 flex items-center justify-center mr-3 transition ' + (isChecked ? 'bg-brand-500 border-brand-500' : 'group-hover:border-brand-400') + '">';
                    html += isChecked ? '<svg class="w-3 h-3 text-white" fill="none" stroke="currentColor" stroke-width="3" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="m4.5 12.75 6 6 9-13.5"/></svg>' : '';
                    html += '</div>';
                    html += '<div class="flex-1 min-w-0">';
                    html += '<div class="text-sm font-medium text-slate-800 truncate">' + course.name + '</div>';
                    html += '<div class="flex flex-wrap gap-2 mt-1">';
                    html += '<span class="text-[11px] text-slate-400">' + course.grade + '</span>';
                    html += '<span class="text-[11px] text-slate-400">Sem ' + course.semester + '</span>';
                    if (course.subject_group !== '-') {
                        html += '<span class="text-[11px] font-medium px-1.5 py-0.5 rounded bg-brand-50 text-brand-600">' + course.subject_group + '</span>';
                    }
                    html += '</div>';
                    html += '</div>';
                    html += '</label>';
                });
                html += '</div>';
                container.innerHTML = html;
                updateSelectedCount();
            });
    }

    function toggleCourse(checkbox) {
        const id = parseInt(checkbox.value);
        const label = checkbox.closest('label');
        const checkIcon = checkbox.nextElementSibling;

        if (checkbox.checked) {
            selectedCourses[id] = {
                id: id,
                name: checkbox.dataset.name,
                grade: checkbox.dataset.grade,
                semester: checkbox.dataset.semester,
                subject_group: checkbox.dataset.subjectGroup,
                education_level_id: checkbox.dataset.educationLevelId || null,
                education_level_name: checkbox.dataset.educationLevelName || null
            };
            label.classList.add('bg-brand-50', 'border-brand-200');
            checkIcon.classList.add('bg-brand-500', 'border-brand-500');
            checkIcon.innerHTML = '<svg class="w-3 h-3 text-white" fill="none" stroke="currentColor" stroke-width="3" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="m4.5 12.75 6 6 9-13.5"/></svg>';
        } else {
            delete selectedCourses[id];
            label.classList.remove('bg-brand-50', 'border-brand-200');
            checkIcon.classList.remove('bg-brand-500', 'border-brand-500');
            checkIcon.innerHTML = '';
        }
        updateSelectedCount();
    }

    // Filter events
    document.getElementById('modalEducationLevelFilter').addEventListener('change', fetchCourses);
    document.getElementById('modalSubjectGroupFilter').addEventListener('change', fetchCourses);
    document.getElementById('modalSemesterFilter').addEventListener('change', fetchCourses);
    document.getElementById('modalSearchInput').addEventListener('input', function() {
        clearTimeout(searchTimer);
        searchTimer = setTimeout(fetchCourses, 300);
    });

    // Serialize unavailable periods into the hidden input on submit
    document.getElementById('teacherForm').addEventListener('submit', function() {
        document.getElementById('unavailablePeriodsInput').value = JSON.stringify(unavailablePeriods);
    });

    function handleSubmit() {
        document.getElementById('unavailablePeriodsInput').value = JSON.stringify(unavailablePeriods);
        document.getElementById('teacherForm').submit();
    }

    // ── Schedule Grid Logic ──────────────────────────────────────────────────

    function getEducationLevelIds() {
        const ids = new Set();
        Object.values(selectedCourses).forEach(function(c) {
            if (c.education_level_id) ids.add(String(c.education_level_id));
        });
        return Array.from(ids);
    }

    function loadScheduleData() {
        const elIds = getEducationLevelIds();
        const section = document.getElementById('scheduleSection');
        const container = document.getElementById('scheduleGridContainer');

        if (elIds.length === 0) {
            section.style.display = 'none';
            container.innerHTML = '';
            return;
        }

        section.style.display = '';

        // Check if we already have all configs cached
        const missing = elIds.filter(function(id) { return !scheduleConfigs[id]; });
        if (missing.length === 0) {
            renderAllScheduleGrids();
            return;
        }

        container.innerHTML = '<div class="text-center py-6 text-slate-400"><p class="text-sm font-medium">' + LANG_LOADING_SCHEDULE + '</p></div>';

        const params = new URLSearchParams();
        elIds.forEach(function(id) { params.append('education_level_ids[]', id); });

        fetch("{{ route('admin.teachers.schedule-data') }}?" + params.toString())
            .then(function(r) { return r.json(); })
            .then(function(data) {
                data.forEach(function(s) {
                    scheduleConfigs[String(s.education_level_id)] = s;
                });
                renderAllScheduleGrids();
            });
    }

    function renderAllScheduleGrids() {
        const elIds = getEducationLevelIds();
        const container = document.getElementById('scheduleGridContainer');

        if (elIds.length === 0) {
            container.innerHTML = '';
            document.getElementById('scheduleSection').style.display = 'none';
            return;
        }

        let html = '';
        elIds.forEach(function(elId) {
            const config = scheduleConfigs[elId];
            if (!config) return;
            html += renderScheduleGrid(elId, config);
        });

        container.innerHTML = html;
    }

    function fmtTime(min) {
        const h = Math.floor(min / 60) % 24, m = min % 60;
        return String(h).padStart(2,'0') + ':' + String(m).padStart(2,'0');
    }

    function calcPeriodStart(cfg, dayNum, p) {
        const dayCfg = cfg.day_configs[String(dayNum)] || {};
        const timeStr = dayCfg.start_time || cfg.start_time || '08:00';
        const parts = timeStr.split(':').map(Number);
        const dur = cfg.period_duration || 50;
        let t = parts[0] * 60 + parts[1];
        for (let i = 1; i < p; i++) {
            t += dur;
            const b = (dayCfg.breaks || {})[String(i)];
            if (b) t += b;
        }
        return t;
    }

    function isUnavailable(elId, day, period) {
        const el = unavailablePeriods[String(elId)];
        if (!el) return false;
        const dayArr = el[String(day)];
        if (!dayArr) return false;
        return dayArr.indexOf(period) !== -1;
    }

    function toggleUnavailable(elId, day, period) {
        const elKey = String(elId);
        const dayKey = String(day);
        if (!unavailablePeriods[elKey]) unavailablePeriods[elKey] = {};
        if (!unavailablePeriods[elKey][dayKey]) unavailablePeriods[elKey][dayKey] = [];

        const arr = unavailablePeriods[elKey][dayKey];
        const idx = arr.indexOf(period);
        if (idx === -1) {
            arr.push(period);
        } else {
            arr.splice(idx, 1);
        }

        // Clean up empty
        if (arr.length === 0) delete unavailablePeriods[elKey][dayKey];
        if (Object.keys(unavailablePeriods[elKey]).length === 0) delete unavailablePeriods[elKey];

        renderAllScheduleGrids();
    }

    function renderScheduleGrid(elId, config) {
        const C = {
            bg:      '#ffffff',
            bgAlt:   '#f8fafc',
            border:  '#e2e8f0',
            text:    '#1f2937',
            muted:   '#94a3b8',
            indigo:  '#2563eb',
            rose:    '#f43f5e',
            roseBg:  'rgba(255,228,230,0.8)',
            roseBdr: '#f43f5e',
        };

        const dayConfigs = config.day_configs || {};
        const dur = config.period_duration || 50;

        // Get active days (with periods > 0)
        const activeDays = [];
        for (let d = 1; d <= 7; d++) {
            const dc = dayConfigs[String(d)];
            if (dc && dc.periods > 0) activeDays.push(d);
        }

        if (activeDays.length === 0) {
            return '<div class="p-4 mb-3 bg-slate-50 rounded-xl border border-slate-100">' +
                '<div class="text-xs font-medium text-slate-400">' + config.education_level_name + ' — ' + LANG_SCHEDULE_NOT_CONFIGURED + '</div></div>';
        }

        const maxP = activeDays.reduce(function(m, d) {
            return Math.max(m, (dayConfigs[String(d)] || {}).periods || 0);
        }, 0);

        let html = '<div class="mb-4 bg-white rounded-xl border border-slate-200 overflow-hidden">';
        html += '<div class="px-4 py-3 border-b border-slate-100 flex items-center justify-between">';
        html += '<span class="text-xs font-semibold text-brand-600 uppercase tracking-wider">' + config.education_level_name + '</span>';
        html += '<span class="text-[11px] text-rose-400 font-medium">' + LANG_CLICK_UNAVAILABLE_PERIODS + '</span>';
        html += '</div>';
        html += '<div class="overflow-x-auto p-3">';
        html += '<table style="border-collapse:separate;border-spacing:0;width:100%">';

        // Header row
        html += '<tr>';
        html += '<td style="padding:8px;border-right:2px solid ' + C.border + ';min-width:56px;width:56px"></td>';
        activeDays.forEach(function(d) {
            const meta = DAY_META[d];
            const dc = dayConfigs[String(d)] || {};
            html += '<td style="padding:8px 6px;text-align:center;background:' + C.bgAlt + ';border:1px solid ' + C.border + ';border-bottom:2px solid ' + C.indigo + ';min-width:90px">';
            html += '<div style="font-size:12px;font-weight:700;color:' + C.indigo + '">' + meta.th + '</div>';
            html += '<div style="font-size:9px;color:' + C.muted + ';text-transform:uppercase">' + meta.en + '</div>';
            html += '<div style="font-size:9px;color:' + C.muted + ';margin-top:2px">' + dc.periods + ' ' + LANG_PERIODS_UNIT + '</div>';
            html += '</td>';
        });
        html += '</tr>';

        // Period rows
        for (let p = 1; p <= maxP; p++) {
            html += '<tr>';
            html += '<td style="padding:6px 8px;border-right:2px solid ' + C.border + ';white-space:nowrap">';
            html += '<span style="font-size:11px;font-weight:700;color:' + C.indigo + '">' + LANG_PERIOD + ' ' + p + '</span>';
            html += '</td>';

            activeDays.forEach(function(d) {
                const dc = dayConfigs[String(d)] || {};
                if (p <= (dc.periods || 0)) {
                    const s = calcPeriodStart(config, d, p);
                    const checked = isUnavailable(elId, d, p);
                    const cellBg = checked ? C.roseBg : C.bg;
                    const cellBorder = checked ? C.roseBdr : C.border;
                    const cursor = 'cursor:pointer';

                    html += '<td style="padding:5px 4px;border:1.5px solid ' + cellBorder + ';background:' + cellBg + ';text-align:center;' + cursor + ';transition:all .15s;user-select:none" ';
                    html += 'onclick="toggleUnavailable(' + elId + ',' + d + ',' + p + ')" ';
                    html += 'title="' + (checked ? LANG_CLICK_TO_DESELECT : LANG_CLICK_TO_MARK_UNAVAILABLE) + '">';

                    if (checked) {
                        html += '<div style="font-size:11px;font-weight:700;color:' + C.rose + '">✕</div>';
                        html += '<div style="font-size:9px;color:' + C.rose + ';font-weight:600">' + LANG_NOT_TEACHING + '</div>';
                    } else {
                        html += '<div style="font-size:11px;font-weight:600;color:' + C.text + '">' + fmtTime(s) + '</div>';
                        html += '<div style="font-size:9px;color:' + C.muted + '">– ' + fmtTime(s + dur) + '</div>';
                    }
                    html += '</td>';
                } else {
                    html += '<td style="padding:5px 4px;border:1px solid ' + C.border + ';background:' + C.bgAlt + ';opacity:.3;text-align:center">';
                    html += '<span style="font-size:11px;color:' + C.muted + '">—</span>';
                    html += '</td>';
                }
            });
            html += '</tr>';

            // Break rows
            if (p < maxP) {
                let hasAnyBreak = false;
                activeDays.forEach(function(d) {
                    const dc = dayConfigs[String(d)] || {};
                    if (p < (dc.periods || 0) && dc.breaks && dc.breaks[String(p)]) hasAnyBreak = true;
                });

                if (hasAnyBreak) {
                    html += '<tr>';
                    html += '<td style="padding:2px 8px;border-right:2px solid ' + C.border + '">';
                    html += '<span style="font-size:9px;color:#f59e0b;font-weight:600">☕ ' + LANG_BREAK + '</span>';
                    html += '</td>';
                    activeDays.forEach(function(d) {
                        const dc = dayConfigs[String(d)] || {};
                        const bDur = (dc.breaks || {})[String(p)];
                        if (bDur && p < (dc.periods || 0)) {
                            html += '<td style="padding:2px 4px;border:1px solid #f59e0b;background:rgba(254,243,199,0.8);text-align:center">';
                            html += '<span style="font-size:10px;color:#d97706;font-weight:600">☕ ' + bDur + ' ' + LANG_MINUTES_SHORT + '</span>';
                            html += '</td>';
                        } else {
                            html += '<td style="padding:2px 4px;border:1px solid ' + C.border + ';background:' + C.bgAlt + ';opacity:.2"></td>';
                        }
                    });
                    html += '</tr>';
                }
            }
        }

        html += '</table></div></div>';
        return html;
    }

    // Initial render
    renderSelectedCourses();
    loadScheduleData();
</script>
@endpush
