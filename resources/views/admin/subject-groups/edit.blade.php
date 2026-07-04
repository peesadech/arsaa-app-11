<x-layouts.admin :header="__('Edit Subject Group')" :subheader="__('Update subject group details')">
    <x-slot name="actions">
        <x-button variant="secondary" icon="arrow-left" :href="route('admin.subject-groups.index')">{{ __('Back to List') }}</x-button>
    </x-slot>

    <form action="{{ route('admin.subject-groups.update', $subjectGroup->id) }}" method="POST" class="space-y-6">
        @csrf
        @method('PUT')
        @include('admin.subject-groups._form', ['subjectGroup' => $subjectGroup])

        <div class="flex items-center justify-end gap-2">
            <x-button variant="secondary" :href="route('admin.subject-groups.index')">{{ __('Cancel') }}</x-button>
            <x-button type="submit" icon="check">{{ __('Save Changes') }}</x-button>
        </div>
    </form>
</x-layouts.admin>
