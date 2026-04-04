@extends('layouts.app')

@push('styles')
    <link rel="stylesheet" href="https://cdn.datatables.net/1.11.5/css/dataTables.bootstrap4.min.css">
    <link rel="stylesheet" href="https://cdn.datatables.net/responsive/2.2.9/css/responsive.bootstrap4.min.css">
    <style>
        .dataTables_wrapper .dataTables_paginate .paginate_button { padding: 0 !important; margin: 0 !important; border: none !important; }
        .dataTables_wrapper .dataTables_length select, .dataTables_wrapper .dataTables_filter input { border: 2px solid #f3f4f6 !important; border-radius: 12px !important; padding: 10px 16px !important; outline: none !important; height: auto !important; font-weight: 500 !important; background: white !important; }
        .dark .dataTables_wrapper .dataTables_length select, .dark .dataTables_wrapper .dataTables_filter input { background: #242526 !important; border-color: #3a3b3c !important; color: #e4e6eb !important; }
        .dataTables_wrapper .dataTables_filter input:focus { border-color: #6366f1 !important; }
        .dataTables_wrapper .dataTables_length { width: 100% !important; margin-bottom: 0 !important; }
        .dataTables_wrapper .dataTables_length label { width: 100% !important; display: flex !important; align-items: center !important; font-size: 0.875rem !important; color: #6b7280 !important; font-weight: 600 !important; }
        .dataTables_wrapper .dataTables_length select { flex: 1 !important; margin: 0 0.75rem !important; max-width: 120px !important; min-height: 46px !important; }
        .dataTables_wrapper .dataTables_filter { width: 100% !important; }
        .dataTables_wrapper .dataTables_filter label { width: 100% !important; display: flex !important; align-items: center !important; }
        .dataTables_wrapper .dataTables_filter input { flex: 1 !important; margin-left: 0 !important; }
        table.dataTable thead th { border-bottom: 1px solid #f3f4f6 !important; }
        table.dataTable.no-footer { border-bottom: none !important; }
        .pagination .page-item.active .page-link { background-color: #6366f1 !important; border-color: #6366f1 !important; color: white !important; }
        .pagination .page-link { border-radius: 12px !important; margin: 0 4px !important; font-weight: 700 !important; color: #4b5563 !important; border: 2px solid #f3f4f6 !important; background: white; }
        .dark .pagination .page-link { background: #242526; border-color: #3a3b3c !important; color: #b0b3b8 !important; }
        table.dataTable tbody tr { background-color: transparent !important; }
    </style>
@endpush

@section('content')
<div class="min-h-screen bg-gray-50 dark:bg-[#18191a] py-8 px-4 sm:px-6 lg:px-8 transition-colors duration-300">
    <div class="max-w-7xl mx-auto">
        {{-- Header --}}
        <div class="flex flex-col md:flex-row md:items-center md:justify-between mb-10 space-y-4 md:space-y-0">
            <div class="flex items-center space-x-6">
                <a href="{{ route('admin.dashboard') }}"
                   class="group flex items-center justify-center w-10 h-10 rounded-full bg-white dark:bg-[#242526] shadow-sm border border-gray-200 dark:border-[#3a3b3c] text-gray-500 dark:text-gray-400 hover:text-indigo-600 dark:hover:text-indigo-400 hover:border-indigo-300 transition-all duration-200">
                    <i class="fas fa-arrow-left group-hover:-translate-x-0.5 transition-transform"></i>
                </a>
                <div>
                    <h1 class="text-3xl font-extrabold text-gray-900 dark:text-white tracking-tight">{{ __('Teacher Term Status') }}</h1>
                    <p class="text-sm text-gray-500 dark:text-gray-400 font-medium mt-1 px-1">
                        @if($academicYear && $semester)
                            {{ __('Academic Year') }} {{ $academicYear->year }} / {{ __('Semester') }} {{ $semester->semester_number }}
                        @else
                            {{ __('Please select academic year and semester first') }}
                        @endif
                    </p>
                </div>
            </div>

            @if($academicYear && $semester)
            <div class="flex items-center gap-3">
                <form action="{{ route('admin.teacher-term-status.bulk-initialize') }}" method="POST"
                      onsubmit="return confirm('{{ __('Initialize term status for all active teachers?') }}')">
                    @csrf
                    <button type="submit" class="btn-app">
                        <i class="fas fa-users-cog text-[10px]"></i> {{ __('Initialize All') }}
                    </button>
                </form>
            </div>
            @endif
        </div>

        {{-- Flash --}}
        @if(session('status'))
        <div class="mb-6 p-4 bg-emerald-50 dark:bg-emerald-900/20 border border-emerald-200 dark:border-emerald-800 rounded-2xl text-emerald-700 dark:text-emerald-300 text-sm">
            {{ session('status') }}
        </div>
        @endif
        @if(session('error'))
        <div class="mb-6 p-4 bg-rose-50 dark:bg-rose-900/20 border border-rose-200 dark:border-rose-800 rounded-2xl text-rose-700 dark:text-rose-300 text-sm">
            {{ session('error') }}
        </div>
        @endif

        {{-- Summary --}}
        @if($summary)
        <div class="grid grid-cols-2 md:grid-cols-4 lg:grid-cols-7 gap-3 mb-8">
            <div class="bg-white dark:bg-[#242526] rounded-2xl p-4 text-center border border-gray-100 dark:border-[#3a3b3c]">
                <div class="text-2xl font-bold text-gray-700 dark:text-gray-300">{{ $summary['total_active_teachers'] }}</div>
                <div class="text-[10px] text-gray-400 font-bold uppercase mt-1">{{ __('Total Active') }}</div>
            </div>
            <div class="bg-white dark:bg-[#242526] rounded-2xl p-4 text-center border border-gray-100 dark:border-[#3a3b3c]">
                <div class="text-2xl font-bold text-gray-400">{{ $summary['no_term_record'] }}</div>
                <div class="text-[10px] text-gray-400 font-bold uppercase mt-1">{{ __('No Record') }}</div>
            </div>
            @foreach(['available' => 'text-emerald-600', 'unavailable' => 'text-rose-600', 'leave' => 'text-amber-600', 'partial' => 'text-blue-600', 'transferred' => 'text-purple-600'] as $s => $color)
            <div class="bg-white dark:bg-[#242526] rounded-2xl p-4 text-center border border-gray-100 dark:border-[#3a3b3c]">
                <div class="text-2xl font-bold {{ $color }}">{{ $summary['by_status'][$s] ?? 0 }}</div>
                <div class="text-[10px] text-gray-400 font-bold uppercase mt-1">{{ __(ucfirst($s)) }}</div>
            </div>
            @endforeach
        </div>
        @endif

        {{-- Table --}}
        @if($academicYear && $semester)
        <div class="bg-white dark:bg-[#242526] rounded-[2.5rem] shadow-xl shadow-gray-200/50 dark:shadow-none border border-gray-100 dark:border-[#3a3b3c] overflow-hidden">
            <div class="p-6 sm:p-8">
                {{-- Filters --}}
                <div class="mb-8 p-6 bg-gray-50/50 dark:bg-[#18191a]/30 rounded-[2rem] border border-gray-100 dark:border-[#3a3b3c]/50">
                    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
                        <div class="flex items-center space-x-3">
                            <div class="w-10 h-10 rounded-xl bg-white dark:bg-[#242526] shadow-sm border border-gray-100 dark:border-[#3a3b3c] flex items-center justify-center text-indigo-500">
                                <i class="fas fa-filter text-sm"></i>
                            </div>
                            <div>
                                <h3 class="text-sm font-bold text-gray-800 dark:text-gray-200 uppercase tracking-wider">{{ __('Quick Filters') }}</h3>
                                <p class="text-[10px] text-gray-400 font-bold uppercase tracking-tight">{{ __('Refine teacher list by criteria') }}</p>
                            </div>
                        </div>
                        <div class="flex flex-wrap items-center gap-3">
                            <div class="relative">
                                <select id="masterStatusFilter" class="appearance-none block w-full md:w-40 pl-4 pr-10 py-2.5 bg-white dark:bg-[#242526] border-2 border-gray-100 dark:border-[#3a3b3c] rounded-xl text-xs font-bold text-gray-600 dark:text-gray-400 focus:outline-none focus:border-indigo-500 transition-all cursor-pointer">
                                    <option value="">{{ __('All Status') }}</option>
                                    <option value="1">{{ __('Active') }}</option>
                                    <option value="2">{{ __('Not Active') }}</option>
                                </select>
                            </div>
                            <div class="relative">
                                <select id="termStatusFilter" class="appearance-none block w-full md:w-44 pl-4 pr-10 py-2.5 bg-white dark:bg-[#242526] border-2 border-gray-100 dark:border-[#3a3b3c] rounded-xl text-xs font-bold text-gray-600 dark:text-gray-400 focus:outline-none focus:border-indigo-500 transition-all cursor-pointer">
                                    <option value="">{{ __('All Term Status') }}</option>
                                    <option value="no_record">{{ __('No Record') }}</option>
                                    @foreach(\App\Models\TeacherTermStatus::STATUSES as $s)
                                    <option value="{{ $s }}">{{ __(ucfirst(str_replace('_', ' ', $s))) }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="relative">
                                <select id="canScheduleFilter" class="appearance-none block w-full md:w-40 pl-4 pr-10 py-2.5 bg-white dark:bg-[#242526] border-2 border-gray-100 dark:border-[#3a3b3c] rounded-xl text-xs font-bold text-gray-600 dark:text-gray-400 focus:outline-none focus:border-indigo-500 transition-all cursor-pointer">
                                    <option value="">{{ __('All') }}</option>
                                    <option value="1">{{ __('Can Schedule') }}</option>
                                    <option value="0">{{ __('Cannot Schedule') }}</option>
                                </select>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="overflow-x-auto lg:overflow-visible">
                    <table id="termStatusTable" class="w-full text-left border-collapse whitespace-nowrap lg:whitespace-normal">
                        <thead>
                            <tr class="bg-gray-50/50 dark:bg-[#18191a]/30 border-b border-gray-100 dark:border-[#3a3b3c]/50">
                                <th class="px-4 py-4 text-xs font-bold text-gray-400 uppercase tracking-widest first:rounded-tl-2xl"></th>
                                <th class="px-4 py-4 text-xs font-bold text-gray-400 uppercase tracking-widest">{{ __('Name') }}</th>
                                <th class="px-4 py-4 text-xs font-bold text-gray-400 uppercase tracking-widest">{{ __('Master') }}</th>
                                <th class="px-4 py-4 text-xs font-bold text-gray-400 uppercase tracking-widest">{{ __('Term Status') }}</th>
                                <th class="px-4 py-4 text-xs font-bold text-gray-400 uppercase tracking-widest">{{ __('Can Schedule') }}</th>
                                <th class="px-4 py-4 text-xs font-bold text-gray-400 uppercase tracking-widest">{{ __('Max Load') }}</th>
                                <th class="px-4 py-4 text-xs font-bold text-gray-400 uppercase tracking-widest text-right last:rounded-tr-2xl">{{ __('Action') }}</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-50 dark:divide-[#3a3b3c]/50 text-gray-600 dark:text-gray-400 text-sm"></tbody>
                    </table>
                </div>
            </div>
        </div>
        @endif
    </div>
</div>

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
@endsection
