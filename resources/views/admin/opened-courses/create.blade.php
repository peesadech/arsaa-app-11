<x-layouts.admin :header="__('Add Opened Course')" :subheader="__('Select grade level, classroom, and course to open')">
    <x-slot name="actions">
        <x-button variant="secondary" icon="arrow-left" :href="route('admin.opened-courses.index')">{{ __('Back to List') }}</x-button>
    </x-slot>

    <form action="{{ route('admin.opened-courses.store') }}" method="POST" class="space-y-6">
        @csrf
        @include('admin.opened-courses._form', [
            'openedCourse' => null,
            'openedGrades' => $openedGrades,
            'classrooms'   => $classrooms,
            'courses'      => $courses,
        ])

        <div class="flex items-center justify-end gap-2">
            <x-button variant="secondary" :href="route('admin.opened-courses.index')">{{ __('Cancel') }}</x-button>
            <x-button type="submit" icon="check">{{ __('Add Course') }}</x-button>
        </div>
    </form>
</x-layouts.admin>
