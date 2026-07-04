<x-layouts.admin :header="__('Global Schedule')" :subheader="__('Select an education level to configure its schedule')">
    <x-slot name="actions">
        <x-button variant="secondary" icon="arrow-left" :href="route('admin.dashboard')">{{ __('Back') }}</x-button>
    </x-slot>

    {{-- Flash --}}
    @if(session('success'))
    <div id="flashMsg" class="fixed bottom-4 right-4 z-50 flex items-center p-4 bg-emerald-50 border-l-4 border-emerald-500 rounded-r-xl shadow-soft transform transition-all duration-500 opacity-100">
        <i class="fas fa-check-circle text-emerald-500 mr-3 text-xl"></i>
        <span class="text-sm font-bold text-emerald-700">{{ session('success') }}</span>
    </div>
    <script>
        setTimeout(() => {
            const el = document.getElementById('flashMsg');
            if (el) {
                el.classList.remove('opacity-100');
                el.classList.add('opacity-0', 'translate-y-4');
                setTimeout(() => el.remove(), 500);
            }
        }, 2000);
    </script>
    @endif

    @if($educationLevels->isEmpty())
    <x-card>
        <div class="p-12 text-center">
            <div class="w-16 h-16 rounded-2xl bg-slate-50 flex items-center justify-center mx-auto mb-4">
                <i class="fas fa-school text-2xl text-slate-300"></i>
            </div>
            <h3 class="text-lg font-semibold text-slate-800 mb-2">{{ __('No Education Levels Found') }}</h3>
            <p class="text-sm text-slate-500 mb-6">{{ __('Please create education levels first before configuring schedules.') }}</p>
            <x-button icon="plus" :href="route('admin.education-levels.create')">{{ __('Create Education Level') }}</x-button>
        </div>
    </x-card>
    @else
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
        @foreach($educationLevels as $level)
            @php
                $schedule = $scheduleMap[$level->id] ?? null;
                $hasSchedule = $schedule !== null;
                $periods = 0;
                $activeDays = 0;
                if ($hasSchedule && is_array($schedule->day_configs)) {
                    foreach ($schedule->day_configs as $cfg) {
                        if (($cfg['periods'] ?? 0) > 0) $activeDays++;
                        $periods = max($periods, $cfg['periods'] ?? 0);
                    }
                }
            @endphp
            <a href="{{ route('admin.global-schedule.edit', $level->id) }}"
               class="group block card overflow-hidden hover:shadow-soft hover:border-brand-200 transition-all duration-300">

                {{-- Top color bar --}}
                <div class="h-1.5 {{ $hasSchedule ? 'bg-brand-600' : 'bg-slate-200' }}"></div>

                <div class="p-6">
                    {{-- Icon + Title --}}
                    <div class="flex items-start gap-4 mb-4">
                        <div class="w-12 h-12 rounded-xl border {{ $hasSchedule ? 'bg-brand-50 text-brand-600 border-brand-100' : 'bg-slate-50 text-slate-400 border-slate-100' }} flex items-center justify-center flex-shrink-0">
                            <i class="fas fa-school text-lg"></i>
                        </div>
                        <div class="flex-1 min-w-0">
                            <h3 class="text-base font-semibold text-slate-900 truncate group-hover:text-brand-600 transition-colors">{{ $level->name_th }}</h3>
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
                    <div class="flex items-center justify-between">
                        @if($hasSchedule)
                            <span class="badge-green uppercase"><i class="fas fa-check-circle"></i>{{ __('Configured') }}</span>
                        @else
                            <span class="badge-amber uppercase"><i class="fas fa-exclamation-circle"></i>{{ __('Not Configured') }}</span>
                        @endif
                        <span class="text-xs font-bold text-slate-300 group-hover:text-brand-400 transition-colors">
                            <i class="fas fa-arrow-right"></i>
                        </span>
                    </div>
                </div>
            </a>
        @endforeach
    </div>
    @endif
</x-layouts.admin>
