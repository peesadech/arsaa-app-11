@extends('layouts.app')

@push('styles')
    <link rel="stylesheet" href="https://cdn.datatables.net/1.11.5/css/dataTables.bootstrap4.min.css">
    <link rel="stylesheet" href="https://cdn.datatables.net/responsive/2.2.9/css/responsive.bootstrap4.min.css">
    <link rel="stylesheet" href="https://cdn.datatables.net/buttons/2.2.2/css/buttons.bootstrap4.min.css">
    <style>
        .dataTables_wrapper .dataTables_paginate .paginate_button {
            padding: 0 !important;
            margin: 0 !important;
            border: none !important;
        }
        .dataTables_wrapper .dataTables_length select,
        .dataTables_wrapper .dataTables_filter input {
            border: 2px solid #f3f4f6 !important;
            border-radius: 12px !important;
            padding: 10px 16px !important;
            outline: none !important;
            height: auto !important;
            font-weight: 500 !important;
            background: white !important;
        }
        .dark .dataTables_wrapper .dataTables_length select,
        .dark .dataTables_wrapper .dataTables_filter input {
            background: #242526 !important;
            border-color: #3a3b3c !important;
            color: #e4e6eb !important;
        }
        .dataTables_wrapper .dataTables_filter input:focus {
            border-color: #6366f1 !important;
        }
        .dark .dataTables_wrapper .dataTables_filter input:focus {
            border-color: #818cf8 !important;
        }
        .dataTables_wrapper .dataTables_length {
            width: 100% !important;
            margin-bottom: 0 !important;
        }
        .dataTables_wrapper .dataTables_length label {
            width: 100% !important;
            display: flex !important;
            align-items: center !important;
            font-size: 0.875rem !important;
            color: #6b7280 !important;
            font-weight: 600 !important;
        }
        .dataTables_wrapper .dataTables_length select {
            flex: 1 !important;
            margin: 0 0.75rem !important;
            max-width: 120px !important;
            min-height: 46px !important;
        }
        .dataTables_wrapper .dataTables_filter {
            width: 100% !important;
        }
        .dataTables_wrapper .dataTables_filter label {
            width: 100% !important;
            display: flex !important;
            align-items: center !important;
        }
        .dataTables_wrapper .dataTables_filter input {
            flex: 1 !important;
            margin-left: 0 !important;
        }
        table.dataTable thead th {
            border-bottom: 1px solid #f3f4f6 !important;
        }
        table.dataTable.no-footer {
            border-bottom: none !important;
        }
        /* Custom Pagination Styling */
        .pagination .page-item.active .page-link {
            background-color: #6366f1 !important;
            border-color: #6366f1 !important;
            color: white !important;
            box-shadow: 0 10px 15px -3px rgba(99, 102, 241, 0.3) !important;
        }
        .pagination .page-link {
            border-radius: 12px !important;
            margin: 0 4px !important;
            font-weight: 700 !important;
            color: #4b5563 !important;
            border: 2px solid #f3f4f6 !important;
            background: white;
        }
        .dark .pagination .page-link {
            background: #242526;
            border-color: #3a3b3c !important;
            color: #b0b3b8 !important;
        }
        table.dataTable tbody tr {
            background-color: transparent !important;
        }
        /* Style DataTables Buttons */
        .dt-buttons.btn-group {
            margin-bottom: 1.5rem;
            gap: 0.5rem;
        }
        .dt-buttons .btn {
            border-radius: 12px !important;
            font-size: 0.75rem !important;
            font-weight: 800 !important;
            text-transform: uppercase !important;
            letter-spacing: 0.05em !important;
            padding: 0.6rem 1.2rem !important;
            border: 1px solid #f3f4f6 !important;
            background: white !important;
            color: #6b7280 !important;
            transition: all 0.2s !important;
            box-shadow: none !important;
        }
        .dark .dt-buttons .btn {
            background: #242526 !important;
            border-color: #3a3b3c !important;
            color: #b0b3b8 !important;
        }
        .dt-buttons .btn:hover {
            border-color: #6366f1 !important;
            color: #6366f1 !important;
            background: #f5f3ff !important;
            transform: translateY(-1px);
        }
        .dark .dt-buttons .btn:hover {
            background: #3a3b3c !important;
            border-color: #818cf8 !important;
            color: #818cf8 !important;
        }
        .dt-buttons .btn i {
            margin-right: 0.5rem;
        }
    </style>
@endpush

@section('content')
<div class="min-h-screen bg-gray-50 py-8 px-4 sm:px-6 lg:px-8">
    <div class="max-w-6xl mx-auto">
        <!-- Header Section -->
        <div class="flex flex-col md:flex-row md:items-center md:justify-between mb-10 space-y-4 md:space-y-0">
           <a href="{{ route('home') }}" 
               class="group flex items-center justify-center w-10 h-10 rounded-full bg-white shadow-sm border border-gray-200 text-gray-500 hover:text-indigo-600 hover:border-indigo-300 transition-all duration-200">
                <i class="fas fa-arrow-left group-hover:-translate-x-0.5 transition-transform"></i>
            </a> 
            <div>
                <h1 class="text-3xl font-extrabold text-gray-900 tracking-tight">System Permissions</h1>
                <p class="text-sm text-gray-500 font-medium px-1 mt-1">Manage and organize granular access controls</p>
            </div>
            <div>
                <a href="{{ route('admin.permissions.create') }}" 
                   class="inline-flex items-center px-6 py-3 border border-transparent text-base font-bold rounded-2xl shadow-lg text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 transition-all duration-200 transform hover:-translate-y-0.5 active:scale-95">
                    <i class="fas fa-plus-circle mr-2 opacity-75"></i>
                    New Permission
                </a>
            </div>
        </div>

        @if (session('status'))
            <div id="statusAlert" class="fixed bottom-4 right-4 z-50 flex items-center p-4 bg-emerald-50 border-l-4 border-emerald-500 rounded-r-2xl shadow-xl transform transition-all duration-500 opacity-100" role="alert">
                <i class="fas fa-check-circle text-emerald-500 mr-3 text-xl"></i>
                <p class="text-sm font-bold text-emerald-800">{{ session('status') }}</p>
            </div>
        @endif

        <!-- Main Content Card -->
        <div class="bg-white rounded-3xl shadow-xl shadow-gray-200/50 border border-gray-100 overflow-hidden transform transition-all">
            <div class="p-4 sm:p-6 lg:p-8">
                <div class="overflow-x-auto lg:overflow-visible">
                    <table id="permissionsTable" class="w-full text-left border-collapse whitespace-nowrap lg:whitespace-normal">
                        <thead>
                            <tr class="bg-gray-50/50 border-b border-gray-100">
                                <th class="px-4 sm:px-6 lg:px-8 py-2 text-xs font-bold text-gray-400 uppercase tracking-widest">ID</th>
                                <th class="px-4 sm:px-6 lg:px-8 py-2 text-xs font-bold text-gray-400 uppercase tracking-widest">Permission Name</th>
                                <th class="px-4 sm:px-6 lg:px-8 py-2 text-xs font-bold text-gray-400 uppercase tracking-widest">Guard</th>
                                <th class="px-4 sm:px-6 lg:px-8 py-2 text-xs font-bold text-gray-400 uppercase tracking-widest text-right">Action</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-50 text-gray-600 text-sm">
                            <!-- DataTables will populate this -->
                        </tbody>
                    </table>
                </div>
            </div>
            
            <!-- Contextual Footer -->
            <div class="px-8 py-5 bg-gray-50/50 border-t border-gray-100 flex items-center justify-between text-xs font-bold text-gray-400 uppercase tracking-widest">
                <span>Access Control System</span>
                <span class="flex items-center">
                    <i class="fas fa-shield-alt mr-2"></i> Authorized Access Only
                </span>
            </div>
        </div>
    </div>
</div>

<!-- Delete Modal -->
<div id="deleteModal" class="fixed inset-0 z-50 overflow-y-auto hidden" aria-labelledby="modal-title" role="dialog" aria-modal="true">
    <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
        <div class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity" aria-hidden="true" onclick="closeModal()"></div>
        <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>
        <div class="inline-block align-bottom bg-white rounded-3xl text-left overflow-hidden shadow-2xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full border border-gray-100">
            <div class="bg-white px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
                <div class="sm:flex sm:items-start">
                    <div class="mx-auto flex-shrink-0 flex items-center justify-center h-16 w-16 rounded-2xl bg-rose-50 sm:mx-0 sm:h-12 sm:w-12 border border-rose-100 shadow-inner">
                        <i class="fas fa-exclamation-triangle text-rose-600 text-xl"></i>
                    </div>
                    <div class="mt-3 text-center sm:mt-0 sm:ml-6 sm:text-left">
                        <h3 class="text-xl leading-6 font-extrabold text-gray-900 tracking-tight" id="modal-title">Confirm Deletion</h3>
                        <div class="mt-2 text-sm text-gray-500 leading-relaxed">
                            Are you sure you want to permanently remove <span id="deleteItemName" class="font-bold text-gray-900 px-1.5 py-0.5 bg-gray-100 rounded-lg"></span>? This action cannot be undone.
                        </div>
                    </div>
                </div>
            </div>
            <div class="bg-gray-50/50 px-4 py-6 sm:px-8 sm:flex sm:flex-row-reverse space-y-3 sm:space-y-0">
                <button type="button" id="confirmDeleteBtn" class="w-full inline-flex justify-center rounded-2xl border border-transparent shadow-lg shadow-rose-200/50 px-8 py-3 bg-rose-600 text-base font-bold text-white hover:bg-rose-700 focus:outline-none transition-all duration-200 sm:ml-3 sm:w-auto transform active:scale-95">Delete</button>
                <button type="button" onclick="closeModal()" class="w-full inline-flex justify-center rounded-2xl border-2 border-gray-100 px-8 py-3 bg-white text-base font-bold text-gray-700 hover:bg-gray-50 hover:border-gray-200 focus:outline-none transition-all duration-200 sm:w-auto transform active:scale-95">Cancel</button>
            </div>
        </div>
    </div>
</div>

@endsection

@push('scripts')
<script src="https://cdn.datatables.net/1.11.5/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.11.5/js/dataTables.bootstrap4.min.js"></script>
<script src="https://cdn.datatables.net/buttons/2.2.2/js/dataTables.buttons.min.js"></script>
<script src="https://cdn.datatables.net/buttons/2.2.2/js/buttons.bootstrap4.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jszip/3.1.3/jszip.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.1.53/pdfmake.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.1.53/vfs_fonts.js"></script>

<script>
    $(function() {
        // Auto-dismiss alert
        const alert = document.getElementById('statusAlert');
        if (alert) {
            setTimeout(() => {
                alert.classList.remove('opacity-100');
                alert.classList.add('opacity-0', 'translate-y-4');
                setTimeout(() => {
                    alert.remove();
                }, 500);
            }, 2000); // 2000ms as per user preference in permission-types logic
        }

        let currentDeleteId = null;

        var table = $('#permissionsTable').DataTable({
            processing: true,
            serverSide: true,
            ajax: {
                url: "{{ route('admin.permissions.data') }}",
                error: function (xhr, error, code) {
                    console.log("DataTable Error:", xhr.responseText);
                }
            },
            dom: '<"flex flex-wrap items-center justify-between mb-6"B><"grid grid-cols-1 lg:grid-cols-2 gap-4 mb-6"lf>rt<"flex flex-col md:flex-row md:items-center md:justify-between mt-6"ip>',
            lengthMenu: [[10, 25, 50, -1], [10, 25, 50, "All"]],
            columns: [
                { 
                    data: 'id', 
                    name: 'id',
                    render: function(data) {
                        return '<span class="inline-flex items-center justify-center w-8 h-8 rounded-lg bg-gray-100 dark:bg-zinc-700 text-gray-500 dark:text-gray-400 text-xs font-bold">#' + data + '</span>';
                    }
                },
                { 
                    data: 'name', 
                    name: 'name',
                    render: function(data) {
                        return '<div class="flex items-center space-x-3">' +
                               '<div class="w-10 h-10 rounded-xl bg-indigo-50 dark:bg-indigo-900/30 flex items-center justify-center text-indigo-600 dark:text-indigo-400 border border-indigo-50 dark:border-indigo-900/50">' + 
                               '<i class="fas fa-shield-alt"></i>' + 
                               '</div>' +
                               '<span class="text-base font-bold text-gray-800 dark:text-gray-200 tracking-tight">' + data + '</span>' +
                               '</div>';
                    }
                },
                { 
                    data: 'guard_name', 
                    name: 'guard_name',
                    render: function(data) {
                        return '<span class="px-2 py-1 rounded-md bg-gray-100 dark:bg-zinc-700 text-gray-500 dark:text-gray-400 text-[10px] font-black uppercase tracking-widest">' + data + '</span>';
                    }
                },
                { 
                    data: 'action', 
                    name: 'action', 
                    orderable: false, 
                    searchable: false,
                    className: 'text-right'
                }
            ],
            language: {
                search: "",
                searchPlaceholder: "Search permissions...",
                lengthMenu: "Show _MENU_",
                paginate: {
                    previous: '<i class="fas fa-chevron-left"></i>',
                    next: '<i class="fas fa-chevron-right"></i>'
                }
            }
        });

        window.confirmDelete = function(id, name) {
            currentDeleteId = id;
            $('#deleteItemName').text(name);
            $('#deleteModal').removeClass('hidden');
            $('body').css('overflow', 'hidden');
        };

        window.closeModal = function() {
            $('#deleteModal').addClass('hidden');
            $('body').css('overflow', 'auto');
        };

        $('#confirmDeleteBtn').on('click', function() {
            if (currentDeleteId) {
                let form = $('<form>', {
                    "method": "POST",
                    "action": "/admin/permissions/" + currentDeleteId
                });
                form.append($('<input>', { "name": "_token", "value": "{{ csrf_token() }}", "type": "hidden" }));
                form.append($('<input>', { "name": "_method", "value": "DELETE", "type": "hidden" }));
                form.appendTo('body').submit();
            }
        });
    });
</script>
@endpush
