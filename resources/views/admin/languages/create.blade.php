<x-layouts.admin :header="__('Create New Language')" :subheader="__('Language Registration')">
    <x-slot name="actions">
        <x-button variant="secondary" icon="arrow-left" :href="route('admin.languages.index')">{{ __('Back to List') }}</x-button>
    </x-slot>

    <form action="{{ route('admin.languages.store') }}" method="POST" class="space-y-6">
        @csrf
        @include('admin.languages._form', ['language' => null])

        <div class="flex items-center justify-end gap-2">
            <x-button variant="secondary" :href="route('admin.languages.index')">{{ __('Cancel') }}</x-button>
            <x-button type="submit" icon="check">{{ __('Create Language') }}</x-button>
        </div>
    </form>
</x-layouts.admin>
