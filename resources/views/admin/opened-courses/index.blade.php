<x-layouts.admin :header="__('Opened Courses')"
    :subheader="($currentYear && $currentSemester)
        ? __('Academic Year').' '.$currentYear->year.' • '.__('Semester').' '.$currentSemester->semester_number
        : __('No academic year selected')">
    <x-slot name="actions">
        @if($currentYear && $currentSemester)
            <x-button icon="plus" :href="route('admin.opened-courses.create')">{{ __('Add Course') }}</x-button>
        @endif
    </x-slot>

    <div x-data="{
            deleteTarget: null,
            deleting: false,
            async confirmDelete() {
                if (!this.deleteTarget) return;
                this.deleting = true;
                try {
                    const res = await fetch(`{{ url('admin/opened-courses') }}/${this.deleteTarget.id}`, {
                        method: 'DELETE',
                        headers: {
                            'X-CSRF-TOKEN': '{{ csrf_token() }}',
                            'Accept': 'application/json',
                            'X-Requested-With': 'XMLHttpRequest',
                        },
                    });
                    if (res.ok) { window.location.reload(); }
                } finally {
                    this.deleting = false;
                }
            }
         }"
         x-on:open-delete.window="deleteTarget = $event.detail">
        <x-card padded="false">
            <x-data-table
                :endpoint="route('admin.opened-courses.index')"
                :columns="[
                    ['key' => 'grade_id', 'label' => __('Grade Level'), 'sortable' => true],
                    ['key' => 'classroom_id', 'label' => __('Classroom'), 'sortable' => true],
                    ['key' => 'course_id', 'label' => __('Course'), 'sortable' => true],
                    ['key' => null, 'label' => '', 'align' => 'right'],
                ]"
                :state="[
                    'search'     => request('search', ''),
                    'sort_by'    => request('sort_by', 'id'),
                    'sort_order' => request('sort_order', 'desc'),
                    'per_page'   => (int) request('per_page', 10),
                    'page'       => (int) request('page', 1),
                    'grade_id'   => request('grade_id', ''),
                    'course_id'  => request('course_id', ''),
                ]"
                :meta="[
                    'total'        => $openedCourses->total(),
                    'per_page'     => $openedCourses->perPage(),
                    'current_page' => $openedCourses->currentPage(),
                    'last_page'    => $openedCourses->lastPage(),
                    'from'         => $openedCourses->firstItem() ?? 0,
                    'to'           => $openedCourses->lastItem() ?? 0,
                ]"
                :show-search="false"
            >
                <x-slot name="filters">
                    <select x-model="state.grade_id" @change="changeFilter()"
                            class="form-select rounded-lg w-full sm:w-48">
                        <option value="">{{ __('All Grade Levels') }}</option>
                        @foreach ($grades as $grade)
                            <option value="{{ $grade->id }}">{{ $grade->name_th }}</option>
                        @endforeach
                    </select>
                    <select x-model="state.course_id" @change="changeFilter()"
                            class="form-select rounded-lg w-full sm:w-56">
                        <option value="">{{ __('All Courses') }}</option>
                        @foreach ($courses as $course)
                            <option value="{{ $course->id }}">{{ $course->name }}</option>
                        @endforeach
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
                    @include('admin.opened-courses._rows', ['openedCourses' => $openedCourses])
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
                    <button type="button" class="btn-danger" x-on:click="confirmDelete()" x-bind:disabled="deleting">{{ __('Confirm Delete') }}</button>
                </div>
            </div>
        </div>
    </div>
</x-layouts.admin>
