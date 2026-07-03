@extends('layouts.app')

@section('content')
@php
    $inputClass = 'w-full px-4 py-2.5 bg-gray-50 dark:bg-[#3a3b3c] border-2 border-transparent rounded-xl text-sm text-gray-800 dark:text-white focus:border-indigo-500 focus:outline-none transition-all';
@endphp
<div class="min-h-screen bg-gray-50 dark:bg-[#18191a] py-10 px-4 sm:px-6 lg:px-8 transition-colors duration-300">
    <div class="max-w-6xl mx-auto">

        {{-- Header --}}
        <div class="flex items-center space-x-4 mb-8">
            <a href="{{ route('admin.students.index') }}"
               class="group flex items-center justify-center w-10 h-10 rounded-full bg-white dark:bg-[#242526] shadow-sm border border-gray-200 dark:border-[#3a3b3c] text-gray-500 dark:text-gray-400 hover:text-indigo-600 dark:hover:text-indigo-400 hover:border-indigo-300 transition-all duration-200">
                <i class="fas fa-arrow-left group-hover:-translate-x-0.5 transition-transform"></i>
            </a>
            <div>
                <h1 class="text-2xl font-extrabold text-gray-900 dark:text-white tracking-tight">{{ __('Student Enrollment') }}</h1>
                <p class="text-sm text-gray-500 dark:text-gray-400 font-medium px-1">
                    {{ __('Academic Year') }} {{ $academicYear->year ?? '?' }} / {{ __('Semester') }} {{ $semester->semester_number ?? '?' }} — {{ __('Assign students to classrooms') }}
                </p>
            </div>
        </div>

        {{-- Flash --}}
        @if(session('status'))
        <div class="mb-6 p-4 bg-emerald-50 dark:bg-emerald-900/20 border border-emerald-200 dark:border-emerald-800 rounded-2xl text-emerald-700 dark:text-emerald-300 text-sm">{{ session('status') }}</div>
        @endif
        @if(session('error'))
        <div class="mb-6 p-4 bg-rose-50 dark:bg-rose-900/20 border border-rose-200 dark:border-rose-800 rounded-2xl text-rose-700 dark:text-rose-300 text-sm">{{ session('error') }}</div>
        @endif

        @if($openedClassrooms->isEmpty())
        <div class="bg-white dark:bg-[#242526] rounded-[2rem] shadow-sm border border-gray-100 dark:border-[#3a3b3c] p-10 text-center text-gray-400">
            <i class="fas fa-door-closed text-3xl mb-3"></i>
            <p class="text-sm font-medium">{{ __('No classrooms opened for this term yet') }}</p>
            <a href="{{ route('admin.dashboard') }}" class="btn-app mt-4"><i class="fas fa-cog text-[10px]"></i> {{ __('Manage') }}</a>
        </div>
        @else

        {{-- Classroom cards --}}
        <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-5 gap-3 mb-6">
            @foreach($openedClassrooms as $oc)
            @php
                $isSelected = $selectedGradeId == $oc->grade_id && $selectedClassroomId == $oc->classroom_id;
                $count = $counts[$oc->grade_id . '-' . $oc->classroom_id]->total ?? 0;
            @endphp
            <a href="{{ route('admin.student-enrollments.index', ['grade_id' => $oc->grade_id, 'classroom_id' => $oc->classroom_id]) }}"
               class="block p-4 rounded-2xl border transition-all {{ $isSelected ? 'bg-indigo-50 dark:bg-indigo-900/20 border-indigo-200 dark:border-indigo-800' : 'bg-white dark:bg-[#242526] border-gray-100 dark:border-[#3a3b3c] hover:border-indigo-200' }}">
                <div class="text-sm font-extrabold {{ $isSelected ? 'text-indigo-600 dark:text-indigo-400' : 'text-gray-800 dark:text-white' }}">
                    {{ $oc->grade->name_th ?? '' }} / {{ $oc->classroom->name ?? '' }}
                </div>
                <div class="text-xs text-gray-400 mt-1"><i class="fas fa-user-graduate mr-1"></i>{{ $count }} {{ __('students') }}</div>
            </a>
            @endforeach
        </div>

        {{-- Student list in selected room --}}
        @if($selectedGradeId && $selectedClassroomId)
        <div class="bg-white dark:bg-[#242526] rounded-[2rem] shadow-sm border border-gray-100 dark:border-[#3a3b3c] p-6">
            <div class="flex flex-wrap items-center justify-between gap-3 mb-4">
                <h2 class="text-sm font-bold text-gray-400 dark:text-gray-500 uppercase tracking-widest">{{ __('Students in classroom') }} ({{ $enrollments->count() }})</h2>
                <button type="button" onclick="openAddModal()" class="btn-app"><i class="fas fa-user-plus text-[10px]"></i> {{ __('Add students') }}</button>
            </div>

            @if($enrollments->isEmpty())
            <p class="text-sm text-gray-400 py-6 text-center">{{ __('No students in this classroom yet') }}</p>
            @else
            <div class="overflow-x-auto">
                <table class="w-full" style="min-width:600px">
                    <thead>
                        <tr class="text-left text-[10px] font-bold text-gray-400 uppercase tracking-widest border-b border-gray-100 dark:border-[#3a3b3c]">
                            <th class="py-2 pr-3 w-12">#</th>
                            <th class="py-2 pr-3">{{ __('Student Code') }}</th>
                            <th class="py-2 pr-3">{{ __('Name') }}</th>
                            <th class="py-2 pr-3">{{ __('Enrolled date') }}</th>
                            <th class="py-2 text-right">{{ __('Action') }}</th>
                        </tr>
                    </thead>
                    <tbody>
                    @foreach($enrollments as $i => $enrollment)
                        <tr class="border-b border-gray-50 dark:border-[#3a3b3c]/50 text-sm text-gray-600 dark:text-gray-400">
                            <td class="py-3 pr-3 text-xs text-gray-400">{{ $i + 1 }}</td>
                            <td class="py-3 pr-3 font-bold text-xs">{{ $enrollment->student->student_code ?? '?' }}</td>
                            <td class="py-3 pr-3">
                                <div class="font-bold text-gray-800 dark:text-gray-200">{{ $enrollment->student->name_th ?? '?' }}</div>
                                @if($enrollment->student?->name_cn)<div class="text-[11px] text-gray-400">{{ $enrollment->student->name_cn }}</div>@endif
                            </td>
                            <td class="py-3 pr-3 text-xs">{{ $enrollment->enrolled_at?->format('d/m/Y') ?? '-' }}</td>
                            <td class="py-3 text-right whitespace-nowrap">
                                <button type="button" onclick="openMoveModal({{ $enrollment->id }}, '{{ addslashes($enrollment->student->name_th ?? '') }}')"
                                        class="inline-flex items-center justify-center w-8 h-8 rounded-lg bg-white dark:bg-[#242526] border border-gray-100 dark:border-[#3a3b3c] text-indigo-500 hover:bg-indigo-50 transition-all shadow-sm" title="{{ __('Move classroom') }}"><i class="fas fa-exchange-alt text-xs"></i></button>
                                <form action="{{ route('admin.student-enrollments.remove', $enrollment->id) }}" method="POST" class="inline" onsubmit="return confirm('{{ __('Remove this student from classroom?') }}')">
                                    @csrf
                                    <button type="submit" class="inline-flex items-center justify-center w-8 h-8 rounded-lg bg-white dark:bg-[#242526] border border-gray-100 dark:border-[#3a3b3c] text-rose-500 hover:bg-rose-50 transition-all shadow-sm" title="{{ __('Remove') }}"><i class="fas fa-user-minus text-xs"></i></button>
                                </form>
                            </td>
                        </tr>
                    @endforeach
                    </tbody>
                </table>
            </div>
            @endif
        </div>
        @else
        <div class="bg-white dark:bg-[#242526] rounded-[2rem] shadow-sm border border-gray-100 dark:border-[#3a3b3c] p-10 text-center text-gray-400">
            <i class="fas fa-hand-pointer text-2xl mb-3"></i>
            <p class="text-sm font-medium">{{ __('Select a classroom above to manage students') }}</p>
        </div>
        @endif
        @endif

    </div>
</div>

{{-- Add Students Modal --}}
<div id="addModal" class="fixed inset-0 z-50 hidden">
    <div class="absolute inset-0 bg-gray-500/75 dark:bg-black/75 backdrop-blur-sm" onclick="closeAddModal()"></div>
    <div class="relative flex items-center justify-center min-h-screen p-4">
        <div class="bg-white dark:bg-[#242526] rounded-[2rem] shadow-2xl border border-gray-100 dark:border-[#3a3b3c] w-full max-w-2xl p-6" style="max-height:85vh; overflow-y:auto">
            <h3 class="text-lg font-extrabold text-gray-900 dark:text-white mb-4">{{ __('Add students') }}</h3>
            <input type="text" id="studentSearchInput" placeholder="{{ __('Search by code, name...') }}" class="{{ $inputClass }} mb-3" oninput="debounceSearch()">
            <form action="{{ route('admin.student-enrollments.store') }}" method="POST">
                @csrf
                <input type="hidden" name="grade_id" value="{{ $selectedGradeId }}">
                <input type="hidden" name="classroom_id" value="{{ $selectedClassroomId }}">
                <div id="selectedInputs"></div>

                <div class="overflow-x-auto min-h-[200px]" id="studentSearchResults">
                    <p class="text-xs text-gray-400 text-center py-6">{{ __('Type to search unassigned students') }}</p>
                </div>

                {{-- Pagination --}}
                <div id="studentPagination" class="hidden flex-wrap items-center justify-between gap-2 mt-3 pt-3 border-t border-gray-100 dark:border-[#3a3b3c]">
                    <span id="studentPageInfo" class="text-xs text-gray-400"></span>
                    <div class="flex items-center gap-1">
                        <button type="button" id="studentPrevBtn" onclick="searchStudents(currentStudentPage - 1)"
                                class="inline-flex items-center px-3 py-1.5 rounded-lg border border-gray-200 dark:border-[#3a3b3c] text-xs font-bold text-gray-600 dark:text-gray-400 hover:bg-gray-50 dark:hover:bg-[#3a3b3c] disabled:opacity-40 disabled:cursor-not-allowed transition-all">
                            <i class="fas fa-chevron-left text-[10px] mr-1"></i>{{ __('Previous') }}
                        </button>
                        <button type="button" id="studentNextBtn" onclick="searchStudents(currentStudentPage + 1)"
                                class="inline-flex items-center px-3 py-1.5 rounded-lg border border-gray-200 dark:border-[#3a3b3c] text-xs font-bold text-gray-600 dark:text-gray-400 hover:bg-gray-50 dark:hover:bg-[#3a3b3c] disabled:opacity-40 disabled:cursor-not-allowed transition-all">
                            {{ __('Next') }}<i class="fas fa-chevron-right text-[10px] ml-1"></i>
                        </button>
                    </div>
                </div>

                <div class="flex items-center justify-between gap-2 mt-4">
                    <span class="text-xs text-gray-400"><span class="font-bold text-indigo-500" id="selectedCount">0</span> {{ __('Selected') }}</span>
                    <div class="flex gap-2">
                        <button type="button" onclick="closeAddModal()" class="inline-flex items-center px-5 py-2.5 border-2 border-gray-100 dark:border-[#3a3b3c] text-xs font-bold rounded-xl text-gray-600 dark:text-gray-400 bg-white dark:bg-[#242526]">{{ __('Cancel') }}</button>
                        <button type="submit" id="addSelectedBtn" disabled class="btn-app disabled:opacity-40 disabled:cursor-not-allowed"><i class="fas fa-user-plus text-[10px]"></i> {{ __('Add selected') }}</button>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>

{{-- Move Modal --}}
<div id="moveModal" class="fixed inset-0 z-50 hidden">
    <div class="absolute inset-0 bg-gray-500/75 dark:bg-black/75 backdrop-blur-sm" onclick="closeMoveModal()"></div>
    <div class="relative flex items-center justify-center min-h-screen p-4">
        <div class="bg-white dark:bg-[#242526] rounded-[2rem] shadow-2xl border border-gray-100 dark:border-[#3a3b3c] w-full max-w-md p-6">
            <h3 class="text-lg font-extrabold text-gray-900 dark:text-white mb-1">{{ __('Move classroom') }}</h3>
            <p id="moveStudentName" class="text-sm text-gray-500 dark:text-gray-400 mb-4"></p>
            <form id="moveForm" method="POST">
                @csrf
                <label class="block text-xs font-medium text-gray-500 dark:text-gray-400 mb-1">{{ __('Move to') }}</label>
                <select id="moveTarget" class="{{ $inputClass }} mb-3" onchange="syncMoveTarget()">
                    @foreach($openedClassrooms as $oc)
                    <option value="{{ $oc->grade_id }}-{{ $oc->classroom_id }}">{{ $oc->grade->name_th ?? '' }} / {{ $oc->classroom->name ?? '' }}</option>
                    @endforeach
                </select>
                <input type="hidden" name="grade_id" id="moveGradeId">
                <input type="hidden" name="classroom_id" id="moveClassroomId">
                <label class="block text-xs font-medium text-gray-500 dark:text-gray-400 mb-1">{{ __('Note') }}</label>
                <input type="text" name="note" maxlength="255" class="{{ $inputClass }} mb-4">
                <div class="flex justify-end gap-2">
                    <button type="button" onclick="closeMoveModal()" class="inline-flex items-center px-5 py-2.5 border-2 border-gray-100 dark:border-[#3a3b3c] text-xs font-bold rounded-xl text-gray-600 dark:text-gray-400 bg-white dark:bg-[#242526]">{{ __('Cancel') }}</button>
                    <button type="submit" class="btn-app"><i class="fas fa-exchange-alt text-[10px]"></i> {{ __('Move') }}</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
let addModalLoaded = false;
function openAddModal() {
    document.getElementById('addModal').classList.remove('hidden');
    document.body.style.overflow = 'hidden';
    if (!addModalLoaded) { addModalLoaded = true; searchStudents(1); }
}
function closeAddModal() { document.getElementById('addModal').classList.add('hidden'); document.body.style.overflow = 'auto'; }

let searchTimer = null;
function debounceSearch() {
    clearTimeout(searchTimer);
    searchTimer = setTimeout(() => searchStudents(1), 300);
}

let currentStudentPage = 1;
const selectedStudents = new Map(); // id -> true (จำที่เลือกไว้ข้ามหน้า)

async function searchStudents(page = 1) {
    const kw = document.getElementById('studentSearchInput').value;
    const res = await fetch(`{{ route('admin.student-enrollments.search') }}?keyword=` + encodeURIComponent(kw) + '&page=' + page);
    const result = await res.json();
    currentStudentPage = result.current_page;
    renderStudentTable(result);
}

function escapeHtml(str) {
    return String(str ?? '').replace(/[&<>"']/g, c => ({'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#39;'}[c]));
}

function renderStudentTable(p) {
    const container = document.getElementById('studentSearchResults');
    const pagination = document.getElementById('studentPagination');

    if (!p.data.length) {
        container.innerHTML = '<p class="text-xs text-gray-400 text-center py-6">{{ __('No unassigned students found') }}</p>';
        pagination.classList.add('hidden');
        pagination.classList.remove('flex');
        return;
    }

    const allChecked = p.data.every(s => selectedStudents.has(s.id));
    container.innerHTML = `
        <table class="w-full" style="min-width:480px">
            <thead>
                <tr class="text-left text-[10px] font-bold text-gray-400 uppercase tracking-widest border-b border-gray-100 dark:border-[#3a3b3c]">
                    <th class="py-2 pr-3 w-10">
                        <input type="checkbox" ${allChecked ? 'checked' : ''} onchange="toggleAllOnPage(this)"
                               class="w-4 h-4 rounded border-gray-300 text-indigo-600 focus:ring-indigo-500" title="{{ __('Add selected') }}">
                    </th>
                    <th class="py-2 pr-3 w-12">#</th>
                    <th class="py-2 pr-3">{{ __('Student Code') }}</th>
                    <th class="py-2 pr-3">{{ __('Name') }}</th>
                    <th class="py-2">{{ __('Chinese Name') }}</th>
                </tr>
            </thead>
            <tbody>
                ${p.data.map((s, i) => `
                <tr class="border-b border-gray-50 dark:border-[#3a3b3c]/50 hover:bg-gray-50 dark:hover:bg-[#3a3b3c]/50 cursor-pointer" onclick="if (event.target.tagName !== 'INPUT') this.querySelector('input').click()">
                    <td class="py-2.5 pr-3">
                        <input type="checkbox" data-student-cb value="${s.id}" ${selectedStudents.has(s.id) ? 'checked' : ''}
                               onchange="toggleStudent(this)" class="w-4 h-4 rounded border-gray-300 text-indigo-600 focus:ring-indigo-500">
                    </td>
                    <td class="py-2.5 pr-3 text-xs text-gray-400">${p.from + i}</td>
                    <td class="py-2.5 pr-3 text-xs font-bold text-gray-500">${escapeHtml(s.student_code)}</td>
                    <td class="py-2.5 pr-3 text-sm font-medium text-gray-800 dark:text-gray-200">${escapeHtml(s.name_th)}</td>
                    <td class="py-2.5 text-xs text-gray-400">${escapeHtml(s.name_cn ?? '-')}</td>
                </tr>`).join('')}
            </tbody>
        </table>`;

    document.getElementById('studentPageInfo').textContent = `${p.from}-${p.to} / ${p.total} {{ __('students') }}`;
    document.getElementById('studentPrevBtn').disabled = p.current_page <= 1;
    document.getElementById('studentNextBtn').disabled = p.current_page >= p.last_page;
    pagination.classList.remove('hidden');
    pagination.classList.add('flex');
}

function toggleStudent(cb) {
    const id = parseInt(cb.value);
    if (cb.checked) selectedStudents.set(id, true); else selectedStudents.delete(id);
    syncSelectedStudents();
}

function toggleAllOnPage(headerCb) {
    document.querySelectorAll('[data-student-cb]').forEach(cb => {
        cb.checked = headerCb.checked;
        const id = parseInt(cb.value);
        if (headerCb.checked) selectedStudents.set(id, true); else selectedStudents.delete(id);
    });
    syncSelectedStudents();
}

function syncSelectedStudents() {
    document.getElementById('selectedInputs').innerHTML = [...selectedStudents.keys()]
        .map(id => `<input type="hidden" name="student_ids[]" value="${id}">`).join('');
    document.getElementById('selectedCount').textContent = selectedStudents.size;
    document.getElementById('addSelectedBtn').disabled = selectedStudents.size === 0;
}

function openMoveModal(enrollmentId, name) {
    document.getElementById('moveStudentName').textContent = name;
    document.getElementById('moveForm').action = '{{ url('/admin/student-enrollments') }}/' + enrollmentId + '/move';
    syncMoveTarget();
    document.getElementById('moveModal').classList.remove('hidden');
    document.body.style.overflow = 'hidden';
}
function closeMoveModal() { document.getElementById('moveModal').classList.add('hidden'); document.body.style.overflow = 'auto'; }
function syncMoveTarget() {
    const [gradeId, classroomId] = document.getElementById('moveTarget').value.split('-');
    document.getElementById('moveGradeId').value = gradeId;
    document.getElementById('moveClassroomId').value = classroomId;
}
</script>
@endsection
