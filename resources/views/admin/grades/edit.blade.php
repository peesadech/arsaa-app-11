<x-layouts.admin :header="__('Edit Grade')" :subheader="__('Update grade details')">
    <x-slot name="actions">
        <x-button variant="secondary" icon="arrow-left" :href="route('admin.grades.index')">{{ __('Back to List') }}</x-button>
    </x-slot>

    <form action="{{ route('admin.grades.update', $grade->id) }}" method="POST" class="space-y-6">
        @csrf
        @method('PUT')
        @include('admin.grades._form', ['grade' => $grade, 'educationLevels' => $educationLevels])

        <div class="flex items-center justify-end gap-2">
            <x-button variant="secondary" :href="route('admin.grades.index')">{{ __('Cancel') }}</x-button>
            <x-button type="submit" icon="check">{{ __('Save Changes') }}</x-button>
        </div>
    </form>
</x-layouts.admin>
