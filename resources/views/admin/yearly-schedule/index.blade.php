@extends('layouts.app')

@section('content')
<div class="min-h-screen bg-gray-50 dark:bg-[#18191a] py-10 px-4 sm:px-6 lg:px-8 transition-colors duration-300">
    <div class="max-w-6xl mx-auto">

        {{-- Header --}}
        <div class="flex items-center justify-between mb-8">
            <div class="flex items-center space-x-4">
                <a href="{{ route('admin.dashboard') }}"
                   class="group flex items-center justify-center w-10 h-10 rounded-full bg-white dark:bg-[#242526] shadow-sm border border-gray-200 dark:border-[#3a3b3c] text-gray-500 dark:text-gray-400 hover:text-indigo-600 dark:hover:text-indigo-400 hover:border-indigo-300 transition-all duration-200">
                    <i class="fas fa-arrow-left group-hover:-translate-x-0.5 transition-transform"></i>
                </a>
                <div>
                    <h1 class="text-2xl font-extrabold text-gray-900 dark:text-white tracking-tight">{{ __('Yearly/Semester Schedule') }}</h1>
                    <p class="text-sm text-gray-500 dark:text-gray-400 font-medium px-1">
                        @if($academicYear && $semester)
                            {{ __('Academic Year :year', ['year' => $academicYear->year]) }} / {{ __('Semester Number') }} {{ $semester->semester_number }}
                        @else
                            {{ __('Please select academic year and semester first') }}
                        @endif
                    </p>
                </div>
            </div>

            @if($academicYear && $semester)
            <form action="{{ route('admin.yearly-schedule.copy-all') }}" method="POST"
                  onsubmit="return confirm('{{ __('Copy all Global Schedule to year :year / semester :sem?', ['year' => $academicYear->year, 'sem' => $semester->semester_number]) }}')">
                @csrf
                <input type="hidden" name="academic_year_id" value="{{ $academicYearId }}">
                <input type="hidden" name="semester_id" value="{{ $semesterId }}">
                <button type="submit" class="btn-app">
                    <i class="fas fa-copy text-[10px]"></i> {{ __('Copy All from Global') }}
                </button>
            </form>
            @endif
        </div>

        {{-- Flash --}}
        @if(session('success'))
        <div id="flashMsg" class="fixed bottom-4 right-4 z-50 flex items-center p-4 bg-emerald-50 dark:bg-emerald-900/30 border-l-4 border-emerald-500 rounded-r-2xl shadow-xl transform transition-all duration-500 opacity-100">
            <i class="fas fa-check-circle text-emerald-500 mr-3 text-xl"></i>
            <span class="text-sm font-bold text-emerald-700 dark:text-emerald-400">{{ session('success') }}</span>
        </div>
        <script>setTimeout(() => { const el = document.getElementById('flashMsg'); if (el) { el.classList.remove('opacity-100'); el.classList.add('opacity-0', 'translate-y-4'); setTimeout(() => el.remove(), 500); } }, 2000);</script>
        @endif
        @if(session('error'))
        <div id="flashErr" class="fixed bottom-4 right-4 z-50 flex items-center p-4 bg-rose-50 dark:bg-rose-900/30 border-l-4 border-rose-500 rounded-r-2xl shadow-xl transform transition-all duration-500 opacity-100">
            <i class="fas fa-exclamation-circle text-rose-500 mr-3 text-xl"></i>
            <span class="text-sm font-bold text-rose-700 dark:text-rose-400">{{ session('error') }}</span>
        </div>
        <script>setTimeout(() => { const el = document.getElementById('flashErr'); if (el) { el.classList.remove('opacity-100'); el.classList.add('opacity-0', 'translate-y-4'); setTimeout(() => el.remove(), 500); } }, 3000);</script>
        @endif

        @if(!$academicYear || !$semester)
        <div class="bg-white dark:bg-[#242526] rounded-[2rem] shadow-sm border border-gray-100 dark:border-[#3a3b3c] p-12 text-center">
            <div class="w-16 h-16 rounded-2xl bg-amber-50 dark:bg-amber-900/20 flex items-center justify-center mx-auto mb-4">
                <i class="fas fa-calendar-alt text-2xl text-amber-400"></i>
            </div>
            <h3 class="text-lg font-bold text-gray-800 dark:text-gray-200 mb-2">{{ __('Academic year not selected') }}</h3>
            <p class="text-sm text-gray-500 dark:text-gray-400">{{ __('Please select academic year and semester from the top menu first') }}</p>
        </div>
        @elseif($educationLevels->isEmpty())
        <div class="bg-white dark:bg-[#242526] rounded-[2rem] shadow-sm border border-gray-100 dark:border-[#3a3b3c] p-12 text-center">
            <div class="w-16 h-16 rounded-2xl bg-gray-50 dark:bg-[#3a3b3c] flex items-center justify-center mx-auto mb-4">
                <i class="fas fa-school text-2xl text-gray-300 dark:text-gray-600"></i>
            </div>
            <h3 class="text-lg font-bold text-gray-800 dark:text-gray-200 mb-2">{{ __('No Education Levels Found') }}</h3>
            <p class="text-sm text-gray-500 dark:text-gray-400">{{ __('Please create education levels first before configuring schedules.') }}</p>
        </div>
        @else
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
            @foreach($educationLevels as $level)
                @php
                    $schedule = $scheduleMap[$level->id] ?? null;
                    $hasSchedule = $schedule !== null;
                    $globalSchedule = $globalScheduleMap[$level->id] ?? null;
                    $hasGlobal = $globalSchedule !== null;
                    $periods = 0;
                    $activeDays = 0;
                    if ($hasSchedule && is_array($schedule->day_configs)) {
                        foreach ($schedule->day_configs as $cfg) {
                            if (($cfg['periods'] ?? 0) > 0) $activeDays++;
                            $periods = max($periods, $cfg['periods'] ?? 0);
                        }
                    }
                @endphp
                <div class="bg-white dark:bg-[#242526] rounded-[2rem] shadow-sm border border-gray-100 dark:border-[#3a3b3c] overflow-hidden hover:shadow-lg transition-all duration-300 transform hover:-translate-y-1">

                    {{-- Top color bar --}}
                    <div class="h-1.5 {{ $hasSchedule ? 'bg-gradient-to-r from-indigo-500 via-purple-500 to-pink-500' : 'bg-gray-200 dark:bg-[#3a3b3c]' }}"></div>

                    <div class="p-6">
                        {{-- Icon + Title --}}
                        <div class="flex items-start space-x-4 mb-4">
                            <div class="w-12 h-12 rounded-xl {{ $hasSchedule ? 'bg-indigo-50 dark:bg-indigo-900/30 text-indigo-600 dark:text-indigo-400 border-indigo-100 dark:border-indigo-900/50' : 'bg-gray-50 dark:bg-[#3a3b3c] text-gray-400 dark:text-gray-500 border-gray-100 dark:border-[#3a3b3c]' }} border flex items-center justify-center flex-shrink-0">
                                <i class="fas fa-school text-lg"></i>
                            </div>
                            <div class="flex-1 min-w-0">
                                <h3 class="text-base font-extrabold text-gray-900 dark:text-white tracking-tight truncate">{{ $level->name_th }}</h3>
                                <p class="text-xs text-gray-400 dark:text-gray-500 font-medium truncate">{{ $level->name_en }}</p>
                            </div>
                        </div>

                        {{-- Stats --}}
                        @if($hasSchedule)
                        <div class="flex items-center gap-3 mb-4">
                            <div class="flex items-center px-2.5 py-1 rounded-lg bg-indigo-50 dark:bg-indigo-900/20">
                                <i class="fas fa-calendar-day text-[10px] text-indigo-500 mr-1.5"></i>
                                <span class="text-[11px] font-bold text-indigo-600 dark:text-indigo-400">{{ $activeDays }} {{ __('days') }}</span>
                            </div>
                            <div class="flex items-center px-2.5 py-1 rounded-lg bg-purple-50 dark:bg-purple-900/20">
                                <i class="fas fa-clock text-[10px] text-purple-500 mr-1.5"></i>
                                <span class="text-[11px] font-bold text-purple-600 dark:text-purple-400">{{ $periods }} {{ __('periods max') }}</span>
                            </div>
                        </div>
                        @endif

                        {{-- Status badge --}}
                        <div class="flex items-center justify-between mb-4">
                            @if($hasSchedule)
                                <span class="px-2.5 py-1 rounded-lg bg-emerald-50 dark:bg-emerald-900/20 text-[10px] font-bold text-emerald-600 dark:text-emerald-400 uppercase tracking-wider">
                                    <i class="fas fa-check-circle mr-1"></i>{{ __('Configured') }}
                                </span>
                            @else
                                <span class="px-2.5 py-1 rounded-lg bg-amber-50 dark:bg-amber-900/20 text-[10px] font-bold text-amber-600 dark:text-amber-400 uppercase tracking-wider">
                                    <i class="fas fa-exclamation-circle mr-1"></i>{{ __('Not Yet Configured') }}
                                </span>
                            @endif
                        </div>

                        {{-- Actions --}}
                        <div class="flex items-center gap-2">
                            <a href="{{ route('admin.yearly-schedule.edit', [$academicYearId, $semesterId, $level->id]) }}"
                               class="btn-app flex-1 text-center">
                                <i class="fas fa-edit text-[10px]"></i> {{ $hasSchedule ? __('Edit') : __('Configure') }}
                            </a>
                            @if(!$hasSchedule && $hasGlobal)
                            <form action="{{ route('admin.yearly-schedule.copy') }}" method="POST" class="flex-1">
                                @csrf
                                <input type="hidden" name="academic_year_id" value="{{ $academicYearId }}">
                                <input type="hidden" name="semester_id" value="{{ $semesterId }}">
                                <input type="hidden" name="education_level_id" value="{{ $level->id }}">
                                <button type="submit" class="btn-app w-full" style="background:#6366f1;border-color:#6366f1;">
                                    <i class="fas fa-copy text-[10px]"></i> {{ __('Copy from Global') }}
                                </button>
                            </form>
                            @endif
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
        @endif
    </div>
</div>
@endsection
