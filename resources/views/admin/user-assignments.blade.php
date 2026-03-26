@extends('layouts.app')

@push('styles')
    <link rel="stylesheet" href="https://cdn.datatables.net/1.11.5/css/dataTables.bootstrap4.min.css">
    <link rel="stylesheet" href="https://cdn.datatables.net/responsive/2.2.9/css/responsive.bootstrap4.min.css">
    <link rel="stylesheet" href="https://cdn.datatables.net/buttons/2.2.2/css/buttons.bootstrap4.min.css">
    <style>
        .dataTables_wrapper .dataTables_paginate .paginate_button { padding: 0 !important; margin: 0 !important; border: none !important; }
        .dataTables_wrapper .dataTables_length select,
        .dataTables_wrapper .dataTables_filter input { border: 2px solid #f3f4f6 !important; border-radius: 12px !important; padding: 10px 16px !important; outline: none !important; height: auto !important; font-weight: 500 !important; background: white !important; }
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
        .pagination .page-item.active .page-link { background-color: #6366f1 !important; border-color: #6366f1 !important; color: white !important; box-shadow: 0 10px 15px -3px rgba(99,102,241,0.3) !important; }
        .pagination .page-link { border-radius: 12px !important; margin: 0 4px !important; font-weight: 700 !important; color: #4b5563 !important; border: 2px solid #f3f4f6 !important; background: white; }
        .dark .pagination .page-link { background: #242526; border-color: #3a3b3c !important; color: #b0b3b8 !important; }
        table.dataTable tbody tr { background-color: transparent !important; }
        .dt-buttons.btn-group { margin-bottom: 1.5rem; gap: 0.5rem; }
        .dt-buttons .btn { border-radius: 12px !important; font-size: 0.75rem !important; font-weight: 800 !important; text-transform: uppercase !important; letter-spacing: 0.05em !important; padding: 0.6rem 1.2rem !important; border: 1px solid #f3f4f6 !important; background: white !important; color: #6b7280 !important; transition: all 0.2s !important; box-shadow: none !important; }
        .dark .dt-buttons .btn { background: #242526 !important; border-color: #3a3b3c !important; color: #b0b3b8 !important; }
        .dt-buttons .btn:hover { border-color: #6366f1 !important; color: #6366f1 !important; background: #f5f3ff !important; transform: translateY(-1px); }
        .dark .dt-buttons .btn:hover { background: #3a3b3c !important; border-color: #818cf8 !important; color: #818cf8 !important; }
        .user-role-select { border: 2px solid #f3f4f6; border-radius: 12px; padding: 6px 10px; font-size: 0.75rem; font-weight: 600; background: #f9fafb; transition: all 0.2s; }
        .user-role-select:focus { outline: none; border-color: #6366f1; background: white; box-shadow: 0 0 0 3px rgba(99,102,241,0.15); }
        .dark .user-role-select { background: #18191a; border-color: #3a3b3c; color: #e4e6eb; }
        .dark .user-role-select:focus { border-color: #818cf8; background: #3a3b3c; }
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
                    <h1 class="text-3xl font-extrabold text-gray-900 dark:text-white tracking-tight">User Role Assignments</h1>
                    <p class="text-sm text-gray-500 dark:text-gray-400 font-medium mt-1 px-1">Assign and manage roles for system users</p>
                </div>
            </div>
            <a href="{{ route('admin.roles-permissions') }}"
               class="inline-flex items-center px-6 py-3 border border-transparent text-base font-bold rounded-2xl shadow-lg text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 transition-all duration-200 transform hover:-translate-y-0.5 active:scale-95">
                <i class="fas fa-user-shield mr-2 opacity-75"></i>
                Manage Roles
            </a>
        </div>

        <!-- Main Content Card -->
        <div class="bg-white dark:bg-[#242526] rounded-[2.5rem] shadow-xl shadow-gray-200/50 dark:shadow-none border border-gray-100 dark:border-[#3a3b3c] overflow-hidden transition-all duration-300">
            <div class="p-6 sm:p-8">
                <!-- Info notice -->
                <div class="mb-6 p-4 bg-amber-50 dark:bg-amber-900/20 rounded-2xl border border-amber-100 dark:border-amber-900/40 flex items-start gap-3">
                    <i class="fas fa-info-circle text-amber-500 mt-0.5 flex-shrink-0"></i>
                    <p class="text-xs text-amber-800 dark:text-amber-400 font-medium leading-relaxed">
                        Changes to user roles are saved automatically upon selection. Use Ctrl+Click (Win) or Cmd+Click (Mac) to select multiple roles.
                    </p>
                </div>
                <div class="overflow-x-auto lg:overflow-visible">
                    <table id="usersTable" class="w-full text-left border-collapse whitespace-nowrap lg:whitespace-normal">
                        <thead>
                            <tr class="bg-gray-50/50 dark:bg-[#18191a]/30 border-b border-gray-100 dark:border-[#3a3b3c]/50">
                                <th class="px-6 py-4 text-xs font-bold text-gray-400 dark:text-gray-500 uppercase tracking-widest">User</th>
                                <th class="px-6 py-4 text-xs font-bold text-gray-400 dark:text-gray-500 uppercase tracking-widest">Email</th>
                                <th class="px-6 py-4 text-xs font-bold text-gray-400 dark:text-gray-500 uppercase tracking-widest">Role Assignment</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-50 dark:divide-[#3a3b3c]/50 text-gray-600 dark:text-gray-400 text-sm">
                        </tbody>
                    </table>
                </div>
            </div>
            <div class="px-8 py-5 bg-gray-50/50 dark:bg-[#18191a]/30 border-t border-gray-100 dark:border-[#3a3b3c]/50 flex items-center justify-between text-xs font-bold text-gray-400 dark:text-gray-500 uppercase tracking-widest">
                <span>User Assignment System</span>
                <span class="flex items-center"><i class="fas fa-user-tag mr-2"></i> Role Control</span>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
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
    $(function() {
        var usersTable = $('#usersTable').DataTable({
            processing: true,
            serverSide: true,
            responsive: true,
            dom: '<"flex flex-wrap items-center justify-between mb-6"B><"grid grid-cols-1 lg:grid-cols-2 gap-4 mb-6"lf>rt<"flex flex-col md:flex-row md:items-center md:justify-between mt-6"ip>',
            buttons: [
                { extend: 'copy',  className: 'btn btn-sm' },
                { extend: 'csv',   className: 'btn btn-sm' },
                { extend: 'excel', className: 'btn btn-sm' },
                { extend: 'pdf',   className: 'btn btn-sm' },
                { extend: 'print', className: 'btn btn-sm' }
            ],
            ajax: "{{ route('admin.users.data') }}",
            columns: [
                { data: 'name',  name: 'name',  className: 'px-6 py-4 font-bold text-gray-800 dark:text-gray-200' },
                { data: 'email', name: 'email', className: 'px-6 py-4 text-xs text-gray-500 dark:text-gray-400' },
                { data: 'roles_assignment', name: 'roles_assignment', orderable: false, searchable: false, className: 'px-6 py-4' }
            ],
            language: {
                search: "",
                searchPlaceholder: "Search users...",
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

        $(document).on('change', '.user-role-select', function() {
            let userId = $(this).data('user-id');
            let selectedRoles = $(this).val();
            let select = $(this);
            select.addClass('opacity-50 pointer-events-none');
            $.ajax({
                url: '{{ url('admin/ajax/users') }}/' + userId + '/roles',
                method: 'PUT',
                data: { _token: '{{ csrf_token() }}', roles: selectedRoles },
                success: function() { select.removeClass('opacity-50 pointer-events-none'); }
            });
        });
    });
</script>
@endpush
