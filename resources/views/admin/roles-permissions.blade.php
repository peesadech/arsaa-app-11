@extends('layouts.app')

@push('styles')
    <link rel="stylesheet" href="https://cdn.datatables.net/1.11.5/css/dataTables.bootstrap4.min.css">
    <style>
        .dataTables_wrapper .dataTables_paginate .paginate_button { padding: 0 !important; margin: 0 !important; border: none !important; }
        .dataTables_wrapper .dataTables_filter input { border: 2px solid #f3f4f6 !important; border-radius: 12px !important; padding: 6px 12px !important; outline: none !important; }
        .pagination .page-item.active .page-link { background-color: #6366f1 !important; border-color: #6366f1 !important; color: white !important; }
        .pagination .page-link { border-radius: 12px !important; margin: 0 4px !important; font-weight: 700 !important; color: #4b5563 !important; border: 2px solid #f3f4f6 !important; }
        .user-role-select:focus { outline: none; box-shadow: 0 0 0 3px rgba(99, 102, 241, 0.2); }
    </style>
@endpush

@section('content')
<div class="min-h-screen bg-gray-50 py-8 px-4 sm:px-6 lg:px-8">
    <div class="max-w-7xl mx-auto">
        <!-- Header Section -->
        <div class="flex flex-col md:flex-row md:items-center md:justify-between mb-10 space-y-4 md:space-y-0">
           <a href="{{ route('home') }}" 
               class="group flex items-center justify-center w-10 h-10 rounded-full bg-white shadow-sm border border-gray-200 text-gray-500 hover:text-indigo-600 hover:border-indigo-300 transition-all duration-200">
                <i class="fas fa-arrow-left group-hover:-translate-x-0.5 transition-transform"></i>
            </a> 
            <div>
                <h1 class="text-3xl font-extrabold text-gray-900 tracking-tight">Access Control Center</h1>
                <p class="text-sm text-gray-500 font-medium px-1 mt-1">Manage system roles and user assignments</p>
            </div>
            <div class="flex space-x-3">
                <button type="button" onclick="openCreateRoleModal()"
                   class="inline-flex items-center px-6 py-3 border border-transparent text-sm font-bold rounded-2xl shadow-lg text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 transition-all duration-200 transform hover:-translate-y-0.5 active:scale-95">
                    <i class="fas fa-user-shield mr-2 opacity-75"></i>
                    New Role
                </button>
            </div>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
            <!-- Roles Column -->
            <div class="space-y-6">
                <div class="bg-white rounded-3xl shadow-xl shadow-gray-200/50 border border-gray-100 overflow-hidden">
                    <div class="p-6 border-b border-gray-50 bg-gray-50/30">
                        <h3 class="text-lg font-bold text-gray-800">Role Management</h3>
                    </div>
                    <div class="p-6">
                        <table id="rolesTable" class="w-full text-left border-collapse">
                            <thead>
                                <tr class="bg-gray-50/50 border-b border-gray-100">
                                    <th class="px-4 py-3 text-[10px] font-bold text-gray-400 uppercase tracking-widest">Role</th>
                                    <th class="px-4 py-3 text-[10px] font-bold text-gray-400 uppercase tracking-widest">Permissions</th>
                                    <th class="px-4 py-3 text-[10px] font-bold text-gray-400 uppercase tracking-widest text-right">Action</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-50 text-gray-600 text-xs">
                                <!-- DataTables -->
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <!-- Users Column -->
            <div class="space-y-6">
                <div class="bg-white rounded-3xl shadow-xl shadow-gray-200/50 border border-gray-100 overflow-hidden">
                    <div class="p-6 border-b border-gray-50 bg-gray-50/30">
                        <h3 class="text-lg font-bold text-gray-800">User Assignments</h3>
                    </div>
                    <div class="p-6">
                        <table id="usersTable" class="w-full text-left border-collapse">
                            <thead>
                                <tr class="bg-gray-50/50 border-b border-gray-100">
                                    <th class="px-4 py-3 text-[10px] font-bold text-gray-400 uppercase tracking-widest">User Info</th>
                                    <th class="px-4 py-3 text-[10px] font-bold text-gray-400 uppercase tracking-widest">Roles Assignment</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-50 text-gray-600 text-xs">
                                <!-- DataTables -->
                            </tbody>
                        </table>
                        <div class="mt-4 p-4 bg-amber-50 rounded-2xl border border-amber-100 flex items-start">
                            <i class="fas fa-info-circle text-amber-500 mt-0.5 mr-3"></i>
                            <p class="text-[10px] text-amber-800 font-medium leading-relaxed">
                                Changes to user roles are saved automatically upon selection. Use Ctrl+Click (Win) or Cmd+Click (Mac) to select multiple roles.
                            </p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Create Role Modal -->
<div id="roleModal" class="fixed inset-0 z-50 overflow-y-auto hidden" aria-labelledby="modal-title" role="dialog" aria-modal="true">
    <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
        <div class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity" aria-hidden="true" onclick="closeRoleModal()"></div>
        <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>
        <div class="inline-block align-bottom bg-white rounded-3xl text-left overflow-hidden shadow-2xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full border border-gray-100">
            <form id="roleForm">
                @csrf
                <div class="bg-white px-4 pt-5 pb-4 sm:p-8">
                    <div>
                        <div class="mx-auto flex items-center justify-center h-12 w-12 rounded-2xl bg-indigo-50 text-indigo-600 mb-6 font-bold">
                            <i class="fas fa-plus"></i>
                        </div>
                        <div class="text-center sm:text-left">
                            <h3 class="text-xl font-extrabold text-gray-900 tracking-tight mb-2">Create New Role</h3>
                            <p class="text-xs text-gray-500 mb-6 uppercase tracking-wider font-bold">Role identity and initial permissions</p>
                            
                            <div class="space-y-6">
                                <div>
                                    <label class="block text-[10px] font-bold text-gray-400 uppercase tracking-widest mb-1.5 px-1">Role Name</label>
                                    <input type="text" name="name" id="roleName" required
                                        class="w-full px-4 py-3 rounded-2xl bg-gray-50 border-2 border-transparent focus:border-indigo-500 focus:bg-white focus:outline-none transition-all duration-200 text-sm font-medium"
                                        placeholder="e.g. Moderator">
                                </div>
                                <div>
                                    <label class="block text-[10px] font-bold text-gray-400 uppercase tracking-widest mb-1.5 px-1">Assign Initial Permissions</label>
                                    <select name="permissions[]" id="rolePermissions" multiple
                                        class="w-full px-4 py-3 rounded-2xl bg-gray-50 border-2 border-transparent focus:border-indigo-500 focus:bg-white focus:outline-none transition-all duration-200 text-sm font-medium h-48">
                                        <!-- Permissions dynamically loaded -->
                                    </select>
                                    <p class="mt-2 text-[10px] text-gray-400 px-1 font-medium">Hold Ctrl/Cmd to select multiple permissions</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="bg-gray-50/50 px-4 py-6 sm:px-8 sm:flex sm:flex-row-reverse space-y-3 sm:space-y-0">
                    <button type="submit" class="w-full inline-flex justify-center rounded-2xl border border-transparent shadow-lg shadow-indigo-200/50 px-8 py-3 bg-indigo-600 text-sm font-bold text-white hover:bg-indigo-700 focus:outline-none transition-all duration-200 sm:ml-3 sm:w-auto transform active:scale-95">Register Role</button>
                    <button type="button" onclick="closeRoleModal()" class="w-full inline-flex justify-center rounded-2xl border-2 border-gray-100 px-8 py-3 bg-white text-sm font-bold text-gray-700 hover:bg-gray-50 hover:border-gray-200 focus:outline-none transition-all duration-200 sm:w-auto transform active:scale-95">Cancel</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Delete Modal -->
<div id="deleteModal" class="fixed inset-0 z-50 overflow-y-auto hidden" aria-labelledby="modal-title" role="dialog" aria-modal="true">
    <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
        <div class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity" aria-hidden="true" onclick="closeDeleteModal()"></div>
        <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>
        <div class="inline-block align-bottom bg-white rounded-3xl text-left overflow-hidden shadow-2xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full border border-gray-100">
            <div class="bg-white px-4 pt-5 pb-4 sm:p-8">
                <div class="sm:flex sm:items-start">
                    <div class="mx-auto flex-shrink-0 flex items-center justify-center h-16 w-16 rounded-2xl bg-rose-50 sm:mx-0 sm:h-12 sm:w-12 border border-rose-100">
                        <i class="fas fa-exclamation-triangle text-rose-600"></i>
                    </div>
                    <div class="mt-3 text-center sm:mt-0 sm:ml-6 sm:text-left">
                        <h3 class="text-xl font-extrabold text-gray-900 tracking-tight" id="modal-title">Remove Role</h3>
                        <div class="mt-2 text-sm text-gray-500 leading-relaxed">
                            Are you sure you want to delete <span id="deleteItemName" class="font-bold text-gray-900"></span>? Users with this role will lose their associated permissions.
                        </div>
                    </div>
                </div>
            </div>
            <div class="bg-gray-50/50 px-4 py-6 sm:px-8 sm:flex sm:flex-row-reverse space-y-3 sm:space-y-0">
                <button type="button" id="confirmDeleteBtn" class="w-full inline-flex justify-center rounded-2xl border border-transparent shadow-lg shadow-rose-200/50 px-8 py-3 bg-rose-600 text-sm font-bold text-white hover:bg-rose-700 focus:outline-none transition-all duration-200 sm:ml-3 sm:w-auto transform active:scale-95">Permanent Delete</button>
                <button type="button" onclick="closeDeleteModal()" class="w-full inline-flex justify-center rounded-2xl border-2 border-gray-100 px-8 py-3 bg-white text-sm font-bold text-gray-700 hover:bg-gray-50 hover:border-gray-200 focus:outline-none transition-all duration-200 sm:w-auto transform active:scale-95">Cancel</button>
            </div>
        </div>
    </div>
</div>

@endsection

@push('scripts')
<script src="https://cdn.datatables.net/1.11.5/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.11.5/js/dataTables.bootstrap4.min.js"></script>
<script>
    $(function() {
        let currentDeleteRoleId = null;

        // Roles Table
        var rolesTable = $('#rolesTable').DataTable({
            processing: true,
            serverSide: true,
            ajax: "{{ route('admin.roles.data') }}",
            paging: false,
            searching: false,
            info: false,
            columns: [
                { 
                    data: 'name', 
                    name: 'name',
                    render: function(data) {
                        return '<span class="font-extrabold text-indigo-600 tracking-tight uppercase text-xs">' + data + '</span>';
                    }
                },
                { data: 'permissions_list', name: 'permissions_list' },
                { data: 'action', name: 'action', orderable: false, searchable: false, className: 'text-right' }
            ]
        });

        // Users Table
        var usersTable = $('#usersTable').DataTable({
            processing: true,
            serverSide: true,
            ajax: "{{ route('admin.users.data') }}",
            columns: [
                { 
                    data: 'name', 
                    name: 'name',
                    render: function(data, type, row) {
                        return '<div class="space-y-0.5">' +
                               '<div class="font-extrabold text-gray-900 tracking-tight">' + data + '</div>' +
                               '<div class="text-[10px] text-gray-400 font-bold">' + row.email + '</div>' +
                               '</div>';
                    }
                },
                { data: 'roles_assignment', name: 'roles_assignment', orderable: false, searchable: false }
            ],
            language: {
                search: "",
                searchPlaceholder: "Search users...",
                lengthMenu: "Show _MENU_"
            }
        });

        // Auto-update User Roles
        $(document).on('change', '.user-role-select', function() {
            let userId = $(this).data('user-id');
            let selectedRoles = $(this).val();
            let select = $(this);
            
            select.addClass('opacity-50 pointer-events-none');
            
            $.ajax({
                url: '/api/users/' + userId + '/roles',
                method: 'PUT',
                data: {
                    _token: '{{ csrf_token() }}',
                    roles: selectedRoles
                },
                success: function() {
                    select.removeClass('opacity-50 pointer-events-none');
                }
            });
        });

        // Load Permissions for Modal Select
        function loadPermissions() {
            $.get('/api/permissions', function(data) {
                let options = '';
                data.forEach(p => {
                    options += '<option value="' + p.name + '">' + p.name + '</option>';
                });
                $('#rolePermissions').html(options);
            });
        }
        loadPermissions();

        // Modal Controls
        window.openCreateRoleModal = function() {
            $('#roleForm')[0].reset();
            $('#roleModal').removeClass('hidden');
        };

        window.closeRoleModal = function() {
            $('#roleModal').addClass('hidden');
        };

        window.confirmDeleteRole = function(id, name) {
            currentDeleteRoleId = id;
            $('#deleteItemName').text(name);
            $('#deleteModal').removeClass('hidden');
        };

        window.closeDeleteModal = function() {
            $('#deleteModal').addClass('hidden');
        };

        // Form Submit
        $('#roleForm').on('submit', function(e) {
            e.preventDefault();
            $.post('/api/roles', $(this).serialize(), function() {
                closeRoleModal();
                rolesTable.ajax.reload();
                usersTable.ajax.reload();
            });
        });

        $('#confirmDeleteBtn').on('click', function() {
            if (currentDeleteRoleId) {
                $.ajax({
                    url: '/api/roles/' + currentDeleteRoleId,
                    method: 'DELETE',
                    data: { _token: '{{ csrf_token() }}' },
                    success: function() {
                        closeDeleteModal();
                        rolesTable.ajax.reload();
                        usersTable.ajax.reload();
                    }
                });
            }
        });
    });
</script>
@endpush
