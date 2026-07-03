@extends('layouts.app')

@section('content')
<div class="min-h-screen bg-gray-50 dark:bg-[#18191a] py-10 px-4 sm:px-6 lg:px-8 transition-colors duration-300">
    <div class="max-w-6xl mx-auto">

        {{-- Header --}}
        <div class="flex items-center space-x-4 mb-8">
            <a href="{{ route('admin.teacher-term-status.edit', $teacher->id) }}"
               class="group flex items-center justify-center w-10 h-10 rounded-full bg-white dark:bg-[#242526] shadow-sm border border-gray-200 dark:border-[#3a3b3c] text-gray-500 dark:text-gray-400 hover:text-indigo-600 dark:hover:text-indigo-400 hover:border-indigo-300 transition-all duration-200">
                <i class="fas fa-arrow-left group-hover:-translate-x-0.5 transition-transform"></i>
            </a>
            <div>
                <h1 class="text-2xl font-extrabold text-gray-900 dark:text-white tracking-tight">{{ __('Teacher Substitution') }}</h1>
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
        @if(session('error'))
        <div class="mb-6 p-4 bg-rose-50 dark:bg-rose-900/20 border border-rose-200 dark:border-rose-800 rounded-2xl text-rose-700 dark:text-rose-300 text-sm">
            {{ session('error') }}
        </div>
        @endif
        @if(session('substitution_errors'))
        <div class="mb-6 p-4 bg-amber-50 dark:bg-amber-900/20 border border-amber-200 dark:border-amber-800 rounded-2xl text-amber-700 dark:text-amber-300 text-sm space-y-1">
            <div class="font-bold">{{ __('Some periods could not be reassigned') }}:</div>
            @foreach(session('substitution_errors') as $entryId => $message)
            <div>• {{ $message }}</div>
            @endforeach
        </div>
        @endif

        {{-- Teacher Info --}}
        <div class="bg-white dark:bg-[#242526] rounded-[2rem] shadow-sm border border-gray-100 dark:border-[#3a3b3c] p-6 mb-6">
            <div class="flex flex-wrap items-center gap-4">
                <div class="w-14 h-14 rounded-2xl overflow-hidden border-2 border-white shadow-sm">
                    <img src="{{ $teacher->image_path ? asset($teacher->image_path) : 'https://ui-avatars.com/api/?name=' . urlencode($teacher->name) . '&color=7F9CF5&background=EBF4FF' }}" class="w-full h-full object-cover" alt="">
                </div>
                <div class="flex-1 min-w-[200px]">
                    <h3 class="text-lg font-bold text-gray-800 dark:text-white">{{ $teacher->name }}</h3>
                    <p class="text-xs text-gray-400">{{ $teacher->email }}</p>
                    @php
                        $statusColors = [
                            'available' => 'bg-emerald-50 text-emerald-600',
                            'unavailable' => 'bg-rose-50 text-rose-600',
                            'leave' => 'bg-amber-50 text-amber-600',
                            'partial' => 'bg-blue-50 text-blue-600',
                            'transferred' => 'bg-purple-50 text-purple-600',
                            'resigned_term' => 'bg-gray-100 text-gray-600',
                        ];
                        $termStatusValue = $termStatus->status ?? null;
                    @endphp
                    @if($termStatusValue)
                    <span class="inline-flex mt-1 px-2 py-0.5 rounded-lg {{ $statusColors[$termStatusValue] ?? 'bg-gray-50 text-gray-500' }} text-[10px] font-bold uppercase">
                        {{ __(ucfirst(str_replace('_', ' ', $termStatusValue))) }}
                    </span>
                    @endif
                </div>
                <div class="text-right">
                    <div class="text-3xl font-extrabold {{ $entries->count() > 0 ? 'text-rose-500' : 'text-emerald-500' }}">{{ $entries->count() }}</div>
                    <div class="text-xs text-gray-400 font-medium">{{ __('Scheduled periods') }}</div>
                </div>
            </div>
        </div>

        @php
            $dayNames = [1=>__('Monday'), 2=>__('Tuesday'), 3=>__('Wednesday'), 4=>__('Thursday'), 5=>__('Friday'), 6=>__('Saturday'), 7=>__('Sunday')];
            $schedule = $yearlySchedules->first();
            $dayConfigs = $schedule ? ($schedule->day_configs ?? []) : [];
            $teachingDays = $schedule ? collect($schedule->teaching_days ?? [])->filter(fn($d) => ($dayConfigs[(string)$d]['periods'] ?? 0) > 0)->values()->all() : [];
            $maxPeriods = collect($dayConfigs)->max('periods') ?? 0;
        @endphp

        @if($entries->isEmpty())
        <div class="bg-white dark:bg-[#242526] rounded-[2rem] shadow-sm border border-gray-100 dark:border-[#3a3b3c] p-10 text-center text-gray-400 mb-6">
            <i class="fas fa-calendar-check text-3xl mb-3 text-emerald-400"></i>
            <p class="text-sm font-medium">{{ __('This teacher has no periods in the active timetable') }}</p>
        </div>
        @else

        {{-- Schedule Grid --}}
        @if($schedule)
        <div class="bg-white dark:bg-[#242526] rounded-[2rem] shadow-sm border border-gray-100 dark:border-[#3a3b3c] p-4 overflow-x-auto mb-6">
            <table class="w-full border-collapse" style="min-width:600px">
                <thead>
                    <tr>
                        <th class="p-2 text-xs font-semibold text-gray-500 dark:text-gray-400 border border-gray-200 dark:border-[#3a3b3c] bg-gray-50 dark:bg-[#3a3b3c] w-20">{{ __('Period') }}</th>
                        @foreach($teachingDays as $d)
                        <th class="p-2 text-xs font-semibold text-gray-700 dark:text-gray-300 border border-gray-200 dark:border-[#3a3b3c] bg-gray-50 dark:bg-[#3a3b3c]">{{ $dayNames[(int)$d] ?? __('Day').' '.$d }}</th>
                        @endforeach
                    </tr>
                </thead>
                <tbody>
                @for($p = 1; $p <= $maxPeriods; $p++)
                    <tr>
                        <td class="p-2 text-center text-xs font-medium text-gray-600 dark:text-gray-400 border border-gray-200 dark:border-[#3a3b3c] bg-gray-50 dark:bg-[#3a3b3c]">{{ __('Period') }} {{ $p }}</td>
                        @foreach($teachingDays as $d)
                            @php $entry = $entries->first(fn($e) => $e->day == (int)$d && $e->period == $p); @endphp
                            @if($entry)
                            <td class="border border-gray-200 dark:border-[#3a3b3c] p-1">
                                <div class="p-2 rounded-xl border bg-rose-50 border-rose-200 text-rose-800 dark:bg-rose-900/20 dark:border-rose-800 dark:text-rose-300 text-xs space-y-0.5">
                                    <div class="font-bold truncate">{{ $entry->openedCourse->course->name ?? '' }}</div>
                                    <div class="text-[10px] opacity-75 truncate">{{ $entry->openedCourse->grade->name_th ?? '' }} / {{ $entry->openedCourse->classroom->name ?? '' }}</div>
                                    <div class="text-[10px] opacity-60 truncate">{{ $entry->room->room_number ?? '-' }}</div>
                                </div>
                            </td>
                            @else
                            <td class="border border-gray-200 dark:border-[#3a3b3c] p-1"><div class="h-12"></div></td>
                            @endif
                        @endforeach
                    </tr>
                @endfor
                </tbody>
            </table>
        </div>
        @endif

        {{-- Substitution Form --}}
        <form action="{{ route('admin.teacher-substitution.apply', $teacher->id) }}" method="POST">
            @csrf
            <div class="bg-white dark:bg-[#242526] rounded-[2rem] shadow-sm border border-gray-100 dark:border-[#3a3b3c] p-6 mb-6">
                <div class="flex flex-wrap items-center justify-between gap-3 mb-4">
                    <h2 class="text-sm font-bold text-gray-400 dark:text-gray-500 uppercase tracking-widest">{{ __('Assign substitute per period') }}</h2>
                    <div class="flex items-center gap-2">
                        <span class="text-xs text-gray-400">{{ __('Set all to') }}:</span>
                        <button type="button" onclick="setAll('unassign')" class="btn-app"><i class="fas fa-ban text-[10px]"></i> {{ __('Unassign (no teaching)') }}</button>
                        <button type="button" onclick="setAll('keep')" class="btn-app"><i class="fas fa-undo text-[10px]"></i> {{ __('Keep current') }}</button>
                    </div>
                </div>

                <div class="overflow-x-auto">
                    <table class="w-full" style="min-width:700px">
                        <thead>
                            <tr class="text-left text-[10px] font-bold text-gray-400 uppercase tracking-widest border-b border-gray-100 dark:border-[#3a3b3c]">
                                <th class="py-2 pr-3">{{ __('Day') }} / {{ __('Period') }}</th>
                                <th class="py-2 pr-3">{{ __('Course') }}</th>
                                <th class="py-2 pr-3">{{ __('Classroom') }}</th>
                                <th class="py-2 pr-3">{{ __('Room') }}</th>
                                <th class="py-2">{{ __('Action') }}</th>
                            </tr>
                        </thead>
                        <tbody>
                        @foreach($entries as $entry)
                            @php $entryCandidates = $candidates[$entry->id] ?? []; @endphp
                            <tr class="border-b border-gray-50 dark:border-[#3a3b3c]/50">
                                <td class="py-3 pr-3 text-xs font-bold text-gray-700 dark:text-gray-300 whitespace-nowrap">
                                    {{ $dayNames[$entry->day] ?? __('Day').' '.$entry->day }} — {{ __('Period') }} {{ $entry->period }}
                                </td>
                                <td class="py-3 pr-3 text-xs text-gray-600 dark:text-gray-400">{{ $entry->openedCourse->course->name ?? '?' }}</td>
                                <td class="py-3 pr-3 text-xs text-gray-600 dark:text-gray-400 whitespace-nowrap">
                                    {{ $entry->openedCourse->grade->name_th ?? '' }} / {{ $entry->openedCourse->classroom->name ?? '' }}
                                </td>
                                <td class="py-3 pr-3 text-xs text-gray-500 dark:text-gray-500">{{ $entry->room->room_number ?? '-' }}</td>
                                <td class="py-3">
                                    <select name="items[{{ $entry->id }}]" data-entry-select
                                            class="w-full max-w-xs px-3 py-2 bg-gray-50 dark:bg-[#3a3b3c] border-2 border-transparent rounded-xl text-xs text-gray-800 dark:text-white focus:border-indigo-500 focus:outline-none transition-all">
                                        <option value="keep">— {{ __('Keep current') }} —</option>
                                        <option value="unassign">{{ __('Unassign (no teaching)') }}</option>
                                        @forelse($entryCandidates as $candidate)
                                        <option value="sub:{{ $candidate['teacher_id'] }}"
                                                {{ $candidate['valid'] ? '' : 'disabled' }}
                                                title="{{ implode(', ', $candidate['violations']) }}">
                                            {{ $candidate['valid'] ? (empty($candidate['violations']) ? '✓' : '⚠') : '✗' }} {{ $candidate['name'] }}
                                            @if(!empty($candidate['violations'])) ({{ implode(', ', $candidate['violations']) }}) @endif
                                        </option>
                                        @empty
                                        <option value="" disabled>{{ __('No qualified teacher for this course') }}</option>
                                        @endforelse
                                    </select>
                                </td>
                            </tr>
                        @endforeach
                        </tbody>
                    </table>
                </div>

                {{-- Reason --}}
                <div class="mt-5">
                    <label class="block text-sm font-medium text-gray-600 dark:text-gray-400 mb-1">{{ __('Reason') }}</label>
                    <textarea name="reason" rows="2" maxlength="500" placeholder="{{ __('e.g. Teacher resigned, replaced by new teacher') }}"
                              class="w-full px-4 py-3 bg-gray-50 dark:bg-[#3a3b3c] border-2 border-transparent rounded-2xl text-gray-800 dark:text-white text-sm focus:border-indigo-500 focus:outline-none transition-all"></textarea>
                </div>

                <div class="mt-5 flex justify-end">
                    <button type="submit" class="btn-app">
                        <i class="fas fa-exchange-alt text-[10px]"></i> {{ __('Apply Substitution') }}
                    </button>
                </div>
            </div>
        </form>
        @endif

        {{-- History --}}
        @if($history->isNotEmpty())
        <div class="bg-white dark:bg-[#242526] rounded-[2rem] shadow-sm border border-gray-100 dark:border-[#3a3b3c] p-6">
            <h2 class="text-sm font-bold text-gray-400 dark:text-gray-500 uppercase tracking-widest mb-4">{{ __('Substitution History') }}</h2>
            <div class="space-y-3">
                @foreach($history as $log)
                <div class="flex flex-wrap items-center gap-2 text-xs text-gray-600 dark:text-gray-400 p-3 bg-gray-50 dark:bg-[#3a3b3c]/50 rounded-xl">
                    <span class="font-bold text-gray-700 dark:text-gray-300">{{ $log->created_at->format('d/m/Y H:i') }}</span>
                    <span>{{ $dayNames[$log->day] ?? $log->day }} {{ __('Period') }} {{ $log->period }}</span>
                    <span class="font-medium">{{ $log->openedCourse->course->name ?? '?' }} ({{ $log->openedCourse->classroom->name ?? '?' }})</span>
                    <span class="text-gray-400">:</span>
                    <span class="text-rose-500 font-medium">{{ $log->fromTeacher->name ?? '?' }}</span>
                    <i class="fas fa-arrow-right text-[9px] text-gray-400"></i>
                    @if($log->action === 'unassign')
                    <span class="px-2 py-0.5 rounded-lg bg-gray-100 dark:bg-gray-700 text-gray-500 dark:text-gray-400 font-bold text-[10px] uppercase">{{ __('Unassigned') }}</span>
                    @else
                    <span class="text-emerald-600 font-medium">{{ $log->toTeacher->name ?? '?' }}</span>
                    @endif
                    @if($log->reason)
                    <span class="text-gray-400 italic">— {{ $log->reason }}</span>
                    @endif
                    <span class="text-gray-300 dark:text-gray-500 ml-auto">{{ __('By') }} {{ $log->createdBy->name ?? '?' }}</span>
                </div>
                @endforeach
            </div>
        </div>
        @endif

    </div>
</div>

<script>
function setAll(value) {
    document.querySelectorAll('[data-entry-select]').forEach(function (select) {
        select.value = value;
    });
}
</script>
@endsection
