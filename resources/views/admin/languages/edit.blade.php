<x-layouts.admin :header="__('Edit Language')" :subheader="__('Update language details')">
    <x-slot name="actions">
        <x-button variant="secondary" icon="arrow-left" :href="route('admin.languages.index')">{{ __('Back to List') }}</x-button>
    </x-slot>

    <form action="{{ route('admin.languages.update', $language->id) }}" method="POST" class="space-y-6">
        @csrf
        @method('PUT')
        @include('admin.languages._form', ['language' => $language])

        <div class="flex items-center justify-end gap-2">
            <x-button variant="secondary" :href="route('admin.languages.index')">{{ __('Cancel') }}</x-button>
            <x-button type="submit" icon="check">{{ __('Save Changes') }}</x-button>
        </div>
    </form>
</x-layouts.admin>
