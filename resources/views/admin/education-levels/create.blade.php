<x-layouts.admin :header="__('Create New Education Level')" :subheader="__('Education Level Registration')">
    <x-slot name="actions">
        <x-button variant="secondary" icon="arrow-left" :href="route('admin.education-levels.index')">{{ __('Back to List') }}</x-button>
    </x-slot>

    <form action="{{ route('admin.education-levels.store') }}" method="POST" class="space-y-6">
        @csrf
        @include('admin.education-levels._form', ['educationLevel' => null])

        <div class="flex items-center justify-end gap-2">
            <x-button variant="secondary" :href="route('admin.education-levels.index')">{{ __('Cancel') }}</x-button>
            <x-button type="submit" icon="check">{{ __('Create Education Level') }}</x-button>
        </div>
    </form>
</x-layouts.admin>
