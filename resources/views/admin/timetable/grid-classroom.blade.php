<x-layouts.admin :header="__('Timetable') . ' ' . $grade->name_th . ' / ' . $classroom->name">
    <x-slot name="actions">
        <x-button variant="secondary" icon="arrow-left" :href="route('admin.timetable.index')">{{ __('Back') }}</x-button>
    </x-slot>

    @if(!$schedule)
    <div class="p-4 bg-amber-50 border border-amber-200 rounded-xl text-amber-700 text-sm">
        {{ __('No schedule data found for this level') }}
    </div>
    @else
    @php
        $dayConfigs = $schedule->day_configs ?? [];
        $teachingDays = collect($schedule->teaching_days ?? [])->filter(fn($d) => ($dayConfigs[(string)$d]['periods'] ?? 0) > 0)->values()->all();
        $dayNames = [1=>__('Monday'), 2=>__('Tuesday'), 3=>__('Wednesday'), 4=>__('Thursday'), 5=>__('Friday'), 6=>__('Saturday'), 7=>__('Sunday')];
        $maxPeriods = collect($dayConfigs)->max('periods') ?? 0;
        $colors = ['bg-indigo-100 border-indigo-300 text-indigo-800', 'bg-emerald-100 border-emerald-300 text-emerald-800', 'bg-amber-100 border-amber-300 text-amber-800', 'bg-rose-100 border-rose-300 text-rose-800', 'bg-purple-100 border-purple-300 text-purple-800', 'bg-teal-100 border-teal-300 text-teal-800'];
    @endphp

    <div class="card p-4 overflow-x-auto">
        <table class="w-full border-collapse" style="min-width:600px">
            <thead>
                <tr>
                    <th class="p-2 text-xs font-semibold text-slate-500 border border-slate-200 bg-slate-50 w-20">{{ __('Period') }}</th>
                    @foreach($teachingDays as $d)
                    <th class="p-2 text-xs font-semibold text-slate-700 border border-slate-200 bg-slate-50">{{ $dayNames[(int)$d] ?? __('Day').' '.$d }}</th>
                    @endforeach
                </tr>
            </thead>
            <tbody>
            @for($p = 1; $p <= $maxPeriods; $p++)
                <tr>
                    <td class="p-2 text-center text-xs font-medium text-slate-600 border border-slate-200 bg-slate-50">{{ __('Period') }} {{ $p }}</td>
                    @foreach($teachingDays as $d)
                        @php
                            $dc = $dayConfigs[$d] ?? null;
                            $entry = $entries->first(fn($e) => $e->day == (int)$d && $e->period == $p);
                        @endphp
                        @if(!$dc || $p > ($dc['periods'] ?? 0))
                        <td class="border border-slate-200 bg-slate-100"></td>
                        @elseif($entry)
                        @php $colorIdx = ($entry->openedCourse->course->subject_group_id ?? 0) % count($colors); @endphp
                        <td class="border border-slate-200 p-1">
                            <div class="p-2 rounded-xl border {{ $colors[$colorIdx] }} text-xs space-y-0.5">
                                <div class="font-bold truncate">{{ $entry->openedCourse->course->name ?? '' }}</div>
                                <div class="text-[10px] opacity-75 truncate">{{ $entry->teacher->name ?? '-' }}</div>
                                <div class="text-[10px] opacity-60 truncate">{{ $entry->room->room_number ?? '-' }}</div>
                                @if($entry->is_locked)<i class="fas fa-lock text-amber-500 text-[9px]"></i>@endif
                            </div>
                        </td>
                        @else
                        <td class="border border-slate-200 p-1"><div class="h-12"></div></td>
                        @endif
                    @endforeach
                </tr>
                @php $breaks = $dayConfigs[$teachingDays[0] ?? '1']['breaks'] ?? []; @endphp
                @if(isset($breaks[(string)$p]))
                <tr>
                    <td colspan="{{ count($teachingDays) + 1 }}" class="py-1 text-center text-[10px] text-amber-600 bg-amber-50 border border-slate-200">
                        {{ __('Break') }} {{ $breaks[(string)$p] }} {{ __('minutes') }}
                    </td>
                </tr>
                @endif
            @endfor
            </tbody>
        </table>
    </div>
    @endif
</x-layouts.admin>
