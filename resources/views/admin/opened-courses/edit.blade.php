<x-layouts.admin :header="__('Edit Opened Course')" :subheader="__('Update opened course information')">
    <x-slot name="actions">
        <x-button variant="secondary" icon="arrow-left" :href="route('admin.opened-courses.index')">{{ __('Back to List') }}</x-button>
    </x-slot>

    <form action="{{ route('admin.opened-courses.update', $openedCourse->id) }}" method="POST" class="space-y-6">
        @csrf
        @method('PUT')
        @include('admin.opened-courses._form', [
            'openedCourse' => $openedCourse,
            'openedGrades' => $openedGrades,
            'classrooms'   => $classrooms,
            'courses'      => $courses,
        ])

        <div class="flex items-center justify-end gap-2">
            <x-button variant="secondary" :href="route('admin.opened-courses.index')">{{ __('Cancel') }}</x-button>
            <x-button type="submit" icon="check">{{ __('Save Changes') }}</x-button>
        </div>
    </form>
</x-layouts.admin>
