<x-layouts.admin :header="__('Create New Course Type')" :subheader="__('Course type registration')">
    <x-slot name="actions">
        <x-button variant="secondary" icon="arrow-left" :href="route('admin.course-types.index')">{{ __('Back to List') }}</x-button>
    </x-slot>

    <form action="{{ route('admin.course-types.store') }}" method="POST" class="space-y-6">
        @csrf
        @include('admin.course-types._form', ['courseType' => null, 'gradingSchemes' => $gradingSchemes])

        <div class="flex items-center justify-end gap-2">
            <x-button variant="secondary" :href="route('admin.course-types.index')">{{ __('Cancel') }}</x-button>
            <x-button type="submit" icon="check">{{ __('Create Course Type') }}</x-button>
        </div>
    </form>
</x-layouts.admin>
