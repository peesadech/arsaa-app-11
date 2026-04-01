@extends('layouts.app')

@section('content')
<div class="min-h-screen bg-gray-50 dark:bg-[#18191a] py-10 px-4 sm:px-6 lg:px-8 transition-colors duration-300">
    <div class="max-w-6xl mx-auto">

        <div class="flex items-center space-x-4 mb-8">
            <a href="{{ route('admin.timetable.index') }}"
               class="group flex items-center justify-center w-10 h-10 rounded-full bg-white dark:bg-[#242526] shadow-sm border border-gray-200 dark:border-[#3a3b3c] text-gray-500 dark:text-gray-400 hover:text-indigo-600 dark:hover:text-indigo-400 hover:border-indigo-300 transition-all duration-200">
                <i class="fas fa-arrow-left group-hover:-translate-x-0.5 transition-transform"></i>
            </a>
            <div>
                <h1 class="text-2xl font-extrabold text-gray-900 dark:text-white tracking-tight">
                    ตารางสอน — ครู{{ $teacher->name }}
                </h1>
            </div>
        </div>

        @php
            $dayNames = [1=>'จันทร์', 2=>'อังคาร', 3=>'พุธ', 4=>'พฤหัสบดี', 5=>'ศุกร์', 6=>'เสาร์', 7=>'อาทิตย์'];
            $colors = ['bg-indigo-100 border-indigo-300 text-indigo-800', 'bg-emerald-100 border-emerald-300 text-emerald-800', 'bg-amber-100 border-amber-300 text-amber-800', 'bg-rose-100 border-rose-300 text-rose-800', 'bg-purple-100 border-purple-300 text-purple-800', 'bg-teal-100 border-teal-300 text-teal-800'];
            // Use first yearly schedule for the grid
            $schedule = $yearlySchedules->first();
            $teachingDays = $schedule ? ($schedule->teaching_days ?? []) : [];
            $dayConfigs = $schedule ? ($schedule->day_configs ?? []) : [];
            $maxPeriods = collect($dayConfigs)->max('periods') ?? 0;
        @endphp

        @if(!$schedule)
        <div class="p-4 bg-amber-50 dark:bg-amber-900/20 border border-amber-200 dark:border-amber-800 rounded-2xl text-amber-700 dark:text-amber-300 text-sm">
            ไม่พบข้อมูลตารางเวลา
        </div>
        @else
        <div class="bg-white dark:bg-[#242526] rounded-[2rem] shadow-sm border border-gray-100 dark:border-[#3a3b3c] p-4 overflow-x-auto">
            <table class="w-full border-collapse" style="min-width:600px">
                <thead>
                    <tr>
                        <th class="p-2 text-xs font-semibold text-gray-500 dark:text-gray-400 border border-gray-200 dark:border-[#3a3b3c] bg-gray-50 dark:bg-[#3a3b3c] w-20">คาบ</th>
                        @foreach($teachingDays as $d)
                        <th class="p-2 text-xs font-semibold text-gray-700 dark:text-gray-300 border border-gray-200 dark:border-[#3a3b3c] bg-gray-50 dark:bg-[#3a3b3c]">{{ $dayNames[(int)$d] ?? 'วัน '.$d }}</th>
                        @endforeach
                    </tr>
                </thead>
                <tbody>
                @for($p = 1; $p <= $maxPeriods; $p++)
                    <tr>
                        <td class="p-2 text-center text-xs font-medium text-gray-600 dark:text-gray-400 border border-gray-200 dark:border-[#3a3b3c] bg-gray-50 dark:bg-[#3a3b3c]">คาบ {{ $p }}</td>
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
        @endif
    </div>
</div>
@endsection
