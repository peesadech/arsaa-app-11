<x-layouts.admin :header="__('Create New Building')" :subheader="__('Building registration')">
    <x-slot name="actions">
        <x-button variant="secondary" icon="arrow-left" :href="route('admin.buildings.index')">{{ __('Back to List') }}</x-button>
    </x-slot>

    <form action="{{ route('admin.buildings.store') }}" method="POST" class="space-y-6">
        @csrf
        @include('admin.buildings._form', ['building' => null])

        <div class="flex items-center justify-end gap-2">
            <x-button variant="secondary" :href="route('admin.buildings.index')">{{ __('Cancel') }}</x-button>
            <x-button type="submit" icon="check">{{ __('Create Building') }}</x-button>
        </div>
    </form>
</x-layouts.admin>
