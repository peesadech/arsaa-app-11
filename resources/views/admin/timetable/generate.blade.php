@extends('layouts.app')

@section('content')
<div class="min-h-screen bg-gray-50 dark:bg-[#18191a] py-10 px-4 sm:px-6 lg:px-8 transition-colors duration-300">
    <div class="max-w-3xl mx-auto">

        {{-- Header --}}
        <div class="flex items-center space-x-4 mb-8">
            <a href="{{ route('admin.timetable.index') }}"
               class="group flex items-center justify-center w-10 h-10 rounded-full bg-white dark:bg-[#242526] shadow-sm border border-gray-200 dark:border-[#3a3b3c] text-gray-500 dark:text-gray-400 hover:text-indigo-600 dark:hover:text-indigo-400 hover:border-indigo-300 transition-all duration-200">
                <i class="fas fa-arrow-left group-hover:-translate-x-0.5 transition-transform"></i>
            </a>
            <div>
                <h1 class="text-2xl font-extrabold text-gray-900 dark:text-white tracking-tight">{{ __('Generate Timetable') }}</h1>
                <p class="text-sm text-gray-500 dark:text-gray-400 font-medium px-1">{{ __('Genetic Algorithm Timetable Generator') }}</p>
            </div>
        </div>

        {{-- Form --}}
        <form action="{{ route('admin.timetable.generate.store') }}" method="POST">
            @csrf
            <div class="bg-white dark:bg-[#242526] rounded-[2rem] shadow-sm border border-gray-100 dark:border-[#3a3b3c] p-6 space-y-6">

                {{-- Algorithm Settings --}}
                <div>
                    <h3 class="text-sm font-bold text-gray-700 dark:text-gray-300 uppercase tracking-wide mb-4">{{ __('Algorithm Settings') }}</h3>
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-600 dark:text-gray-400 mb-1">{{ __('Population Size') }}</label>
                            <input type="number" name="population_size" value="30" min="10" max="100"
                                   class="w-full px-4 py-3 bg-gray-50 dark:bg-[#3a3b3c] border-2 border-transparent rounded-2xl text-gray-800 dark:text-white focus:border-indigo-500 focus:outline-none transition-all">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-600 dark:text-gray-400 mb-1">{{ __('Max Generations') }}</label>
                            <input type="number" name="max_generations" value="500" min="50" max="2000"
                                   class="w-full px-4 py-3 bg-gray-50 dark:bg-[#3a3b3c] border-2 border-transparent rounded-2xl text-gray-800 dark:text-white focus:border-indigo-500 focus:outline-none transition-all">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-600 dark:text-gray-400 mb-1">{{ __('Number of Solutions') }}</label>
                            <input type="number" name="solutions_requested" value="3" min="1" max="10"
                                   class="w-full px-4 py-3 bg-gray-50 dark:bg-[#3a3b3c] border-2 border-transparent rounded-2xl text-gray-800 dark:text-white focus:border-indigo-500 focus:outline-none transition-all">
                        </div>
                    </div>
                </div>

                {{-- Scope --}}
                <div>
                    <h3 class="text-sm font-bold text-gray-700 dark:text-gray-300 uppercase tracking-wide mb-4">{{ __('Scope (none = all)') }}</h3>

                    <div class="mb-4">
                        <label class="block text-sm font-medium text-gray-600 dark:text-gray-400 mb-2">{{ __('Grade Level') }}</label>
                        <div class="grid grid-cols-2 md:grid-cols-3 gap-2">
                            @foreach(\App\Models\Grade::where('status', 1)->with('educationLevel')->get() as $grade)
                            <label class="flex items-center space-x-2 p-2 bg-gray-50 dark:bg-[#3a3b3c] rounded-xl cursor-pointer hover:bg-gray-100 dark:hover:bg-[#4a4b4c] transition-colors">
                                <input type="checkbox" name="scope_grade_ids[]" value="{{ $grade->id }}"
                                       class="rounded border-gray-300 text-indigo-600 focus:ring-indigo-500">
                                <span class="text-sm text-gray-700 dark:text-gray-300">{{ $grade->name_th }}</span>
                            </label>
                            @endforeach
                        </div>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-600 dark:text-gray-400 mb-2">{{ __('Classroom') }}</label>
                        <div class="grid grid-cols-2 md:grid-cols-4 gap-2">
                            @foreach(\App\Models\Classroom::where('status', 1)->get() as $classroom)
                            <label class="flex items-center space-x-2 p-2 bg-gray-50 dark:bg-[#3a3b3c] rounded-xl cursor-pointer hover:bg-gray-100 dark:hover:bg-[#4a4b4c] transition-colors">
                                <input type="checkbox" name="scope_classroom_ids[]" value="{{ $classroom->id }}"
                                       class="rounded border-gray-300 text-indigo-600 focus:ring-indigo-500">
                                <span class="text-sm text-gray-700 dark:text-gray-300">{{ $classroom->name }}</span>
                            </label>
                            @endforeach
                        </div>
                    </div>
                </div>

                {{-- Info box --}}
                <div class="p-4 bg-blue-50 dark:bg-blue-900/20 border border-blue-100 dark:border-blue-800 rounded-2xl text-blue-700 dark:text-blue-300 text-sm">
                    <i class="fas fa-info-circle mr-1"></i>
                    {{ __('Generate info line 1') }}
                    {{ __('Generate info line 2') }}
                </div>

                <div class="flex justify-end">
                    <button type="submit" class="btn-app px-8"
                            onclick="return confirm('{{ __('Confirm generate timetable?') }}')">
                        <i class="fas fa-play text-[10px]"></i> {{ __('Start Generate') }}
                    </button>
                </div>
            </div>
        </form>
    </div>
</div>
@endsection
