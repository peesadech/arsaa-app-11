<x-layouts.admin :header="__('User Assignments')" :subheader="__('Assign roles and permissions to users')">
    <x-card :title="__('User Assignments')" :description="__('Manage which roles and permissions are assigned to each user.')">
        <div class="flex flex-col items-center justify-center py-12 text-center">
            <div class="h-14 w-14 rounded-2xl bg-brand-50 text-brand-600 flex items-center justify-center border border-brand-100 mb-4">
                <x-icon name="users" class="h-7 w-7" />
            </div>
            <h3 class="text-base font-semibold text-slate-800">{{ __('No assignments to display') }}</h3>
            <p class="text-sm text-slate-500 mt-1 max-w-sm">
                {{ __('User role and permission assignments will appear here once configured.') }}
            </p>
            <div class="mt-6 flex items-center gap-2">
                <x-button variant="secondary" icon="shield" :href="route('admin.roles-permissions')">{{ __('Manage Roles') }}</x-button>
                <x-button variant="secondary" icon="users" :href="route('admin.users.index')">{{ __('Manage Users') }}</x-button>
            </div>
        </div>
    </x-card>
</x-layouts.admin>
