<x-layouts.admin :header="__('Create New Floor')" :subheader="__('Floor registration')">
    <x-slot name="actions">
        <x-button variant="secondary" icon="arrow-left" :href="route('admin.floors.index')">{{ __('Back to List') }}</x-button>
    </x-slot>

    <form action="{{ route('admin.floors.store') }}" method="POST" class="space-y-6">
        @csrf
        @include('admin.floors._form', ['floor' => null, 'buildings' => $buildings])

        <div class="flex items-center justify-end gap-2">
            <x-button variant="secondary" :href="route('admin.floors.index')">{{ __('Cancel') }}</x-button>
            <x-button type="submit" icon="check">{{ __('Create Floor') }}</x-button>
        </div>
    </form>
</x-layouts.admin>
