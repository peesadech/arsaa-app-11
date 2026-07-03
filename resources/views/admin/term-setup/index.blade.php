@extends('layouts.app')

@section('content')
<div class="min-h-screen bg-gray-50 dark:bg-[#18191a] py-10 px-4 sm:px-6 lg:px-8 transition-colors duration-300">
    <div class="max-w-5xl mx-auto">

        {{-- Header --}}
        <div class="flex items-center space-x-4 mb-8">
            <a href="{{ route('admin.dashboard') }}"
               class="group flex items-center justify-center w-10 h-10 rounded-full bg-white dark:bg-[#242526] shadow-sm border border-gray-200 dark:border-[#3a3b3c] text-gray-500 dark:text-gray-400 hover:text-indigo-600 dark:hover:text-indigo-400 hover:border-indigo-300 transition-all duration-200">
                <i class="fas fa-arrow-left group-hover:-translate-x-0.5 transition-transform"></i>
            </a>
            <div>
                <h1 class="text-2xl font-extrabold text-gray-900 dark:text-white tracking-tight">{{ __('New Term Setup') }}</h1>
                <p class="text-sm text-gray-500 dark:text-gray-400 font-medium px-1">
                    {{ __('Academic Year') }} {{ $academicYear->year ?? '?' }} / {{ __('Semester') }} {{ $semester->semester_number ?? '?' }}
                </p>
            </div>
        </div>

        {{-- Flash --}}
        @if(session('status'))
        <div class="mb-6 p-4 bg-emerald-50 dark:bg-emerald-900/20 border border-emerald-200 dark:border-emerald-800 rounded-2xl text-emerald-700 dark:text-emerald-300 text-sm">
            {{ session('status') }}
        </div>
        @endif
        @if(session('error'))
        <div class="mb-6 p-4 bg-rose-50 dark:bg-rose-900/20 border border-rose-200 dark:border-rose-800 rounded-2xl text-rose-700 dark:text-rose-300 text-sm">
            {{ session('error') }}
        </div>
        @endif

        {{-- Target Term Selector --}}
        <div class="bg-white dark:bg-[#242526] rounded-[2rem] shadow-sm border border-gray-100 dark:border-[#3a3b3c] p-6 mb-6">
            <div class="flex flex-wrap items-center justify-between gap-3 mb-4">
                <h2 class="text-sm font-bold text-gray-400 dark:text-gray-500 uppercase tracking-widest">{{ __('Term to set up') }}</h2>
                <a href="{{ route('admin.academic-years.index') }}" class="text-xs font-bold text-indigo-500 hover:text-indigo-600">
                    <i class="fas fa-plus-circle mr-1"></i>{{ __('Add new academic year') }}
                </a>
            </div>
            <form action="{{ route('admin.academic-years.select-current') }}" method="POST" class="flex flex-wrap items-end gap-3">
                @csrf
                <div class="flex-1 min-w-[150px]">
                    <label class="block text-xs font-medium text-gray-500 dark:text-gray-400 mb-1">{{ __('Academic Year') }}</label>
                    <select name="academic_year_id" id="targetYearSelect"
                            class="w-full px-4 py-3 bg-gray-50 dark:bg-[#3a3b3c] border-2 border-transparent rounded-2xl text-sm text-gray-800 dark:text-white focus:border-indigo-500 focus:outline-none transition-all">
                        @foreach($allYears as $y)
                        <option value="{{ $y->id }}" {{ (int) $yearId === $y->id ? 'selected' : '' }}>{{ $y->year }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="flex-1 min-w-[150px]">
                    <label class="block text-xs font-medium text-gray-500 dark:text-gray-400 mb-1">{{ __('Semester') }}</label>
                    <select name="semester_id" id="targetSemesterSelect"
                            class="w-full px-4 py-3 bg-gray-50 dark:bg-[#3a3b3c] border-2 border-transparent rounded-2xl text-sm text-gray-800 dark:text-white focus:border-indigo-500 focus:outline-none transition-all">
                        @foreach($allSemesters as $s)
                        <option value="{{ $s->id }}" {{ (int) $semesterId === $s->id ? 'selected' : '' }}>{{ __('Semester') }} {{ $s->semester_number }}</option>
                        @endforeach
                    </select>
                </div>
                <button type="submit" class="btn-app">
                    <i class="fas fa-check text-[10px]"></i> {{ __('Use this term') }}
                </button>
            </form>
            <div id="termDataBadge" class="mt-3 text-xs font-bold {{ $termHasData ? 'text-amber-500' : 'text-emerald-500' }}">
                <i class="fas {{ $termHasData ? 'fa-database' : 'fa-sparkles' }} mr-1"></i>
                <span id="termDataBadgeText">{{ $termHasData ? __('This term already has data') : __('New term — no data yet') }}</span>
            </div>
        </div>

        {{-- Readiness Checklist --}}
        <div class="bg-white dark:bg-[#242526] rounded-[2rem] shadow-sm border border-gray-100 dark:border-[#3a3b3c] p-6 mb-6">
            <h2 class="text-sm font-bold text-gray-400 dark:text-gray-500 uppercase tracking-widest mb-4">{{ __('Term readiness') }}</h2>
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
                <a href="{{ $item['route'] }}" class="block p-4 rounded-2xl border {{ $ok ? 'border-emerald-100 dark:border-emerald-900 bg-emerald-50/50 dark:bg-emerald-900/10' : 'border-amber-100 dark:border-amber-900 bg-amber-50/50 dark:bg-amber-900/10' }} hover:shadow-sm transition-all">
                    <div class="flex items-center justify-between mb-1">
                        <i class="fas {{ $item['icon'] }} {{ $ok ? 'text-emerald-500' : 'text-amber-500' }}"></i>
                        <i class="fas {{ $ok ? 'fa-check-circle text-emerald-500' : 'fa-exclamation-circle text-amber-400' }} text-sm"></i>
                    </div>
                    <div class="text-xl font-extrabold text-gray-800 dark:text-white">
                        {{ $item['count'] }}@if($item['total'] !== null)<span class="text-sm font-medium text-gray-400">/{{ $item['total'] }}</span>@endif
                    </div>
                    <div class="text-[11px] font-medium text-gray-500 dark:text-gray-400">{{ $item['label'] }}</div>
                </a>
                @endforeach
            </div>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">

            {{-- Pull from Global --}}
            <div class="bg-white dark:bg-[#242526] rounded-[2rem] shadow-sm border border-gray-100 dark:border-[#3a3b3c] p-6 flex flex-col">
                <div class="flex items-center gap-3 mb-3">
                    <div class="w-10 h-10 rounded-xl bg-indigo-50 dark:bg-indigo-900/20 flex items-center justify-center">
                        <i class="fas fa-globe text-indigo-500"></i>
                    </div>
                    <h2 class="text-base font-bold text-gray-800 dark:text-white">{{ __('Pull from Global') }}</h2>
                </div>
                <p class="text-xs text-gray-500 dark:text-gray-400 mb-4 flex-1">
                    {{ __('Copy semester schedules from Global Schedule (every education level) and initialize teacher term statuses as Available. Existing data will not be overwritten.') }}
                </p>
                <div class="space-y-2">
                    <form action="{{ route('admin.yearly-schedule.copy-all') }}" method="POST">
                        @csrf
                        <input type="hidden" name="academic_year_id" value="{{ $yearId }}">
                        <input type="hidden" name="semester_id" value="{{ $semesterId }}">
                        <button type="submit" class="btn-app w-full justify-center">
                            <i class="fas fa-calendar-alt text-[10px]"></i> {{ __('Copy all schedules from Global') }}
                        </button>
                    </form>
                    <form action="{{ route('admin.teacher-term-status.bulk-initialize') }}" method="POST">
                        @csrf
                        <button type="submit" class="btn-app w-full justify-center">
                            <i class="fas fa-user-check text-[10px]"></i> {{ __('Initialize all teacher statuses') }}
                        </button>
                    </form>
                </div>
            </div>

            {{-- Clone from previous term --}}
            <div class="bg-white dark:bg-[#242526] rounded-[2rem] shadow-sm border border-gray-100 dark:border-[#3a3b3c] p-6">
                <div class="flex items-center gap-3 mb-3">
                    <div class="w-10 h-10 rounded-xl bg-purple-50 dark:bg-purple-900/20 flex items-center justify-center">
                        <i class="fas fa-clone text-purple-500"></i>
                    </div>
                    <h2 class="text-base font-bold text-gray-800 dark:text-white">{{ __('Clone from previous term') }}</h2>
                </div>

                @if($termHasData)
                <div class="p-4 bg-amber-50 dark:bg-amber-900/20 border border-amber-200 dark:border-amber-800 rounded-2xl text-amber-700 dark:text-amber-300 text-xs">
                    <i class="fas fa-exclamation-triangle mr-1"></i>
                    {{ __('This term already has data — cannot create a duplicate. Please select a new term.') }}
                </div>
                @elseif($sourceTerms->isEmpty())
                <p class="text-xs text-gray-400">{{ __('No previous term with data found') }}</p>
                @else
                <form action="{{ route('admin.term-setup.clone') }}" method="POST" class="space-y-4">
                    @csrf
                    <div>
                        <label class="block text-xs font-medium text-gray-500 dark:text-gray-400 mb-1">{{ __('Source term') }}</label>
                        <select name="source_term" required
                                class="w-full px-4 py-3 bg-gray-50 dark:bg-[#3a3b3c] border-2 border-transparent rounded-2xl text-sm text-gray-800 dark:text-white focus:border-indigo-500 focus:outline-none transition-all">
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
                        <label class="block text-xs font-medium text-gray-500 dark:text-gray-400">{{ __('What to clone') }}</label>
                        <label class="flex items-center gap-3 cursor-pointer text-sm text-gray-700 dark:text-gray-300">
                            <input type="checkbox" name="parts[]" value="schedules" checked class="w-4 h-4 rounded border-gray-300 text-indigo-600 focus:ring-indigo-500">
                            {{ __('Semester schedules (teaching days & periods)') }}
                        </label>
                        <label class="flex items-center gap-3 cursor-pointer text-sm text-gray-700 dark:text-gray-300">
                            <input type="checkbox" name="parts[]" value="opened" checked class="w-4 h-4 rounded border-gray-300 text-indigo-600 focus:ring-indigo-500">
                            {{ __('Opened grades, classrooms and courses') }}
                        </label>
                        <label class="flex items-center gap-3 cursor-pointer text-sm text-gray-700 dark:text-gray-300">
                            <input type="checkbox" name="parts[]" value="teachers" checked class="w-4 h-4 rounded border-gray-300 text-indigo-600 focus:ring-indigo-500">
                            {{ __('Teacher term data (status, courses, unavailable periods)') }}
                        </label>
                        <p class="text-[10px] text-gray-400">{{ __('When cloning across different semesters, opened courses and teacher courses are regenerated from the course master of the target semester.') }}</p>
                    </div>

                    <button type="submit" class="btn-app w-full justify-center">
                        <i class="fas fa-clone text-[10px]"></i> {{ __('Clone') }}
                    </button>
                </form>
                @endif
            </div>
        </div>

    </div>
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
@endsection
