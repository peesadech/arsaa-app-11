@php
    $subheader = __('Academic Year') . ' ' . ($openedCourse->academicYear->year ?? '?') . ' / ' . __('Semester') . ' ' . ($openedCourse->semester->semester_number ?? '?') . ' · ' . ($openedCourse->grade->name_th ?? '') . ' / ' . ($openedCourse->classroom->name ?? '');
@endphp
<x-layouts.admin :header="__('Record Scores') . ' — ' . ($openedCourse->course->name ?? '?')" :subheader="$subheader">
    <x-slot name="actions">
        <x-button variant="secondary" icon="arrow-left"
                  :href="route('admin.student-scores.index', ['grade_id' => $openedCourse->grade_id, 'classroom_id' => $openedCourse->classroom_id])">{{ __('Back') }}</x-button>
    </x-slot>

    @include('partials.score-grid')
</x-layouts.admin>
