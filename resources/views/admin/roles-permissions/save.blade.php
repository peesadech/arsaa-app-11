@php
    $isEdit = isset($role);
    $actionUrl = $isEdit ? route('admin.roles.update', $role->id) : route('admin.roles.store');

    $title = $isEdit ? __('Edit Role') : __('Create New Role');
    $subtitle = $isEdit ? __('Update role details') : __('Role Registration');
    $cardTitle = $isEdit ? __('Modify Role') : __('Role Details');
    $cardDesc = $isEdit
        ? __('You are updating role #:id. Ensure permissions are assigned correctly.', ['id' => $role->id])
        : __('Define a new system role.');

    $btnText = $isEdit ? __('Save Changes') : __('Create Role');
@endphp

<x-layouts.admin :header="$title" :subheader="$subtitle">
    <x-slot name="actions">
        <x-button variant="secondary" icon="arrow-left" :href="route('admin.roles-permissions')">{{ __('Back to List') }}</x-button>
    </x-slot>

    <div class="max-w-2xl mx-auto">
        <x-card :title="$cardTitle" :description="$cardDesc">
            <form action="{{ $actionUrl }}" method="POST" class="space-y-6" id="roleForm">
                @csrf
                @if($isEdit)
                    @method('PUT')
                @endif

                <div>
                    <label for="name" class="form-label">{{ __('Role Name') }}</label>
                    <input
                        type="text"
                        id="name"
                        name="name"
                        class="form-input @error('name') border-red-300 @enderror"
                        placeholder="{{ __('e.g. Administrator') }}"
                        value="{{ old('name', $isEdit ? $role->name : '') }}"
                        required
                    />
                    @error('name')
                        <p class="form-error">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label class="form-label">{{ __('Permissions') }}</label>
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-2 max-h-64 overflow-y-auto p-4 bg-slate-50 rounded-xl border border-slate-100">
                        @foreach($permissions as $permission)
                            <label class="flex items-center gap-3 p-2.5 rounded-lg hover:bg-white cursor-pointer transition-all">
                                <input type="checkbox" name="permissions[]" value="{{ $permission->id }}"
                                    class="w-4 h-4 rounded border-slate-300 text-brand-600 focus:ring-brand-200"
                                    {{ (is_array(old('permissions')) && in_array($permission->id, old('permissions'))) || ($isEdit && $role->permissions->contains($permission->id)) ? 'checked' : '' }}>
                                <span class="text-sm font-medium text-slate-700">{{ $permission->name }}</span>
                            </label>
                        @endforeach
                    </div>
                    @error('permissions')
                        <p class="form-error">{{ $message }}</p>
                    @enderror
                </div>

                <div class="flex items-center gap-3 pt-2">
                    <button type="submit" class="btn-primary">
                        <x-icon name="check" class="h-4 w-4" />
                        {{ $btnText }}
                    </button>
                    <a href="{{ route('admin.roles-permissions') }}" class="btn-secondary">
                        {{ $isEdit ? __('Cancel') : __('Back to List') }}
                    </a>
                </div>
            </form>
        </x-card>
    </div>
</x-layouts.admin>
