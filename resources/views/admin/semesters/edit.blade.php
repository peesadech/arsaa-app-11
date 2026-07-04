<x-layouts.admin :header="__('Edit Semester')" :subheader="__('Update semester details')">
    <x-slot name="actions">
        <x-button variant="secondary" icon="arrow-left" :href="route('admin.semesters.index')">{{ __('Back to List') }}</x-button>
    </x-slot>

    <form action="{{ route('admin.semesters.update', $semester->id) }}" method="POST" class="space-y-6">
        @csrf
        @method('PUT')
        @include('admin.semesters._form', ['semester' => $semester])

        <div class="flex items-center justify-end gap-2">
            <x-button variant="secondary" :href="route('admin.semesters.index')">{{ __('Cancel') }}</x-button>
            <x-button type="submit" icon="check">{{ __('Save Changes') }}</x-button>
        </div>
    </form>
</x-layouts.admin>
