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
    </style>
@endpush

@section('content')
<div class="min-h-screen bg-gray-50 dark:bg-[#18191a] py-8 px-4 sm:px-6 lg:px-8 transition-colors duration-300">
    <div class="max-w-7xl mx-auto">

        {{-- Flash Message (overlay bottom-right, auto-dismiss 2s) --}}
        @if(session('success'))
        <div id="flashMsg" style="position:fixed;bottom:1.5rem;right:1.5rem;z-index:99999;max-width:22rem;width:100%;animation:fadeInUp .3s ease">
            <div style="display:flex;align-items:center;gap:.75rem;padding:1rem 1.25rem;border-radius:1rem;background:rgba(16,185,129,.9);backdrop-filter:blur(6px);box-shadow:0 8px 24px rgba(0,0,0,.15)">
                <i class="fas fa-check-circle" style="color:#fff;font-size:1.1rem;flex-shrink:0"></i>
                <span style="color:#fff;font-size:.85rem;font-weight:600;flex:1">{{ session('success') }}</span>
                <button onclick="document.getElementById('flashMsg').remove()" style="background:none;border:0;color:rgba(255,255,255,.7);cursor:pointer;font-size:.8rem;padding:0"><i class="fas fa-times"></i></button>
            </div>
        </div>
        <script>setTimeout(()=>document.getElementById('flashMsg')?.remove(),2000)</script>
        @endif

        {{-- Header --}}
        <div class="flex flex-col md:flex-row md:items-center md:justify-between mb-10 space-y-4 md:space-y-0">
            <div class="flex items-center space-x-6">
                <a href="{{ route('admin.dashboard') }}"
                   class="group flex items-center justify-center w-10 h-10 rounded-full bg-white dark:bg-[#242526] shadow-sm border border-gray-200 dark:border-[#3a3b3c] text-gray-500 dark:text-gray-400 hover:text-indigo-600 dark:hover:text-indigo-400 hover:border-indigo-300 transition-all duration-200">
                    <i class="fas fa-arrow-left group-hover:-translate-x-0.5 transition-transform"></i>
                </a>
                <div>
                    <h1 class="text-3xl font-extrabold text-gray-900 dark:text-white tracking-tight">{{ __('Opened Courses') }}</h1>
                    <p class="text-sm text-gray-500 dark:text-gray-400 font-medium mt-1 px-1">
                        @if($currentYear && $currentSemester)
                            {{ __('Academic Year') }} {{ $currentYear->year }} &bull; {{ __('Semester') }} {{ $currentSemester->semester_number }}
                        @else
                            {{ __('No academic year selected') }}
                        @endif
                    </p>
                </div>
            </div>
            @if($currentYear && $currentSemester)
            <a href="{{ route('admin.opened-courses.create') }}" class="btn-app">
                <i class="fas fa-plus text-[10px]"></i> {{ __('Add Course') }}
            </a>
            @endif
        </div>

        {{-- Main Content Card --}}
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
                                <h3 class="text-sm font-bold text-gray-800 dark:text-gray-200 uppercase tracking-wider">{{ __('Filters') }}</h3>
                                <p class="text-[10px] text-gray-400 font-bold uppercase tracking-tight">{{ __('Filter by grade level and course') }}</p>
                            </div>
                        </div>
                        <div class="flex flex-wrap items-center gap-3">
                            {{-- Grade Filter --}}
                            <div class="relative group">
                                <select id="gradeFilter" class="appearance-none block w-full md:w-48 pl-4 pr-10 py-2.5 bg-white dark:bg-[#242526] border-2 border-gray-100 dark:border-[#3a3b3c] rounded-xl text-xs font-bold text-gray-600 dark:text-gray-400 focus:outline-none focus:ring-0 focus:border-indigo-500 transition-all cursor-pointer">
                                    <option value="">{{ __('All Grade Levels') }}</option>
                                    @foreach(\App\Models\Grade::where('status',1)->get() as $grade)
                                        <option value="{{ $grade->id }}">{{ $grade->name_th }}</option>
                                    @endforeach
                                </select>
                                <div class="absolute inset-y-0 right-0 pr-3 flex items-center pointer-events-none text-gray-400 group-hover:text-indigo-500 transition-colors">
                                    <i class="fas fa-chevron-down text-[10px]"></i>
                                </div>
                            </div>
                            {{-- Course Filter --}}
                            <div class="relative group">
                                <select id="courseFilter" class="appearance-none block w-full md:w-56 pl-4 pr-10 py-2.5 bg-white dark:bg-[#242526] border-2 border-gray-100 dark:border-[#3a3b3c] rounded-xl text-xs font-bold text-gray-600 dark:text-gray-400 focus:outline-none focus:ring-0 focus:border-indigo-500 transition-all cursor-pointer">
                                    <option value="">{{ __('All Courses') }}</option>
                                    @if($semesterId)
                                        @foreach(\App\Models\Course::where('status',1)->where('semester_id',$semesterId)->orderBy('name')->get() as $course)
                                            <option value="{{ $course->id }}">{{ $course->name }}</option>
                                        @endforeach
                                    @endif
                                </select>
                                <div class="absolute inset-y-0 right-0 pr-3 flex items-center pointer-events-none text-gray-400 group-hover:text-indigo-500 transition-colors">
                                    <i class="fas fa-chevron-down text-[10px]"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Table --}}
                <div class="overflow-x-auto lg:overflow-visible">
                    <table id="openedCoursesTable" class="w-full text-left border-collapse whitespace-nowrap lg:whitespace-normal">
                        <thead>
                            <tr class="bg-gray-50/50 dark:bg-[#18191a]/30 border-b border-gray-100 dark:border-[#3a3b3c]/50">
                                <th class="px-6 py-4 text-xs font-bold text-gray-400 dark:text-gray-500 uppercase tracking-widest w-12">#</th>
                                <th class="px-6 py-4 text-xs font-bold text-gray-400 dark:text-gray-500 uppercase tracking-widest">{{ __('Grade Level') }}</th>
                                <th class="px-6 py-4 text-xs font-bold text-gray-400 dark:text-gray-500 uppercase tracking-widest">{{ __('Classroom') }}</th>
                                <th class="px-6 py-4 text-xs font-bold text-gray-400 dark:text-gray-500 uppercase tracking-widest">{{ __('Course') }}</th>
                                <th class="px-6 py-4 text-xs font-bold text-gray-400 dark:text-gray-500 uppercase tracking-widest text-right w-36">{{ __('Actions') }}</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-50 dark:divide-[#3a3b3c]/50 text-gray-600 dark:text-gray-400 text-sm">
                        </tbody>
                    </table>
                </div>

            </div>

            {{-- Card Footer --}}
            <div class="px-8 py-5 bg-gray-50/50 dark:bg-[#18191a]/30 border-t border-gray-100 dark:border-[#3a3b3c]/50 flex items-center justify-between text-xs font-bold text-gray-400 dark:text-gray-500 uppercase tracking-widest">
                <span>{{ __('Opened Courses Management') }}</span>
                <span class="flex items-center">
                    <i class="fas fa-book-open mr-2"></i> {{ __('Academic Control') }}
                </span>
            </div>
        </div>

    </div>
</div>

{{-- Delete Confirm Modal --}}
<div id="deleteModal" style="display:none" class="fixed inset-0 z-[9999] flex items-center justify-center bg-black/50 backdrop-blur-sm">
    <div class="bg-white dark:bg-[#242526] rounded-2xl shadow-2xl w-full max-w-sm mx-4 overflow-hidden">
        <div class="p-6 text-center">
            <div class="w-14 h-14 rounded-full bg-rose-50 dark:bg-rose-900/20 flex items-center justify-center mx-auto mb-4">
                <i class="fas fa-trash-alt text-rose-500 text-xl"></i>
            </div>
            <h3 class="text-base font-bold text-gray-800 dark:text-white mb-2">{{ __('Confirm Delete') }}</h3>
            <p class="text-sm text-gray-500 dark:text-gray-400 mb-6">{{ __('Do you want to delete this course? This action cannot be undone.') }}</p>
            <div class="flex gap-3">
                <button onclick="closeDeleteModal()" class="flex-1 px-4 py-2.5 rounded-xl border-2 border-gray-100 dark:border-[#3a3b3c] text-sm font-bold text-gray-500 hover:bg-gray-50 dark:hover:bg-[#3a3b3c] transition-colors">
                    {{ __('Cancel') }}
                </button>
                <button id="confirmDeleteBtn" onclick="confirmDelete()" class="flex-1 px-4 py-2.5 rounded-xl bg-rose-500 hover:bg-rose-600 text-white text-sm font-bold transition-colors">
                    {{ __('Delete') }}
                </button>
            </div>
        </div>
    </div>
</div>
@endsection

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
        let deleteId = null;
        const CSRF = '{{ csrf_token() }}';
        const LANG_EDIT = @json(__('Edit'));
        const LANG_DELETE = @json(__('Delete'));
        const LANG_SEARCH_PLACEHOLDER = @json(__('Search...'));
        const LANG_SHOW = @json(__('Show'));
        const LANG_LOADING = @json(__('Loading...'));
        const LANG_NO_OPENED_COURSES = @json(__('No opened courses available'));
        const LANG_DELETING = @json(__('Deleting...'));

        $(document).ready(function() {
            const table = $('#openedCoursesTable').DataTable({
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
                ajax: {
                    url: "{{ route('admin.opened-courses.data') }}",
                    data: function(d) {
                        d.grade_id  = $('#gradeFilter').val();
                        d.course_id = $('#courseFilter').val();
                    }
                },
                columns: [
                    {
                        data: null,
                        orderable: false,
                        searchable: false,
                        className: 'px-6 py-4 text-gray-400 text-xs font-bold',
                        render: function(data, type, row, meta) {
                            return meta.row + meta.settings._iDisplayStart + 1;
                        }
                    },
                    { data: 'grade_name',     name: 'grade_name',     className: 'px-6 py-4 font-semibold text-slate-700 dark:text-slate-200' },
                    { data: 'classroom_name', name: 'classroom_name', className: 'px-6 py-4' },
                    { data: 'course_name',    name: 'course_name',    className: 'px-6 py-4' },
                    {
                        data: 'actions',
                        name: 'actions',
                        orderable: false,
                        searchable: false,
                        className: 'px-6 py-4 text-right',
                        render: function(id) {
                            return `
                                <div class="flex items-center justify-end gap-2">
                                    <a href="/admin/opened-courses/${id}/edit" class="btn-app">
                                        <i class="fas fa-edit text-[10px]"></i> ${LANG_EDIT}
                                    </a>
                                    <button onclick="openDeleteModal(${id})" class="btn-app" style="background:#ef4444;border-color:#ef4444;">
                                        <i class="fas fa-trash-alt text-[10px]"></i> ${LANG_DELETE}
                                    </button>
                                </div>`;
                        }
                    },
                ],
                language: {
                    search: "",
                    searchPlaceholder: LANG_SEARCH_PLACEHOLDER,
                    lengthMenu: LANG_SHOW + " _MENU_",
                    paginate: {
                        previous: '<i class="fas fa-chevron-left"></i>',
                        next:     '<i class="fas fa-chevron-right"></i>'
                    },
                    processing: '<div class="text-indigo-500 text-xs font-bold">' + LANG_LOADING + '</div>',
                    emptyTable: '<div class="text-gray-400 text-xs py-4">' + LANG_NO_OPENED_COURSES + '</div>',
                },
                drawCallback: function() {
                    $('.dataTables_filter input').addClass('dark:bg-[#242526] dark:border-[#3a3b3c] dark:text-white');
                    $('.dataTables_length select').addClass('dark:bg-[#242526] dark:border-[#3a3b3c] dark:text-white');
                }
            });

            $('#gradeFilter, #courseFilter').on('change', function() {
                table.draw();
            });
        });

        function openDeleteModal(id) {
            deleteId = id;
            document.getElementById('deleteModal').style.display = 'flex';
        }

        function closeDeleteModal() {
            deleteId = null;
            document.getElementById('deleteModal').style.display = 'none';
        }

        function confirmDelete() {
            if (!deleteId) return;
            const btn = document.getElementById('confirmDeleteBtn');
            btn.disabled = true;
            btn.textContent = LANG_DELETING;

            fetch(`/admin/opened-courses/${deleteId}`, {
                method: 'DELETE',
                headers: { 'X-CSRF-TOKEN': CSRF, 'X-Requested-With': 'XMLHttpRequest' }
            })
            .then(r => r.json())
            .then(d => {
                if (d.success) {
                    closeDeleteModal();
                    $('#openedCoursesTable').DataTable().draw();
                }
            })
            .finally(() => {
                btn.disabled = false;
                btn.textContent = LANG_DELETE;
            });
        }
    </script>
@endpush
