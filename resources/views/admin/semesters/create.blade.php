<x-layouts.admin :header="__('Create New Semester')" :subheader="__('Semester Registration')">
    <x-slot name="actions">
        <x-button variant="secondary" icon="arrow-left" :href="route('admin.semesters.index')">{{ __('Back to List') }}</x-button>
    </x-slot>

    <form action="{{ route('admin.semesters.store') }}" method="POST" class="space-y-6">
        @csrf
        @include('admin.semesters._form', ['semester' => null])

        <div class="flex items-center justify-end gap-2">
            <x-button variant="secondary" :href="route('admin.semesters.index')">{{ __('Cancel') }}</x-button>
            <x-button type="submit" icon="check">{{ __('Create Semester') }}</x-button>
        </div>
    </form>
</x-layouts.admin>
