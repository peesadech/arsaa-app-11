<x-layouts.admin :header="__('Student Management')" :subheader="__('Manage student records, guardians and enrollments')">
    <x-slot name="actions">
        <x-button variant="secondary" :href="route('admin.student-master.index')">{{ __('Student Master Data') }}</x-button>
        <x-button variant="secondary" icon="chart" :href="route('admin.student-reports.index')">{{ __('Reports') }}</x-button>
        <x-button icon="plus" :href="route('admin.students.create')">{{ __('New Student') }}</x-button>
    </x-slot>

    <div x-data="{ deleteTarget: null }" x-on:open-delete.window="deleteTarget = $event.detail">
        <x-card padded="false">
            <x-data-table
                :endpoint="route('admin.students.index')"
                :columns="[
                    ['key' => 'student_code', 'label' => __('Student Code'), 'sortable' => true],
                    ['key' => 'name_th', 'label' => __('Name'), 'sortable' => true],
                    ['key' => null, 'label' => __('Classroom'), 'sortable' => false],
                    ['key' => null, 'label' => __('Mobile'), 'sortable' => false],
                    ['key' => 'status', 'label' => __('Status'), 'sortable' => true],
                    ['key' => null, 'label' => '', 'align' => 'right'],
                ]"
                :state="[
                    'search'           => request('search', ''),
                    'sort_by'          => request('sort_by', 'id'),
                    'sort_order'       => request('sort_order', 'desc'),
                    'per_page'         => (int) request('per_page', 10),
                    'page'             => (int) request('page', 1),
                    'status'           => request('status', ''),
                    'academic_year_id' => request('academic_year_id', ''),
                    'semester_id'      => request('semester_id', ''),
                    'classroom_id'     => request('classroom_id', ''),
                ]"
                :meta="[
                    'total'        => $students->total(),
                    'per_page'     => $students->perPage(),
                    'current_page' => $students->currentPage(),
                    'last_page'    => $students->lastPage(),
                    'from'         => $students->firstItem() ?? 0,
                    'to'           => $students->lastItem() ?? 0,
                ]"
                :show-search="false"
            >
                <x-slot name="filters">
                    <select x-model="state.academic_year_id" @change="changeFilter()"
                            class="form-select rounded-lg w-full sm:w-36">
                        <option value="">{{ __('All Years') }}</option>
                        @foreach ($academicYears as $y)
                            <option value="{{ $y->id }}">{{ $y->year }}</option>
                        @endforeach
                    </select>
                    <select x-model="state.semester_id" @change="changeFilter()"
                            class="form-select rounded-lg w-full sm:w-36">
                        <option value="">{{ __('All Semesters') }}</option>
                        @foreach ($semesters as $s)
                            <option value="{{ $s->id }}">{{ __('Semester') }} {{ $s->semester_number }}</option>
                        @endforeach
                    </select>
                    <select x-model="state.classroom_id" @change="changeFilter()"
                            class="form-select rounded-lg w-full sm:w-36">
                        <option value="">{{ __('All Classrooms') }}</option>
                        @foreach ($classrooms as $c)
                            <option value="{{ $c->id }}">{{ $c->name }}</option>
                        @endforeach
                    </select>
                    <select x-model="state.status" @change="changeFilter()"
                            class="form-select rounded-lg w-full sm:w-36">
                        <option value="">{{ __('All Status') }}</option>
                        <option value="studying">{{ __('Studying') }}</option>
                        <option value="suspended">{{ __('Suspended') }}</option>
                        <option value="resigned">{{ __('Resigned') }}</option>
                        <option value="graduated">{{ __('Graduated') }}</option>
                    </select>
                    <label class="relative block w-full sm:w-64">
                        <span class="absolute inset-y-0 left-0 flex items-center pl-3 text-slate-400 pointer-events-none">
                            <x-icon name="search" class="h-4 w-4" />
                        </span>
                        <input type="search" x-model="state.search" @input.debounce.350ms="changeFilter()"
                               placeholder="{{ __('Search by code, name, phone...') }}" class="form-input pl-9 rounded-lg w-full">
                    </label>
                </x-slot>

                <x-slot name="rows">
                    @include('admin.students._rows', ['students' => $students])
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
                    <form method="POST" x-bind:action="`{{ url('admin/students') }}/${deleteTarget?.id}`">
                        @csrf @method('DELETE')
                        <button type="submit" class="btn-danger">{{ __('Confirm Delete') }}</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</x-layouts.admin>
