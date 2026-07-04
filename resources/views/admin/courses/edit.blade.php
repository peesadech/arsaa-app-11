<x-layouts.admin :header="__('Edit Course')" :subheader="__('Update course details')">
    <x-slot name="actions">
        <x-button variant="secondary" icon="arrow-left" :href="route('admin.courses.index')">{{ __('Back to List') }}</x-button>
    </x-slot>

    <form action="{{ route('admin.courses.update', $course->id) }}" method="POST" class="space-y-6">
        @csrf
        @method('PUT')
        @include('admin.courses._form', [
            'course'         => $course,
            'grades'         => $grades,
            'semesters'      => $semesters,
            'subjectGroups'  => $subjectGroups,
            'courseTypes'    => $courseTypes,
            'gradingSchemes' => $gradingSchemes,
        ])

        <div class="flex items-center justify-end gap-2">
            <x-button variant="secondary" :href="route('admin.courses.index')">{{ __('Cancel') }}</x-button>
            <x-button type="submit" icon="check">{{ __('Save Changes') }}</x-button>
        </div>
    </form>
</x-layouts.admin>
