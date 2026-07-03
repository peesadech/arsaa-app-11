@extends('layouts.app')

@section('content')
<div class="min-h-screen bg-gray-50 dark:bg-[#18191a] py-10 px-4 sm:px-6 lg:px-8 transition-colors duration-300">
    <div class="max-w-5xl mx-auto">

        {{-- Header --}}
        <div class="flex items-center space-x-4 mb-8">
            <div class="w-12 h-12 rounded-2xl overflow-hidden border-2 border-white shadow-sm">
                <img src="{{ $teacher->image_path ? asset($teacher->image_path) : 'https://ui-avatars.com/api/?name=' . urlencode($teacher->name) . '&color=7F9CF5&background=EBF4FF' }}" class="w-full h-full object-cover" alt="">
            </div>
            <div>
                <h1 class="text-2xl font-extrabold text-gray-900 dark:text-white tracking-tight">{{ __('My Courses') }}</h1>
                <p class="text-sm text-gray-500 dark:text-gray-400 font-medium px-1">
                    {{ $teacher->name }} — {{ __('Academic Year') }} {{ $academicYear->year ?? '?' }} / {{ __('Semester') }} {{ $semester->semester_number ?? '?' }}
                </p>
            </div>
        </div>

        @if(session('status'))
        <div class="mb-6 p-4 bg-emerald-50 dark:bg-emerald-900/20 border border-emerald-200 dark:border-emerald-800 rounded-2xl text-emerald-700 dark:text-emerald-300 text-sm">{{ session('status') }}</div>
        @endif

        @if($openedCourses->isEmpty())
        <div class="bg-white dark:bg-[#242526] rounded-[2rem] shadow-sm border border-gray-100 dark:border-[#3a3b3c] p-10 text-center text-gray-400">
            <i class="fas fa-book-open text-3xl mb-3"></i>
            <p class="text-sm font-medium">{{ __('You have no courses in the current term') }}</p>
        </div>
        @else
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-3">
            @foreach($openedCourses as $oc)
            <a href="{{ route('teacher.scores.entry', $oc->id) }}"
               class="block p-5 rounded-[2rem] bg-white dark:bg-[#242526] border border-gray-100 dark:border-[#3a3b3c] hover:border-indigo-300 hover:shadow-sm transition-all">
                <div class="text-sm font-extrabold text-gray-800 dark:text-white">{{ $oc->course->name ?? '?' }}</div>
                <div class="text-[11px] text-gray-400 mt-1">
                    {{ $oc->grade->name_th ?? '' }} / {{ $oc->classroom->name ?? '' }}
                    @if($oc->course?->subjectGroup) · {{ $oc->course->subjectGroup->name_th }}@endif
                </div>
                <div class="mt-3 text-[11px] font-bold {{ $oc->scored_count > 0 ? 'text-emerald-500' : 'text-gray-300' }}">
                    <i class="fas fa-clipboard-check mr-1"></i>{{ $oc->scored_count }} {{ __('scores recorded') }}
                </div>
            </a>
            @endforeach
        </div>
        @endif

    </div>
</div>
@endsection
