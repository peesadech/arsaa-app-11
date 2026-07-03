@extends('layouts.app')

@section('content')
<div class="min-h-screen bg-gray-50 dark:bg-[#18191a] py-10 px-4 sm:px-6 lg:px-8 transition-colors duration-300">
    <div class="max-w-6xl mx-auto">

        {{-- Header --}}
        <div class="flex items-center space-x-4 mb-8">
            <a href="{{ route('admin.students.index') }}"
               class="group flex items-center justify-center w-10 h-10 rounded-full bg-white dark:bg-[#242526] shadow-sm border border-gray-200 dark:border-[#3a3b3c] text-gray-500 dark:text-gray-400 hover:text-indigo-600 dark:hover:text-indigo-400 hover:border-indigo-300 transition-all duration-200">
                <i class="fas fa-arrow-left group-hover:-translate-x-0.5 transition-transform"></i>
            </a>
            <div>
                <h1 class="text-2xl font-extrabold text-gray-900 dark:text-white tracking-tight">{{ __('Academic Results') }}</h1>
                <p class="text-sm text-gray-500 dark:text-gray-400 font-medium px-1">
                    {{ __('Academic Year') }} {{ $academicYear->year ?? '?' }} / {{ __('Semester') }} {{ $semester->semester_number ?? '?' }} — {{ __('Select classroom then course to record scores') }}
                </p>
            </div>
        </div>

        @if(session('status'))
        <div class="mb-6 p-4 bg-emerald-50 dark:bg-emerald-900/20 border border-emerald-200 dark:border-emerald-800 rounded-2xl text-emerald-700 dark:text-emerald-300 text-sm">{{ session('status') }}</div>
        @endif

        @if($openedClassrooms->isEmpty())
        <div class="bg-white dark:bg-[#242526] rounded-[2rem] shadow-sm border border-gray-100 dark:border-[#3a3b3c] p-10 text-center text-gray-400">
            <i class="fas fa-door-closed text-3xl mb-3"></i>
            <p class="text-sm font-medium">{{ __('No classrooms opened for this term yet') }}</p>
        </div>
        @else

        {{-- Classroom cards --}}
        <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-5 gap-3 mb-6">
            @foreach($openedClassrooms as $oc)
            @php $isSelected = $selectedGradeId == $oc->grade_id && $selectedClassroomId == $oc->classroom_id; @endphp
            <a href="{{ route('admin.student-scores.index', ['grade_id' => $oc->grade_id, 'classroom_id' => $oc->classroom_id]) }}"
               class="block p-4 rounded-2xl border transition-all {{ $isSelected ? 'bg-indigo-50 dark:bg-indigo-900/20 border-indigo-200 dark:border-indigo-800' : 'bg-white dark:bg-[#242526] border-gray-100 dark:border-[#3a3b3c] hover:border-indigo-200' }}">
                <div class="text-sm font-extrabold {{ $isSelected ? 'text-indigo-600 dark:text-indigo-400' : 'text-gray-800 dark:text-white' }}">
                    {{ $oc->grade->name_th ?? '' }} / {{ $oc->classroom->name ?? '' }}
                </div>
            </a>
            @endforeach
        </div>

        {{-- Course list --}}
        @if($selectedGradeId && $selectedClassroomId)
        <div class="bg-white dark:bg-[#242526] rounded-[2rem] shadow-sm border border-gray-100 dark:border-[#3a3b3c] p-6">
            <h2 class="text-sm font-bold text-gray-400 dark:text-gray-500 uppercase tracking-widest mb-4">{{ __('Courses in this classroom') }}</h2>
            @if($openedCourses->isEmpty())
            <p class="text-sm text-gray-400 py-6 text-center">{{ __('No courses opened for this classroom') }}</p>
            @else
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-3">
                @foreach($openedCourses as $oc)
                <a href="{{ route('admin.student-scores.entry', $oc->id) }}"
                   class="block p-4 rounded-2xl border border-gray-100 dark:border-[#3a3b3c] bg-gray-50/50 dark:bg-[#3a3b3c]/30 hover:border-indigo-300 hover:shadow-sm transition-all">
                    <div class="text-sm font-bold text-gray-800 dark:text-white">{{ $oc->course->name ?? '?' }}</div>
                    <div class="text-[11px] text-gray-400 mt-1">
                        {{ $oc->course->subjectGroup->name_th ?? '' }}
                        @if($oc->course?->teachers?->isNotEmpty()) · <i class="fas fa-chalkboard-teacher"></i> {{ $oc->course->teachers->pluck('name')->take(2)->join(', ') }}@endif
                    </div>
                    <div class="mt-2 text-[11px] font-bold {{ $oc->scored_count > 0 ? 'text-emerald-500' : 'text-gray-300' }}">
                        <i class="fas fa-clipboard-check mr-1"></i>{{ $oc->scored_count }} {{ __('scores recorded') }}
                    </div>
                </a>
                @endforeach
            </div>
            @endif
        </div>
        @else
        <div class="bg-white dark:bg-[#242526] rounded-[2rem] shadow-sm border border-gray-100 dark:border-[#3a3b3c] p-10 text-center text-gray-400">
            <i class="fas fa-hand-pointer text-2xl mb-3"></i>
            <p class="text-sm font-medium">{{ __('Select a classroom above') }}</p>
        </div>
        @endif
        @endif

    </div>
</div>
@endsection
