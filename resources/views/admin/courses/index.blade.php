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
<div class="min-h-screen bg-gray-50 dark:bg-[#18191a] py-8 px-4 sm:px-6 lg:px-8 transition-colors duration-300">
    <div class="max-w-7xl mx-auto">
        <!-- Header Section -->
        <div class="flex flex-col md:flex-row md:items-center md:justify-between mb-10 space-y-4 md:space-y-0">
            <div class="flex items-center space-x-6">
                <a href="{{ route('home') }}" 
                   class="group flex items-center justify-center w-10 h-10 rounded-full bg-white dark:bg-[#242526] shadow-sm border border-gray-200 dark:border-[#3a3b3c] text-gray-500 dark:text-gray-400 hover:text-indigo-600 dark:hover:text-indigo-400 hover:border-indigo-300 transition-all duration-200">
                    <i class="fas fa-arrow-left group-hover:-translate-x-0.5 transition-transform"></i>
                </a> 
                <div>
                    <h1 class="text-3xl font-extrabold text-gray-900 dark:text-white tracking-tight">Course Management</h1>
                    <p class="text-sm text-gray-500 dark:text-gray-400 font-medium mt-1 px-1">Manage system courses</p>
                </div>
            </div>
            
            <div>
                <a href="{{ route('admin.courses.create') }}" 
                   class="inline-flex items-center px-6 py-3 border border-transparent text-base font-bold rounded-2xl shadow-lg text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 transition-all duration-200 transform hover:-translate-y-0.5 active:scale-95">
                    <i class="fas fa-plus mr-2 opacity-75"></i>
                    New Course
                </a>
            </div>
        </div>

        @if (session('status'))
            <div id="statusAlert" class="fixed bottom-4 right-4 z-50 flex items-center p-4 bg-emerald-50 dark:bg-emerald-900/30 border-l-4 border-emerald-500 rounded-r-2xl shadow-xl transform transition-all duration-500 opacity-100" role="alert">
                <i class="fas fa-check-circle text-emerald-500 mr-3 text-xl"></i>
                <p class="text-sm font-bold text-emerald-800 dark:text-emerald-400">{{ session('status') }}</p>
            </div>
        @endif

        @if (session('error'))
            <div id="errorAlert" class="fixed bottom-4 right-4 z-50 flex items-center p-4 bg-rose-50 dark:bg-rose-900/30 border-l-4 border-rose-500 rounded-r-2xl shadow-xl transform transition-all duration-500 opacity-100" role="alert">
                <i class="fas fa-exclamation-circle text-rose-500 mr-3 text-xl"></i>
                <p class="text-sm font-bold text-rose-800 dark:text-rose-400">{{ session('error') }}</p>
            </div>
        @endif

        <!-- Main Content Card -->
        <div class="bg-white dark:bg-[#242526] rounded-[2.5rem] shadow-xl shadow-gray-200/50 dark:shadow-none border border-gray-100 dark:border-[#3a3b3c] overflow-hidden transition-all duration-300 transform transition-all">
            <div class="p-6 sm:p-8">
                <!-- Advanced Filters -->
                <div class="mb-8 p-6 bg-gray-50/50 dark:bg-[#18191a]/30 rounded-[2rem] border border-gray-100 dark:border-[#3a3b3c]/50">
                    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
                        <div class="flex items-center space-x-3">
                            <div class="w-10 h-10 rounded-xl bg-white dark:bg-[#242526] shadow-sm border border-gray-100 dark:border-[#3a3b3c] flex items-center justify-center text-indigo-500">
                                <i class="fas fa-filter text-sm"></i>
                            </div>
                            <div>
                                <h3 class="text-sm font-bold text-gray-800 dark:text-gray-200 uppercase tracking-wider">Quick Filters</h3>
                                <p class="text-[10px] text-gray-400 font-bold uppercase tracking-tight">Refine course list</p>
                            </div>
                        </div>

                        <div class="flex flex-wrap items-center gap-3">
                            <!-- Status Filter -->
                            <div class="relative group">
                                <select id="statusFilter" class="appearance-none block w-full md:w-48 pl-4 pr-10 py-2.5 bg-white dark:bg-[#242526] border-2 border-gray-100 dark:border-[#3a3b3c] rounded-xl text-xs font-bold text-gray-600 dark:text-gray-400 focus:outline-none focus:ring-0 focus:border-indigo-500 transition-all cursor-pointer">
                                    <option value="">All Status</option>
                                    <option value="1">Active</option>
                                    <option value="2">Not Active</option>
                                </select>
                                <div class="absolute inset-y-0 right-0 pr-3 flex items-center pointer-events-none text-gray-400 group-hover:text-indigo-500 transition-colors">
                                    <i class="fas fa-chevron-down text-[10px]"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="overflow-x-auto lg:overflow-visible">
                    <table id="coursesTable" class="w-full text-left border-collapse whitespace-nowrap lg:whitespace-normal">
                        <thead>
                            <tr class="bg-gray-50/50 dark:bg-[#18191a]/30 border-b border-gray-100 dark:border-[#3a3b3c]/50">
                                <th class="px-6 py-4 text-xs font-bold text-gray-400 dark:text-gray-500 uppercase tracking-widest first:rounded-tl-2xl">Course Name</th>
                                <th class="px-6 py-4 text-xs font-bold text-gray-400 dark:text-gray-500 uppercase tracking-widest">Grade</th>
                                <th class="px-6 py-4 text-xs font-bold text-gray-400 dark:text-gray-500 uppercase tracking-widest">Semester</th>
                                <th class="px-6 py-4 text-xs font-bold text-gray-400 dark:text-gray-500 uppercase tracking-widest text-center">Status</th>
                                <th class="px-6 py-4 text-xs font-bold text-gray-400 dark:text-gray-500 uppercase tracking-widest text-right last:rounded-tr-2xl">Action</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-50 dark:divide-[#3a3b3c]/50 text-gray-600 dark:text-gray-400 text-sm">
                            <!-- DataTables will fill this -->
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Contextual Footer -->
            <div class="px-8 py-5 bg-gray-50/50 dark:bg-[#18191a]/30 border-t border-gray-100 dark:border-[#3a3b3c]/50 flex items-center justify-between text-xs font-bold text-gray-400 dark:text-gray-500 uppercase tracking-widest">
                <span>Course Management System</span>
                <span class="flex items-center">
                    <i class="fas fa-layer-group mr-2"></i> Administrative Control
                </span>
            </div>
        </div>
    </div>
</div>

<!-- Delete Modal -->
<div id="deleteModal" class="fixed inset-0 z-50 overflow-y-auto hidden" aria-labelledby="modal-title" role="dialog" aria-modal="true">
    <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
        <div class="fixed inset-0 bg-gray-500/75 dark:bg-black/75 backdrop-blur-sm transition-opacity" aria-hidden="true" onclick="closeModal()"></div>
        <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>
        <div class="inline-block align-bottom bg-white dark:bg-[#242526] rounded-[2.5rem] text-left overflow-hidden shadow-2xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full border border-gray-100 dark:border-[#3a3b3c] animate-modal-pop">
            <div class="bg-white dark:bg-[#242526] px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
                <div class="sm:flex sm:items-start">
                    <div class="mx-auto flex-shrink-0 flex items-center justify-center h-16 w-16 rounded-2xl bg-rose-50 dark:bg-rose-900/30 sm:mx-0 sm:h-12 sm:w-12 border border-rose-100 dark:border-rose-900/50 shadow-inner">
                        <i class="fas fa-exclamation-triangle text-rose-600 text-xl"></i>
                    </div>
                    <div class="mt-3 text-center sm:mt-0 sm:ml-6 sm:text-left">
                        <h3 class="text-xl leading-6 font-extrabold text-gray-900 dark:text-white tracking-tight" id="modal-title">Confirm Deletion</h3>
                        <div class="mt-2 text-sm text-gray-500 dark:text-gray-400 leading-relaxed">
                            Are you sure you want to permanently remove <span id="itemNameToDelete" class="font-bold text-gray-900 dark:text-white px-1.5 py-0.5 bg-gray-100 dark:bg-zinc-700 rounded-lg"></span>? This action cannot be undone.
                        </div>
                    </div>
                </div>
            </div>
            <div class="bg-gray-50/50 dark:bg-[#18191a]/30 px-4 py-6 sm:px-8 sm:flex sm:flex-row-reverse space-y-3 sm:space-y-0 mt-8">
                <form id="deleteForm" method="POST" class="sm:ml-3">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="w-full inline-flex justify-center rounded-2xl border border-transparent shadow-lg shadow-rose-200/50 dark:shadow-none px-8 py-3 bg-rose-600 text-base font-bold text-white hover:bg-rose-700 focus:outline-none transition-all duration-200 sm:w-auto transform active:scale-95 uppercase tracking-wider text-xs">Confirm Delete</button>
                </form>
                <button type="button" onclick="closeModal()" class="w-full inline-flex justify-center rounded-2xl border-2 border-gray-100 dark:border-[#3a3b3c] px-8 py-3 bg-white dark:bg-[#242526] text-base font-bold text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-[#3a3b3c] focus:outline-none transition-all duration-200 sm:w-auto transform active:scale-95 uppercase tracking-wider text-xs">Cancel</button>
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
    <script src="https://cdn.datatables.net/buttons/2.2.2/js/dataTables.buttons.min.js"></script>
    <script src="https://cdn.datatables.net/buttons/2.2.2/js/buttons.bootstrap4.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jszip/3.1.3/jszip.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.1.53/pdfmake.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.1.53/vfs_fonts.js"></script>
    <script src="https://cdn.datatables.net/buttons/2.2.2/js/buttons.html5.min.js"></script>
    <script src="https://cdn.datatables.net/buttons/2.2.2/js/buttons.print.min.js"></script>

    <script>
        $(document).ready(function() {
            // Auto-dismiss alert
            const alerts = ['statusAlert', 'errorAlert'];
            alerts.forEach(alertId => {
                const alert = document.getElementById(alertId);
                if (alert) {
                    setTimeout(() => {
                        alert.classList.remove('opacity-100');
                        alert.classList.add('opacity-0', 'translate-y-4');
                        setTimeout(() => alert.remove(), 500);
                    }, 2500);
                }
            });

            $('#coursesTable').DataTable({
                processing: true,
                serverSide: true,
                responsive: true,
                dom: '<"flex flex-wrap items-center justify-between mb-6"B><"grid grid-cols-1 lg:grid-cols-2 gap-4 mb-6"lf>rt<"flex flex-col md:flex-row md:items-center md:justify-between mt-6"ip>',
                buttons: [
                    { extend: 'copy', className: 'btn btn-sm' },
                    { extend: 'csv', className: 'btn btn-sm' },
                    { extend: 'excel', className: 'btn btn-sm' },
                    { extend: 'pdf', className: 'btn btn-sm' },
                    { extend: 'print', className: 'btn btn-sm' }
                ],
                ajax: {
                    url: "{{ route('admin.courses.data') }}",
                    data: function (d) {
                        d.status = $('#statusFilter').val();
                    }
                },
                columns: [
                    { data: 'name', name: 'name', className: 'px-6 py-4 font-bold text-gray-800 dark:text-gray-200' },
                    { data: 'grade_name', name: 'grade.name_th', orderable: false, searchable: false, className: 'px-6 py-4' },
                    { data: 'semester_name', name: 'semester.semester_number', orderable: false, searchable: false, className: 'px-6 py-4' },
                    { data: 'status', name: 'status', orderable: true, className: 'px-6 py-4 text-center' },
                    { data: 'action', name: 'action', orderable: false, searchable: false, className: 'px-6 py-4 text-right' }
                ],
                language: {
                    search: "",
                    searchPlaceholder: "Search courses...",
                    lengthMenu: "Show _MENU_",
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

            // Handle Filter Change
            $('#statusFilter').on('change', function() {
                $('#coursesTable').DataTable().draw();
            });
        });

        function confirmDelete(id, name) {
            $('#itemNameToDelete').text(name);
            $('#deleteForm').attr('action', '/admin/courses/' + id);
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
