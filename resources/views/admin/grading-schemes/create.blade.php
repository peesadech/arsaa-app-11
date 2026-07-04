<x-layouts.admin :header="__('Create New Grading Scheme')" :subheader="__('Grading Scheme Registration')">
    <x-slot name="actions">
        <x-button variant="secondary" icon="arrow-left" :href="route('admin.grading-schemes.index')">{{ __('Back to List') }}</x-button>
    </x-slot>

    <form action="{{ route('admin.grading-schemes.store') }}" method="POST" id="gradingSchemeForm" class="space-y-6">
        @csrf
        @include('admin.grading-schemes._form', ['gradingScheme' => null])

        <div class="flex items-center justify-end gap-2">
            <x-button variant="secondary" :href="route('admin.grading-schemes.index')">{{ __('Cancel') }}</x-button>
            <x-button type="submit" icon="check">{{ __('Create Grading Scheme') }}</x-button>
        </div>
    </form>
</x-layouts.admin>
