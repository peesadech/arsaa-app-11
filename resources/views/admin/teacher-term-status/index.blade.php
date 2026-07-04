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
        <div class="p-6">
            {{-- Filters --}}
            <div class="mb-6 p-5 bg-slate-50 rounded-xl border border-slate-100">
                <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
                    <div class="flex items-center gap-3">
                        <div class="w-10 h-10 rounded-lg bg-white shadow-card border border-slate-100 flex items-center justify-center text-brand-600">
                            <x-icon name="filter" class="h-5 w-5" />
                        </div>
                        <div>
                            <h3 class="text-sm font-semibold text-slate-800 uppercase tracking-wide">{{ __('Quick Filters') }}</h3>
                            <p class="text-[10px] text-slate-400 font-bold uppercase">{{ __('Refine teacher list by criteria') }}</p>
                        </div>
                    </div>
                    <div class="flex flex-wrap items-center gap-3">
                        <select id="masterStatusFilter" class="form-select w-full md:w-40 text-sm">
                            <option value="">{{ __('All Status') }}</option>
                            <option value="1">{{ __('Active') }}</option>
                            <option value="2">{{ __('Not Active') }}</option>
                        </select>
                        <select id="termStatusFilter" class="form-select w-full md:w-44 text-sm">
                            <option value="">{{ __('All Term Status') }}</option>
                            <option value="no_record">{{ __('No Record') }}</option>
                            @foreach(\App\Models\TeacherTermStatus::STATUSES as $s)
                            <option value="{{ $s }}">{{ __(ucfirst(str_replace('_', ' ', $s))) }}</option>
                            @endforeach
                        </select>
                        <select id="canScheduleFilter" class="form-select w-full md:w-40 text-sm">
                            <option value="">{{ __('All') }}</option>
                            <option value="1">{{ __('Can Schedule') }}</option>
                            <option value="0">{{ __('Cannot Schedule') }}</option>
                        </select>
                    </div>
                </div>
            </div>

            <div class="overflow-x-auto lg:overflow-visible">
                <table id="termStatusTable" class="w-full text-left border-collapse whitespace-nowrap lg:whitespace-normal">
                    <thead>
                        <tr class="bg-slate-50 border-b border-slate-100">
                            <th class="px-4 py-4 text-xs font-medium text-slate-500 uppercase tracking-wide"></th>
                            <th class="px-4 py-4 text-xs font-medium text-slate-500 uppercase tracking-wide">{{ __('Name') }}</th>
                            <th class="px-4 py-4 text-xs font-medium text-slate-500 uppercase tracking-wide">{{ __('Master') }}</th>
                            <th class="px-4 py-4 text-xs font-medium text-slate-500 uppercase tracking-wide">{{ __('Term Status') }}</th>
                            <th class="px-4 py-4 text-xs font-medium text-slate-500 uppercase tracking-wide">{{ __('Can Schedule') }}</th>
                            <th class="px-4 py-4 text-xs font-medium text-slate-500 uppercase tracking-wide">{{ __('Max Load') }}</th>
                            <th class="px-4 py-4 text-xs font-medium text-slate-500 uppercase tracking-wide text-right">{{ __('Action') }}</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 text-slate-600 text-sm"></tbody>
                </table>
            </div>
        </div>
    </x-card>
    @endif

@push('styles')
    <link rel="stylesheet" href="https://cdn.datatables.net/1.11.5/css/dataTables.bootstrap4.min.css">
    <link rel="stylesheet" href="https://cdn.datatables.net/responsive/2.2.9/css/responsive.bootstrap4.min.css">
    <style>
        .dataTables_wrapper .dataTables_paginate .paginate_button { padding: 0 !important; margin: 0 !important; border: none !important; }
        .dataTables_wrapper .dataTables_length select, .dataTables_wrapper .dataTables_filter input { border: 1px solid #e2e8f0 !important; border-radius: 0.5rem !important; padding: 10px 16px !important; outline: none !important; height: auto !important; font-weight: 500 !important; background: white !important; }
        .dataTables_wrapper .dataTables_filter input:focus { border-color: #2563eb !important; }
        .dataTables_wrapper .dataTables_length { width: 100% !important; margin-bottom: 0 !important; }
        .dataTables_wrapper .dataTables_length label { width: 100% !important; display: flex !important; align-items: center !important; font-size: 0.875rem !important; color: #64748b !important; font-weight: 600 !important; }
        .dataTables_wrapper .dataTables_length select { flex: 1 !important; margin: 0 0.75rem !important; max-width: 120px !important; min-height: 46px !important; }
        .dataTables_wrapper .dataTables_filter { width: 100% !important; }
        .dataTables_wrapper .dataTables_filter label { width: 100% !important; display: flex !important; align-items: center !important; }
        .dataTables_wrapper .dataTables_filter input { flex: 1 !important; margin-left: 0 !important; }
        table.dataTable thead th { border-bottom: 1px solid #f1f5f9 !important; }
        table.dataTable.no-footer { border-bottom: none !important; }
        .pagination .page-item.active .page-link { background-color: #2563eb !important; border-color: #2563eb !important; color: white !important; }
        .pagination .page-link { border-radius: 0.5rem !important; margin: 0 4px !important; font-weight: 700 !important; color: #475569 !important; border: 1px solid #e2e8f0 !important; background: white; }
        table.dataTable tbody tr { background-color: transparent !important; }
    </style>
@endpush

@push('scripts')
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.datatables.net/1.11.5/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.11.5/js/dataTables.bootstrap4.min.js"></script>
    <script src="https://cdn.datatables.net/responsive/2.2.9/js/dataTables.responsive.min.js"></script>

    <script>
    $(document).ready(function() {
        @if($academicYear && $semester)
        const table = $('#termStatusTable').DataTable({
            processing: true,
            serverSide: true,
            responsive: true,
            dom: '<"grid grid-cols-1 lg:grid-cols-2 gap-4 mb-6"lf>rt<"flex flex-col md:flex-row md:items-center md:justify-between mt-6"ip>',
            ajax: {
                url: "{{ route('admin.teacher-term-status.data') }}",
                data: function(d) {
                    d.master_status = $('#masterStatusFilter').val();
                    d.term_status = $('#termStatusFilter').val();
                    d.can_schedule = $('#canScheduleFilter').val();
                }
            },
            columns: [
                { data: 'avatar', name: 'avatar', orderable: false, searchable: false, className: 'px-4 py-3 w-14' },
                { data: 'name', name: 'teachers.name', className: 'px-4 py-3 font-bold' },
                { data: 'master_status_badge', name: 'teachers.status', orderable: true, className: 'px-4 py-3 text-center' },
                { data: 'term_status_badge', name: 'tts.status', orderable: true, className: 'px-4 py-3 text-center' },
                { data: 'schedule_badge', name: 'tts.can_be_scheduled', orderable: true, className: 'px-4 py-3 text-center' },
                { data: 'max_load', name: 'max_load', orderable: false, searchable: false, className: 'px-4 py-3 text-center' },
                { data: 'action', name: 'action', orderable: false, searchable: false, className: 'px-4 py-3 text-right' },
            ],
            language: {
                search: "",
                searchPlaceholder: @json(__('Search teachers...')),
                lengthMenu: @json(__('Show')) + " _MENU_",
                paginate: { previous: '<i class="fas fa-chevron-left"></i>', next: '<i class="fas fa-chevron-right"></i>' }
            }
        });

        $('#masterStatusFilter, #termStatusFilter, #canScheduleFilter').on('change', function() { table.draw(); });
        @endif
    });
    </script>
@endpush
</x-layouts.admin>
