<x-layouts.admin :header="__('New Student')" :subheader="__('Manage student records, guardians and enrollments')">
    <x-slot name="actions">
        <x-button variant="secondary" icon="arrow-left" :href="route('admin.students.index')">{{ __('Back to List') }}</x-button>
    </x-slot>

    @include('admin.students._form')
</x-layouts.admin>
