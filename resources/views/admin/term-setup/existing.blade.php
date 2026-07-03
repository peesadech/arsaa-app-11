@extends('layouts.app')

@section('content')
<div class="min-h-screen bg-gray-50 dark:bg-[#18191a] py-10 px-4 sm:px-6 lg:px-8 transition-colors duration-300">
    <div class="max-w-5xl mx-auto">

        {{-- Header --}}
        <div class="flex items-center justify-between mb-8">
            <div class="flex items-center space-x-4">
                <a href="{{ route('admin.dashboard') }}"
                   class="group flex items-center justify-center w-10 h-10 rounded-full bg-white dark:bg-[#242526] shadow-sm border border-gray-200 dark:border-[#3a3b3c] text-gray-500 dark:text-gray-400 hover:text-indigo-600 dark:hover:text-indigo-400 hover:border-indigo-300 transition-all duration-200">
                    <i class="fas fa-arrow-left group-hover:-translate-x-0.5 transition-transform"></i>
                </a>
                <div>
                    <h1 class="text-2xl font-extrabold text-gray-900 dark:text-white tracking-tight">{{ __('Existing terms') }}</h1>
                    <p class="text-sm text-gray-500 dark:text-gray-400 font-medium px-1">{{ __('Select a term to open it') }}</p>
                </div>
            </div>
            <a href="{{ route('admin.term-setup.index') }}" class="btn-app">
                <i class="fas fa-rocket text-[10px]"></i> {{ __('New Term Setup') }}
            </a>
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

        @if($existingTerms->isEmpty())
        <div class="bg-white dark:bg-[#242526] rounded-[2rem] shadow-sm border border-gray-100 dark:border-[#3a3b3c] p-10 text-center text-gray-400">
            <i class="fas fa-calendar-times text-3xl mb-3"></i>
            <p class="text-sm font-medium">{{ __('No previous term with data found') }}</p>
        </div>
        @else
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
            @foreach($existingTerms as $term)
            @php $isCurrent = $term['academic_year_id'] == $yearId && $term['semester_id'] == $semesterId; @endphp
            <div class="bg-white dark:bg-[#242526] p-5 rounded-[2rem] shadow-sm border {{ $isCurrent ? 'border-indigo-200 dark:border-indigo-800' : 'border-gray-100 dark:border-[#3a3b3c]' }} transition-all">
                <div class="flex items-center justify-between mb-3">
                    <div class="text-base font-extrabold text-gray-800 dark:text-white">
                        {{ __('Academic Year') }} {{ $term['year'] }} / {{ __('Semester') }} {{ $term['semester_number'] }}
                    </div>
                    @if($isCurrent)
                    <span class="px-2 py-0.5 rounded-lg bg-indigo-100 dark:bg-indigo-900/40 text-indigo-600 dark:text-indigo-300 text-[10px] font-bold uppercase">{{ __('Currently viewing') }}</span>
                    @endif
                </div>
                <div class="grid grid-cols-2 gap-2 text-[11px] text-gray-500 dark:text-gray-400 mb-4">
                    <div><i class="fas fa-layer-group mr-1 text-gray-300"></i>{{ __('Grade Levels') }}: <span class="font-bold text-gray-700 dark:text-gray-300">{{ $term['summary']['opened_grades']['count'] }}</span></div>
                    <div><i class="fas fa-school mr-1 text-gray-300"></i>{{ __('Classrooms') }}: <span class="font-bold text-gray-700 dark:text-gray-300">{{ $term['summary']['opened_classrooms']['count'] }}</span></div>
                    <div><i class="fas fa-book mr-1 text-gray-300"></i>{{ __('Courses') }}: <span class="font-bold text-gray-700 dark:text-gray-300">{{ $term['summary']['opened_courses']['count'] }}</span></div>
                    <div><i class="fas fa-user-check mr-1 text-gray-300"></i>{{ __('Teachers') }}: <span class="font-bold text-gray-700 dark:text-gray-300">{{ $term['summary']['teacher_term_statuses']['count'] }}</span></div>
                    <div><i class="fas fa-calendar-alt mr-1 text-gray-300"></i>{{ __('Schedules') }}: <span class="font-bold text-gray-700 dark:text-gray-300">{{ $term['summary']['yearly_schedules']['count'] }}</span></div>
                </div>
                @unless($isCurrent)
                <form action="{{ route('admin.academic-years.select-current') }}" method="POST">
                    @csrf
                    <input type="hidden" name="academic_year_id" value="{{ $term['academic_year_id'] }}">
                    <input type="hidden" name="semester_id" value="{{ $term['semester_id'] }}">
                    <input type="hidden" name="redirect_dashboard" value="1">
                    <button type="submit" class="btn-app w-full justify-center">
                        <i class="fas fa-sign-in-alt text-[10px]"></i> {{ __('Open this term') }}
                    </button>
                </form>
                @endunless
            </div>
            @endforeach
        </div>
        @endif

    </div>
</div>
@endsection
