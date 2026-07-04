@php
    $subheader = ($academicYear && $semester)
        ? __('Academic Year :year', ['year' => $academicYear->year]) . ' / ' . __('Semester Number') . ' ' . $semester->semester_number
        : __('Please select academic year and semester first');
@endphp

<x-layouts.admin :header="__('Yearly/Semester Schedule')" :subheader="$subheader">
    <x-slot name="actions">
        <x-button variant="secondary" icon="arrow-left" :href="route('admin.dashboard')">{{ __('Back') }}</x-button>
        @if($academicYear && $semester)
        <form action="{{ route('admin.yearly-schedule.copy-all') }}" method="POST"
              onsubmit="return confirm('{{ __('Copy all Global Schedule to year :year / semester :sem?', ['year' => $academicYear->year, 'sem' => $semester->semester_number]) }}')">
            @csrf
            <input type="hidden" name="academic_year_id" value="{{ $academicYearId }}">
            <input type="hidden" name="semester_id" value="{{ $semesterId }}">
            <button type="submit" class="btn-primary">
                <i class="fas fa-copy text-xs"></i> {{ __('Copy All from Global') }}
            </button>
        </form>
        @endif
    </x-slot>

    {{-- Flash --}}
    @if(session('success'))
    <div id="flashMsg" class="fixed bottom-4 right-4 z-50 flex items-center p-4 bg-emerald-50 border-l-4 border-emerald-500 rounded-r-xl shadow-soft transform transition-all duration-500 opacity-100">
        <i class="fas fa-check-circle text-emerald-500 mr-3 text-xl"></i>
        <span class="text-sm font-bold text-emerald-700">{{ session('success') }}</span>
    </div>
    <script>setTimeout(() => { const el = document.getElementById('flashMsg'); if (el) { el.classList.remove('opacity-100'); el.classList.add('opacity-0', 'translate-y-4'); setTimeout(() => el.remove(), 500); } }, 2000);</script>
    @endif
    @if(session('error'))
    <div id="flashErr" class="fixed bottom-4 right-4 z-50 flex items-center p-4 bg-red-50 border-l-4 border-red-500 rounded-r-xl shadow-soft transform transition-all duration-500 opacity-100">
        <i class="fas fa-exclamation-circle text-red-500 mr-3 text-xl"></i>
        <span class="text-sm font-bold text-red-700">{{ session('error') }}</span>
    </div>
    <script>setTimeout(() => { const el = document.getElementById('flashErr'); if (el) { el.classList.remove('opacity-100'); el.classList.add('opacity-0', 'translate-y-4'); setTimeout(() => el.remove(), 500); } }, 3000);</script>
    @endif

    @if(!$academicYear || !$semester)
    <x-card>
        <div class="p-12 text-center">
            <div class="w-16 h-16 rounded-2xl bg-amber-50 flex items-center justify-center mx-auto mb-4">
                <i class="fas fa-calendar-alt text-2xl text-amber-400"></i>
            </div>
            <h3 class="text-lg font-semibold text-slate-800 mb-2">{{ __('Academic year not selected') }}</h3>
            <p class="text-sm text-slate-500">{{ __('Please select academic year and semester from the top menu first') }}</p>
        </div>
    </x-card>
    @elseif($educationLevels->isEmpty())
    <x-card>
        <div class="p-12 text-center">
            <div class="w-16 h-16 rounded-2xl bg-slate-50 flex items-center justify-center mx-auto mb-4">
                <i class="fas fa-school text-2xl text-slate-300"></i>
            </div>
            <h3 class="text-lg font-semibold text-slate-800 mb-2">{{ __('No Education Levels Found') }}</h3>
            <p class="text-sm text-slate-500">{{ __('Please create education levels first before configuring schedules.') }}</p>
        </div>
    </x-card>
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
            <div class="card overflow-hidden hover:shadow-soft transition-all duration-300">

                {{-- Top color bar --}}
                <div class="h-1.5 {{ $hasSchedule ? 'bg-brand-600' : 'bg-slate-200' }}"></div>

                <div class="p-6">
                    {{-- Icon + Title --}}
                    <div class="flex items-start gap-4 mb-4">
                        <div class="w-12 h-12 rounded-xl border {{ $hasSchedule ? 'bg-brand-50 text-brand-600 border-brand-100' : 'bg-slate-50 text-slate-400 border-slate-100' }} flex items-center justify-center flex-shrink-0">
                            <i class="fas fa-school text-lg"></i>
                        </div>
                        <div class="flex-1 min-w-0">
                            <h3 class="text-base font-semibold text-slate-900 truncate">{{ $level->name_th }}</h3>
                            <p class="text-xs text-slate-400 font-medium truncate">{{ $level->name_en }}</p>
                        </div>
                    </div>

                    {{-- Stats --}}
                    @if($hasSchedule)
                    <div class="flex items-center gap-2 mb-4">
                        <span class="badge-blue"><i class="fas fa-calendar-day text-[10px]"></i>{{ $activeDays }} {{ __('days') }}</span>
                        <span class="badge-gray"><i class="fas fa-clock text-[10px]"></i>{{ $periods }} {{ __('periods max') }}</span>
                    </div>
                    @endif

                    {{-- Status badge --}}
                    <div class="flex items-center justify-between mb-4">
                        @if($hasSchedule)
                            <span class="badge-green uppercase"><i class="fas fa-check-circle"></i>{{ __('Configured') }}</span>
                        @else
                            <span class="badge-amber uppercase"><i class="fas fa-exclamation-circle"></i>{{ __('Not Yet Configured') }}</span>
                        @endif
                    </div>

                    {{-- Actions --}}
                    <div class="flex items-center gap-2">
                        <a href="{{ route('admin.yearly-schedule.edit', [$academicYearId, $semesterId, $level->id]) }}"
                           class="btn-secondary flex-1 justify-center">
                            <i class="fas fa-edit text-xs"></i> {{ $hasSchedule ? __('Edit') : __('Configure') }}
                        </a>
                        @if(!$hasSchedule && $hasGlobal)
                        <form action="{{ route('admin.yearly-schedule.copy') }}" method="POST" class="flex-1">
                            @csrf
                            <input type="hidden" name="academic_year_id" value="{{ $academicYearId }}">
                            <input type="hidden" name="semester_id" value="{{ $semesterId }}">
                            <input type="hidden" name="education_level_id" value="{{ $level->id }}">
                            <button type="submit" class="btn-primary w-full">
                                <i class="fas fa-copy text-xs"></i> {{ __('Copy from Global') }}
                            </button>
                        </form>
                        @endif
                    </div>
                </div>
            </div>
        @endforeach
    </div>
    @endif
</x-layouts.admin>
