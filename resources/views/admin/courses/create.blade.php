<x-layouts.admin :header="__('Create New Course')" :subheader="__('Course Registration')">
    <x-slot name="actions">
        <x-button variant="secondary" icon="arrow-left" :href="route('admin.courses.index')">{{ __('Back to List') }}</x-button>
    </x-slot>

    <form action="{{ route('admin.courses.store') }}" method="POST" class="space-y-6">
        @csrf
        @include('admin.courses._form', [
            'course'         => null,
            'grades'         => $grades,
            'semesters'      => $semesters,
            'subjectGroups'  => $subjectGroups,
            'courseTypes'    => $courseTypes,
            'gradingSchemes' => $gradingSchemes,
        ])

        <div class="flex items-center justify-end gap-2">
            <x-button variant="secondary" :href="route('admin.courses.index')">{{ __('Cancel') }}</x-button>
            <x-button type="submit" icon="check">{{ __('Create Course') }}</x-button>
        </div>
    </form>
</x-layouts.admin>
