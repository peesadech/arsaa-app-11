@extends('layouts.app')

@section('content')
<div class="min-h-screen bg-gray-50 dark:bg-[#18191a] py-10 px-4 sm:px-6 lg:px-8 transition-colors duration-300">
    <div class="max-w-5xl mx-auto">

        <div class="flex items-center space-x-4 mb-8">
            <a href="{{ route('admin.timetable.generations.show', $solution->generation_id) }}"
               class="group flex items-center justify-center w-10 h-10 rounded-full bg-white dark:bg-[#242526] shadow-sm border border-gray-200 dark:border-[#3a3b3c] text-gray-500 dark:text-gray-400 hover:text-indigo-600 dark:hover:text-indigo-400 hover:border-indigo-300 transition-all duration-200">
                <i class="fas fa-arrow-left group-hover:-translate-x-0.5 transition-transform"></i>
            </a>
            <div>
                <h1 class="text-2xl font-extrabold text-gray-900 dark:text-white tracking-tight">รายงาน Conflict</h1>
                <p class="text-sm text-gray-500 dark:text-gray-400 font-medium px-1">
                    Solution #{{ $solution->rank }} — {{ $conflicts->count() }} conflicts
                </p>
            </div>
        </div>

        {{-- Summary --}}
        @php
            $hardCount = $conflicts->where('severity', 'hard')->count();
            $softCount = $conflicts->where('severity', 'soft')->count();
            $byType = $conflicts->groupBy('type');
        @endphp
        <div class="grid grid-cols-2 md:grid-cols-4 gap-4 mb-6">
            <div class="bg-white dark:bg-[#242526] rounded-2xl p-4 text-center border border-gray-100 dark:border-[#3a3b3c]">
                <div class="text-2xl font-bold {{ $hardCount > 0 ? 'text-rose-600' : 'text-emerald-600' }}">{{ $hardCount }}</div>
                <div class="text-xs text-gray-500 dark:text-gray-400">Hard</div>
            </div>
            <div class="bg-white dark:bg-[#242526] rounded-2xl p-4 text-center border border-gray-100 dark:border-[#3a3b3c]">
                <div class="text-2xl font-bold text-amber-600">{{ $softCount }}</div>
                <div class="text-xs text-gray-500 dark:text-gray-400">Soft</div>
            </div>
            @foreach($byType->take(2) as $type => $items)
            <div class="bg-white dark:bg-[#242526] rounded-2xl p-4 text-center border border-gray-100 dark:border-[#3a3b3c]">
                <div class="text-2xl font-bold text-gray-700 dark:text-gray-300">{{ $items->count() }}</div>
                <div class="text-xs text-gray-500 dark:text-gray-400">{{ str_replace('_', ' ', ucfirst($type)) }}</div>
            </div>
            @endforeach
        </div>

        {{-- Conflict List --}}
        <div class="bg-white dark:bg-[#242526] rounded-[2rem] shadow-sm border border-gray-100 dark:border-[#3a3b3c] p-6">
            @if($conflicts->isEmpty())
            <p class="text-center text-emerald-600 dark:text-emerald-400 py-8 text-lg font-bold">
                <i class="fas fa-check-circle mr-2"></i> ไม่มี Conflict
            </p>
            @else
            <div class="space-y-3">
                @foreach($conflicts as $conflict)
                @php
                    $isSoft = $conflict->severity === 'soft';
                    $details = $conflict->details ?? [];
                    $dayNames = [1=>'จันทร์', 2=>'อังคาร', 3=>'พุธ', 4=>'พฤหัสบดี', 5=>'ศุกร์', 6=>'เสาร์', 7=>'อาทิตย์'];
                @endphp
                <div class="flex items-start space-x-3 p-3 rounded-xl {{ $isSoft ? 'bg-amber-50 dark:bg-amber-900/10 border border-amber-200 dark:border-amber-800' : 'bg-rose-50 dark:bg-rose-900/10 border border-rose-200 dark:border-rose-800' }}">
                    <div class="flex-shrink-0 mt-0.5">
                        <span class="inline-flex px-2 py-0.5 rounded-full text-[10px] font-bold {{ $isSoft ? 'bg-amber-200 text-amber-800' : 'bg-rose-200 text-rose-800' }}">
                            {{ strtoupper($conflict->severity) }}
                        </span>
                    </div>
                    <div class="flex-1 min-w-0">
                        <div class="text-sm font-medium {{ $isSoft ? 'text-amber-800 dark:text-amber-300' : 'text-rose-800 dark:text-rose-300' }}">
                            {{ $details['message'] ?? str_replace('_', ' ', $conflict->type) }}
                        </div>
                        @if($conflict->day)
                        <div class="text-xs {{ $isSoft ? 'text-amber-600 dark:text-amber-400' : 'text-rose-600 dark:text-rose-400' }} mt-0.5">
                            วัน{{ $dayNames[$conflict->day] ?? $conflict->day }} คาบที่ {{ $conflict->period }}
                        </div>
                        @endif
                        <div class="text-[10px] text-gray-500 dark:text-gray-500 mt-0.5">
                            ประเภท: {{ str_replace('_', ' ', $conflict->type) }}
                        </div>
                    </div>
                </div>
                @endforeach
            </div>
            @endif
        </div>
    </div>
</div>
@endsection
