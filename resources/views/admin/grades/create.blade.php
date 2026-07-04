<x-layouts.admin :header="__('Create New Grade')" :subheader="__('Grade Registration')">
    <x-slot name="actions">
        <x-button variant="secondary" icon="arrow-left" :href="route('admin.grades.index')">{{ __('Back to List') }}</x-button>
    </x-slot>

    <form action="{{ route('admin.grades.store') }}" method="POST" class="space-y-6">
        @csrf
        @include('admin.grades._form', ['grade' => null, 'educationLevels' => $educationLevels])

        <div class="flex items-center justify-end gap-2">
            <x-button variant="secondary" :href="route('admin.grades.index')">{{ __('Cancel') }}</x-button>
            <x-button type="submit" icon="check">{{ __('Create Grade') }}</x-button>
        </div>
    </form>
</x-layouts.admin>
