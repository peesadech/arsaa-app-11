<x-layouts.admin
    :header="__('Record Scores') . ' — ' . ($openedCourse->course->name ?? '?')"
    :subheader="__('Academic Year') . ' ' . ($openedCourse->academicYear->year ?? '?') . ' / ' . __('Semester') . ' ' . ($openedCourse->semester->semester_number ?? '?') . ' · ' . ($openedCourse->grade->name_th ?? '') . ' / ' . ($openedCourse->classroom->name ?? '')">

    <x-slot name="actions">
        <x-button variant="secondary" icon="arrow-left" :href="route('teacher.scores.index')">{{ __('Back') }}</x-button>
    </x-slot>

    @include('partials.score-grid')
</x-layouts.admin>
