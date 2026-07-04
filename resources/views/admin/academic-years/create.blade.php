<x-layouts.admin :header="__('Create New Academic Year')" :subheader="__('Academic Year Registration')">
    <x-slot name="actions">
        <x-button variant="secondary" icon="arrow-left" :href="route('admin.academic-years.index')">{{ __('Back to List') }}</x-button>
    </x-slot>

    <form action="{{ route('admin.academic-years.store') }}" method="POST" class="space-y-6">
        @csrf
        @include('admin.academic-years._form', ['academicYear' => null])

        <div class="flex items-center justify-end gap-2">
            <x-button variant="secondary" :href="route('admin.academic-years.index')">{{ __('Cancel') }}</x-button>
            <x-button type="submit" icon="check">{{ __('Create Year') }}</x-button>
        </div>
    </form>
</x-layouts.admin>
