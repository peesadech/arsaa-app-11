<x-layouts.admin :header="__('Create New User')" :subheader="__('User Registration')">
    <x-slot name="actions">
        <x-button variant="secondary" icon="arrow-left" :href="route('admin.users.index')">{{ __('Back to List') }}</x-button>
    </x-slot>

    <form action="{{ route('admin.users.store') }}" method="POST" class="space-y-6">
        @csrf
        @include('admin.users._form', ['user' => null, 'roles' => $roles])

        <div class="flex items-center justify-end gap-2">
            <x-button variant="secondary" :href="route('admin.users.index')">{{ __('Cancel') }}</x-button>
            <x-button type="submit" icon="check">{{ __('Create User') }}</x-button>
        </div>
    </form>
</x-layouts.admin>
