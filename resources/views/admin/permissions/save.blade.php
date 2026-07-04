@php
    $isEdit = isset($permission);
    $actionUrl = $isEdit ? route('admin.permissions.update', $permission->id) : route('admin.permissions.store');
    $title = $isEdit ? __('Edit Permission') : __('Create New Permission');
    $subtitle = $isEdit ? __('Update access control details') : __('Access Control System');
    $cardTitle = $isEdit ? __('Modify Permission') : __('Permission Details');
    $cardDesc = $isEdit
        ? __('You are updating permission #:id. Ensure the name remains meaningful and consistent.', ['id' => $permission->id])
        : __('Define a new permission to control access to specific features or resources.');
    $btnText = $isEdit ? __('Save Changes') : __('Create Permission');
@endphp

<x-layouts.admin :header="$title" :subheader="$subtitle">
    <x-slot name="actions">
        <x-button variant="secondary" icon="arrow-left" :href="route('admin.permissions')">{{ __('Back to List') }}</x-button>
    </x-slot>

    <div class="max-w-xl mx-auto">
        <x-card :title="$cardTitle" :description="$cardDesc">
            <form action="{{ $actionUrl }}" method="POST" class="space-y-6" id="permissionForm">
                @csrf
                @if($isEdit)
                    @method('PUT')
                @endif

                <div>
                    <label for="name" class="form-label">{{ __('Permission Name') }}</label>
                    <input
                        type="text"
                        id="name"
                        name="name"
                        class="form-input @error('name') border-red-300 @enderror"
                        placeholder="{{ __('e.g. edit articles') }}"
                        value="{{ old('name', $isEdit ? $permission->name : '') }}"
                        required
                        autofocus
                    />
                    @error('name')
                        <p class="form-error">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="guard_name" class="form-label">{{ __('Guard Name') }}</label>
                    <select id="guard_name" name="guard_name" class="form-select" required>
                        <option value="web" {{ old('guard_name', $isEdit ? $permission->guard_name : 'web') === 'web' ? 'selected' : '' }}>WEB</option>
                        <option value="api" {{ old('guard_name', $isEdit ? $permission->guard_name : 'web') === 'api' ? 'selected' : '' }}>API</option>
                    </select>
                </div>

                <div class="flex items-center gap-3 pt-2">
                    <button type="submit" class="btn-primary">
                        <x-icon name="check" class="h-4 w-4" />
                        {{ $btnText }}
                    </button>
                    <a href="{{ route('admin.permissions') }}" class="btn-secondary">
                        {{ $isEdit ? __('Cancel') : __('Back to List') }}
                    </a>
                </div>
            </form>
        </x-card>

        <div class="mt-4 flex items-start gap-3 p-4 bg-brand-50 rounded-xl border border-brand-100">
            <div class="shrink-0 h-8 w-8 rounded-full bg-white flex items-center justify-center border border-brand-100 text-brand-500">
                <x-icon name="shield" class="h-4 w-4" />
            </div>
            <div>
                <h4 class="text-xs font-semibold text-slate-700 uppercase mb-1">{{ __('Naming Convention') }}</h4>
                <p class="text-xs text-slate-500 leading-relaxed">
                    {{ __('Use consistent naming patterns like resource.action (e.g., posts.create, users.edit) to keep your permissions organized.') }}
                </p>
            </div>
        </div>
    </div>
</x-layouts.admin>
