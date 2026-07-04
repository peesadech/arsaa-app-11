<x-layouts.admin :header="__('Create New Teacher')" :subheader="__('Teacher Registration')">
    <x-slot name="actions">
        <x-button variant="secondary" icon="arrow-left" :href="route('admin.teachers.index')">{{ __('Back to List') }}</x-button>
    </x-slot>

    <form action="{{ route('admin.teachers.store') }}" method="POST" class="space-y-6" id="teacherForm">
        @csrf
        @include('admin.teachers._form', [
            'teacher' => null,
            'subjectGroups' => $subjectGroups,
            'semesters' => $semesters,
            'educationLevels' => $educationLevels,
        ])

        <div class="flex items-center justify-end gap-2">
            <x-button variant="secondary" :href="route('admin.teachers.index')">{{ __('Cancel') }}</x-button>
            <x-button type="submit" icon="check">{{ __('Create Teacher') }}</x-button>
        </div>
    </form>
</x-layouts.admin>
