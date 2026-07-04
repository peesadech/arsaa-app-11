<x-layouts.admin :header="__('Edit Teacher')" :subheader="__('Update teacher details')">
    <x-slot name="actions">
        <x-button variant="secondary" icon="arrow-left" :href="route('admin.teachers.index')">{{ __('Back to List') }}</x-button>
    </x-slot>

    <form action="{{ route('admin.teachers.update', $teacher->id) }}" method="POST" class="space-y-6" id="teacherForm">
        @csrf
        @method('PUT')
        @include('admin.teachers._form', [
            'teacher' => $teacher,
            'subjectGroups' => $subjectGroups,
            'semesters' => $semesters,
            'educationLevels' => $educationLevels,
            'teacherCourseIds' => $teacherCourseIds,
        ])

        <div class="flex items-center justify-end gap-2">
            <x-button variant="secondary" :href="route('admin.teachers.index')">{{ __('Cancel') }}</x-button>
            <x-button type="submit" icon="check">{{ __('Save Changes') }}</x-button>
        </div>
    </form>
</x-layouts.admin>
