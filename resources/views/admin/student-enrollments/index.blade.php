<x-layouts.admin :header="__('Student Enrollment')"
    :subheader="__('Academic Year') . ' ' . ($academicYear->year ?? '?') . ' / ' . __('Semester') . ' ' . ($semester->semester_number ?? '?') . ' — ' . __('Assign students to classrooms')">
    <x-slot name="actions">
        <x-button variant="secondary" icon="arrow-left" :href="route('admin.students.index')">{{ __('Back') }}</x-button>
    </x-slot>

    @if($openedClassrooms->isEmpty())
    <x-card class="text-center text-slate-400 py-10">
        <x-icon name="door" class="h-10 w-10 mx-auto mb-3" />
        <p class="text-sm font-medium">{{ __('No classrooms opened for this term yet') }}</p>
        <a href="{{ route('admin.dashboard') }}" class="btn-primary mt-4"><x-icon name="cog" class="h-4 w-4" /> {{ __('Manage') }}</a>
    </x-card>
    @else

    {{-- Classroom cards --}}
    <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-5 gap-3 mb-6">
        @foreach($openedClassrooms as $oc)
        @php
            $isSelected = $selectedGradeId == $oc->grade_id && $selectedClassroomId == $oc->classroom_id;
            $count = $counts[$oc->grade_id . '-' . $oc->classroom_id]->total ?? 0;
        @endphp
        <a href="{{ route('admin.student-enrollments.index', ['grade_id' => $oc->grade_id, 'classroom_id' => $oc->classroom_id]) }}"
           class="block p-4 rounded-2xl border transition {{ $isSelected ? 'bg-brand-50 border-brand-200' : 'bg-white border-slate-100 shadow-card hover:border-brand-200' }}">
            <div class="text-sm font-semibold {{ $isSelected ? 'text-brand-700' : 'text-slate-800' }}">
                {{ $oc->grade->name_th ?? '' }} / {{ $oc->classroom->name ?? '' }}
            </div>
            <div class="text-xs text-slate-400 mt-1 flex items-center gap-1"><x-icon name="user" class="h-3.5 w-3.5" />{{ $count }} {{ __('students') }}</div>
        </a>
        @endforeach
    </div>

    {{-- Student list in selected room --}}
    @if($selectedGradeId && $selectedClassroomId)
    <x-card>
        <div class="flex flex-wrap items-center justify-between gap-3 mb-4">
            <h2 class="text-xs font-semibold text-slate-500 uppercase tracking-wide">{{ __('Students in classroom') }} ({{ $enrollments->count() }})</h2>
            <button type="button" onclick="openAddModal()" class="btn-primary"><x-icon name="plus" class="h-4 w-4" /> {{ __('Add students') }}</button>
        </div>

        @if($enrollments->isEmpty())
        <p class="text-sm text-slate-400 py-6 text-center">{{ __('No students in this classroom yet') }}</p>
        @else
        <div class="overflow-x-auto">
            <table class="w-full text-sm" style="min-width:600px">
                <thead>
                    <tr class="text-left text-xs font-medium text-slate-500 uppercase tracking-wide border-b border-slate-100">
                        <th class="py-2 pr-3 w-12">#</th>
                        <th class="py-2 pr-3">{{ __('Student Code') }}</th>
                        <th class="py-2 pr-3">{{ __('Name') }}</th>
                        <th class="py-2 pr-3">{{ __('Enrolled date') }}</th>
                        <th class="py-2 text-right">{{ __('Action') }}</th>
                    </tr>
                </thead>
                <tbody>
                @foreach($enrollments as $i => $enrollment)
                    <tr class="border-b border-slate-50 text-sm text-slate-600">
                        <td class="py-3 pr-3 text-xs text-slate-400">{{ $i + 1 }}</td>
                        <td class="py-3 pr-3 font-semibold text-xs">{{ $enrollment->student->student_code ?? '?' }}</td>
                        <td class="py-3 pr-3">
                            <div class="font-semibold text-slate-800">{{ $enrollment->student->name_th ?? '?' }}</div>
                            @if($enrollment->student?->name_cn)<div class="text-[11px] text-slate-400">{{ $enrollment->student->name_cn }}</div>@endif
                        </td>
                        <td class="py-3 pr-3 text-xs">{{ $enrollment->enrolled_at?->format('d/m/Y') ?? '-' }}</td>
                        <td class="py-3 text-right whitespace-nowrap">
                            <button type="button" onclick="openMoveModal({{ $enrollment->id }}, '{{ addslashes($enrollment->student->name_th ?? '') }}')"
                                    class="inline-flex items-center justify-center w-8 h-8 rounded-lg bg-white border border-slate-200 text-brand-600 hover:bg-brand-50 transition shadow-sm" title="{{ __('Move classroom') }}"><x-icon name="layers" class="h-4 w-4" /></button>
                            <form action="{{ route('admin.student-enrollments.remove', $enrollment->id) }}" method="POST" class="inline" onsubmit="return confirm('{{ __('Remove this student from classroom?') }}')">
                                @csrf
                                <button type="submit" class="inline-flex items-center justify-center w-8 h-8 rounded-lg bg-white border border-slate-200 text-red-600 hover:bg-red-50 transition shadow-sm" title="{{ __('Remove') }}"><x-icon name="trash" class="h-4 w-4" /></button>
                            </form>
                        </td>
                    </tr>
                @endforeach
                </tbody>
            </table>
        </div>
        @endif
    </x-card>
    @else
    <x-card class="text-center text-slate-400 py-10">
        <x-icon name="filter" class="h-9 w-9 mx-auto mb-3" />
        <p class="text-sm font-medium">{{ __('Select a classroom above to manage students') }}</p>
    </x-card>
    @endif
    @endif

    {{-- Add Students Modal --}}
    <div id="addModal" class="fixed inset-0 z-50 hidden">
        <div class="absolute inset-0 bg-slate-900/50 backdrop-blur-sm" onclick="closeAddModal()"></div>
        <div class="relative flex items-center justify-center min-h-screen p-4">
            <div class="bg-white rounded-2xl shadow-2xl border border-slate-100 w-full max-w-2xl p-6" style="max-height:85vh; overflow-y:auto">
                <h3 class="text-lg font-semibold text-slate-900 mb-4">{{ __('Add students') }}</h3>
                <input type="text" id="studentSearchInput" placeholder="{{ __('Search by code, name...') }}" class="form-input mb-3" oninput="debounceSearch()">
                <form action="{{ route('admin.student-enrollments.store') }}" method="POST">
                    @csrf
                    <input type="hidden" name="grade_id" value="{{ $selectedGradeId }}">
                    <input type="hidden" name="classroom_id" value="{{ $selectedClassroomId }}">
                    <div id="selectedInputs"></div>

                    <div class="overflow-x-auto min-h-[200px]" id="studentSearchResults">
                        <p class="text-xs text-slate-400 text-center py-6">{{ __('Type to search unassigned students') }}</p>
                    </div>

                    {{-- Pagination --}}
                    <div id="studentPagination" class="hidden flex-wrap items-center justify-between gap-2 mt-3 pt-3 border-t border-slate-100">
                        <span id="studentPageInfo" class="text-xs text-slate-400"></span>
                        <div class="flex items-center gap-1">
                            <button type="button" id="studentPrevBtn" onclick="searchStudents(currentStudentPage - 1)"
                                    class="inline-flex items-center px-3 py-1.5 rounded-lg border border-slate-200 text-xs font-medium text-slate-600 hover:bg-slate-50 disabled:opacity-40 disabled:cursor-not-allowed transition">
                                <x-icon name="arrow-left" class="h-3.5 w-3.5 mr-1" />{{ __('Previous') }}
                            </button>
                            <button type="button" id="studentNextBtn" onclick="searchStudents(currentStudentPage + 1)"
                                    class="inline-flex items-center px-3 py-1.5 rounded-lg border border-slate-200 text-xs font-medium text-slate-600 hover:bg-slate-50 disabled:opacity-40 disabled:cursor-not-allowed transition">
                                {{ __('Next') }}<x-icon name="arrow-left" class="h-3.5 w-3.5 ml-1 rotate-180" />
                            </button>
                        </div>
                    </div>

                    <div class="flex items-center justify-between gap-2 mt-4">
                        <span class="text-xs text-slate-400"><span class="font-semibold text-brand-600" id="selectedCount">0</span> {{ __('Selected') }}</span>
                        <div class="flex gap-2">
                            <button type="button" onclick="closeAddModal()" class="btn-secondary">{{ __('Cancel') }}</button>
                            <button type="submit" id="addSelectedBtn" disabled class="btn-primary disabled:opacity-40 disabled:cursor-not-allowed"><x-icon name="plus" class="h-4 w-4" /> {{ __('Add selected') }}</button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>

    {{-- Move Modal --}}
    <div id="moveModal" class="fixed inset-0 z-50 hidden">
        <div class="absolute inset-0 bg-slate-900/50 backdrop-blur-sm" onclick="closeMoveModal()"></div>
        <div class="relative flex items-center justify-center min-h-screen p-4">
            <div class="bg-white rounded-2xl shadow-2xl border border-slate-100 w-full max-w-md p-6">
                <h3 class="text-lg font-semibold text-slate-900 mb-1">{{ __('Move classroom') }}</h3>
                <p id="moveStudentName" class="text-sm text-slate-500 mb-4"></p>
                <form id="moveForm" method="POST">
                    @csrf
                    <label class="form-label">{{ __('Move to') }}</label>
                    <select id="moveTarget" class="form-select mb-3" onchange="syncMoveTarget()">
                        @foreach($openedClassrooms as $oc)
                        <option value="{{ $oc->grade_id }}-{{ $oc->classroom_id }}">{{ $oc->grade->name_th ?? '' }} / {{ $oc->classroom->name ?? '' }}</option>
                        @endforeach
                    </select>
                    <input type="hidden" name="grade_id" id="moveGradeId">
                    <input type="hidden" name="classroom_id" id="moveClassroomId">
                    <label class="form-label">{{ __('Note') }}</label>
                    <input type="text" name="note" maxlength="255" class="form-input mb-4">
                    <div class="flex justify-end gap-2">
                        <button type="button" onclick="closeMoveModal()" class="btn-secondary">{{ __('Cancel') }}</button>
                        <button type="submit" class="btn-primary"><x-icon name="layers" class="h-4 w-4" /> {{ __('Move') }}</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    @push('scripts')
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
            container.innerHTML = '<p class="text-xs text-slate-400 text-center py-6">{{ __('No unassigned students found') }}</p>';
            pagination.classList.add('hidden');
            pagination.classList.remove('flex');
            return;
        }

        const allChecked = p.data.every(s => selectedStudents.has(s.id));
        container.innerHTML = `
            <table class="w-full" style="min-width:480px">
                <thead>
                    <tr class="text-left text-xs font-medium text-slate-500 uppercase tracking-wide border-b border-slate-100">
                        <th class="py-2 pr-3 w-10">
                            <input type="checkbox" ${allChecked ? 'checked' : ''} onchange="toggleAllOnPage(this)"
                                   class="w-4 h-4 rounded border-slate-300 text-brand-600 focus:ring-brand-400" title="{{ __('Add selected') }}">
                        </th>
                        <th class="py-2 pr-3 w-12">#</th>
                        <th class="py-2 pr-3">{{ __('Student Code') }}</th>
                        <th class="py-2 pr-3">{{ __('Name') }}</th>
                        <th class="py-2">{{ __('Chinese Name') }}</th>
                    </tr>
                </thead>
                <tbody>
                    ${p.data.map((s, i) => `
                    <tr class="border-b border-slate-50 hover:bg-slate-50 cursor-pointer" onclick="if (event.target.tagName !== 'INPUT') this.querySelector('input').click()">
                        <td class="py-2.5 pr-3">
                            <input type="checkbox" data-student-cb value="${s.id}" ${selectedStudents.has(s.id) ? 'checked' : ''}
                                   onchange="toggleStudent(this)" class="w-4 h-4 rounded border-slate-300 text-brand-600 focus:ring-brand-400">
                        </td>
                        <td class="py-2.5 pr-3 text-xs text-slate-400">${p.from + i}</td>
                        <td class="py-2.5 pr-3 text-xs font-semibold text-slate-500">${escapeHtml(s.student_code)}</td>
                        <td class="py-2.5 pr-3 text-sm font-medium text-slate-800">${escapeHtml(s.name_th)}</td>
                        <td class="py-2.5 text-xs text-slate-400">${escapeHtml(s.name_cn ?? '-')}</td>
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
    @endpush
</x-layouts.admin>
