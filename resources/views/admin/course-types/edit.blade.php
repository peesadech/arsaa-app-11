<x-layouts.admin :header="__('Edit Course Type')" :subheader="__('Update course type details')">
    <x-slot name="actions">
        <x-button variant="secondary" icon="arrow-left" :href="route('admin.course-types.index')">{{ __('Back to List') }}</x-button>
    </x-slot>

    <form action="{{ route('admin.course-types.update', $courseType->id) }}" method="POST" class="space-y-6">
        @csrf
        @method('PUT')
        @include('admin.course-types._form', ['courseType' => $courseType, 'gradingSchemes' => $gradingSchemes])

        <div class="flex items-center justify-end gap-2">
            <x-button variant="secondary" :href="route('admin.course-types.index')">{{ __('Cancel') }}</x-button>
            <x-button type="submit" icon="check">{{ __('Save Changes') }}</x-button>
        </div>
    </form>
</x-layouts.admin>
