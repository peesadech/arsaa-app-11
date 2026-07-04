<x-layouts.admin :header="__('Attendance Status')" :subheader="__('Manage attendance statuses (present, late, leave, absent, ...)')">
    <x-slot name="actions">
        <x-button icon="plus" :href="route('admin.attendance-statuses.create')">{{ __('New Attendance Status') }}</x-button>
    </x-slot>

    <div x-data="{ deleteTarget: null }" x-on:open-delete.window="deleteTarget = $event.detail">
        <x-card padded="false">
            <x-data-table
                :endpoint="route('admin.attendance-statuses.index')"
                :columns="[
                    ['key' => 'name_th', 'label' => __('Name'), 'sortable' => true],
                    ['key' => 'code', 'label' => __('Code'), 'sortable' => true],
                    ['key' => null, 'label' => __('Type'), 'sortable' => false],
                    ['key' => null, 'label' => __('Flags'), 'sortable' => false],
                    ['key' => null, 'label' => __('Status'), 'sortable' => false],
                    ['key' => null, 'label' => '', 'align' => 'right'],
                ]"
                :state="[
                    'search'     => request('search', ''),
                    'sort_by'    => request('sort_by', 'sort_order'),
                    'sort_order' => request('sort_order', 'asc'),
                    'per_page'   => (int) request('per_page', 10),
                    'page'       => (int) request('page', 1),
                    'is_active'  => request('is_active', ''),
                ]"
                :meta="[
                    'total'        => $statuses->total(),
                    'per_page'     => $statuses->perPage(),
                    'current_page' => $statuses->currentPage(),
                    'last_page'    => $statuses->lastPage(),
                    'from'         => $statuses->firstItem() ?? 0,
                    'to'           => $statuses->lastItem() ?? 0,
                ]"
                :show-search="false"
            >
                <x-slot name="filters">
                    <select x-model="state.is_active" @change="changeFilter()"
                            class="form-select rounded-lg w-full sm:w-48">
                        <option value="">{{ __('All Status') }}</option>
                        <option value="1">{{ __('Active') }}</option>
                        <option value="0">{{ __('Inactive') }}</option>
                    </select>
                    <label class="relative block w-full sm:w-64">
                        <span class="absolute inset-y-0 left-0 flex items-center pl-3 text-slate-400 pointer-events-none">
                            <x-icon name="search" class="h-4 w-4" />
                        </span>
                        <input type="search" x-model="state.search" @input.debounce.350ms="changeFilter()"
                               placeholder="{{ __('Search') }}..." class="form-input pl-9 rounded-lg w-full">
                    </label>
                </x-slot>

                <x-slot name="rows">
                    @include('admin.attendance-statuses._rows', ['statuses' => $statuses])
                </x-slot>
            </x-data-table>
        </x-card>

        {{-- Delete confirm modal --}}
        <div x-show="deleteTarget !== null" x-cloak class="fixed inset-0 z-50 flex items-center justify-center p-4" x-transition.opacity>
            <div class="absolute inset-0 bg-slate-900/50" x-on:click="deleteTarget = null"></div>
            <div class="relative bg-white rounded-2xl shadow-2xl w-full max-w-md p-6">
                <div class="flex items-start gap-4">
                    <div class="h-12 w-12 rounded-full bg-red-50 text-red-600 flex items-center justify-center shrink-0"><x-icon name="trash" class="h-6 w-6" /></div>
                    <div>
                        <h3 class="text-lg font-semibold text-slate-900">{{ __('Confirm Deletion') }}</h3>
                        <p class="text-sm text-slate-500 mt-1">{{ __('Are you sure you want to permanently remove') }} <span class="font-medium text-slate-700" x-text="deleteTarget?.name"></span>?</p>
                    </div>
                </div>
                <div class="mt-6 flex justify-end gap-2">
                    <button type="button" class="btn-secondary" x-on:click="deleteTarget = null">{{ __('Cancel') }}</button>
                    <form method="POST" x-bind:action="`{{ url('admin/attendance-statuses') }}/${deleteTarget?.id}`">
                        @csrf @method('DELETE')
                        <button type="submit" class="btn-danger">{{ __('Confirm Delete') }}</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</x-layouts.admin>
