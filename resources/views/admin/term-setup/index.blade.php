<x-layouts.admin :header="__('New Term Setup')" :subheader="__('Academic Year') . ' ' . ($academicYear->year ?? '?') . ' / ' . __('Semester') . ' ' . ($semester->semester_number ?? '?')">
    <x-slot name="actions">
        <x-button variant="secondary" icon="arrow-left" :href="route('admin.dashboard')">{{ __('Back') }}</x-button>
    </x-slot>

    {{-- Flash --}}
    @if(session('status'))
    <div class="mb-6 rounded-xl bg-emerald-50 border border-emerald-200 text-emerald-700 text-sm px-4 py-3">
        {{ session('status') }}
    </div>
    @endif
    @if(session('error'))
    <div class="mb-6 rounded-xl bg-red-50 border border-red-200 text-red-700 text-sm px-4 py-3">
        {{ session('error') }}
    </div>
    @endif

    {{-- Target Term Selector --}}
    <x-card class="mb-6">
        <div class="flex flex-wrap items-center justify-between gap-3 mb-4">
            <h2 class="text-sm font-semibold text-slate-500 uppercase tracking-wide">{{ __('Term to set up') }}</h2>
            <a href="{{ route('admin.academic-years.index') }}" class="inline-flex items-center gap-1 text-xs font-semibold text-brand-600 hover:text-brand-700">
                <x-icon name="plus" class="h-3.5 w-3.5" />{{ __('Add new academic year') }}
            </a>
        </div>
        <form action="{{ route('admin.academic-years.select-current') }}" method="POST" class="flex flex-wrap items-end gap-3">
            @csrf
            <div class="flex-1 min-w-[150px]">
                <label class="form-label">{{ __('Academic Year') }}</label>
                <select name="academic_year_id" id="targetYearSelect" class="form-select">
                    @foreach($allYears as $y)
                    <option value="{{ $y->id }}" {{ (int) $yearId === $y->id ? 'selected' : '' }}>{{ $y->year }}</option>
                    @endforeach
                </select>
            </div>
            <div class="flex-1 min-w-[150px]">
                <label class="form-label">{{ __('Semester') }}</label>
                <select name="semester_id" id="targetSemesterSelect" class="form-select">
                    @foreach($allSemesters as $s)
                    <option value="{{ $s->id }}" {{ (int) $semesterId === $s->id ? 'selected' : '' }}>{{ __('Semester') }} {{ $s->semester_number }}</option>
                    @endforeach
                </select>
            </div>
            <button type="submit" class="btn-primary">
                <x-icon name="check" class="h-4 w-4" /> {{ __('Use this term') }}
            </button>
        </form>
        <div id="termDataBadge" class="mt-3 text-xs font-bold {{ $termHasData ? 'text-amber-500' : 'text-emerald-500' }}">
            <i class="fas {{ $termHasData ? 'fa-database' : 'fa-sparkles' }} mr-1"></i>
            <span id="termDataBadgeText">{{ $termHasData ? __('This term already has data') : __('New term — no data yet') }}</span>
        </div>
    </x-card>

    {{-- Readiness Checklist --}}
    <x-card class="mb-6">
        <h2 class="text-sm font-semibold text-slate-500 uppercase tracking-wide mb-4">{{ __('Term readiness') }}</h2>
        @php
            $items = [
                ['label' => __('Semester Schedule'), 'icon' => 'fa-calendar-alt', 'count' => $readiness['yearly_schedules']['count'], 'total' => $readiness['yearly_schedules']['total'], 'route' => route('admin.yearly-schedule.index')],
                ['label' => __('Opened Grade Levels'), 'icon' => 'fa-layer-group', 'count' => $readiness['opened_grades']['count'], 'total' => null, 'route' => route('admin.dashboard')],
                ['label' => __('Opened Classrooms'), 'icon' => 'fa-school', 'count' => $readiness['opened_classrooms']['count'], 'total' => null, 'route' => route('admin.dashboard')],
                ['label' => __('Opened Courses'), 'icon' => 'fa-book', 'count' => $readiness['opened_courses']['count'], 'total' => null, 'route' => route('admin.opened-courses.index')],
                ['label' => __('Teacher Term Status'), 'icon' => 'fa-user-check', 'count' => $readiness['teacher_term_statuses']['count'], 'total' => $readiness['teacher_term_statuses']['total'], 'route' => route('admin.teacher-term-status.index')],
                ['label' => __('Teachers with term courses'), 'icon' => 'fa-chalkboard-teacher', 'count' => $readiness['teacher_term_courses']['count'], 'total' => null, 'route' => route('admin.teacher-term-status.index')],
            ];
        @endphp
        <div class="grid grid-cols-2 md:grid-cols-3 gap-4">
            @foreach($items as $item)
            @php
                $ok = $item['total'] !== null ? ($item['count'] >= $item['total'] && $item['total'] > 0) : $item['count'] > 0;
            @endphp
            <a href="{{ $item['route'] }}" class="block p-4 rounded-xl border {{ $ok ? 'border-emerald-100 bg-emerald-50/50' : 'border-amber-100 bg-amber-50/50' }} hover:shadow-soft transition-all">
                <div class="flex items-center justify-between mb-1">
                    <i class="fas {{ $item['icon'] }} {{ $ok ? 'text-emerald-500' : 'text-amber-500' }}"></i>
                    <i class="fas {{ $ok ? 'fa-check-circle text-emerald-500' : 'fa-exclamation-circle text-amber-400' }} text-sm"></i>
                </div>
                <div class="text-xl font-bold text-slate-800">
                    {{ $item['count'] }}@if($item['total'] !== null)<span class="text-sm font-medium text-slate-400">/{{ $item['total'] }}</span>@endif
                </div>
                <div class="text-[11px] font-medium text-slate-500">{{ $item['label'] }}</div>
            </a>
            @endforeach
        </div>
    </x-card>

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">

        {{-- Pull from Global --}}
        <x-card class="flex flex-col">
            <div class="flex items-center gap-3 mb-3">
                <div class="w-10 h-10 rounded-xl bg-brand-50 flex items-center justify-center text-brand-600">
                    <x-icon name="globe" class="h-5 w-5" />
                </div>
                <h2 class="text-base font-semibold text-slate-900">{{ __('Pull from Global') }}</h2>
            </div>
            <p class="text-xs text-slate-500 mb-4 flex-1">
                {{ __('Copy semester schedules from Global Schedule (every education level) and initialize teacher term statuses as Available. Existing data will not be overwritten.') }}
            </p>
            <div class="space-y-2">
                <form action="{{ route('admin.yearly-schedule.copy-all') }}" method="POST">
                    @csrf
                    <input type="hidden" name="academic_year_id" value="{{ $yearId }}">
                    <input type="hidden" name="semester_id" value="{{ $semesterId }}">
                    <button type="submit" class="btn-primary w-full">
                        <x-icon name="calendar" class="h-4 w-4" /> {{ __('Copy all schedules from Global') }}
                    </button>
                </form>
                <form action="{{ route('admin.teacher-term-status.bulk-initialize') }}" method="POST">
                    @csrf
                    <button type="submit" class="btn-secondary w-full">
                        <x-icon name="check" class="h-4 w-4" /> {{ __('Initialize all teacher statuses') }}
                    </button>
                </form>
            </div>
        </x-card>

        {{-- Clone from previous term --}}
        <x-card>
            <div class="flex items-center gap-3 mb-3">
                <div class="w-10 h-10 rounded-xl bg-brand-50 flex items-center justify-center text-brand-600">
                    <x-icon name="layers" class="h-5 w-5" />
                </div>
                <h2 class="text-base font-semibold text-slate-900">{{ __('Clone from previous term') }}</h2>
            </div>

            @if($termHasData)
            <div class="p-4 bg-amber-50 border border-amber-200 rounded-xl text-amber-700 text-xs">
                <i class="fas fa-exclamation-triangle mr-1"></i>
                {{ __('This term already has data — cannot create a duplicate. Please select a new term.') }}
            </div>
            @elseif($sourceTerms->isEmpty())
            <p class="text-xs text-slate-400">{{ __('No previous term with data found') }}</p>
            @else
            <form action="{{ route('admin.term-setup.clone') }}" method="POST" class="space-y-4">
                @csrf
                <div>
                    <label class="form-label">{{ __('Source term') }}</label>
                    <select name="source_term" required class="form-select">
                        @foreach($sourceTerms as $term)
                        <option value="{{ $term['academic_year_id'] }}-{{ $term['semester_id'] }}">
                            {{ __('Academic Year') }} {{ $term['year'] }} / {{ __('Semester') }} {{ $term['semester_number'] }}
                            ({{ __('Grade Levels') }}: {{ $term['summary']['opened_grades']['count'] }},
                            {{ __('Courses') }}: {{ $term['summary']['opened_courses']['count'] }},
                            {{ __('Teachers') }}: {{ $term['summary']['teacher_term_statuses']['count'] }})
                        </option>
                        @endforeach
                    </select>
                </div>

                <div class="space-y-2">
                    <label class="form-label">{{ __('What to clone') }}</label>
                    <label class="flex items-center gap-3 cursor-pointer text-sm text-slate-700">
                        <input type="checkbox" name="parts[]" value="schedules" checked class="w-4 h-4 rounded border-slate-300 text-brand-600 focus:ring-brand-500">
                        {{ __('Semester schedules (teaching days & periods)') }}
                    </label>
                    <label class="flex items-center gap-3 cursor-pointer text-sm text-slate-700">
                        <input type="checkbox" name="parts[]" value="opened" checked class="w-4 h-4 rounded border-slate-300 text-brand-600 focus:ring-brand-500">
                        {{ __('Opened grades, classrooms and courses') }}
                    </label>
                    <label class="flex items-center gap-3 cursor-pointer text-sm text-slate-700">
                        <input type="checkbox" name="parts[]" value="teachers" checked class="w-4 h-4 rounded border-slate-300 text-brand-600 focus:ring-brand-500">
                        {{ __('Teacher term data (status, courses, unavailable periods)') }}
                    </label>
                    <p class="text-[10px] text-slate-400">{{ __('When cloning across different semesters, opened courses and teacher courses are regenerated from the course master of the target semester.') }}</p>
                </div>

                <button type="submit" class="btn-primary w-full">
                    <x-icon name="layers" class="h-4 w-4" /> {{ __('Clone') }}
                </button>
            </form>
            @endif
        </x-card>
    </div>

    <script>
    (function () {
        // เทอมที่มีข้อมูลแล้ว — แสดง badge เตือนตอนเลือกปี/เทอม
        const EXISTING_TERMS = @json($existingTermKeys);
        const yearSelect = document.getElementById('targetYearSelect');
        const semesterSelect = document.getElementById('targetSemesterSelect');
        const badge = document.getElementById('termDataBadge');
        const badgeText = document.getElementById('termDataBadgeText');
        const hasDataText = @json(__('This term already has data'));
        const newTermText = @json(__('New term — no data yet'));

        function updateBadge() {
            const key = yearSelect.value + '-' + semesterSelect.value;
            const exists = EXISTING_TERMS.includes(key);
            badge.className = 'mt-3 text-xs font-bold ' + (exists ? 'text-amber-500' : 'text-emerald-500');
            badge.querySelector('i').className = 'fas ' + (exists ? 'fa-database' : 'fa-sparkles') + ' mr-1';
            badgeText.textContent = exists ? hasDataText : newTermText;
        }

        yearSelect.addEventListener('change', updateBadge);
        semesterSelect.addEventListener('change', updateBadge);
    })();
    </script>
</x-layouts.admin>
