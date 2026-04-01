@extends('layouts.app')

@php
    $isEdit = isset($course);
    $actionUrl = $isEdit ? route('admin.courses.update', $course->id) : route('admin.courses.store');
    
    $title = $isEdit ? __('Edit Course') : __('Create New Course');
    $subtitle = $isEdit ? __('Update course details') : __('Course Registration');
    
    // Theme Configuration
    $gradientClass = $isEdit ? 'bg-gradient-to-r from-amber-500 via-orange-500 to-rose-500' : 'bg-gradient-to-r from-indigo-500 via-purple-500 to-pink-500';
    $blurClass = $isEdit ? 'bg-amber-500/20' : 'bg-indigo-500/20';
    $iconBgClass = $isEdit ? 'bg-amber-50 border-amber-100' : 'bg-indigo-50 border-indigo-100 shadow-inner';
    $iconClass = $isEdit ? 'fa-edit text-amber-600 rotate-3' : 'fa-plus text-indigo-600 -rotate-3';
    $cardTitle = $isEdit ? __('Modify Course') : __('Course Details');
    $cardDesc = $isEdit
        ? __('You are updating course #:id. Ensure all details are correct.', ['id' => $course->id])
        : __('Define a new system course.');
    
    $focusRing = $isEdit ? 'focus:border-amber-400' : 'focus:border-indigo-500';
    $focusText = $isEdit ? 'group-focus-within:text-amber-500' : 'group-focus-within:text-indigo-500';
    
    $btnClass = $isEdit 
        ? 'bg-amber-500 text-white hover:bg-amber-600 shadow-amber-200'
        : 'bg-indigo-600 text-white hover:bg-indigo-700 shadow-indigo-200';
    
    $btnText = $isEdit ? __('Save Changes') : __('Create Course');
    $btnIcon = $isEdit ? 'fa-save' : 'fa-check-circle';
@endphp

@section('content')
<div class="min-h-screen bg-gray-50 dark:bg-[#18191a] py-12 px-4 sm:px-6 lg:px-8 transition-colors duration-300">
    <div class="max-w-2xl mx-auto">
        <!-- Breadcrumb / Header -->
        <div class="flex items-center space-x-4 mb-8">
            <a href="{{ route('admin.courses.index') }}" 
               class="group flex items-center justify-center w-10 h-10 rounded-xl bg-white dark:bg-[#242526] shadow-sm border border-gray-200 dark:border-[#3a3b3c] text-gray-500 dark:text-gray-400 hover:text-indigo-600 dark:hover:text-indigo-400 hover:border-indigo-300 transition-all duration-200">
                <i class="fas fa-arrow-left group-hover:-translate-x-0.5 transition-transform"></i>
            </a>
            <div>
                <h1 class="text-2xl font-extrabold text-gray-900 dark:text-white tracking-tight">{{ $title }}</h1>
                <p class="text-sm text-gray-500 dark:text-gray-400 font-medium px-1">{{ $subtitle }}</p>
            </div>
        </div>

        <!-- Main Card -->
        <div class="bg-white dark:bg-[#242526] rounded-[2.5rem] shadow-xl shadow-gray-200/50 dark:shadow-none border border-gray-100 dark:border-[#3a3b3c] overflow-hidden transform transition-all">
            <!-- Decorative Top Border -->
            <div class="h-2 {{ $gradientClass }}"></div>
            
            <div class="p-8 sm:p-10">
                <!-- Visual Identity Section -->
                <div class="flex flex-col items-center text-center mb-10">
                    <div class="relative">
                        <div class="absolute inset-0 {{ $blurClass }} blur-2xl rounded-full"></div>
                        <div class="relative w-20 h-20 rounded-2xl flex items-center justify-center mb-4 transform border {{ $iconBgClass }}">
                            <i class="fas {{ $iconClass }} text-3xl"></i>
                        </div>
                    </div>
                    <h2 class="text-xl font-bold text-gray-800 dark:text-white mb-2">{{ $cardTitle }}</h2>
                    <p class="text-sm text-gray-500 dark:text-gray-400 max-w-xs mx-auto">
                        {{ $cardDesc }}
                    </p>
                </div>

                <!-- Form -->
                <form action="{{ $actionUrl }}" method="POST" class="space-y-6" id="courseForm">
                    @csrf
                    @if($isEdit)
                        @method('PUT')
                    @endif
                    
                    <div class="grid grid-cols-1 gap-6">
                        <!-- Name -->
                        <div class="space-y-2">
                            <label for="name" class="block text-xs font-bold text-gray-400 dark:text-gray-500 uppercase tracking-widest px-1">
                                {{ __('Name (Course)') }}
                            </label>
                            <div class="group relative">
                                <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none text-gray-400 {{ $focusText }} transition-colors">
                                    <i class="fas fa-book text-sm"></i>
                                </div>
                                <input
                                    type="text"
                                    id="name"
                                    name="name"
                                    class="block w-full pl-10 pr-4 py-4 bg-gray-50 dark:bg-[#3a3b3c] border-2 border-transparent rounded-2xl text-gray-900 dark:text-white placeholder-gray-400 focus:outline-none focus:ring-0 {{ $focusRing }} focus:bg-white dark:focus:bg-[#3a3b3c] transition-all duration-200 @error('name') border-rose-300 bg-rose-50 dark:bg-rose-900/20 @enderror"
                                    placeholder="e.g. Mathematics 1"
                                    value="{{ old('name', $isEdit ? $course->name : '') }}"
                                    required
                                />
                            </div>
                            @error('name')
                                <p class="text-[10px] font-bold text-rose-500 mt-1 px-1">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>

                    <!-- Subject Group -->
                    <div class="space-y-2">
                        <label for="subject_group_id" class="block text-xs font-bold text-gray-400 dark:text-gray-500 uppercase tracking-widest px-1">
                            {{ __('Subject Group') }}
                        </label>
                        <div class="group relative">
                            <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none text-gray-400 {{ $focusText }} transition-colors">
                                <i class="fas fa-th-large text-sm"></i>
                            </div>
                            <select
                                id="subject_group_id"
                                name="subject_group_id"
                                class="block w-full pl-10 pr-10 py-4 bg-gray-50 dark:bg-[#3a3b3c] border-2 border-transparent rounded-2xl text-gray-900 dark:text-white appearance-none focus:outline-none focus:ring-0 {{ $focusRing }} focus:bg-white dark:focus:bg-[#3a3b3c] transition-all duration-200 @error('subject_group_id') border-rose-300 bg-rose-50 dark:bg-rose-900/20 @enderror"
                                required
                            >
                                <option value="" disabled {{ !old('subject_group_id', $isEdit ? $course->subject_group_id : '') ? 'selected' : '' }}>{{ __('-- Please Select --') }}</option>
                                @foreach($subjectGroups as $sg)
                                    <option value="{{ $sg->id }}" {{ old('subject_group_id', $isEdit ? $course->subject_group_id : '') == $sg->id ? 'selected' : '' }}>
                                        {{ $sg->name_th }} / {{ $sg->name_en }}
                                    </option>
                                @endforeach
                            </select>
                            <div class="absolute inset-y-0 right-0 pr-4 flex items-center pointer-events-none text-gray-400">
                                <i class="fas fa-chevron-down text-sm"></i>
                            </div>
                        </div>
                        @error('subject_group_id')
                            <p class="text-[10px] font-bold text-rose-500 mt-1 px-1">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Course Type -->
                    <div class="space-y-2">
                        <label for="course_type_id" class="block text-xs font-bold text-gray-400 dark:text-gray-500 uppercase tracking-widest px-1">
                            {{ __('Course Type') }}
                        </label>
                        <div class="group relative">
                            <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none text-gray-400 {{ $focusText }} transition-colors">
                                <i class="fas fa-tags text-sm"></i>
                            </div>
                            <select
                                id="course_type_id"
                                name="course_type_id"
                                class="block w-full pl-10 pr-10 py-4 bg-gray-50 dark:bg-[#3a3b3c] border-2 border-transparent rounded-2xl text-gray-900 dark:text-white appearance-none focus:outline-none focus:ring-0 {{ $focusRing }} focus:bg-white dark:focus:bg-[#3a3b3c] transition-all duration-200 @error('course_type_id') border-rose-300 bg-rose-50 dark:bg-rose-900/20 @enderror"
                                required
                            >
                                <option value="" disabled {{ !old('course_type_id', $isEdit ? $course->course_type_id : '') ? 'selected' : '' }}>{{ __('-- Please Select --') }}</option>
                                @foreach($courseTypes as $ct)
                                    <option value="{{ $ct->id }}" {{ old('course_type_id', $isEdit ? $course->course_type_id : '') == $ct->id ? 'selected' : '' }}>
                                        {{ $ct->name_th }} / {{ $ct->name_en }}
                                    </option>
                                @endforeach
                            </select>
                            <div class="absolute inset-y-0 right-0 pr-4 flex items-center pointer-events-none text-gray-400">
                                <i class="fas fa-chevron-down text-sm"></i>
                            </div>
                        </div>
                        @error('course_type_id')
                            <p class="text-[10px] font-bold text-rose-500 mt-1 px-1">{{ $message }}</p>
                        @enderror
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <!-- Grade ID -->
                        <div class="space-y-2">
                            <label for="grade_id" class="block text-xs font-bold text-gray-400 dark:text-gray-500 uppercase tracking-widest px-1">
                                {{ __('Select Grade') }}
                            </label>
                            <div class="group relative">
                                <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none text-gray-400 {{ $focusText }} transition-colors">
                                    <i class="fas fa-layer-group text-sm"></i>
                                </div>
                                <select
                                    id="grade_id"
                                    name="grade_id"
                                    class="block w-full pl-10 pr-10 py-4 bg-gray-50 dark:bg-[#3a3b3c] border-2 border-transparent rounded-2xl text-gray-900 dark:text-white appearance-none focus:outline-none focus:ring-0 {{ $focusRing }} focus:bg-white dark:focus:bg-[#3a3b3c] transition-all duration-200 @error('grade_id') border-rose-300 bg-rose-50 dark:bg-rose-900/20 @enderror"
                                    required
                                >
                                    <option value="" disabled {{ !old('grade_id', $isEdit ? $course->grade_id : '') ? 'selected' : '' }}>{{ __('-- Please Select --') }}</option>
                                    @foreach($grades as $grade)
                                        <option value="{{ $grade->id }}" {{ old('grade_id', $isEdit ? $course->grade_id : '') == $grade->id ? 'selected' : '' }}>
                                            {{ $grade->name_th }} / {{ $grade->name_en }}
                                        </option>
                                    @endforeach
                                </select>
                                <div class="absolute inset-y-0 right-0 pr-4 flex items-center pointer-events-none text-gray-400">
                                    <i class="fas fa-chevron-down text-sm"></i>
                                </div>
                            </div>
                            @error('grade_id')
                                <p class="text-[10px] font-bold text-rose-500 mt-1 px-1">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Semester ID -->
                        <div class="space-y-2">
                            <label for="semester_id" class="block text-xs font-bold text-gray-400 dark:text-gray-500 uppercase tracking-widest px-1">
                                {{ __('Select Semester') }}
                            </label>
                            <div class="group relative">
                                <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none text-gray-400 {{ $focusText }} transition-colors">
                                    <i class="fas fa-list-ol text-sm"></i>
                                </div>
                                <select
                                    id="semester_id"
                                    name="semester_id"
                                    class="block w-full pl-10 pr-10 py-4 bg-gray-50 dark:bg-[#3a3b3c] border-2 border-transparent rounded-2xl text-gray-900 dark:text-white appearance-none focus:outline-none focus:ring-0 {{ $focusRing }} focus:bg-white dark:focus:bg-[#3a3b3c] transition-all duration-200 @error('semester_id') border-rose-300 bg-rose-50 dark:bg-rose-900/20 @enderror"
                                    required
                                >
                                    <option value="" disabled {{ !old('semester_id', $isEdit ? $course->semester_id : '') ? 'selected' : '' }}>{{ __('-- Please Select --') }}</option>
                                    @foreach($semesters as $semester)
                                        <option value="{{ $semester->id }}" {{ old('semester_id', $isEdit ? $course->semester_id : '') == $semester->id ? 'selected' : '' }}>
                                            {{ __('Semester') }} {{ $semester->semester_number }}
                                        </option>
                                    @endforeach
                                </select>
                                <div class="absolute inset-y-0 right-0 pr-4 flex items-center pointer-events-none text-gray-400">
                                    <i class="fas fa-chevron-down text-sm"></i>
                                </div>
                            </div>
                            @error('semester_id')
                                <p class="text-[10px] font-bold text-rose-500 mt-1 px-1">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>

                    <!-- Scheduling: Periods per Week + Periods per Session + Preferred Days -->
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                        <!-- Periods per Week -->
                        <div class="space-y-2">
                            <label for="periods_per_week" class="block text-xs font-bold text-gray-400 dark:text-gray-500 uppercase tracking-widest px-1">
                                {{ __('Periods per Week') }}
                            </label>
                            <p class="text-[10px] text-gray-400 dark:text-gray-500 px-1">{{ __('Total periods in a week') }}</p>
                            <div class="group relative">
                                <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none text-gray-400 {{ $focusText }} transition-colors">
                                    <i class="fas fa-clock text-sm"></i>
                                </div>
                                <input
                                    type="number"
                                    id="periods_per_week"
                                    name="periods_per_week"
                                    min="1"
                                    max="20"
                                    class="block w-full pl-10 pr-16 py-4 bg-gray-50 dark:bg-[#3a3b3c] border-2 border-transparent rounded-2xl text-gray-900 dark:text-white placeholder-gray-400 focus:outline-none focus:ring-0 {{ $focusRing }} focus:bg-white dark:focus:bg-[#3a3b3c] transition-all duration-200 @error('periods_per_week') border-rose-300 bg-rose-50 dark:bg-rose-900/20 @enderror"
                                    placeholder="e.g. 2"
                                    value="{{ old('periods_per_week', $isEdit ? $course->periods_per_week : 1) }}"
                                    required
                                />
                                <div class="absolute inset-y-0 right-0 pr-4 flex items-center pointer-events-none text-xs font-bold text-gray-400 dark:text-gray-500">
                                    {{ __('periods') }}
                                </div>
                            </div>
                            @error('periods_per_week')
                                <p class="text-[10px] font-bold text-rose-500 mt-1 px-1">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Periods per Session -->
                        <div class="space-y-2">
                            <label for="periods_per_session" class="block text-xs font-bold text-gray-400 dark:text-gray-500 uppercase tracking-widest px-1">
                                {{ __('Periods per Session') }}
                            </label>
                            <p class="text-[10px] text-gray-400 dark:text-gray-500 px-1">{{ __('Consecutive periods per class') }}</p>
                            <div class="group relative">
                                <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none text-gray-400 {{ $focusText }} transition-colors">
                                    <i class="fas fa-layer-group text-sm"></i>
                                </div>
                                <input
                                    type="number"
                                    id="periods_per_session"
                                    name="periods_per_session"
                                    min="1"
                                    max="10"
                                    class="block w-full pl-10 pr-16 py-4 bg-gray-50 dark:bg-[#3a3b3c] border-2 border-transparent rounded-2xl text-gray-900 dark:text-white placeholder-gray-400 focus:outline-none focus:ring-0 {{ $focusRing }} focus:bg-white dark:focus:bg-[#3a3b3c] transition-all duration-200 @error('periods_per_session') border-rose-300 bg-rose-50 dark:bg-rose-900/20 @enderror"
                                    placeholder="e.g. 2"
                                    value="{{ old('periods_per_session', $isEdit ? ($course->periods_per_session ?? 1) : 1) }}"
                                    required
                                />
                                <div class="absolute inset-y-0 right-0 pr-4 flex items-center pointer-events-none text-xs font-bold text-gray-400 dark:text-gray-500">
                                    {{ __('periods') }}
                                </div>
                            </div>
                            @error('periods_per_session')
                                <p class="text-[10px] font-bold text-rose-500 mt-1 px-1">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Preferred Days -->
                        <div class="space-y-2">
                            <label class="block text-xs font-bold text-gray-400 dark:text-gray-500 uppercase tracking-widest px-1">
                                {{ __('Preferred Days') }}
                            </label>
                            <p class="text-[10px] text-gray-400 dark:text-gray-500 px-1">{{ __('Leave empty for any day') }}</p>
                            @php
                                $savedDays = old('preferred_days', $isEdit ? ($course->preferred_days ?? []) : []);
                                $dayOptions = [
                                    1 => __('Mon'),
                                    2 => __('Tue'),
                                    3 => __('Wed'),
                                    4 => __('Thu'),
                                    5 => __('Fri'),
                                    6 => __('Sat'),
                                    7 => __('Sun'),
                                ];
                            @endphp
                            <div class="flex flex-wrap gap-2 pt-1">
                                @foreach($dayOptions as $dayNum => $dayLabel)
                                <label class="relative group cursor-pointer">
                                    <input type="checkbox" name="preferred_days[]" value="{{ $dayNum }}" class="peer hidden" {{ in_array($dayNum, $savedDays) ? 'checked' : '' }}>
                                    <div class="px-3.5 py-2.5 rounded-xl border-2 border-gray-100 dark:border-[#3a3b3c] bg-white dark:bg-[#242526] text-xs font-bold text-gray-500 dark:text-gray-400 transition-all duration-200 peer-checked:border-indigo-500 peer-checked:bg-indigo-50 dark:peer-checked:bg-indigo-900/30 peer-checked:text-indigo-600 dark:peer-checked:text-indigo-400 group-hover:border-gray-200 dark:group-hover:border-[#4a4b4c]">
                                        {{ $dayLabel }}
                                    </div>
                                    <div class="absolute -top-1 -right-1 w-3.5 h-3.5 bg-indigo-500 rounded-full flex items-center justify-center text-[7px] text-white opacity-0 peer-checked:opacity-100 transition-all scale-50 peer-checked:scale-100">
                                        <i class="fas fa-check"></i>
                                    </div>
                                </label>
                                @endforeach
                            </div>
                            @error('preferred_days')
                                <p class="text-[10px] font-bold text-rose-500 mt-1 px-1">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>

                    <!-- Status -->
                    <div class="space-y-3">
                        <label class="block text-xs font-bold text-gray-400 dark:text-gray-500 uppercase tracking-widest px-1">
                            {{ __('Status') }}
                        </label>
                        <div class="flex flex-wrap gap-2">
                            <!-- Active Status -->
                            <label class="relative group cursor-pointer">
                                <input type="radio" name="status" value="1" class="peer hidden" {{ old('status', $isEdit ? $course->status : 1) == 1 ? 'checked' : '' }}>
                                <div class="px-4 py-2.5 rounded-xl border-2 border-gray-100 dark:border-[#3a3b3c] bg-white dark:bg-[#242526] text-sm font-bold text-gray-500 dark:text-gray-400 transition-all duration-200 peer-checked:border-emerald-500 peer-checked:bg-emerald-50 dark:peer-checked:bg-emerald-900/30 peer-checked:text-emerald-600 dark:peer-checked:text-emerald-400 group-hover:border-gray-200 dark:group-hover:border-[#4a4b4c]">
                                    <div class="flex items-center">
                                        <i class="fas fa-check-circle mr-2 text-[10px] opacity-50"></i>
                                        {{ __('Active') }}
                                    </div>
                                </div>
                                <div class="absolute -top-1 -right-1 w-4 h-4 bg-emerald-500 rounded-full flex items-center justify-center text-[8px] text-white opacity-0 peer-checked:opacity-100 transition-all scale-50 peer-checked:scale-100">
                                    <i class="fas fa-check"></i>
                                </div>
                            </label>
                            
                            <!-- Not Active Status -->
                            <label class="relative group cursor-pointer">
                                <input type="radio" name="status" value="2" class="peer hidden" {{ old('status', $isEdit ? $course->status : 1) == 2 ? 'checked' : '' }}>
                                <div class="px-4 py-2.5 rounded-xl border-2 border-gray-100 dark:border-[#3a3b3c] bg-white dark:bg-[#242526] text-sm font-bold text-gray-500 dark:text-gray-400 transition-all duration-200 peer-checked:border-rose-500 peer-checked:bg-rose-50 dark:peer-checked:bg-rose-900/30 peer-checked:text-rose-600 dark:peer-checked:text-rose-400 group-hover:border-gray-200 dark:group-hover:border-[#4a4b4c]">
                                    <div class="flex items-center">
                                        <i class="fas fa-times-circle mr-2 text-[10px] opacity-50"></i>
                                        {{ __('Not Active') }}
                                    </div>
                                </div>
                                <div class="absolute -top-1 -right-1 w-4 h-4 bg-rose-500 rounded-full flex items-center justify-center text-[8px] text-white opacity-0 peer-checked:opacity-100 transition-all scale-50 peer-checked:scale-100">
                                    <i class="fas fa-check"></i>
                                </div>
                            </label>
                        </div>
                        @error('status')
                            <p class="text-[10px] font-bold text-rose-500 mt-1 px-1">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Actions -->
                    <div class="flex flex-col sm:flex-row space-y-3 sm:space-y-0 sm:space-x-4 pt-6">
                        <button 
                            type="submit"
                            class="flex-1 group relative flex items-center justify-center px-8 py-4 {{ $btnClass }} font-bold rounded-2xl active:scale-95 transition-all duration-200 shadow-lg overflow-hidden"
                        >
                            <span class="relative z-10 flex items-center">
                                <i class="fas {{ $btnIcon }} mr-2 opacity-50 group-hover:opacity-100 transition-opacity"></i>
                                {{ $btnText }}
                            </span>
                            <div class="absolute inset-0 bg-gradient-to-r from-transparent via-white/10 to-transparent -translate-x-full group-hover:translate-x-full transition-transform duration-1000 ease-in-out"></div>
                        </button>
                        
                        <a href="{{ route('admin.courses.index') }}" 
                           class="flex-1 flex items-center justify-center px-8 py-4 bg-white dark:bg-[#242526] text-gray-700 dark:text-gray-300 font-bold rounded-2xl border-2 border-gray-100 dark:border-[#3a3b3c] hover:border-gray-200 dark:hover:border-[#4a4b4c] hover:bg-gray-50 dark:hover:bg-[#3a3b3c] active:scale-95 transition-all duration-200">
                            {{ $isEdit ? __('Cancel') : __('Back to List') }}
                        </a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
