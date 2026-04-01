@extends('layouts.app')

@section('content')
<div class="min-h-screen bg-gray-50 dark:bg-[#18191a] py-10 px-4 sm:px-6 lg:px-8 transition-colors duration-300">
    <div class="max-w-6xl mx-auto">

        {{-- Header --}}
        <div class="flex items-center justify-between mb-8">
            <div class="flex items-center space-x-4">
                <a href="{{ route('admin.dashboard') }}"
                   class="group flex items-center justify-center w-10 h-10 rounded-full bg-white dark:bg-[#242526] shadow-sm border border-gray-200 dark:border-[#3a3b3c] text-gray-500 dark:text-gray-400 hover:text-indigo-600 dark:hover:text-indigo-400 hover:border-indigo-300 transition-all duration-200">
                    <i class="fas fa-arrow-left group-hover:-translate-x-0.5 transition-transform"></i>
                </a>
                <div>
                    <h1 class="text-2xl font-extrabold text-gray-900 dark:text-white tracking-tight">จัดตารางเรียน</h1>
                    <p class="text-sm text-gray-500 dark:text-gray-400 font-medium px-1">Timetable Scheduling</p>
                </div>
            </div>

            <div class="flex items-center gap-3">
                <a href="{{ route('admin.timetable.manual.select') }}" class="btn-app">
                    <i class="fas fa-hand-pointer text-[10px]"></i> จัดด้วยมือ
                </a>
                <a href="{{ route('admin.timetable.generate') }}" class="btn-app">
                    <i class="fas fa-magic text-[10px]"></i> Auto Generate
                </a>
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

        {{-- Active Solution Summary --}}
        @if($activeSolution)
        <div class="bg-white dark:bg-[#242526] rounded-[2rem] shadow-sm border border-gray-100 dark:border-[#3a3b3c] p-6 mb-8">
            <div class="flex items-center justify-between mb-4">
                <h2 class="text-lg font-bold text-gray-900 dark:text-white">ตารางเรียนปัจจุบัน</h2>
                <a href="{{ route('admin.timetable.solutions.show', $activeSolution->id) }}" class="btn-app text-sm">
                    <i class="fas fa-eye text-[10px]"></i> ดูตาราง
                </a>
            </div>
            <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                <div class="text-center p-4 bg-gray-50 dark:bg-[#3a3b3c] rounded-2xl">
                    <div class="text-2xl font-bold {{ $activeSolution->hard_violations > 0 ? 'text-rose-600' : 'text-emerald-600' }}">{{ $activeSolution->hard_violations }}</div>
                    <div class="text-xs text-gray-500 dark:text-gray-400 mt-1">Hard Violations</div>
                </div>
                <div class="text-center p-4 bg-gray-50 dark:bg-[#3a3b3c] rounded-2xl">
                    <div class="text-2xl font-bold text-amber-600">{{ $activeSolution->soft_violations }}</div>
                    <div class="text-xs text-gray-500 dark:text-gray-400 mt-1">Soft Violations</div>
                </div>
                <div class="text-center p-4 bg-gray-50 dark:bg-[#3a3b3c] rounded-2xl">
                    <div class="text-2xl font-bold text-indigo-600">{{ number_format($activeSolution->fitness_score, 0) }}</div>
                    <div class="text-xs text-gray-500 dark:text-gray-400 mt-1">Fitness Score</div>
                </div>
                <div class="text-center p-4 bg-gray-50 dark:bg-[#3a3b3c] rounded-2xl">
                    <div class="text-2xl font-bold text-gray-700 dark:text-gray-300">{{ $activeSolution->conflicts->count() }}</div>
                    <div class="text-xs text-gray-500 dark:text-gray-400 mt-1">Total Conflicts</div>
                </div>
            </div>
        </div>
        @endif

        {{-- Generation History --}}
        <div class="bg-white dark:bg-[#242526] rounded-[2rem] shadow-sm border border-gray-100 dark:border-[#3a3b3c] p-6">
            <h2 class="text-lg font-bold text-gray-900 dark:text-white mb-4">ประวัติการ Generate</h2>

            @if($generations->isEmpty())
            <p class="text-center text-gray-400 dark:text-gray-500 py-8">ยังไม่มีการ Generate ตาราง</p>
            @else
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead>
                        <tr class="border-b border-gray-100 dark:border-[#3a3b3c]">
                            <th class="text-left py-3 px-4 text-gray-500 dark:text-gray-400 font-medium">วันที่</th>
                            <th class="text-left py-3 px-4 text-gray-500 dark:text-gray-400 font-medium">สถานะ</th>
                            <th class="text-left py-3 px-4 text-gray-500 dark:text-gray-400 font-medium">Progress</th>
                            <th class="text-left py-3 px-4 text-gray-500 dark:text-gray-400 font-medium">Solutions</th>
                            <th class="text-left py-3 px-4 text-gray-500 dark:text-gray-400 font-medium">ผู้สร้าง</th>
                            <th class="text-right py-3 px-4 text-gray-500 dark:text-gray-400 font-medium"></th>
                        </tr>
                    </thead>
                    <tbody>
                    @foreach($generations as $gen)
                        <tr class="border-b border-gray-50 dark:border-[#3a3b3c] hover:bg-gray-50 dark:hover:bg-[#3a3b3c]/50">
                            <td class="py-3 px-4 text-gray-700 dark:text-gray-300">{{ $gen->created_at->format('d/m/Y H:i') }}</td>
                            <td class="py-3 px-4">
                                @php
                                    $colors = ['pending' => 'bg-gray-100 text-gray-600 dark:bg-gray-700 dark:text-gray-300', 'running' => 'bg-blue-100 text-blue-700 dark:bg-blue-900/30 dark:text-blue-400', 'completed' => 'bg-emerald-100 text-emerald-700 dark:bg-emerald-900/30 dark:text-emerald-400', 'failed' => 'bg-rose-100 text-rose-700 dark:bg-rose-900/30 dark:text-rose-400'];
                                @endphp
                                <span class="inline-flex px-2.5 py-1 rounded-full text-xs font-semibold {{ $colors[$gen->status] ?? $colors['pending'] }}">
                                    {{ ucfirst($gen->status) }}
                                </span>
                            </td>
                            <td class="py-3 px-4 text-gray-600 dark:text-gray-400">{{ $gen->current_generation }}/{{ $gen->max_generations }}</td>
                            <td class="py-3 px-4 text-gray-600 dark:text-gray-400">{{ $gen->solutions->count() }}</td>
                            <td class="py-3 px-4 text-gray-600 dark:text-gray-400">{{ $gen->user->name ?? '-' }}</td>
                            <td class="py-3 px-4 text-right">
                                @if($gen->status === 'completed')
                                <a href="{{ route('admin.timetable.generations.show', $gen->id) }}" class="text-indigo-600 dark:text-indigo-400 hover:underline text-sm font-medium">
                                    ดูผลลัพธ์
                                </a>
                                @elseif($gen->status === 'running' || $gen->status === 'pending')
                                <a href="{{ route('admin.timetable.generations.show', $gen->id) }}" class="text-blue-600 dark:text-blue-400 hover:underline text-sm font-medium">
                                    ดูความคืบหน้า
                                </a>
                                @endif
                            </td>
                        </tr>
                    @endforeach
                    </tbody>
                </table>
            </div>
            @endif
        </div>
    </div>
</div>
@endsection
