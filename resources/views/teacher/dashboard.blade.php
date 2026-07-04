<x-layouts.admin
    :header="__('Teacher Dashboard')"
    :subheader="$teacher->name . ' — ' . __('Academic Year') . ' ' . ($academicYear->year ?? '?') . ' / ' . __('Semester') . ' ' . ($semester->semester_number ?? '?')">

    <x-slot name="actions">
        <x-button icon="clipboard" :href="route('teacher.scores.index')">{{ __('Record Scores') }}</x-button>
    </x-slot>

    <div class="space-y-6">

        {{-- Stats --}}
        <div class="grid grid-cols-2 lg:grid-cols-4 gap-4">
            @foreach([
                ['icon' => 'building', 'color' => 'text-brand-600 bg-brand-50', 'value' => $stats['rooms'], 'label' => __('Classrooms taught')],
                ['icon' => 'book', 'color' => 'text-emerald-600 bg-emerald-50', 'value' => $stats['courses'], 'label' => __('Courses taught')],
                ['icon' => 'calendar', 'color' => 'text-amber-600 bg-amber-50', 'value' => $stats['periods_per_week'], 'label' => __('Periods / week')],
                ['icon' => 'clipboard', 'color' => 'text-violet-600 bg-violet-50', 'value' => $stats['scores_recorded'], 'label' => __('Scores recorded')],
            ] as $stat)
            <x-card>
                <div class="flex items-center justify-between">
                    <span class="text-3xl font-semibold text-slate-900">{{ $stat['value'] }}</span>
                    <span class="h-10 w-10 rounded-xl flex items-center justify-center {{ $stat['color'] }}">
                        <x-icon :name="$stat['icon']" class="h-5 w-5" />
                    </span>
                </div>
                <p class="text-xs font-medium text-slate-500 uppercase tracking-wide mt-2">{{ $stat['label'] }}</p>
            </x-card>
            @endforeach
        </div>

        {{-- Rooms + courses --}}
        <x-card :title="__('My classrooms and courses')">
            @if($rooms->isEmpty())
            <div class="py-10 text-center text-slate-400">
                <x-icon name="book" class="h-8 w-8 mx-auto mb-3" />
                <p class="text-sm font-medium">{{ __('You have no courses in the current term') }}</p>
            </div>
            @else
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                @foreach($rooms as $room)
                <div class="p-5 rounded-xl border border-slate-200 bg-slate-50">
                    <div class="flex items-center justify-between mb-3">
                        <div class="text-sm font-semibold text-slate-900 flex items-center gap-1.5">
                            <x-icon name="building" class="h-4 w-4 text-brand-600" />
                            {{ $room['grade']->name_th ?? '' }} / {{ $room['classroom']->name ?? '' }}
                        </div>
                        <span class="text-xs text-slate-500 font-medium flex items-center gap-1">
                            <x-icon name="users" class="h-4 w-4" />{{ $room['student_count'] }} {{ __('students') }}
                        </span>
                    </div>
                    <div class="space-y-2">
                        @foreach($room['courses'] as $oc)
                        <a href="{{ route('teacher.scores.entry', $oc->id) }}"
                           class="flex items-center justify-between p-3 rounded-lg bg-white border border-slate-200 hover:border-brand-300 hover:shadow-soft transition-all">
                            <div>
                                <div class="text-sm font-medium text-slate-800">{{ $oc->course->name ?? '?' }}</div>
                                @if($oc->course?->subjectGroup)<div class="text-xs text-slate-400">{{ $oc->course->subjectGroup->name_th }}</div>@endif
                            </div>
                            <div class="text-right">
                                <span class="text-xs font-semibold flex items-center gap-1 justify-end {{ $oc->scored_count >= $oc->student_count && $oc->student_count > 0 ? 'text-emerald-600' : ($oc->scored_count > 0 ? 'text-amber-600' : 'text-slate-300') }}">
                                    <x-icon name="clipboard" class="h-4 w-4" />{{ $oc->scored_count }}/{{ $oc->student_count }}
                                </span>
                                <div class="text-xs text-slate-400">{{ __('scores recorded') }}</div>
                            </div>
                        </a>
                        @endforeach
                    </div>
                </div>
                @endforeach
            </div>
            @endif
        </x-card>

        {{-- Weekly schedule --}}
        @php
            $dayNames = [1=>__('Monday'), 2=>__('Tuesday'), 3=>__('Wednesday'), 4=>__('Thursday'), 5=>__('Friday'), 6=>__('Saturday'), 7=>__('Sunday')];
            $colors = ['bg-brand-50 border-brand-200 text-brand-800', 'bg-emerald-50 border-emerald-200 text-emerald-800', 'bg-amber-50 border-amber-200 text-amber-800', 'bg-rose-50 border-rose-200 text-rose-800', 'bg-violet-50 border-violet-200 text-violet-800', 'bg-teal-50 border-teal-200 text-teal-800'];
            $schedule = $yearlySchedules->first();
            $dayConfigs = $schedule ? ($schedule->day_configs ?? []) : [];
            $teachingDays = $schedule ? collect($schedule->teaching_days ?? [])->filter(fn($d) => ($dayConfigs[(string)$d]['periods'] ?? 0) > 0)->values()->all() : [];
            $maxPeriods = collect($dayConfigs)->max('periods') ?? 0;
        @endphp
        @if($schedule && $entries->isNotEmpty())
        <x-card :title="__('My teaching schedule')">
            <div class="overflow-x-auto">
                <table class="w-full border-collapse" style="min-width:600px">
                    <thead>
                        <tr>
                            <th class="p-2 text-xs font-medium text-slate-500 border border-slate-200 bg-slate-50 w-20">{{ __('Period') }}</th>
                            @foreach($teachingDays as $d)
                            <th class="p-2 text-xs font-medium text-slate-700 border border-slate-200 bg-slate-50">{{ $dayNames[(int)$d] ?? __('Day').' '.$d }}</th>
                            @endforeach
                        </tr>
                    </thead>
                    <tbody>
                    @for($p = 1; $p <= $maxPeriods; $p++)
                        <tr>
                            <td class="p-2 text-center text-xs font-medium text-slate-600 border border-slate-200 bg-slate-50">{{ __('Period') }} {{ $p }}</td>
                            @foreach($teachingDays as $d)
                                @php $entry = $entries->first(fn($e) => $e->day == (int)$d && $e->period == $p); @endphp
                                @if($entry)
                                @php $colorIdx = ($entry->openedCourse->course->subject_group_id ?? 0) % count($colors); @endphp
                                <td class="border border-slate-200 p-1">
                                    <div class="p-2 rounded-lg border {{ $colors[$colorIdx] }} text-xs space-y-0.5">
                                        <div class="font-semibold truncate">{{ $entry->openedCourse->course->name ?? '' }}</div>
                                        <div class="text-[10px] opacity-75 truncate">{{ $entry->openedCourse->grade->name_th ?? '' }} / {{ $entry->openedCourse->classroom->name ?? '' }}</div>
                                        <div class="text-[10px] opacity-60 truncate">{{ $entry->room->room_number ?? '-' }}</div>
                                    </div>
                                </td>
                                @else
                                <td class="border border-slate-200 p-1"><div class="h-12"></div></td>
                                @endif
                            @endforeach
                        </tr>
                    @endfor
                    </tbody>
                </table>
            </div>
        </x-card>
        @endif

    </div>
</x-layouts.admin>
