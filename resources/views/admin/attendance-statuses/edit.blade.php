<x-layouts.admin :header="__('Edit Attendance Status')" :subheader="__('Update attendance status details')">
    <x-slot name="actions">
        <x-button variant="secondary" icon="arrow-left" :href="route('admin.attendance-statuses.index')">{{ __('Back to List') }}</x-button>
    </x-slot>

    <form action="{{ route('admin.attendance-statuses.update', $status->id) }}" method="POST" class="space-y-6">
        @csrf
        @method('PUT')
        @include('admin.attendance-statuses._form', ['status' => $status])

        <div class="flex items-center justify-end gap-2">
            <x-button variant="secondary" :href="route('admin.attendance-statuses.index')">{{ __('Cancel') }}</x-button>
            <x-button type="submit" icon="check">{{ __('Save Changes') }}</x-button>
        </div>
    </form>
</x-layouts.admin>
