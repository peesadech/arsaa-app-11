<x-layouts.admin :header="__('Edit Floor')" :subheader="__('Update floor details')">
    <x-slot name="actions">
        <x-button variant="secondary" icon="arrow-left" :href="route('admin.floors.index')">{{ __('Back to List') }}</x-button>
    </x-slot>

    <form action="{{ route('admin.floors.update', $floor->id) }}" method="POST" class="space-y-6">
        @csrf
        @method('PUT')
        @include('admin.floors._form', ['floor' => $floor, 'buildings' => $buildings])

        <div class="flex items-center justify-end gap-2">
            <x-button variant="secondary" :href="route('admin.floors.index')">{{ __('Cancel') }}</x-button>
            <x-button type="submit" icon="check">{{ __('Save Changes') }}</x-button>
        </div>
    </form>
</x-layouts.admin>
