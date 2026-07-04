<x-layouts.admin :header="__('Edit Building')" :subheader="__('Update building details')">
    <x-slot name="actions">
        <x-button variant="secondary" icon="arrow-left" :href="route('admin.buildings.index')">{{ __('Back to List') }}</x-button>
    </x-slot>

    <form action="{{ route('admin.buildings.update', $building->id) }}" method="POST" class="space-y-6">
        @csrf
        @method('PUT')
        @include('admin.buildings._form', ['building' => $building])

        <div class="flex items-center justify-end gap-2">
            <x-button variant="secondary" :href="route('admin.buildings.index')">{{ __('Cancel') }}</x-button>
            <x-button type="submit" icon="check">{{ __('Save Changes') }}</x-button>
        </div>
    </form>
</x-layouts.admin>
