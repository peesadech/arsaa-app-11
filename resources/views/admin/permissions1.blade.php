@extends('layouts.app')

@push('styles')
    <link rel="stylesheet" href="https://cdn.datatables.net/1.11.5/css/dataTables.bootstrap4.min.css">
    <link rel="stylesheet" href="https://cdn.datatables.net/responsive/2.2.9/css/responsive.bootstrap4.min.css">
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
            padding: 6px 12px !important;
            outline: none !important;
        }
        .dataTables_wrapper .dataTables_filter input:focus {
            border-color: #6366f1 !important;
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
        }
    </style>
@endpush

@section('content')
<div class="min-h-screen bg-gray-50 py-8 px-4 sm:px-6 lg:px-8">
    <div class="max-w-6xl mx-auto">
        <!-- Header Section -->
        <div class="flex flex-col md:flex-row md:items-center md:justify-between mb-10 space-y-4 md:space-y-0">
           <a href="{{ route('admin.dashboard') }}" 
               class="group flex items-center justify-center w-10 h-10 rounded-full bg-white shadow-sm border border-gray-200 text-gray-500 hover:text-indigo-600 hover:border-indigo-300 transition-all duration-200">
                <i class="fas fa-arrow-left group-hover:-translate-x-0.5 transition-transform"></i>
            </a> 
            <div>
                <h1 class="text-3xl font-extrabold text-gray-900 tracking-tight">System Permissions</h1>
                <p class="text-sm text-gray-500 font-medium px-1 mt-1">Define and manage granular access controls</p>
            </div>
            <div>
                <button type="button" onclick="openCreateModal()"
                   class="inline-flex items-center px-6 py-3 border border-transparent text-base font-bold rounded-2xl shadow-lg text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 transition-all duration-200 transform hover:-translate-y-0.5 active:scale-95">
                    <i class="fas fa-plus-circle mr-2 opacity-75"></i>
                    New Permission
                </button>
            </div>
        </div>

        <!-- List Card -->
        <div class="bg-white rounded-3xl shadow-xl shadow-gray-200/50 border border-gray-100 overflow-hidden transform transition-all">
            <div class="p-8">
                <table id="permissionsTable" class="w-full text-left border-collapse">
                    <thead>
                        <tr class="bg-gray-50/50 border-b border-gray-100">
                            <th class="px-8 py-5 text-xs font-bold text-gray-400 uppercase tracking-widest">ID</th>
                            <th class="px-8 py-5 text-xs font-bold text-gray-400 uppercase tracking-widest">Permission Name</th>
                            <th class="px-8 py-5 text-xs font-bold text-gray-400 uppercase tracking-widest">Guard</th>
                            <th class="px-8 py-5 text-xs font-bold text-gray-400 uppercase tracking-widest text-right">Action</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-50 text-gray-600 text-sm">
                        <!-- DataTables will populate this -->
                    </tbody>
                </table>
            </div>
            
            <!-- Contextual Footer -->
            <div class="px-8 py-5 bg-gray-50/50 border-t border-gray-100 flex items-center justify-between text-xs font-bold text-gray-400 uppercase tracking-widest">
                <span>Access Control Management</span>
                <span class="flex items-center">
                    <i class="fas fa-shield-alt mr-2"></i> Authorized Access Only
                </span>
            </div>
        </div>
    </div>
</div>

<!-- Create/Edit Modal -->
<div id="permissionModal" class="fixed inset-0 z-50 overflow-y-auto hidden" aria-labelledby="modal-title" role="dialog" aria-modal="true">
    <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
        <div class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity" aria-hidden="true" onclick="closePermissionModal()"></div>
        <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>
        <div class="inline-block align-bottom bg-white rounded-3xl text-left overflow-hidden shadow-2xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full border border-gray-100">
            <form id="permissionForm">
                @csrf
                <input type="hidden" name="_method" id="formMethod" value="POST">
                <input type="hidden" name="id" id="permissionId">
                <div class="bg-white px-4 pt-5 pb-4 sm:p-8">
                    <div>
                        <div class="mx-auto flex items-center justify-center h-12 w-12 rounded-2xl bg-indigo-50 text-indigo-600 mb-6">
                            <i class="fas fa-shield-alt text-xl"></i>
                        </div>
                        <div class="text-center sm:text-left">
                            <h3 class="text-xl leading-6 font-extrabold text-gray-900 tracking-tight mb-2" id="modalTitle">Create Permission</h3>
                            <p class="text-sm text-gray-500 mb-6">Ensure the name is unique and follows the naming convention.</p>
                            
                            <div class="space-y-4">
                                <div>
                                    <label class="block text-xs font-bold text-gray-400 uppercase tracking-widest mb-2">Permission Name</label>
                                    <input type="text" name="name" id="permissionName" required
                                        class="w-full px-4 py-3 rounded-2xl bg-gray-50 border-2 border-transparent focus:border-indigo-500 focus:bg-white focus:outline-none transition-all duration-200"
                                        placeholder="e.g. edit-content">
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="bg-gray-50/50 px-4 py-6 sm:px-8 sm:flex sm:flex-row-reverse space-y-3 sm:space-y-0">
                    <button type="submit" class="w-full inline-flex justify-center rounded-2xl border border-transparent shadow-lg shadow-indigo-200/50 px-8 py-3 bg-indigo-600 text-base font-bold text-white hover:bg-indigo-700 focus:outline-none transition-all duration-200 sm:ml-3 sm:w-auto transform active:scale-95">Save Permission</button>
                    <button type="button" onclick="closePermissionModal()" class="w-full inline-flex justify-center rounded-2xl border-2 border-gray-100 px-8 py-3 bg-white text-base font-bold text-gray-700 hover:bg-gray-50 hover:border-gray-200 focus:outline-none transition-all duration-200 sm:w-auto transform active:scale-95">Cancel</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Delete Confirmation Modal -->
<div id="deleteModal" class="fixed inset-0 z-50 overflow-y-auto hidden" aria-labelledby="modal-title" role="dialog" aria-modal="true">
    <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
        <div class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity" aria-hidden="true" onclick="closeDeleteModal()"></div>
        <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>
        <div class="inline-block align-bottom bg-white rounded-3xl text-left overflow-hidden shadow-2xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full border border-gray-100">
            <div class="bg-white px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
                <div class="sm:flex sm:items-start">
                    <div class="mx-auto flex-shrink-0 flex items-center justify-center h-16 w-16 rounded-2xl bg-rose-50 sm:mx-0 sm:h-12 sm:w-12 border border-rose-100 shadow-inner">
                        <i class="fas fa-trash-alt text-rose-600 text-xl"></i>
                    </div>
                    <div class="mt-3 text-center sm:mt-0 sm:ml-6 sm:text-left">
                        <h3 class="text-xl leading-6 font-extrabold text-gray-900 tracking-tight" id="modal-title">Delete Permission</h3>
                        <div class="mt-2 text-sm text-gray-500 leading-relaxed">
                            Are you sure you want to permanently remove <span id="deleteItemName" class="font-bold text-gray-900 px-1.5 py-0.5 bg-gray-100 rounded-lg"></span>? This will affect all assigned roles.
                        </div>
                    </div>
                </div>
            </div>
            <div class="bg-gray-50/50 px-4 py-6 sm:px-8 sm:flex sm:flex-row-reverse space-y-3 sm:space-y-0">
                <button type="button" id="confirmDeleteBtn" class="w-full inline-flex justify-center rounded-2xl border border-transparent shadow-lg shadow-rose-200/50 px-8 py-3 bg-rose-600 text-base font-bold text-white hover:bg-rose-700 focus:outline-none transition-all duration-200 sm:ml-3 sm:w-auto transform active:scale-95">Confirm Delete</button>
                <button type="button" onclick="closeDeleteModal()" class="w-full inline-flex justify-center rounded-2xl border-2 border-gray-100 px-8 py-3 bg-white text-base font-bold text-gray-700 hover:bg-gray-50 hover:border-gray-200 focus:outline-none transition-all duration-200 sm:w-auto transform active:scale-95">Cancel</button>
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
        let currentDeleteId = null;

        var table = $('#permissionsTable').DataTable({
            processing: true,
            serverSide: true,
            ajax: "{{ route('admin.permissions.data') }}",
            columns: [
                { 
                    data: 'id', 
                    name: 'id',
                    render: function(data) {
                        return '<span class="inline-flex items-center justify-center w-8 h-8 rounded-lg bg-gray-100 text-gray-500 text-xs font-bold">#' + data + '</span>';
                    }
                },
                { 
                    data: 'name', 
                    name: 'name',
                    render: function(data) {
                        return '<div class="flex items-center space-x-3">' +
                               '<div class="w-8 h-8 rounded-lg bg-indigo-50 flex items-center justify-center text-indigo-600">' +
                               '<i class="fas fa-lock"></i></div>' +
                               '<span class="text-base font-bold text-gray-800 tracking-tight">' + data + '</span>' +
                               '</div>';
                    }
                },
                { 
                    data: 'guard_name', 
                    name: 'guard_name',
                    render: function(data) {
                        return '<span class="px-2 py-1 rounded-md bg-gray-100 text-gray-400 text-[10px] font-black uppercase tracking-widest">' + data + '</span>';
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
                searchPlaceholder: "Filter permissions...",
                lengthMenu: "Show _MENU_",
                paginate: {
                    previous: '<i class="fas fa-chevron-left"></i>',
                    next: '<i class="fas fa-chevron-right"></i>'
                }
            }
        });

        // Global functions
        window.openCreateModal = function() {
            $('#modalTitle').text('Create Permission');
            $('#permissionId').val('');
            $('#permissionName').val('');
            $('#formMethod').val('POST');
            $('#permissionModal').removeClass('hidden');
        };

        window.editPermission = function(id, name) {
            $('#modalTitle').text('Edit Permission');
            $('#permissionId').val(id);
            $('#permissionName').val(name);
            $('#formMethod').val('PUT');
            $('#permissionModal').removeClass('hidden');
        };

        window.closePermissionModal = function() {
            $('#permissionModal').addClass('hidden');
        };

        window.confirmDelete = function(id, name) {
            currentDeleteId = id;
            $('#deleteItemName').text(name);
            $('#deleteModal').removeClass('hidden');
        };

        window.closeDeleteModal = function() {
            $('#deleteModal').addClass('hidden');
        };

        // Form Submission
        $('#permissionForm').on('submit', function(e) {
            e.preventDefault();
            let id = $('#permissionId').val();
            let url = id ? '/api/permissions/' + id : '/api/permissions';
            
            $.ajax({
                url: url,
                method: 'POST', // We use POST even for updates due to _method spoofing
                data: $(this).serialize(),
                success: function() {
                    closePermissionModal();
                    table.ajax.reload();
                },
                error: function(xhr) {
                    alert(xhr.responseJSON?.message || 'Error processing request');
                }
            });
        });

        $('#confirmDeleteBtn').on('click', function() {
            if (currentDeleteId) {
                $.ajax({
                    url: '/api/permissions/' + currentDeleteId,
                    method: 'POST',
                    data: {
                        _token: '{{ csrf_token() }}',
                        _method: 'DELETE'
                    },
                    success: function() {
                        closeDeleteModal();
                        table.ajax.reload();
                    }
                });
            }
        });
    });
</script>
@endpush
