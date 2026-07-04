<x-layouts.admin :header="__('Create New Attendance Status')" :subheader="__('Attendance status registration')">
    <x-slot name="actions">
        <x-button variant="secondary" icon="arrow-left" :href="route('admin.attendance-statuses.index')">{{ __('Back to List') }}</x-button>
    </x-slot>

    <form action="{{ route('admin.attendance-statuses.store') }}" method="POST" class="space-y-6">
        @csrf
        @include('admin.attendance-statuses._form', ['status' => null])

        <div class="flex items-center justify-end gap-2">
            <x-button variant="secondary" :href="route('admin.attendance-statuses.index')">{{ __('Cancel') }}</x-button>
            <x-button type="submit" icon="check">{{ __('Create Attendance Status') }}</x-button>
        </div>
    </form>
</x-layouts.admin>
