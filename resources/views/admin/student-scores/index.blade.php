@php
    $subheader = __('Academic Year') . ' ' . ($academicYear->year ?? '?') . ' / ' . __('Semester') . ' ' . ($semester->semester_number ?? '?') . ' — ' . __('Select classroom then course to record scores');
@endphp
<x-layouts.admin :header="__('Academic Results')" :subheader="$subheader">
    <x-slot name="actions">
        <x-button variant="secondary" icon="arrow-left" :href="route('admin.students.index')">{{ __('Back') }}</x-button>
    </x-slot>

    @if(session('status'))
    <div class="mb-6 badge-green w-full rounded-xl px-4 py-3 text-sm justify-start">{{ session('status') }}</div>
    @endif

    @if($openedClassrooms->isEmpty())
    <x-card>
        <div class="py-10 text-center text-slate-400">
            <x-icon name="door" class="h-8 w-8 mx-auto mb-3" />
            <p class="text-sm font-medium">{{ __('No classrooms opened for this term yet') }}</p>
        </div>
    </x-card>
    @else

    {{-- Classroom cards --}}
    <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-5 gap-3 mb-6">
        @foreach($openedClassrooms as $oc)
        @php $isSelected = $selectedGradeId == $oc->grade_id && $selectedClassroomId == $oc->classroom_id; @endphp
        <a href="{{ route('admin.student-scores.index', ['grade_id' => $oc->grade_id, 'classroom_id' => $oc->classroom_id]) }}"
           class="block p-4 rounded-xl border transition {{ $isSelected ? 'bg-brand-50 border-brand-200' : 'bg-white border-slate-100 shadow-card hover:border-brand-200' }}">
            <div class="text-sm font-semibold {{ $isSelected ? 'text-brand-700' : 'text-slate-800' }}">
                {{ $oc->grade->name_th ?? '' }} / {{ $oc->classroom->name ?? '' }}
            </div>
        </a>
        @endforeach
    </div>

    {{-- Course list --}}
    @if($selectedGradeId && $selectedClassroomId)
    <x-card>
        <h2 class="text-xs font-semibold text-slate-500 uppercase tracking-wide mb-4">{{ __('Courses in this classroom') }}</h2>
        @if($openedCourses->isEmpty())
        <p class="text-sm text-slate-400 py-6 text-center">{{ __('No courses opened for this classroom') }}</p>
        @else
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-3">
            @foreach($openedCourses as $oc)
            <a href="{{ route('admin.student-scores.entry', $oc->id) }}"
               class="block p-4 rounded-xl border border-slate-100 bg-slate-50 hover:border-brand-300 hover:bg-white hover:shadow-card transition">
                <div class="text-sm font-semibold text-slate-800">{{ $oc->course->name ?? '?' }}</div>
                <div class="text-xs text-slate-400 mt-1 flex items-center gap-1 flex-wrap">
                    <span>{{ $oc->course->subjectGroup->name_th ?? '' }}</span>
                    @if($oc->course?->teachers?->isNotEmpty())
                    <span>·</span><x-icon name="academic" class="h-3.5 w-3.5" /> <span>{{ $oc->course->teachers->pluck('name')->take(2)->join(', ') }}</span>
                    @endif
                </div>
                <div class="mt-2 text-xs font-semibold flex items-center gap-1 {{ $oc->scored_count > 0 ? 'text-emerald-600' : 'text-slate-300' }}">
                    <x-icon name="clipboard" class="h-3.5 w-3.5" />{{ $oc->scored_count }} {{ __('scores recorded') }}
                </div>
            </a>
            @endforeach
        </div>
        @endif
    </x-card>
    @else
    <x-card>
        <div class="py-10 text-center text-slate-400">
            <x-icon name="filter" class="h-7 w-7 mx-auto mb-3" />
            <p class="text-sm font-medium">{{ __('Select a classroom above') }}</p>
        </div>
    </x-card>
    @endif
    @endif
</x-layouts.admin>
