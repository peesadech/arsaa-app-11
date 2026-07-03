@extends('layouts.app')

@section('content')
<div class="min-h-screen bg-gray-50 dark:bg-[#18191a] py-10 px-4 sm:px-6 lg:px-8 transition-colors duration-300">
    <div class="max-w-6xl mx-auto space-y-6">

        {{-- Header --}}
        <div class="flex flex-wrap items-center justify-between gap-4">
            <div class="flex items-center space-x-4">
                <div class="w-14 h-14 rounded-2xl overflow-hidden border-2 border-white shadow-sm">
                    <img src="{{ $teacher->image_path ? asset($teacher->image_path) : 'https://ui-avatars.com/api/?name=' . urlencode($teacher->name) . '&color=7F9CF5&background=EBF4FF' }}" class="w-full h-full object-cover" alt="">
                </div>
                <div>
                    <h1 class="text-2xl font-extrabold text-gray-900 dark:text-white tracking-tight">{{ __('Teacher Dashboard') }}</h1>
                    <p class="text-sm text-gray-500 dark:text-gray-400 font-medium">
                        {{ $teacher->name }} — {{ __('Academic Year') }} {{ $academicYear->year ?? '?' }} / {{ __('Semester') }} {{ $semester->semester_number ?? '?' }}
                    </p>
                </div>
            </div>
            <a href="{{ route('teacher.scores.index') }}" class="btn-app"><i class="fas fa-clipboard-check text-[10px]"></i> {{ __('Record Scores') }}</a>
        </div>

        {{-- Stats --}}
        <div class="grid grid-cols-2 lg:grid-cols-4 gap-4">
            @foreach([
                ['icon' => 'fa-school', 'color' => 'text-indigo-500', 'value' => $stats['rooms'], 'label' => __('Classrooms taught')],
                ['icon' => 'fa-book', 'color' => 'text-emerald-500', 'value' => $stats['courses'], 'label' => __('Courses taught')],
                ['icon' => 'fa-calendar-week', 'color' => 'text-amber-500', 'value' => $stats['periods_per_week'], 'label' => __('Periods / week')],
                ['icon' => 'fa-clipboard-check', 'color' => 'text-purple-500', 'value' => $stats['scores_recorded'], 'label' => __('Scores recorded')],
            ] as $stat)
            <div class="bg-white dark:bg-[#242526] rounded-2xl shadow-sm border border-gray-100 dark:border-[#3a3b3c] px-5 py-4">
                <div class="flex items-center justify-between">
                    <span class="text-3xl font-extrabold text-gray-800 dark:text-white">{{ $stat['value'] }}</span>
                    <i class="fas {{ $stat['icon'] }} {{ $stat['color'] }} text-lg"></i>
                </div>
                <p class="text-[11px] font-bold text-gray-400 uppercase tracking-widest mt-1">{{ $stat['label'] }}</p>
            </div>
            @endforeach
        </div>

        {{-- Rooms + courses --}}
        <div class="bg-white dark:bg-[#242526] rounded-[2rem] shadow-sm border border-gray-100 dark:border-[#3a3b3c] p-6">
            <h2 class="text-sm font-bold text-gray-400 dark:text-gray-500 uppercase tracking-widest mb-4">{{ __('My classrooms and courses') }}</h2>

            @if($rooms->isEmpty())
            <div class="py-10 text-center text-gray-400">
                <i class="fas fa-book-open text-3xl mb-3"></i>
                <p class="text-sm font-medium">{{ __('You have no courses in the current term') }}</p>
            </div>
            @else
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                @foreach($rooms as $room)
                <div class="p-5 rounded-2xl border border-gray-100 dark:border-[#3a3b3c] bg-gray-50/50 dark:bg-[#3a3b3c]/30">
                    <div class="flex items-center justify-between mb-3">
                        <div class="text-sm font-extrabold text-gray-800 dark:text-white">
                            <i class="fas fa-school text-indigo-400 mr-1"></i>
                            {{ $room['grade']->name_th ?? '' }} / {{ $room['classroom']->name ?? '' }}
                        </div>
                        <span class="text-[11px] text-gray-400 font-bold"><i class="fas fa-user-graduate mr-1"></i>{{ $room['student_count'] }} {{ __('students') }}</span>
                    </div>
                    <div class="space-y-2">
                        @foreach($room['courses'] as $oc)
                        <a href="{{ route('teacher.scores.entry', $oc->id) }}"
                           class="flex items-center justify-between p-3 rounded-xl bg-white dark:bg-[#242526] border border-gray-100 dark:border-[#3a3b3c] hover:border-indigo-300 transition-all">
                            <div>
                                <div class="text-xs font-bold text-gray-800 dark:text-gray-200">{{ $oc->course->name ?? '?' }}</div>
                                @if($oc->course?->subjectGroup)<div class="text-[10px] text-gray-400">{{ $oc->course->subjectGroup->name_th }}</div>@endif
                            </div>
                            <div class="text-right">
                                <span class="text-[11px] font-bold {{ $oc->scored_count >= $oc->student_count && $oc->student_count > 0 ? 'text-emerald-500' : ($oc->scored_count > 0 ? 'text-amber-500' : 'text-gray-300') }}">
                                    <i class="fas fa-clipboard-check mr-1"></i>{{ $oc->scored_count }}/{{ $oc->student_count }}
                                </span>
                                <div class="text-[10px] text-gray-400">{{ __('scores recorded') }}</div>
                            </div>
                        </a>
                        @endforeach
                    </div>
                </div>
                @endforeach
            </div>
            @endif
        </div>

        {{-- Weekly schedule --}}
        @php
            $dayNames = [1=>__('Monday'), 2=>__('Tuesday'), 3=>__('Wednesday'), 4=>__('Thursday'), 5=>__('Friday'), 6=>__('Saturday'), 7=>__('Sunday')];
            $colors = ['bg-indigo-100 border-indigo-300 text-indigo-800', 'bg-emerald-100 border-emerald-300 text-emerald-800', 'bg-amber-100 border-amber-300 text-amber-800', 'bg-rose-100 border-rose-300 text-rose-800', 'bg-purple-100 border-purple-300 text-purple-800', 'bg-teal-100 border-teal-300 text-teal-800'];
            $schedule = $yearlySchedules->first();
            $dayConfigs = $schedule ? ($schedule->day_configs ?? []) : [];
            $teachingDays = $schedule ? collect($schedule->teaching_days ?? [])->filter(fn($d) => ($dayConfigs[(string)$d]['periods'] ?? 0) > 0)->values()->all() : [];
            $maxPeriods = collect($dayConfigs)->max('periods') ?? 0;
        @endphp
        @if($schedule && $entries->isNotEmpty())
        <div class="bg-white dark:bg-[#242526] rounded-[2rem] shadow-sm border border-gray-100 dark:border-[#3a3b3c] p-6">
            <h2 class="text-sm font-bold text-gray-400 dark:text-gray-500 uppercase tracking-widest mb-4">{{ __('My teaching schedule') }}</h2>
            <div class="overflow-x-auto">
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
                                @php $colorIdx = ($entry->openedCourse->course->subject_group_id ?? 0) % count($colors); @endphp
                                <td class="border border-gray-200 dark:border-[#3a3b3c] p-1">
                                    <div class="p-2 rounded-xl border {{ $colors[$colorIdx] }} text-xs space-y-0.5">
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
        </div>
        @endif

    </div>
</div>
@endsection
