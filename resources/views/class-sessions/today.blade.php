<x-layouts.admin :header="__('Class Periods')" :subheader="$date->isToday() ? __('Today') . ' · ' . $date->format('D, d M Y') : $date->format('D, d M Y')">
    <x-slot name="actions">
        <form method="GET" action="{{ route('class-sessions.today') }}" class="flex items-center gap-2" data-no-progress>
            <input type="date" name="date" value="{{ $date->toDateString() }}" onchange="this.form.submit()"
                   class="form-input rounded-lg w-auto">
            @if($isAdmin)
                <select name="teacher_id" onchange="this.form.submit()" class="form-select rounded-lg w-auto">
                    <option value="">{{ __('All Teachers') }}</option>
                    @foreach($teachers as $t)
                        <option value="{{ $t->id }}" {{ (string)$filterTeacherId === (string)$t->id ? 'selected' : '' }}>{{ $t->name }}</option>
                    @endforeach
                </select>
            @endif
        </form>
    </x-slot>

    @if(!$hasTerm)
        <x-card>
            <div class="text-center py-10 text-slate-400">
                <x-icon name="calendar" class="h-8 w-8 mx-auto mb-2" />
                <p class="text-sm font-medium">{{ __('No academic year selected') }}</p>
            </div>
        </x-card>
    @elseif($schedule->isEmpty())
        <x-card>
            <div class="text-center py-10 text-slate-400">
                <x-icon name="calendar" class="h-8 w-8 mx-auto mb-2" />
                <p class="text-sm font-medium">{{ __('No classes scheduled for this day') }}</p>
            </div>
        </x-card>
    @else
        <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 gap-4">
            @foreach($schedule as $entry)
                @php
                    $oc = $entry->openedCourse;
                    $session = $entry->existing_session;
                @endphp
                <x-card>
                    <div class="flex items-start justify-between gap-3">
                        <div class="min-w-0">
                            <div class="flex items-center gap-2 text-xs font-bold text-brand-600">
                                <x-icon name="calendar" class="h-3.5 w-3.5" />
                                <span>{{ __('Period') }} {{ $entry->period }}</span>
                                @if($entry->computed_start)
                                    <span class="text-slate-400">· {{ $entry->computed_start }}–{{ $entry->computed_end }}</span>
                                @endif
                            </div>
                            <h3 class="text-base font-semibold text-slate-900 mt-1 truncate">{{ $oc?->course?->name ?? '—' }}</h3>
                            <p class="text-sm text-slate-500 mt-0.5">
                                {{ $oc?->grade?->name_th }} / {{ $oc?->classroom?->name }}
                                @if($isAdmin && $entry->teacher)· {{ $entry->teacher->name }}@endif
                            </p>
                        </div>
                        @if($session)
                            @php $sc = ['OPEN'=>'green','CLOSED'=>'gray','CANCELLED'=>'red','POSTPONED'=>'amber'][$session->status] ?? 'gray'; @endphp
                            <x-badge :color="$sc">{{ __($session->status) }}</x-badge>
                        @endif
                    </div>

                    <div class="mt-4">
                        @if($session)
                            <a href="{{ route('class-sessions.show', $session->id) }}" class="btn-primary w-full">
                                <x-icon name="clipboard" class="h-4 w-4" /> {{ __('Open session') }}
                            </a>
                        @else
                            <form method="POST" action="{{ route('class-sessions.open') }}">
                                @csrf
                                <input type="hidden" name="timetable_entry_id" value="{{ $entry->id }}">
                                <input type="hidden" name="date" value="{{ $date->toDateString() }}">
                                <button type="submit" class="btn-primary w-full">
                                    <x-icon name="check" class="h-4 w-4" /> {{ __('Start teaching') }}
                                </button>
                            </form>
                        @endif
                    </div>
                </x-card>
            @endforeach
        </div>
    @endif
</x-layouts.admin>
