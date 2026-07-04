<x-layouts.admin :header="__('Edit Room')" :subheader="__('Update room details')">
    <x-slot name="actions">
        <x-button variant="secondary" icon="arrow-left" :href="route('admin.rooms.index')">{{ __('Back to List') }}</x-button>
    </x-slot>

    <form action="{{ route('admin.rooms.update', $room->id) }}" method="POST" class="space-y-6">
        @csrf
        @method('PUT')
        @include('admin.rooms._form', ['room' => $room, 'buildings' => $buildings, 'courses' => $courses])

        <div class="flex items-center justify-end gap-2">
            <x-button variant="secondary" :href="route('admin.rooms.index')">{{ __('Cancel') }}</x-button>
            <x-button type="submit" icon="check">{{ __('Save Changes') }}</x-button>
        </div>
    </form>
</x-layouts.admin>
