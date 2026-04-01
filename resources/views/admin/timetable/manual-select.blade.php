@extends('layouts.app')

@section('content')
<div class="min-h-screen bg-gray-50 dark:bg-[#18191a] py-10 px-4 sm:px-6 lg:px-8 transition-colors duration-300">
    <div class="max-w-6xl mx-auto">

        {{-- Header --}}
        <div class="flex items-center justify-between mb-8">
            <div class="flex items-center space-x-4">
                <a href="{{ route('admin.timetable.index') }}"
                   class="group flex items-center justify-center w-10 h-10 rounded-full bg-white dark:bg-[#242526] shadow-sm border border-gray-200 dark:border-[#3a3b3c] text-gray-500 dark:text-gray-400 hover:text-indigo-600 dark:hover:text-indigo-400 hover:border-indigo-300 transition-all duration-200">
                    <i class="fas fa-arrow-left group-hover:-translate-x-0.5 transition-transform"></i>
                </a>
                <div>
                    <h1 class="text-2xl font-extrabold text-gray-900 dark:text-white tracking-tight">จัดตารางเรียนด้วยมือ</h1>
                    <p class="text-sm text-gray-500 dark:text-gray-400 font-medium px-1">เลือกห้องเรียนที่ต้องการจัดตาราง</p>
                </div>
            </div>
        </div>

        {{-- Flash --}}
        @if(session('error'))
        <div class="mb-6 p-4 bg-rose-50 dark:bg-rose-900/20 border border-rose-200 dark:border-rose-800 rounded-2xl text-rose-700 dark:text-rose-300 text-sm">
            {{ session('error') }}
        </div>
        @endif

        {{-- Classroom Cards --}}
        @if($classroomGroups->isEmpty())
        <div class="bg-white dark:bg-[#242526] rounded-[2rem] shadow-sm border border-gray-100 dark:border-[#3a3b3c] p-12 text-center">
            <div class="text-gray-300 dark:text-gray-600 mb-4"><i class="fas fa-inbox text-5xl"></i></div>
            <p class="text-gray-500 dark:text-gray-400 text-lg font-medium">ไม่พบวิชาที่เปิดสอน</p>
            <p class="text-gray-400 dark:text-gray-500 text-sm mt-1">กรุณาเพิ่มวิชาที่เปิดสอนใน "Opened Courses" ก่อน</p>
        </div>
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
               class="block bg-white dark:bg-[#242526] rounded-2xl shadow-sm border-2 {{ $isComplete ? 'border-emerald-200 dark:border-emerald-800' : 'border-gray-100 dark:border-[#3a3b3c]' }} p-5 hover:shadow-md hover:border-indigo-200 dark:hover:border-indigo-700 transition-all duration-200 group">

                <div class="flex items-start justify-between mb-3">
                    <div>
                        <h3 class="text-lg font-bold text-gray-800 dark:text-white group-hover:text-indigo-600 dark:group-hover:text-indigo-400 transition-colors">
                            {{ $group['grade_name'] }} / {{ $group['classroom_name'] }}
                        </h3>
                        <p class="text-xs text-gray-400 dark:text-gray-500 mt-0.5">{{ $group['education_level'] }}</p>
                    </div>
                    @if($isComplete)
                    <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full bg-emerald-100 dark:bg-emerald-900/30 text-emerald-700 dark:text-emerald-400 text-[11px] font-bold">
                        <i class="fas fa-check-circle text-[9px]"></i> ครบ
                    </span>
                    @endif
                </div>

                <div class="flex items-center gap-4 text-sm mb-3">
                    <div class="flex items-center gap-1.5 text-gray-500 dark:text-gray-400">
                        <i class="fas fa-book text-[10px]"></i>
                        <span>{{ $group['course_count'] }} วิชา</span>
                    </div>
                    <div class="flex items-center gap-1.5 text-gray-500 dark:text-gray-400">
                        <i class="fas fa-clock text-[10px]"></i>
                        <span>{{ $entryCount }}/{{ $totalPeriods }} คาบ</span>
                    </div>
                </div>

                {{-- Progress bar --}}
                <div class="w-full bg-gray-100 dark:bg-[#3a3b3c] rounded-full h-2">
                    <div class="h-2 rounded-full transition-all duration-500 {{ $isComplete ? 'bg-emerald-500' : ($progress > 0 ? 'bg-indigo-500' : 'bg-gray-300 dark:bg-gray-600') }}"
                         style="width: {{ $progress }}%"></div>
                </div>
                <p class="text-[10px] text-gray-400 dark:text-gray-500 mt-1 text-right">{{ $progress }}%</p>
            </a>
            @endforeach
        </div>
        @endif
    </div>
</div>
@endsection
