@php
    $u = $user ?? null;
    $isEdit = $u !== null;
    $assignedRoles = old('roles', $userRoles ?? []);
@endphp
<div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
    <div class="lg:col-span-2 space-y-6">
        <x-card :title="__('Account Details')">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <x-form.input :label="__('Full Name')" name="name" :value="$u->name ?? null" required />
                <x-form.input :label="__('Email Address')" name="email" type="email" :value="$u->email ?? null" required />
                <x-form.input :label="__('Password')" name="password" type="password"
                    :help="$isEdit ? __('Leave blank to keep current') : null"
                    :required="!$isEdit" autocomplete="new-password" />
                <x-form.input :label="__('Confirm Password')" name="password_confirmation" type="password"
                    :required="!$isEdit" autocomplete="new-password" />
            </div>
        </x-card>
    </div>

    <div class="space-y-6">
        <x-card :title="__('Assign Roles')">
            <div class="space-y-2">
                @forelse ($roles as $role)
                    <label class="flex items-center gap-2">
                        <input type="checkbox" name="roles[]" value="{{ $role->name }}"
                               class="form-checkbox rounded text-brand-600"
                               @checked(in_array($role->name, $assignedRoles))>
                        <span class="text-sm text-slate-700">{{ $role->name }}</span>
                    </label>
                @empty
                    <p class="text-sm text-slate-400 italic">{{ __('No roles available') }}</p>
                @endforelse
            </div>
            @error('roles')<p class="form-error">{{ $message }}</p>@enderror
        </x-card>

        <x-card :title="__('Status')">
            <x-form.select
                name="status"
                :options="[1 => __('Active'), 2 => __('Not Active')]"
                :selected="$u->status ?? 1" />
        </x-card>
    </div>
</div>
