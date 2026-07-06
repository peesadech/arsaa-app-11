@php
    $subheader = ($academicYear && $semester)
        ? __('Academic Year') . ' ' . $academicYear->year . ' / ' . __('Semester') . ' ' . $semester->semester_number
        : __('Please select academic year and semester first');
@endphp

<x-layouts.admin :header="__('Teacher Term Status')" :subheader="$subheader">
    <x-slot name="actions">
        <x-button variant="secondary" icon="arrow-left" :href="route('admin.dashboard')">{{ __('Back') }}</x-button>
        @if($academicYear && $semester)
        <form action="{{ route('admin.teacher-term-status.bulk-initialize') }}" method="POST"
              onsubmit="return confirm('{{ __('Initialize term status for all active teachers?') }}')">
            @csrf
            <button type="submit" class="btn-primary">
                <x-icon name="users" class="h-4 w-4" /> {{ __('Initialize All') }}
            </button>
        </form>
        @endif
    </x-slot>

    {{-- Flash --}}
    @if(session('status'))
    <div class="mb-6 rounded-xl bg-emerald-50 border border-emerald-200 text-emerald-700 text-sm px-4 py-3">
        {{ session('status') }}
    </div>
    @endif
    @if(session('error'))
    <div class="mb-6 rounded-xl bg-red-50 border border-red-200 text-red-700 text-sm px-4 py-3">
        {{ session('error') }}
    </div>
    @endif

    {{-- Summary --}}
    @if($summary)
    <div class="grid grid-cols-2 md:grid-cols-4 lg:grid-cols-7 gap-3 mb-8">
        <div class="card card-body text-center py-4">
            <div class="text-2xl font-bold text-slate-700">{{ $summary['total_active_teachers'] }}</div>
            <div class="text-[10px] text-slate-400 font-bold uppercase mt-1">{{ __('Total Active') }}</div>
        </div>
        <div class="card card-body text-center py-4">
            <div class="text-2xl font-bold text-slate-400">{{ $summary['no_term_record'] }}</div>
            <div class="text-[10px] text-slate-400 font-bold uppercase mt-1">{{ __('No Record') }}</div>
        </div>
        @foreach(['available' => 'text-emerald-600', 'unavailable' => 'text-red-600', 'leave' => 'text-amber-600', 'partial' => 'text-brand-600', 'transferred' => 'text-purple-600'] as $s => $color)
        <div class="card card-body text-center py-4">
            <div class="text-2xl font-bold {{ $color }}">{{ $summary['by_status'][$s] ?? 0 }}</div>
            <div class="text-[10px] text-slate-400 font-bold uppercase mt-1">{{ __(ucfirst($s)) }}</div>
        </div>
        @endforeach
    </div>
    @endif

    {{-- Table --}}
    @if($academicYear && $semester)
    <x-card padded="false">
        <x-data-table
            :endpoint="route('admin.teacher-term-status.data')"
            :columns="[
                ['key' => null, 'label' => ''],
                ['key' => 'name', 'label' => __('Name'), 'sortable' => true],
                ['key' => 'status', 'label' => __('Master'), 'sortable' => true, 'align' => 'center'],
                ['key' => 'term_status', 'label' => __('Term Status'), 'sortable' => true, 'align' => 'center'],
                ['key' => 'can_be_scheduled', 'label' => __('Can Schedule'), 'sortable' => true, 'align' => 'center'],
                ['key' => null, 'label' => __('Max Load'), 'align' => 'center'],
                ['key' => null, 'label' => __('Action'), 'align' => 'right'],
            ]"
            :state="[
                'search'        => request('search', ''),
                'sort_by'       => request('sort_by', 'id'),
                'sort_order'    => request('sort_order', 'desc'),
                'per_page'      => (int) request('per_page', 10),
                'page'          => (int) request('page', 1),
                'master_status' => request('master_status', ''),
                'term_status'   => request('term_status', ''),
                'can_schedule'  => request('can_schedule', ''),
            ]"
            :meta="[
                'total'        => $teachers->total(),
                'per_page'     => $teachers->perPage(),
                'current_page' => $teachers->currentPage(),
                'last_page'    => $teachers->lastPage(),
                'from'         => $teachers->firstItem() ?? 0,
                'to'           => $teachers->lastItem() ?? 0,
            ]"
            :show-search="false"
        >
            <x-slot name="filters">
                <select x-model="state.master_status" @change="changeFilter()"
                        class="form-select rounded-lg w-full sm:w-40 text-sm">
                    <option value="">{{ __('All Status') }}</option>
                    <option value="1">{{ __('Active') }}</option>
                    <option value="2">{{ __('Not Active') }}</option>
                </select>
                <select x-model="state.term_status" @change="changeFilter()"
                        class="form-select rounded-lg w-full sm:w-44 text-sm">
                    <option value="">{{ __('All Term Status') }}</option>
                    <option value="no_record">{{ __('No Record') }}</option>
                    @foreach(\App\Models\TeacherTermStatus::STATUSES as $s)
                    <option value="{{ $s }}">{{ __(ucfirst(str_replace('_', ' ', $s))) }}</option>
                    @endforeach
                </select>
                <select x-model="state.can_schedule" @change="changeFilter()"
                        class="form-select rounded-lg w-full sm:w-40 text-sm">
                    <option value="">{{ __('All') }}</option>
                    <option value="1">{{ __('Can Schedule') }}</option>
                    <option value="0">{{ __('Cannot Schedule') }}</option>
                </select>
                <label class="relative block w-full sm:w-64">
                    <span class="absolute inset-y-0 left-0 flex items-center pl-3 text-slate-400 pointer-events-none">
                        <x-icon name="search" class="h-4 w-4" />
                    </span>
                    <input type="search" x-model="state.search" @input.debounce.350ms="changeFilter()"
                           placeholder="{{ __('Search teachers...') }}" class="form-input pl-9 rounded-lg w-full">
                </label>
            </x-slot>

            <x-slot name="rows">
                @include('admin.teacher-term-status._rows', ['teachers' => $teachers])
            </x-slot>
        </x-data-table>
    </x-card>
    @endif
</x-layouts.admin>
