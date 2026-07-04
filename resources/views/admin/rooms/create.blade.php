<x-layouts.admin :header="__('Create New Room')" :subheader="__('Room registration')">
    <x-slot name="actions">
        <x-button variant="secondary" icon="arrow-left" :href="route('admin.rooms.index')">{{ __('Back to List') }}</x-button>
    </x-slot>

    <form action="{{ route('admin.rooms.store') }}" method="POST" class="space-y-6">
        @csrf
        @include('admin.rooms._form', ['room' => null, 'buildings' => $buildings, 'courses' => $courses])

        <div class="flex items-center justify-end gap-2">
            <x-button variant="secondary" :href="route('admin.rooms.index')">{{ __('Cancel') }}</x-button>
            <x-button type="submit" icon="check">{{ __('Create Room') }}</x-button>
        </div>
    </form>
</x-layouts.admin>
