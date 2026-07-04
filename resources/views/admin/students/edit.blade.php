<x-layouts.admin :header="__('Edit Student')" :subheader="$student->student_code . ' — ' . $student->name_th">
    <x-slot name="actions">
        <x-button variant="secondary" :href="route('admin.student-reports.profile', $student->id)" target="_blank">{{ __('Student Profile') }}</x-button>
        <x-button variant="secondary" :href="route('admin.student-reports.transcript', $student->id)" target="_blank">{{ __('Transcript') }}</x-button>
        <x-button variant="secondary" icon="arrow-left" :href="route('admin.students.index')">{{ __('Back to List') }}</x-button>
    </x-slot>

    @include('admin.students._form')
</x-layouts.admin>
