@extends('layouts.app')

@push('styles')
    <link rel="stylesheet" href="https://cdn.datatables.net/1.11.5/css/dataTables.bootstrap4.min.css">
    <link rel="stylesheet" href="https://cdn.datatables.net/responsive/2.2.9/css/responsive.bootstrap4.min.css">
    <link rel="stylesheet" href="https://cdn.datatables.net/buttons/2.2.2/css/buttons.bootstrap4.min.css">
    <style>
        .dataTables_wrapper .dataTables_paginate .paginate_button { padding: 0 !important; margin: 0 !important; border: none !important; }
        .dataTables_wrapper .dataTables_length select,
        .dataTables_wrapper .dataTables_filter input {
            border: 2px solid #f3f4f6 !important; border-radius: 12px !important; padding: 10px 16px !important;
            outline: none !important; height: auto !important; font-weight: 500 !important; background: white !important;
        }
        .dark .dataTables_wrapper .dataTables_length select,
        .dark .dataTables_wrapper .dataTables_filter input { background: #242526 !important; border-color: #3a3b3c !important; color: #e4e6eb !important; }
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
        <!-- Header -->
        <div class="flex flex-col md:flex-row md:items-center md:justify-between mb-10 space-y-4 md:space-y-0">
            <div class="flex items-center space-x-6">
                <a href="{{ route('admin.dashboard') }}"
                   class="group flex items-center justify-center w-10 h-10 rounded-full bg-white dark:bg-[#242526] shadow-sm border border-gray-200 dark:border-[#3a3b3c] text-gray-500 dark:text-gray-400 hover:text-indigo-600 dark:hover:text-indigo-400 hover:border-indigo-300 transition-all duration-200">
                    <i class="fas fa-arrow-left group-hover:-translate-x-0.5 transition-transform"></i>
                </a>
                <div>
                    <h1 class="text-3xl font-extrabold text-gray-900 dark:text-white tracking-tight">{{ __('Grading Scheme Management') }}</h1>
                    <p class="text-sm text-gray-500 dark:text-gray-400 font-medium mt-1 px-1">{{ __('Define how scores are converted to results') }}</p>
                </div>
            </div>

            <div>
                <a href="{{ route('admin.grading-schemes.create') }}"
                   class="inline-flex items-center px-6 py-3 border border-transparent text-base font-bold rounded-2xl shadow-lg text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none transition-all duration-200 transform hover:-translate-y-0.5 active:scale-95">
                    <i class="fas fa-award mr-2 opacity-75"></i>
                    {{ __('New Grading Scheme') }}
                </a>
            </div>
        </div>

        @if (session('status'))
        <div id="statusAlert" class="fixed bottom-4 right-4 z-50 flex items-center p-4 bg-emerald-50 dark:bg-emerald-900/30 border-l-4 border-emerald-500 rounded-r-2xl shadow-xl opacity-100" role="alert">
            <i class="fas fa-check-circle text-emerald-500 mr-3 text-xl"></i>
            <p class="text-sm font-bold text-emerald-800 dark:text-emerald-400">{{ session('status') }}</p>
        </div>
        @endif

        <!-- Main Card -->
        <div class="bg-white dark:bg-[#242526] rounded-[2.5rem] shadow-xl shadow-gray-200/50 dark:shadow-none border border-gray-100 dark:border-[#3a3b3c] overflow-hidden transition-all duration-300">
            <div class="p-6 sm:p-8">
                <!-- Filters -->
                <div class="mb-8 p-6 bg-gray-50/50 dark:bg-[#18191a]/30 rounded-[2rem] border border-gray-100 dark:border-[#3a3b3c]/50">
                    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
                        <div class="flex items-center space-x-3">
                            <div class="w-10 h-10 rounded-xl bg-white dark:bg-[#242526] shadow-sm border border-gray-100 dark:border-[#3a3b3c] flex items-center justify-center text-indigo-500">
                                <i class="fas fa-filter text-sm"></i>
                            </div>
                            <div>
                                <h3 class="text-sm font-bold text-gray-800 dark:text-gray-200 uppercase tracking-wider">{{ __('Quick Filters') }}</h3>
                            </div>
                        </div>

                        <div class="flex flex-wrap items-center gap-3">
                            <select id="statusFilter" class="appearance-none block w-full md:w-48 pl-4 pr-10 py-2.5 bg-white dark:bg-[#242526] border-2 border-gray-100 dark:border-[#3a3b3c] rounded-xl text-xs font-bold text-gray-600 dark:text-gray-400 focus:outline-none focus:border-indigo-500 transition-all cursor-pointer">
                                <option value="">{{ __('All Status') }}</option>
                                <option value="1">{{ __('Active') }}</option>
                                <option value="2">{{ __('Not Active') }}</option>
                            </select>
                        </div>
                    </div>
                </div>

                <div class="overflow-x-auto lg:overflow-visible">
                    <table id="gradingSchemesTable" class="w-full text-left border-collapse whitespace-nowrap lg:whitespace-normal">
                        <thead>
                            <tr class="bg-gray-50/50 dark:bg-[#18191a]/30 border-b border-gray-100 dark:border-[#3a3b3c]/50">
                                <th class="px-6 py-4 text-xs font-bold text-gray-400 dark:text-gray-500 uppercase tracking-widest">{{ __('Scheme name') }}</th>
                                <th class="px-6 py-4 text-xs font-bold text-gray-400 dark:text-gray-500 uppercase tracking-widest">{{ __('Result type') }}</th>
                                <th class="px-6 py-4 text-xs font-bold text-gray-400 dark:text-gray-500 uppercase tracking-widest">{{ __('Details') }}</th>
                                <th class="px-6 py-4 text-xs font-bold text-gray-400 dark:text-gray-500 uppercase tracking-widest">{{ __('Status') }}</th>
                                <th class="px-6 py-4 text-xs font-bold text-gray-400 dark:text-gray-500 uppercase tracking-widest text-right">{{ __('Action') }}</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-50 dark:divide-[#3a3b3c]/50 text-gray-600 dark:text-gray-400 text-sm"></tbody>
                    </table>
                </div>
            </div>

            <div class="px-8 py-5 bg-gray-50/50 dark:bg-[#18191a]/30 border-t border-gray-100 dark:border-[#3a3b3c]/50 flex items-center justify-between text-xs font-bold text-gray-400 dark:text-gray-500 uppercase tracking-widest">
                <span>{{ __('Grading Scheme Management') }}</span>
                <span class="flex items-center"><i class="fas fa-award mr-2"></i> {{ __('Administrative Control') }}</span>
            </div>
        </div>
    </div>
</div>

<!-- Delete Modal -->
<div id="deleteModal" class="fixed inset-0 z-50 overflow-y-auto hidden" role="dialog" aria-modal="true">
    <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
        <div class="fixed inset-0 bg-gray-500/75 dark:bg-black/75 backdrop-blur-sm transition-opacity" onclick="closeModal()"></div>
        <span class="hidden sm:inline-block sm:align-middle sm:h-screen">&#8203;</span>
        <div class="inline-block align-bottom bg-white dark:bg-[#242526] rounded-[2.5rem] text-left overflow-hidden shadow-2xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full border border-gray-100 dark:border-[#3a3b3c]">
            <div class="bg-white dark:bg-[#242526] px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
                <div class="sm:flex sm:items-start">
                    <div class="mx-auto flex-shrink-0 flex items-center justify-center h-16 w-16 rounded-2xl bg-rose-50 dark:bg-rose-900/30 sm:mx-0 sm:h-12 sm:w-12 border border-rose-100 dark:border-rose-900/50 shadow-inner">
                        <i class="fas fa-exclamation-triangle text-rose-600 text-xl"></i>
                    </div>
                    <div class="mt-3 text-center sm:mt-0 sm:ml-6 sm:text-left">
                        <h3 class="text-xl leading-6 font-extrabold text-gray-900 dark:text-white tracking-tight">{{ __('Confirm Deletion') }}</h3>
                        <div class="mt-2 text-sm text-gray-500 dark:text-gray-400 leading-relaxed">
                            {{ __('Are you sure you want to permanently remove') }} <span id="schemeNameToDelete" class="font-bold text-gray-900 dark:text-white px-1.5 py-0.5 bg-gray-100 dark:bg-zinc-700 rounded-lg"></span>? {{ __('This action cannot be undone.') }}
                        </div>
                    </div>
                </div>
            </div>
            <div class="bg-gray-50/50 dark:bg-[#18191a]/30 px-4 py-6 sm:px-8 sm:flex sm:flex-row-reverse space-y-3 sm:space-y-0 mt-8">
                <form id="deleteForm" method="POST" class="sm:ml-3">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="w-full inline-flex justify-center rounded-2xl border border-transparent shadow-lg px-8 py-3 bg-rose-600 text-base font-bold text-white hover:bg-rose-700 focus:outline-none transition-all duration-200 sm:w-auto transform active:scale-95 uppercase tracking-wider text-xs">{{ __('Confirm Delete') }}</button>
                </form>
                <button type="button" onclick="closeModal()" class="w-full inline-flex justify-center rounded-2xl border-2 border-gray-100 dark:border-[#3a3b3c] px-8 py-3 bg-white dark:bg-[#242526] text-base font-bold text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-[#3a3b3c] focus:outline-none transition-all duration-200 sm:w-auto transform active:scale-95 uppercase tracking-wider text-xs">{{ __('Cancel') }}</button>
            </div>
        </div>
    </div>
</div>

@push('scripts')
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.datatables.net/1.11.5/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.11.5/js/dataTables.bootstrap4.min.js"></script>
    <script src="https://cdn.datatables.net/responsive/2.2.9/js/dataTables.responsive.min.js"></script>
    <script src="https://cdn.datatables.net/responsive/2.2.9/js/responsive.bootstrap4.min.js"></script>

    <script>
        const LANG_SEARCH_SCHEMES = {!! json_encode(__('Search grading schemes...')) !!};
        const LANG_SHOW = {!! json_encode(__('Show')) !!};

        $(document).ready(function() {
            const alert = document.getElementById('statusAlert');
            if (alert) {
                setTimeout(() => {
                    alert.classList.add('opacity-0', 'translate-y-4');
                    setTimeout(() => alert.remove(), 500);
                }, 2500);
            }

            $('#gradingSchemesTable').DataTable({
                processing: true,
                serverSide: true,
                responsive: true,
                dom: '<"grid grid-cols-1 lg:grid-cols-2 gap-4 mb-6"lf>rt<"flex flex-col md:flex-row md:items-center md:justify-between mt-6"ip>',
                ajax: {
                    url: "{{ route('admin.grading-schemes.data') }}",
                    data: function (d) {
                        d.status = $('#statusFilter').val();
                    }
                },
                columns: [
                    { data: 'name', name: 'name', className: 'px-6 py-4 font-bold' },
                    { data: 'result_type_badge', name: 'result_type', className: 'px-6 py-4' },
                    { data: 'details_count', name: 'details_count', orderable: false, searchable: false, className: 'px-6 py-4' },
                    { data: 'status', name: 'status', className: 'px-6 py-4' },
                    { data: 'action', name: 'action', orderable: false, searchable: false, className: 'px-6 py-4 text-right' }
                ],
                language: {
                    search: "",
                    searchPlaceholder: LANG_SEARCH_SCHEMES,
                    lengthMenu: LANG_SHOW + " _MENU_",
                    paginate: {
                        previous: '<i class="fas fa-chevron-left"></i>',
                        next: '<i class="fas fa-chevron-right"></i>'
                    }
                },
                drawCallback: function() {
                    $('.dataTables_filter input').addClass('dark:bg-[#242526] dark:border-[#3a3b3c] dark:text-white');
                    $('.dataTables_length select').addClass('dark:bg-[#242526] dark:border-[#3a3b3c] dark:text-white');
                }
            });

            $('#statusFilter').on('change', function() {
                $('#gradingSchemesTable').DataTable().draw();
            });
        });

        function confirmDelete(id, name) {
            $('#schemeNameToDelete').text(name);
            $('#deleteForm').attr('action', '/admin/grading-schemes/' + id);
            $('#deleteModal').removeClass('hidden');
            $('body').css('overflow', 'hidden');
        }

        function closeModal() {
            $('#deleteModal').addClass('hidden');
            $('body').css('overflow', 'auto');
        }
    </script>
@endpush
@endsection
