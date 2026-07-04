<x-layouts.admin :header="__('Edit Academic Year')" :subheader="__('Update academic year details')">
    <x-slot name="actions">
        <x-button variant="secondary" icon="arrow-left" :href="route('admin.academic-years.index')">{{ __('Back to List') }}</x-button>
    </x-slot>

    <form action="{{ route('admin.academic-years.update', $academicYear->id) }}" method="POST" class="space-y-6">
        @csrf
        @method('PUT')
        @include('admin.academic-years._form', ['academicYear' => $academicYear])

        <div class="flex items-center justify-end gap-2">
            <x-button variant="secondary" :href="route('admin.academic-years.index')">{{ __('Cancel') }}</x-button>
            <x-button type="submit" icon="check">{{ __('Save Changes') }}</x-button>
        </div>
    </form>
</x-layouts.admin>
