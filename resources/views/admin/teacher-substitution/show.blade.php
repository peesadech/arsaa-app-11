<x-layouts.admin :header="__('Teacher Substitution')" :subheader="$teacher->name . ' — ' . __('Academic Year') . ' ' . ($academicYear->year ?? '?') . ' / ' . __('Semester') . ' ' . ($semester->semester_number ?? '?')">
    <x-slot name="actions">
        <x-button variant="secondary" icon="arrow-left" :href="route('admin.teacher-term-status.edit', $teacher->id)">{{ __('Back') }}</x-button>
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
    @if(session('substitution_errors'))
    <div class="mb-6 p-4 bg-amber-50 border border-amber-200 rounded-xl text-amber-700 text-sm space-y-1">
        <div class="font-bold">{{ __('Some periods could not be reassigned') }}:</div>
        @foreach(session('substitution_errors') as $entryId => $message)
        <div>• {{ $message }}</div>
        @endforeach
    </div>
    @endif

    {{-- Teacher Info --}}
    <x-card class="mb-6">
        <div class="flex flex-wrap items-center gap-4">
            <div class="w-14 h-14 rounded-2xl overflow-hidden border-2 border-white shadow-card">
                <img src="{{ $teacher->image_path ? asset($teacher->image_path) : 'https://ui-avatars.com/api/?name=' . urlencode($teacher->name) . '&color=7F9CF5&background=EBF4FF' }}" class="w-full h-full object-cover" alt="">
            </div>
            <div class="flex-1 min-w-[200px]">
                <h3 class="text-lg font-semibold text-slate-800">{{ $teacher->name }}</h3>
                <p class="text-xs text-slate-400">{{ $teacher->email }}</p>
                @php
                    $statusBadges = [
                        'available' => 'badge-green',
                        'unavailable' => 'badge-red',
                        'leave' => 'badge-amber',
                        'partial' => 'badge-blue',
                        'transferred' => 'badge-gray',
                        'resigned_term' => 'badge-gray',
                    ];
                    $termStatusValue = $termStatus->status ?? null;
                @endphp
                @if($termStatusValue)
                <span class="{{ $statusBadges[$termStatusValue] ?? 'badge-gray' }} uppercase mt-1">
                    {{ __(ucfirst(str_replace('_', ' ', $termStatusValue))) }}
                </span>
                @endif
            </div>
            <div class="text-right">
                <div class="text-3xl font-bold {{ $entries->count() > 0 ? 'text-red-500' : 'text-emerald-500' }}">{{ $entries->count() }}</div>
                <div class="text-xs text-slate-400 font-medium">{{ __('Scheduled periods') }}</div>
            </div>
        </div>
    </x-card>

    @php
        $dayNames = [1=>__('Monday'), 2=>__('Tuesday'), 3=>__('Wednesday'), 4=>__('Thursday'), 5=>__('Friday'), 6=>__('Saturday'), 7=>__('Sunday')];
        $schedule = $yearlySchedules->first();
        $dayConfigs = $schedule ? ($schedule->day_configs ?? []) : [];
        $teachingDays = $schedule ? collect($schedule->teaching_days ?? [])->filter(fn($d) => ($dayConfigs[(string)$d]['periods'] ?? 0) > 0)->values()->all() : [];
        $maxPeriods = collect($dayConfigs)->max('periods') ?? 0;
    @endphp

    @if($entries->isEmpty())
    <x-card class="mb-6">
        <div class="py-10 text-center text-slate-400">
            <i class="fas fa-calendar-check text-3xl mb-3 text-emerald-400"></i>
            <p class="text-sm font-medium">{{ __('This teacher has no periods in the active timetable') }}</p>
        </div>
    </x-card>
    @else

    {{-- Schedule Grid --}}
    @if($schedule)
    <x-card padded="false" class="mb-6">
        <div class="p-4 overflow-x-auto">
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
                            @php $entry = $entries->first(fn($e) => $e->day == (int)$d && $e->period == $p); @endphp
                            @if($entry)
                            <td class="border border-slate-200 p-1">
                                <div class="p-2 rounded-lg border bg-red-50 border-red-200 text-red-800 text-xs space-y-0.5">
                                    <div class="font-bold truncate">{{ $entry->openedCourse->course->name ?? '' }}</div>
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

    {{-- Substitution Form --}}
    <form action="{{ route('admin.teacher-substitution.apply', $teacher->id) }}" method="POST">
        @csrf
        <x-card class="mb-6">
            <div class="flex flex-wrap items-center justify-between gap-3 mb-4">
                <h2 class="text-sm font-semibold text-slate-500 uppercase tracking-wide">{{ __('Assign substitute per period') }}</h2>
                <div class="flex items-center gap-2">
                    <span class="text-xs text-slate-400">{{ __('Set all to') }}:</span>
                    <button type="button" onclick="setAll('unassign')" class="btn-secondary"><i class="fas fa-ban text-xs"></i> {{ __('Unassign (no teaching)') }}</button>
                    <button type="button" onclick="setAll('keep')" class="btn-secondary"><i class="fas fa-undo text-xs"></i> {{ __('Keep current') }}</button>
                </div>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full" style="min-width:700px">
                    <thead>
                        <tr class="text-left text-[10px] font-bold text-slate-400 uppercase tracking-wide border-b border-slate-100">
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
                        <tr class="border-b border-slate-50">
                            <td class="py-3 pr-3 text-xs font-bold text-slate-700 whitespace-nowrap">
                                {{ $dayNames[$entry->day] ?? __('Day').' '.$entry->day }} — {{ __('Period') }} {{ $entry->period }}
                            </td>
                            <td class="py-3 pr-3 text-xs text-slate-600">{{ $entry->openedCourse->course->name ?? '?' }}</td>
                            <td class="py-3 pr-3 text-xs text-slate-600 whitespace-nowrap">
                                {{ $entry->openedCourse->grade->name_th ?? '' }} / {{ $entry->openedCourse->classroom->name ?? '' }}
                            </td>
                            <td class="py-3 pr-3 text-xs text-slate-500">{{ $entry->room->room_number ?? '-' }}</td>
                            <td class="py-3">
                                <select name="items[{{ $entry->id }}]" data-entry-select class="form-select max-w-xs text-xs">
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
                <label class="form-label">{{ __('Reason') }}</label>
                <textarea name="reason" rows="2" maxlength="500" placeholder="{{ __('e.g. Teacher resigned, replaced by new teacher') }}" class="form-textarea"></textarea>
            </div>

            <div class="mt-5 flex justify-end">
                <button type="submit" class="btn-primary">
                    <i class="fas fa-exchange-alt text-xs"></i> {{ __('Apply Substitution') }}
                </button>
            </div>
        </x-card>
    </form>
    @endif

    {{-- History --}}
    @if($history->isNotEmpty())
    <x-card>
        <h2 class="text-sm font-semibold text-slate-500 uppercase tracking-wide mb-4">{{ __('Substitution History') }}</h2>
        <div class="space-y-3">
            @foreach($history as $log)
            <div class="flex flex-wrap items-center gap-2 text-xs text-slate-600 p-3 bg-slate-50 rounded-lg">
                <span class="font-bold text-slate-700">{{ $log->created_at->format('d/m/Y H:i') }}</span>
                <span>{{ $dayNames[$log->day] ?? $log->day }} {{ __('Period') }} {{ $log->period }}</span>
                <span class="font-medium">{{ $log->openedCourse->course->name ?? '?' }} ({{ $log->openedCourse->classroom->name ?? '?' }})</span>
                <span class="text-slate-400">:</span>
                <span class="text-red-500 font-medium">{{ $log->fromTeacher->name ?? '?' }}</span>
                <i class="fas fa-arrow-right text-[9px] text-slate-400"></i>
                @if($log->action === 'unassign')
                <span class="badge-gray uppercase">{{ __('Unassigned') }}</span>
                @else
                <span class="text-emerald-600 font-medium">{{ $log->toTeacher->name ?? '?' }}</span>
                @endif
                @if($log->reason)
                <span class="text-slate-400 italic">— {{ $log->reason }}</span>
                @endif
                <span class="text-slate-300 ml-auto">{{ __('By') }} {{ $log->createdBy->name ?? '?' }}</span>
            </div>
            @endforeach
        </div>
    </x-card>
    @endif

    <script>
    function setAll(value) {
        document.querySelectorAll('[data-entry-select]').forEach(function (select) {
            select.value = value;
        });
    }
    </script>
</x-layouts.admin>
