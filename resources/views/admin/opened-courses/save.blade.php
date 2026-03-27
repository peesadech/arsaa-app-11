@extends('layouts.app')

@php
    $isEdit     = isset($openedCourse);
    $actionUrl  = $isEdit
        ? route('admin.opened-courses.update', $openedCourse->id)
        : route('admin.opened-courses.store');

    $gradientClass = $isEdit
        ? 'bg-gradient-to-r from-amber-500 via-orange-500 to-rose-500'
        : 'bg-gradient-to-r from-indigo-500 via-purple-500 to-pink-500';
    $blurClass   = $isEdit ? 'bg-amber-500/20'  : 'bg-indigo-500/20';
    $iconBgClass = $isEdit ? 'bg-amber-50 border-amber-100' : 'bg-indigo-50 border-indigo-100';
    $iconClass   = $isEdit ? 'fa-book text-amber-600' : 'fa-book-open text-indigo-600';
    $focusRing   = $isEdit ? 'focus:border-amber-400' : 'focus:border-indigo-500';
    $focusText   = $isEdit ? 'group-focus-within:text-amber-500' : 'group-focus-within:text-indigo-500';
    $btnText     = $isEdit ? 'บันทึกการแก้ไข' : 'เพิ่มรายวิชา';
    $btnIcon     = $isEdit ? 'fa-save' : 'fa-check-circle';
    $btnClass    = $isEdit
        ? 'bg-amber-500 text-white hover:bg-amber-600 shadow-amber-200'
        : 'bg-indigo-600 text-white hover:bg-indigo-700 shadow-indigo-200';

    $selectedGrade     = old('grade_id',     $isEdit ? $openedCourse->grade_id     : '');
    $selectedClassroom = old('classroom_id', $isEdit ? $openedCourse->classroom_id : '');
    $selectedCourse    = old('course_id',    $isEdit ? $openedCourse->course_id    : '');
@endphp

@section('content')
<div class="min-h-screen bg-gray-50 dark:bg-[#18191a] py-12 px-4 sm:px-6 lg:px-8 transition-colors duration-300">
    <div class="max-w-2xl mx-auto">

        {{-- Header --}}
        <div class="flex items-center space-x-4 mb-8">
            <a href="{{ route('admin.opened-courses.index') }}"
               class="group flex items-center justify-center w-10 h-10 rounded-xl bg-white dark:bg-[#242526] shadow-sm border border-gray-200 dark:border-[#3a3b3c] text-gray-500 dark:text-gray-400 hover:text-indigo-600 dark:hover:text-indigo-400 hover:border-indigo-300 transition-all duration-200">
                <i class="fas fa-arrow-left group-hover:-translate-x-0.5 transition-transform"></i>
            </a>
            <div>
                <h1 class="text-2xl font-extrabold text-gray-900 dark:text-white tracking-tight">
                    {{ $isEdit ? 'แก้ไขรายวิชาที่เปิดสอน' : 'เพิ่มรายวิชาที่เปิดสอน' }}
                </h1>
                <p class="text-sm text-gray-500 dark:text-gray-400 font-medium px-1">
                    @if($currentYear && $currentSemester)
                        ปีการศึกษา {{ $currentYear->year }} &bull; ภาคเรียนที่ {{ $currentSemester->semester_number }}
                    @else
                        ยังไม่ได้เลือกปีการศึกษา
                    @endif
                </p>
            </div>
        </div>

        {{-- Main Card --}}
        <div class="bg-white dark:bg-[#242526] rounded-[2.5rem] shadow-xl shadow-gray-200/50 dark:shadow-none border border-gray-100 dark:border-[#3a3b3c] overflow-hidden transform transition-all">
            <div class="h-2 {{ $gradientClass }}"></div>

            <div class="p-8 sm:p-10">
                {{-- Icon Header --}}
                <div class="flex flex-col items-center text-center mb-10">
                    <div class="relative">
                        <div class="absolute inset-0 {{ $blurClass }} blur-2xl rounded-full"></div>
                        <div class="relative w-20 h-20 rounded-3xl flex items-center justify-center mb-4 border-2 border-white dark:border-[#3a3b3c] shadow-xl {{ $iconBgClass }}">
                            <i class="fas {{ $iconClass }} text-3xl"></i>
                        </div>
                    </div>
                    <h2 class="text-xl font-bold text-gray-800 dark:text-white mb-2">
                        {{ $isEdit ? 'แก้ไขข้อมูล' : 'เพิ่มรายวิชาใหม่' }}
                    </h2>
                    <p class="text-sm text-gray-500 dark:text-gray-400 max-w-xs mx-auto">
                        {{ $isEdit ? 'อัปเดตข้อมูลรายวิชาที่เปิดสอน' : 'เลือกระดับชั้น ห้องเรียน และรายวิชาที่ต้องการเปิดสอน' }}
                    </p>
                </div>

                {{-- Form --}}
                <form action="{{ $actionUrl }}" method="POST" class="space-y-6" id="courseForm">
                    @csrf
                    @if($isEdit) @method('PUT') @endif

                    {{-- Grade --}}
                    <div class="space-y-2">
                        <label for="grade_id" class="block text-xs font-bold text-gray-400 dark:text-gray-500 uppercase tracking-widest px-1">
                            ระดับชั้น
                        </label>
                        <div class="group relative">
                            <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none text-gray-400 {{ $focusText }} transition-colors">
                                <i class="fas fa-graduation-cap text-sm"></i>
                            </div>
                            <select id="grade_id" name="grade_id"
                                class="block w-full pl-10 pr-10 py-4 bg-gray-50 dark:bg-[#3a3b3c] border-2 border-transparent rounded-2xl text-gray-900 dark:text-white focus:outline-none focus:ring-0 {{ $focusRing }} focus:bg-white dark:focus:bg-[#3a3b3c] transition-all duration-200 appearance-none @error('grade_id') border-rose-300 bg-rose-50 dark:bg-rose-900/20 @enderror">
                                <option value="">-- เลือกระดับชั้น --</option>
                                @foreach($openedGrades as $og)
                                    <option value="{{ $og->grade_id }}"
                                        {{ $selectedGrade == $og->grade_id ? 'selected' : '' }}>
                                        {{ $og->grade?->name_th }}
                                    </option>
                                @endforeach
                            </select>
                            <div class="absolute inset-y-0 right-0 pr-4 flex items-center pointer-events-none text-gray-400">
                                <i class="fas fa-chevron-down text-[10px]"></i>
                            </div>
                        </div>
                        @error('grade_id')
                            <p class="text-[10px] font-bold text-rose-500 mt-1 px-1">{{ $message }}</p>
                        @enderror
                    </div>

                    {{-- Classroom --}}
                    <div class="space-y-2">
                        <label for="classroom_id" class="block text-xs font-bold text-gray-400 dark:text-gray-500 uppercase tracking-widest px-1">
                            ห้องเรียน
                        </label>
                        <div class="group relative">
                            <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none text-gray-400 {{ $focusText }} transition-colors">
                                <i class="fas fa-door-open text-sm"></i>
                            </div>
                            <select id="classroom_id" name="classroom_id"
                                class="block w-full pl-10 pr-10 py-4 bg-gray-50 dark:bg-[#3a3b3c] border-2 border-transparent rounded-2xl text-gray-900 dark:text-white focus:outline-none focus:ring-0 {{ $focusRing }} focus:bg-white dark:focus:bg-[#3a3b3c] transition-all duration-200 appearance-none @error('classroom_id') border-rose-300 bg-rose-50 dark:bg-rose-900/20 @enderror">
                                <option value="">-- เลือกระดับชั้นก่อน --</option>
                                @if($isEdit && isset($classrooms))
                                    @foreach($classrooms as $oc)
                                        <option value="{{ $oc->classroom_id }}"
                                            {{ $selectedClassroom == $oc->classroom_id ? 'selected' : '' }}>
                                            {{ $oc->classroom?->name }}
                                        </option>
                                    @endforeach
                                @endif
                            </select>
                            <div class="absolute inset-y-0 right-0 pr-4 flex items-center pointer-events-none text-gray-400">
                                <i class="fas fa-chevron-down text-[10px]"></i>
                            </div>
                        </div>
                        @error('classroom_id')
                            <p class="text-[10px] font-bold text-rose-500 mt-1 px-1">{{ $message }}</p>
                        @enderror
                    </div>

                    {{-- Course --}}
                    <div class="space-y-2">
                        <label for="course_id" class="block text-xs font-bold text-gray-400 dark:text-gray-500 uppercase tracking-widest px-1">
                            รายวิชา
                        </label>
                        <div class="group relative">
                            <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none text-gray-400 {{ $focusText }} transition-colors">
                                <i class="fas fa-book text-sm"></i>
                            </div>
                            <select id="course_id" name="course_id"
                                class="block w-full pl-10 pr-10 py-4 bg-gray-50 dark:bg-[#3a3b3c] border-2 border-transparent rounded-2xl text-gray-900 dark:text-white focus:outline-none focus:ring-0 {{ $focusRing }} focus:bg-white dark:focus:bg-[#3a3b3c] transition-all duration-200 appearance-none @error('course_id') border-rose-300 bg-rose-50 dark:bg-rose-900/20 @enderror">
                                <option value="">-- เลือกระดับชั้นก่อน --</option>
                                @if($isEdit && isset($courses))
                                    @foreach($courses as $course)
                                        <option value="{{ $course->id }}"
                                            {{ $selectedCourse == $course->id ? 'selected' : '' }}>
                                            {{ $course->name }}
                                        </option>
                                    @endforeach
                                @endif
                            </select>
                            <div class="absolute inset-y-0 right-0 pr-4 flex items-center pointer-events-none text-gray-400">
                                <i class="fas fa-chevron-down text-[10px]"></i>
                            </div>
                        </div>
                        @error('course_id')
                            <p class="text-[10px] font-bold text-rose-500 mt-1 px-1">{{ $message }}</p>
                        @enderror
                    </div>

                    {{-- Actions --}}
                    <div class="flex flex-col sm:flex-row space-y-3 sm:space-y-0 sm:space-x-4 pt-6">
                        <button type="submit"
                            class="flex-1 group relative flex items-center justify-center px-8 py-4 {{ $btnClass }} font-bold rounded-2xl active:scale-95 transition-all duration-200 shadow-lg overflow-hidden">
                            <span class="relative z-10 flex items-center">
                                <i class="fas {{ $btnIcon }} mr-2 opacity-50 group-hover:opacity-100 transition-opacity"></i>
                                {{ $btnText }}
                            </span>
                            <div class="absolute inset-0 bg-gradient-to-r from-transparent via-white/10 to-transparent -translate-x-full group-hover:translate-x-full transition-transform duration-1000 ease-in-out"></div>
                        </button>
                        <a href="{{ route('admin.opened-courses.index') }}"
                           class="flex-1 flex items-center justify-center px-8 py-4 bg-white dark:bg-[#242526] text-gray-700 dark:text-gray-300 font-bold rounded-2xl border-2 border-gray-100 dark:border-[#3a3b3c] hover:border-gray-200 dark:hover:border-[#4a4b4c] hover:bg-gray-50 dark:hover:bg-[#3a3b3c] active:scale-95 transition-all duration-200">
                            {{ $isEdit ? 'ยกเลิก' : 'ย้อนกลับ' }}
                        </a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
const CLASSROOMS_URL = "{{ route('admin.opened-courses.classrooms-by-grade') }}";
const COURSES_URL    = "{{ route('admin.opened-courses.courses-by-grade') }}";
const SELECTED_CLASSROOM = "{{ $selectedClassroom }}";
const SELECTED_COURSE    = "{{ $selectedCourse }}";

document.getElementById('grade_id').addEventListener('change', function() {
    const gradeId      = this.value;
    const classroomSel = document.getElementById('classroom_id');
    const courseSel    = document.getElementById('course_id');

    if (!gradeId) {
        classroomSel.innerHTML = '<option value="">-- เลือกระดับชั้นก่อน --</option>';
        courseSel.innerHTML    = '<option value="">-- เลือกระดับชั้นก่อน --</option>';
        return;
    }

    classroomSel.innerHTML = '<option value="">กำลังโหลด...</option>';
    courseSel.innerHTML    = '<option value="">กำลังโหลด...</option>';

    Promise.all([
        fetch(`${CLASSROOMS_URL}?grade_id=${gradeId}`).then(r => r.json()),
        fetch(`${COURSES_URL}?grade_id=${gradeId}`).then(r => r.json()),
    ]).then(([classrooms, courses]) => {
        classroomSel.innerHTML = '<option value="">-- เลือกห้องเรียน --</option>' +
            classrooms.map(c => `<option value="${c.id}"${c.id == SELECTED_CLASSROOM ? ' selected' : ''}>${c.name}</option>`).join('');

        courseSel.innerHTML = '<option value="">-- เลือกรายวิชา --</option>' +
            courses.map(c => `<option value="${c.id}"${c.id == SELECTED_COURSE ? ' selected' : ''}>${c.name}</option>`).join('');
    });
});

// Auto-load on edit (when grade already selected)
window.addEventListener('DOMContentLoaded', function() {
    const gradeId = document.getElementById('grade_id').value;
    if (gradeId) {
        document.getElementById('grade_id').dispatchEvent(new Event('change'));
    }
});
</script>
@endpush
@endsection
