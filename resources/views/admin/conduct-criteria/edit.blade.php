<x-layouts.admin :header="__('Edit Conduct Criterion')" :subheader="__('Update conduct assessment item')">
    <x-slot name="actions">
        <x-button variant="secondary" icon="arrow-left" :href="route('admin.conduct-criteria.index')">{{ __('Back to List') }}</x-button>
    </x-slot>
    <form action="{{ route('admin.conduct-criteria.update', $item->id) }}" method="POST" class="space-y-6">
        @csrf @method('PUT')
        @include('admin.conduct-criteria._form', ['item' => $item])
        <div class="flex items-center justify-end gap-2">
            <x-button variant="secondary" :href="route('admin.conduct-criteria.index')">{{ __('Cancel') }}</x-button>
            <x-button type="submit" icon="check">{{ __('Save Changes') }}</x-button>
        </div>
    </form>
</x-layouts.admin>
