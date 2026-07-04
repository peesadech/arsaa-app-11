<x-layouts.admin :header="__('Edit Classroom')" :subheader="__('Update classroom details')">
    <x-slot name="actions">
        <x-button variant="secondary" icon="arrow-left" :href="route('admin.classrooms.index')">{{ __('Back to List') }}</x-button>
    </x-slot>

    <form action="{{ route('admin.classrooms.update', $classroom->id) }}" method="POST" class="space-y-6">
        @csrf
        @method('PUT')
        @include('admin.classrooms._form', ['classroom' => $classroom])

        <div class="flex items-center justify-end gap-2">
            <x-button variant="secondary" :href="route('admin.classrooms.index')">{{ __('Cancel') }}</x-button>
            <x-button type="submit" icon="check">{{ __('Save Changes') }}</x-button>
        </div>
    </form>
</x-layouts.admin>
