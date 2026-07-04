<x-layouts.admin :header="__('Permission Categories')" :subheader="__('Manage and organize your system access levels')">
    <x-slot name="actions">
        <x-button icon="plus" :href="route('admin.permission-types.create')">{{ __('Permission Category') }}</x-button>
    </x-slot>

    @push('styles')
        <link rel="stylesheet" href="https://cdn.datatables.net/1.11.5/css/dataTables.bootstrap4.min.css">
        <link rel="stylesheet" href="https://cdn.datatables.net/responsive/2.2.9/css/responsive.bootstrap4.min.css">
        <link rel="stylesheet" href="https://cdn.datatables.net/buttons/2.2.2/css/buttons.bootstrap4.min.css">
        <style>
            .dataTables_wrapper .dataTables_paginate .paginate_button {
                padding: 0 !important; margin: 0 !important; border: none !important;
            }
            .dataTables_wrapper .dataTables_length select,
            .dataTables_wrapper .dataTables_filter input {
                border: 1px solid #e2e8f0 !important; border-radius: 0.5rem !important;
                padding: 8px 14px !important; outline: none !important; height: auto !important;
                font-weight: 500 !important; background: white !important; color: #334155 !important;
            }
            .dataTables_wrapper .dataTables_filter input:focus { border-color: #60a5fa !important; }
            .dataTables_wrapper .dataTables_length { width: 100% !important; margin-bottom: 0 !important; }
            .dataTables_wrapper .dataTables_length label {
                width: 100% !important; display: flex !important; align-items: center !important;
                font-size: 0.875rem !important; color: #64748b !important; font-weight: 500 !important;
            }
            .dataTables_wrapper .dataTables_length select {
                flex: 1 !important; margin: 0 0.75rem !important; max-width: 120px !important; min-height: 42px !important;
            }
            .dataTables_wrapper .dataTables_filter { width: 100% !important; }
            .dataTables_wrapper .dataTables_filter label { width: 100% !important; display: flex !important; align-items: center !important; }
            .dataTables_wrapper .dataTables_filter input { flex: 1 !important; margin-left: 0 !important; }
            table.dataTable thead th { border-bottom: 1px solid #f1f5f9 !important; }
            table.dataTable.no-footer { border-bottom: none !important; }
            .pagination .page-item.active .page-link {
                background-color: #2563eb !important; border-color: #2563eb !important; color: white !important;
                box-shadow: 0 10px 15px -3px rgba(37, 99, 235, 0.3) !important;
            }
            .pagination .page-link {
                border-radius: 0.5rem !important; margin: 0 4px !important; font-weight: 600 !important;
                color: #475569 !important; border: 1px solid #e2e8f0 !important; background: white;
            }
            table.dataTable tbody tr { background-color: transparent !important; }
            .dt-buttons.btn-group { margin-bottom: 1.5rem; gap: 0.5rem; }
            .dt-buttons .btn {
                border-radius: 0.5rem !important; font-size: 0.75rem !important; font-weight: 600 !important;
                text-transform: uppercase !important; letter-spacing: 0.05em !important; padding: 0.6rem 1.2rem !important;
                border: 1px solid #e2e8f0 !important; background: white !important; color: #64748b !important;
                transition: all 0.2s !important; box-shadow: none !important;
            }
            .dt-buttons .btn:hover { border-color: #2563eb !important; color: #2563eb !important; background: #eff6ff !important; }
            .dt-buttons .btn i { margin-right: 0.5rem; }
        </style>
    @endpush

    <x-card padded="false">
        <div class="p-4 sm:p-6">
            <div class="overflow-x-auto lg:overflow-visible">
                <table id="permissionTypesTable" class="w-full text-left border-collapse whitespace-nowrap lg:whitespace-normal">
                    <thead>
                        <tr class="bg-slate-50 border-b border-slate-100">
                            <th class="px-4 sm:px-6 py-3 text-xs font-medium text-slate-500 uppercase tracking-wide">{{ __('ID') }}</th>
                            <th class="px-4 sm:px-6 py-3 text-xs font-medium text-slate-500 uppercase tracking-wide">{{ __('Category Name') }}</th>
                            <th class="px-4 sm:px-6 py-3 text-xs font-medium text-slate-500 uppercase tracking-wide text-right">{{ __('Action') }}</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 text-slate-600 text-sm">
                        {{-- DataTables will populate this --}}
                    </tbody>
                </table>
            </div>
        </div>

        <div class="px-6 py-4 bg-slate-50 border-t border-slate-100 flex items-center justify-between text-xs font-medium text-slate-400 uppercase tracking-wide">
            <span>{{ __('Management System Optimized') }}</span>
            <span class="flex items-center gap-2">
                <x-icon name="shield" class="h-4 w-4" /> {{ __('Authorized Access Only') }}
            </span>
        </div>
    </x-card>

    {{-- Delete Modal --}}
    <div id="deleteModal" class="fixed inset-0 z-50 overflow-y-auto hidden" aria-labelledby="modal-title" role="dialog" aria-modal="true">
        <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
            <div class="fixed inset-0 bg-slate-900/50 transition-opacity" aria-hidden="true" onclick="closeModal()"></div>
            <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>
            <div class="inline-block align-bottom bg-white rounded-2xl text-left overflow-hidden shadow-2xl transform transition-all sm:my-8 sm:align-middle sm:max-w-md sm:w-full border border-slate-100">
                <div class="p-6">
                    <div class="flex items-start gap-4">
                        <div class="h-12 w-12 rounded-full bg-red-50 text-red-600 flex items-center justify-center shrink-0">
                            <x-icon name="trash" class="h-6 w-6" />
                        </div>
                        <div>
                            <h3 class="text-lg font-semibold text-slate-900" id="modal-title">{{ __('Confirm Deletion') }}</h3>
                            <p class="text-sm text-slate-500 mt-1">
                                {{ __('Are you sure you want to permanently remove') }} <span id="deleteItemName" class="font-medium text-slate-700"></span>{{ __('? This action cannot be undone.') }}
                            </p>
                        </div>
                    </div>
                </div>
                <div class="px-6 py-4 bg-slate-50 border-t border-slate-100 flex justify-end gap-2">
                    <button type="button" onclick="closeModal()" class="btn-secondary">{{ __('Cancel') }}</button>
                    <button type="button" id="confirmDeleteBtn" class="btn-danger">{{ __('Delete') }}</button>
                </div>
            </div>
        </div>
    </div>

    @push('scripts')
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.datatables.net/1.11.5/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.11.5/js/dataTables.bootstrap4.min.js"></script>
    <script src="https://cdn.datatables.net/buttons/2.2.2/js/dataTables.buttons.min.js"></script>
    <script src="https://cdn.datatables.net/buttons/2.2.2/js/buttons.bootstrap4.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jszip/3.1.3/jszip.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.1.53/pdfmake.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.1.53/vfs_fonts.js"></script>

    <script>
        $(function() {
            const LANG_SEARCH_CATEGORIES = @json(__('Search categories...'));
            const LANG_SHOW = @json(__('Show'));
            const LANG_ALL = @json(__('All'));

            // Auto-dismiss alert
            const alert = document.getElementById('statusAlert');
            if (alert) {
                setTimeout(() => {
                    alert.classList.remove('opacity-100');
                    alert.classList.add('opacity-0', 'translate-y-4');
                    setTimeout(() => { alert.remove(); }, 500);
                }, 2000);
            }

            let currentDeleteId = null;

            var table = $('#permissionTypesTable').DataTable({
                processing: true,
                serverSide: true,
                ajax: {
                    url: "{{ route('admin.permission-types.data') }}",
                    error: function (xhr, error, code) {
                        console.log("DataTable Error:", xhr.responseText);
                    }
                },
                dom: '<"flex flex-wrap items-center justify-between mb-6"B><"grid grid-cols-1 lg:grid-cols-2 gap-4 mb-6"lf>rt<"flex flex-col md:flex-row md:items-center md:justify-between mt-6"ip>',
                lengthMenu: [[10, 25, 50, -1], [10, 25, 50, LANG_ALL]],
                columns: [
                    {
                        data: 'permissionType_id',
                        name: 'permissionType_id',
                        render: function(data) {
                            return '<span class="inline-flex items-center justify-center w-8 h-8 rounded-lg bg-slate-100 text-slate-500 text-xs font-bold">#' + data + '</span>';
                        }
                    },
                    {
                        data: 'permissionType_name',
                        name: 'permissionType_name',
                        render: function(data, type, row) {
                            let img_path = row.permissionType_image_path;
                            let img = img_path
                                ? '<img src="' + img_path + '" class="w-full h-full object-cover">'
                                : '<i class="fas fa-layer-group"></i>';

                            return '<div class="flex items-center space-x-3">' +
                                   '<div class="w-10 h-10 rounded-xl bg-brand-50 flex items-center justify-center text-brand-600 border border-brand-100 overflow-hidden">' + img + '</div>' +
                                   '<span class="text-base font-semibold text-slate-800">' + data + '</span>' +
                                   '</div>';
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
                    searchPlaceholder: LANG_SEARCH_CATEGORIES,
                    lengthMenu: LANG_SHOW + " _MENU_",
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
                        "action": "/admin/permission-types/" + currentDeleteId
                    });
                    form.append($('<input>', { "name": "_token", "value": "{{ csrf_token() }}", "type": "hidden" }));
                    form.append($('<input>', { "name": "_method", "value": "DELETE", "type": "hidden" }));
                    form.appendTo('body').submit();
                }
            });
        });
    </script>
    @endpush
</x-layouts.admin>
