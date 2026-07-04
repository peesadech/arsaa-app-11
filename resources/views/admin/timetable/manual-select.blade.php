<x-layouts.admin :header="__('Manual Timetable Scheduling')" :subheader="__('Select classroom to schedule')">
    <x-slot name="actions">
        <x-button variant="secondary" icon="arrow-left" :href="route('admin.timetable.index')">{{ __('Back') }}</x-button>
    </x-slot>

    {{-- Classroom Cards --}}
    @if($classroomGroups->isEmpty())
    <x-card>
        <div class="py-12 text-center">
            <div class="text-slate-300 mb-4"><x-icon name="calendar" class="h-14 w-14 mx-auto" /></div>
            <p class="text-slate-500 text-lg font-medium">{{ __('No opened courses found') }}</p>
            <p class="text-slate-400 text-sm mt-1">{{ __('Please add opened courses first') }}</p>
        </div>
    </x-card>
    @else
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
        @foreach($classroomGroups as $group)
        @php
            $key = $group['grade_id'] . '_' . $group['classroom_id'];
            $entryCount = $entryCounts[$key]->entry_count ?? 0;
            $totalPeriods = $group['total_periods'];
            $progress = $totalPeriods > 0 ? min(100, round(($entryCount / $totalPeriods) * 100)) : 0;
            $isComplete = $entryCount >= $totalPeriods && $totalPeriods > 0;
        @endphp
        <a href="{{ route('admin.timetable.manual.editor', [$group['grade_id'], $group['classroom_id']]) }}"
           class="block bg-white rounded-2xl shadow-card border-2 {{ $isComplete ? 'border-emerald-200' : 'border-slate-100' }} p-5 hover:shadow-soft hover:border-brand-200 transition-all duration-200 group">

            <div class="flex items-start justify-between mb-3">
                <div>
                    <h3 class="text-lg font-semibold text-slate-800 group-hover:text-brand-600 transition-colors">
                        {{ $group['grade_name'] }} / {{ $group['classroom_name'] }}
                    </h3>
                    <p class="text-xs text-slate-400 mt-0.5">{{ $group['education_level'] }}</p>
                </div>
                @if($isComplete)
                <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full bg-emerald-100 text-emerald-700 text-[11px] font-bold">
                    <x-icon name="check" class="h-3 w-3" /> {{ __('Complete') }}
                </span>
                @endif
            </div>

            <div class="flex items-center gap-4 text-sm mb-3">
                <div class="flex items-center gap-1.5 text-slate-500">
                    <x-icon name="book" class="h-3.5 w-3.5" />
                    <span>{{ $group['course_count'] }} {{ __('courses') }}</span>
                </div>
                <div class="flex items-center gap-1.5 text-slate-500">
                    <x-icon name="calendar" class="h-3.5 w-3.5" />
                    <span>{{ $entryCount }}/{{ $totalPeriods }} {{ __('periods') }}</span>
                </div>
            </div>

            {{-- Progress bar --}}
            <div class="w-full bg-slate-100 rounded-full h-2">
                <div class="h-2 rounded-full transition-all duration-500 {{ $isComplete ? 'bg-emerald-500' : ($progress > 0 ? 'bg-brand-500' : 'bg-slate-300') }}"
                     style="width: {{ $progress }}%"></div>
            </div>
            <p class="text-[10px] text-slate-400 mt-1 text-right">{{ $progress }}%</p>
        </a>
        @endforeach
    </div>
    @endif
</x-layouts.admin>
