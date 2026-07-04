<x-layouts.admin :header="__('Edit User')" :subheader="__('Update account details')">
    <x-slot name="actions">
        <x-button variant="secondary" icon="arrow-left" :href="route('admin.users.index')">{{ __('Back to List') }}</x-button>
    </x-slot>

    <form action="{{ route('admin.users.update', $user->id) }}" method="POST" class="space-y-6">
        @csrf
        @method('PUT')
        @include('admin.users._form', ['user' => $user, 'roles' => $roles, 'userRoles' => $userRoles])

        <div class="flex items-center justify-end gap-2">
            <x-button variant="secondary" :href="route('admin.users.index')">{{ __('Cancel') }}</x-button>
            <x-button type="submit" icon="check">{{ __('Save Changes') }}</x-button>
        </div>
    </form>
</x-layouts.admin>
