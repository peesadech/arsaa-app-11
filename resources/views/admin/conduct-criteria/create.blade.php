<x-layouts.admin :header="__('Create Conduct Criterion')" :subheader="__('Conduct assessment item')">
    <x-slot name="actions">
        <x-button variant="secondary" icon="arrow-left" :href="route('admin.conduct-criteria.index')">{{ __('Back to List') }}</x-button>
    </x-slot>
    <form action="{{ route('admin.conduct-criteria.store') }}" method="POST" class="space-y-6">
        @csrf
        @include('admin.conduct-criteria._form', ['item' => null])
        <div class="flex items-center justify-end gap-2">
            <x-button variant="secondary" :href="route('admin.conduct-criteria.index')">{{ __('Cancel') }}</x-button>
            <x-button type="submit" icon="check">{{ __('Create') }}</x-button>
        </div>
    </form>
</x-layouts.admin>
