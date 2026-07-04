<x-layouts.admin :header="__('Create New Subject Group')" :subheader="__('Subject Group Registration')">
    <x-slot name="actions">
        <x-button variant="secondary" icon="arrow-left" :href="route('admin.subject-groups.index')">{{ __('Back to List') }}</x-button>
    </x-slot>

    <form action="{{ route('admin.subject-groups.store') }}" method="POST" class="space-y-6">
        @csrf
        @include('admin.subject-groups._form', ['subjectGroup' => null])

        <div class="flex items-center justify-end gap-2">
            <x-button variant="secondary" :href="route('admin.subject-groups.index')">{{ __('Cancel') }}</x-button>
            <x-button type="submit" icon="check">{{ __('Create Subject Group') }}</x-button>
        </div>
    </form>
</x-layouts.admin>
