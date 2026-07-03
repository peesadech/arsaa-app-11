@extends('layouts.app')

@section('content')
@php
    $inputClass = 'w-full px-4 py-2.5 bg-gray-50 dark:bg-[#3a3b3c] border-2 border-transparent rounded-xl text-sm text-gray-800 dark:text-white focus:border-indigo-500 focus:outline-none transition-all';
    $labelClass = 'block text-xs font-medium text-gray-500 dark:text-gray-400 mb-1';
@endphp
<div class="min-h-screen bg-gray-50 dark:bg-[#18191a] py-10 px-4 sm:px-6 lg:px-8 transition-colors duration-300">
    <div class="max-w-5xl mx-auto">

        {{-- Header --}}
        <div class="flex items-center space-x-4 mb-8">
            <a href="{{ route('admin.students.index') }}"
               class="group flex items-center justify-center w-10 h-10 rounded-full bg-white dark:bg-[#242526] shadow-sm border border-gray-200 dark:border-[#3a3b3c] text-gray-500 dark:text-gray-400 hover:text-indigo-600 dark:hover:text-indigo-400 hover:border-indigo-300 transition-all duration-200">
                <i class="fas fa-arrow-left group-hover:-translate-x-0.5 transition-transform"></i>
            </a>
            <div>
                <h1 class="text-2xl font-extrabold text-gray-900 dark:text-white tracking-tight">{{ __('Student Reports') }}</h1>
                <p class="text-sm text-gray-500 dark:text-gray-400 font-medium px-1">{{ __('Export and print student reports') }}</p>
            </div>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">

            {{-- Student list export --}}
            <div class="bg-white dark:bg-[#242526] rounded-[2rem] shadow-sm border border-gray-100 dark:border-[#3a3b3c] p-6">
                <div class="flex items-center gap-3 mb-3">
                    <div class="w-10 h-10 rounded-xl bg-indigo-50 dark:bg-indigo-900/20 flex items-center justify-center"><i class="fas fa-file-csv text-indigo-500"></i></div>
                    <h2 class="text-base font-bold text-gray-800 dark:text-white">{{ __('Student list (Excel/CSV)') }}</h2>
                </div>
                <form action="{{ route('admin.student-reports.students-csv') }}" method="GET" class="space-y-3">
                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <label class="{{ $labelClass }}">{{ __('Academic Year') }}</label>
                            <select name="academic_year_id" class="{{ $inputClass }}">
                                <option value="">{{ __('All Years') }}</option>
                                @foreach($academicYears as $y)<option value="{{ $y->id }}" {{ $yearId == $y->id ? 'selected' : '' }}>{{ $y->year }}</option>@endforeach
                            </select>
                        </div>
                        <div>
                            <label class="{{ $labelClass }}">{{ __('Semester') }}</label>
                            <select name="semester_id" class="{{ $inputClass }}">
                                <option value="">{{ __('All Semesters') }}</option>
                                @foreach($semesters as $s)<option value="{{ $s->id }}" {{ $semesterId == $s->id ? 'selected' : '' }}>{{ __('Semester') }} {{ $s->semester_number }}</option>@endforeach
                            </select>
                        </div>
                        <div>
                            <label class="{{ $labelClass }}">{{ __('Classroom') }}</label>
                            <select name="classroom_id" class="{{ $inputClass }}">
                                <option value="">{{ __('All Classrooms') }}</option>
                                @foreach($classrooms as $c)<option value="{{ $c->id }}">{{ $c->name }}</option>@endforeach
                            </select>
                        </div>
                        <div>
                            <label class="{{ $labelClass }}">{{ __('Status') }}</label>
                            <select name="status" class="{{ $inputClass }}">
                                <option value="">{{ __('All Status') }}</option>
                                <option value="studying">{{ __('Studying') }}</option>
                                <option value="suspended">{{ __('Suspended') }}</option>
                                <option value="resigned">{{ __('Resigned') }}</option>
                                <option value="graduated">{{ __('Graduated') }}</option>
                            </select>
                        </div>
                    </div>
                    <button type="submit" class="btn-app w-full justify-center"><i class="fas fa-download text-[10px]"></i> {{ __('Export CSV') }}</button>
                </form>
            </div>

            {{-- Class scores report --}}
            <div class="bg-white dark:bg-[#242526] rounded-[2rem] shadow-sm border border-gray-100 dark:border-[#3a3b3c] p-6">
                <div class="flex items-center gap-3 mb-3">
                    <div class="w-10 h-10 rounded-xl bg-emerald-50 dark:bg-emerald-900/20 flex items-center justify-center"><i class="fas fa-table text-emerald-500"></i></div>
                    <h2 class="text-base font-bold text-gray-800 dark:text-white">{{ __('Class scores report') }}</h2>
                </div>
                <p class="text-xs text-gray-400 mb-3">{{ __('Score summary of every student and course in a classroom') }}</p>
                <form action="{{ route('admin.student-reports.class-scores') }}" method="GET" target="_blank" class="space-y-3" id="classScoresForm">
                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <label class="{{ $labelClass }}">{{ __('Academic Year') }} *</label>
                            <select name="academic_year_id" required class="{{ $inputClass }}">
                                @foreach($academicYears as $y)<option value="{{ $y->id }}" {{ $yearId == $y->id ? 'selected' : '' }}>{{ $y->year }}</option>@endforeach
                            </select>
                        </div>
                        <div>
                            <label class="{{ $labelClass }}">{{ __('Semester') }} *</label>
                            <select name="semester_id" required class="{{ $inputClass }}">
                                @foreach($semesters as $s)<option value="{{ $s->id }}" {{ $semesterId == $s->id ? 'selected' : '' }}>{{ __('Semester') }} {{ $s->semester_number }}</option>@endforeach
                            </select>
                        </div>
                        <div>
                            <label class="{{ $labelClass }}">{{ __('Grade Level') }} *</label>
                            <select name="grade_id" required class="{{ $inputClass }}">
                                @foreach(\App\Models\Grade::where('status', 1)->get() as $g)<option value="{{ $g->id }}">{{ $g->name_th }}</option>@endforeach
                            </select>
                        </div>
                        <div>
                            <label class="{{ $labelClass }}">{{ __('Classroom') }} *</label>
                            <select name="classroom_id" required class="{{ $inputClass }}">
                                @foreach($classrooms as $c)<option value="{{ $c->id }}">{{ $c->name }}</option>@endforeach
                            </select>
                        </div>
                    </div>
                    <button type="submit" class="btn-app w-full justify-center"><i class="fas fa-print text-[10px]"></i> {{ __('Open report') }}</button>
                </form>
            </div>

            {{-- Incomplete documents --}}
            <div class="bg-white dark:bg-[#242526] rounded-[2rem] shadow-sm border border-gray-100 dark:border-[#3a3b3c] p-6">
                <div class="flex items-center gap-3 mb-3">
                    <div class="w-10 h-10 rounded-xl bg-amber-50 dark:bg-amber-900/20 flex items-center justify-center"><i class="fas fa-folder-minus text-amber-500"></i></div>
                    <h2 class="text-base font-bold text-gray-800 dark:text-white">{{ __('Students with incomplete documents') }}</h2>
                </div>
                <p class="text-xs text-gray-400 mb-3">{{ __('List of studying students whose application documents are not complete') }}</p>
                <a href="{{ route('admin.student-reports.incomplete-documents') }}" class="btn-app w-full justify-center"><i class="fas fa-list text-[10px]"></i> {{ __('Open report') }}</a>
            </div>

            {{-- Per-student reports hint --}}
            <div class="bg-white dark:bg-[#242526] rounded-[2rem] shadow-sm border border-gray-100 dark:border-[#3a3b3c] p-6">
                <div class="flex items-center gap-3 mb-3">
                    <div class="w-10 h-10 rounded-xl bg-purple-50 dark:bg-purple-900/20 flex items-center justify-center"><i class="fas fa-id-card text-purple-500"></i></div>
                    <h2 class="text-base font-bold text-gray-800 dark:text-white">{{ __('Student profile & Transcript') }}</h2>
                </div>
                <p class="text-xs text-gray-400 mb-3">{{ __('Open from the student list — use the profile icon, or the Transcript button on the student page') }}</p>
                <a href="{{ route('admin.students.index') }}" class="btn-app w-full justify-center"><i class="fas fa-user-graduate text-[10px]"></i> {{ __('Go to student list') }}</a>
            </div>

        </div>
    </div>
</div>
@endsection
