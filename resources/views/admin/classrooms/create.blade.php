<x-layouts.admin :header="__('Create New Classroom')" :subheader="__('Classroom Registration')">
    <x-slot name="actions">
        <x-button variant="secondary" icon="arrow-left" :href="route('admin.classrooms.index')">{{ __('Back to List') }}</x-button>
    </x-slot>

    <form action="{{ route('admin.classrooms.store') }}" method="POST" class="space-y-6">
        @csrf
        @include('admin.classrooms._form', ['classroom' => null])

        <div class="flex items-center justify-end gap-2">
            <x-button variant="secondary" :href="route('admin.classrooms.index')">{{ __('Cancel') }}</x-button>
            <x-button type="submit" icon="check">{{ __('Create Classroom') }}</x-button>
        </div>
    </form>
</x-layouts.admin>
