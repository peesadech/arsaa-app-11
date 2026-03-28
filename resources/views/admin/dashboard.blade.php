@extends('layouts.app')

@push('styles')
<style>
    a.group.block:hover { text-decoration: none !important; }
    @keyframes fadeInUp {
        from { opacity: 0; transform: translateY(12px); }
        to   { opacity: 1; transform: translateY(0); }
    }
</style>
@endpush

@section('content')
<div class="min-h-screen bg-gray-50/50 dark:bg-[#18191a] py-8 px-4 sm:px-6 lg:px-8 transition-colors duration-300">
    <div class="max-w-7xl mx-auto space-y-8">

        {{-- Stats Row --}}
        <div class="flex gap-4">
            {{-- Opened Grades Block (30%) --}}
            <div class="w-[30%] bg-white dark:bg-[#242526] rounded-2xl shadow-sm border border-gray-100 dark:border-[#3a3b3c] px-5 py-4 flex flex-col gap-1">
                <div class="flex items-center justify-between">
                    <p class="text-xs font-bold text-gray-400 uppercase tracking-widest">ระดับชั้นที่เปิดสอน</p>
                    @if($currentYear && $currentSemester)
                    <button onclick="openAddGradeModal()" class="btn-app">
                        <i class="fas fa-cog text-[10px]"></i> จัดการ
                    </button>
                    @endif
                </div>
                <div class="flex items-center gap-4">
                    <div class="flex items-baseline gap-1.5">
                        <span id="openedGradeCount" class="text-3xl font-bold text-slate-800 dark:text-white">{{ $openedGrades->count() }}</span>
                        <span class="text-xs text-gray-400">ระดับชั้น</span>
                    </div>
                    <div class="w-px h-6 bg-gray-200 dark:bg-[#3a3b3c]"></div>
                    <div class="flex items-baseline gap-1.5">
                        <span id="openedClassroomCount" class="text-3xl font-bold text-slate-800 dark:text-white">{{ $openedClassroomCount }}</span>
                        <span class="text-xs text-gray-400">ห้องเรียน</span>
                    </div>
                </div>
            </div>

            {{-- Opened Courses Block (30%) --}}
            <div class="w-[30%] bg-white dark:bg-[#242526] rounded-2xl shadow-sm border border-gray-100 dark:border-[#3a3b3c] px-5 py-4 flex flex-col gap-1">
                <div class="flex items-center justify-between">
                    <p class="text-xs font-bold text-gray-400 uppercase tracking-widest">รายวิชาที่เปิดสอน</p>
                    @if($currentYear && $currentSemester)
                    <a href="{{ route('admin.opened-courses.index') }}" class="btn-app" id="btn-open-course">
                        <i class="fas fa-cog text-[10px]"></i> จัดการ
                    </a>
                    @endif
                </div>
                <div class="flex items-center gap-4">
                    <div class="flex items-baseline gap-1.5">
                        <span id="openedCourseCount" class="text-3xl font-bold text-slate-800 dark:text-white">{{ $openedCourseCount }}</span>
                        <span class="text-xs text-gray-400">รายวิชา</span>
                    </div>
                    <div class="w-px h-6 bg-gray-200 dark:bg-[#3a3b3c]"></div>
                    <div class="flex items-baseline gap-1.5">
                        <span id="openedCourseTotalCount" class="text-3xl font-bold text-slate-800 dark:text-white">{{ $openedCourseTotalCount }}</span>
                        <span class="text-xs text-gray-400">ห้องเรียน</span>
                    </div>
                </div>
            </div>

            {{-- Yearly Schedule Block (30%) --}}
            <div class="w-[30%] bg-white dark:bg-[#242526] rounded-2xl shadow-sm border border-gray-100 dark:border-[#3a3b3c] px-5 py-4 flex flex-col gap-1">
                <div class="flex items-center justify-between">
                    <p class="text-xs font-bold text-gray-400 uppercase tracking-widest">ตารางสอนประจำภาค</p>
                    @if($currentYear && $currentSemester)
                    <a href="{{ route('admin.yearly-schedule.index') }}" class="btn-app">
                        <i class="fas fa-cog text-[10px]"></i> จัดการ
                    </a>
                    @endif
                </div>
                <div class="flex items-center gap-4">
                    <div class="flex items-baseline gap-1.5">
                        <span class="text-3xl font-bold text-slate-800 dark:text-white">{{ $yearlyScheduleConfigured }}</span>
                        <span class="text-xs text-gray-400">ตั้งค่าแล้ว</span>
                    </div>
                    <div class="w-px h-6 bg-gray-200 dark:bg-[#3a3b3c]"></div>
                    <div class="flex items-baseline gap-1.5">
                        <span class="text-3xl font-bold text-slate-800 dark:text-white">{{ $yearlyScheduleTotal }}</span>
                        <span class="text-xs text-gray-400">ทั้งหมด</span>
                    </div>
                </div>
                @if($currentYear && $currentSemester)
                <div class="mt-1">
                    @if($yearlyScheduleTotal > 0 && $yearlyScheduleConfigured >= $yearlyScheduleTotal)
                        <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full bg-emerald-50 text-emerald-600 text-[11px] font-bold">
                            <i class="fas fa-check-circle text-[9px]"></i> ตั้งค่าครบแล้ว
                        </span>
                    @elseif($yearlyScheduleConfigured > 0)
                        <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full bg-amber-50 text-amber-600 text-[11px] font-bold">
                            <i class="fas fa-exclamation-circle text-[9px]"></i> ยังตั้งค่าไม่ครบ
                        </span>
                    @else
                        <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full bg-gray-100 dark:bg-[#3a3b3c] text-gray-400 text-[11px]">
                            ยังไม่ได้ตั้งค่า
                        </span>
                    @endif
                </div>
                @endif
            </div>
        </div>

        {{-- Footer --}}
        <div class="pt-4 border-t border-gray-100 dark:border-[#3a3b3c] flex flex-col md:flex-row items-center justify-between gap-4 text-xs font-bold text-gray-400 dark:text-gray-500 uppercase tracking-widest">
            <div class="flex items-center">
                <span class="w-2 h-2 rounded-full bg-emerald-500 mr-2 shadow-sm shadow-emerald-200 animate-pulse"></span>
                {{ __('All Systems Operational') }}
            </div>
            <div class="flex items-center space-x-6">
                <span>{{ __('Setting') }}</span>
                <span class="w-1 h-1 rounded-full bg-gray-300"></span>
                <span>v2.0.4</span>
            </div>
        </div>

    </div>
</div>

{{-- Modal: Manage Grades --}}
<div id="addGradeModal" style="display:none" class="fixed inset-0 z-[9999] flex items-center justify-center bg-black/50 backdrop-blur-sm" onclick="if(event.target===this)closeAddGradeModal()">
    <div class="bg-white dark:bg-[#242526] rounded-2xl shadow-2xl w-full max-w-2xl mx-4 overflow-hidden flex flex-col" style="max-height:85vh">
        {{-- Header --}}
        <div class="px-5 py-4 border-b border-gray-100 dark:border-[#3a3b3c] flex items-center justify-between shrink-0">
            <div>
                <h3 class="font-bold text-slate-800 dark:text-white text-base">จัดการระดับชั้นที่เปิดสอน</h3>
                <p class="text-xs text-gray-400 mt-0.5">คลิก <i class="fas fa-chevron-right text-[9px]"></i> เพื่อจัดการห้องเรียนของแต่ละระดับชั้น</p>
            </div>
            <button onclick="closeAddGradeModal()" class="w-8 h-8 rounded-full hover:bg-gray-100 dark:hover:bg-[#3a3b3c] flex items-center justify-center text-gray-400">
                <i class="fas fa-times text-sm"></i>
            </button>
        </div>

        {{-- Table --}}
        <div class="overflow-y-auto flex-1">
            <table class="w-full text-sm">
                <thead class="sticky top-0 z-10 bg-gray-50 dark:bg-[#18191a] border-b border-gray-100 dark:border-[#3a3b3c]">
                    <tr>
                        <th class="px-4 py-2.5 w-10">
                            <input type="checkbox" id="checkAll" onchange="toggleCheckAll(this)" class="rounded accent-indigo-600 cursor-pointer">
                        </th>
                        <th class="px-4 py-2.5 text-left text-xs font-bold text-gray-400 uppercase tracking-wider">ระดับชั้น / ห้องเรียน</th>
                        <th class="px-4 py-2.5 text-center text-xs font-bold text-gray-400 uppercase tracking-wider w-20">สถานะ</th>
                        <th class="w-10"></th>
                    </tr>
                </thead>
                <tbody id="gradeTableBody">
                    <tr><td colspan="4" class="text-center py-8 text-gray-400 text-xs">กำลังโหลด...</td></tr>
                </tbody>
            </table>
        </div>

        {{-- Footer --}}
        <div class="px-5 py-3 border-t border-gray-100 dark:border-[#3a3b3c] flex items-center justify-between shrink-0">
            <span id="selectedCount" class="text-xs text-gray-400">เปลี่ยนแปลง 0 รายการ</span>
            <div class="flex gap-2">
                <button onclick="closeAddGradeModal()" class="btn-app" style="background:#6b7280;border-color:#6b7280;">ปิด</button>
                <button id="saveGradeBtn" onclick="saveGrades()" disabled class="btn-app">บันทึก</button>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
const ACADEMIC_YEAR_ID = {{ $academicYearId ?? 'null' }};
const SEMESTER_ID      = {{ $semesterId ?? 'null' }};
const CSRF             = '{{ csrf_token() }}';

const OPEN_BADGE  = `<span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full bg-emerald-50 text-emerald-600 text-[11px] font-bold"><i class="fas fa-check text-[9px]"></i> เปิด</span>`;
const CLOSE_BADGE = `<span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full bg-gray-100 dark:bg-[#3a3b3c] text-gray-400 text-[11px]">ปิด</span>`;

function openAddGradeModal() {
    document.getElementById('addGradeModal').style.display = 'flex';
    loadGradeTable();
}

function closeAddGradeModal() {
    document.getElementById('addGradeModal').style.display = 'none';
}

function loadGradeTable() {
    const tbody = document.getElementById('gradeTableBody');
    tbody.innerHTML = '<tr><td colspan="4" class="text-center py-8 text-gray-400 text-xs">กำลังโหลด...</td></tr>';
    document.getElementById('checkAll').checked = false;
    updateSelectedCount();

    fetch(`{{ route('admin.dashboard.available-grades') }}?academic_year_id=${ACADEMIC_YEAR_ID}&semester_id=${SEMESTER_ID}`, {
        headers: { 'X-Requested-With': 'XMLHttpRequest' }
    })
    .then(r => r.json())
    .then(grades => {
        if (grades.length === 0) {
            tbody.innerHTML = '<tr><td colspan="4" class="text-center py-8 text-gray-400 text-xs">ไม่มีข้อมูลระดับชั้น</td></tr>';
            return;
        }
        tbody.innerHTML = grades.map(g => buildGradeRows(g)).join('');
        updateSelectedCount();
    });
}

function buildGradeRows(g) {
    const hasRooms = g.classrooms && g.classrooms.length > 0;

    let html = `
        <tr class="hover:bg-gray-50 dark:hover:bg-[#18191a] transition-colors border-b border-gray-100 dark:border-[#3a3b3c]">
            <td class="px-4 py-3 w-10">
                <input type="checkbox"
                    class="grade-cb rounded accent-indigo-600 cursor-pointer"
                    value="${g.id}"
                    data-opened-id="${g.opened_id ?? ''}"
                    data-was-opened="${g.is_opened ? '1' : '0'}"
                    ${g.is_opened ? 'checked' : ''}
                    onchange="onGradeChange(${g.id}, this)">
            </td>
            <td class="px-4 py-3">
                <div class="font-medium text-slate-700 dark:text-slate-200">
                    ${g.name_th} <span class="text-gray-400 font-normal text-xs ml-1">${g.name_en}</span>
                </div>
                ${(() => {
                    const opened = g.classrooms?.filter(c => c.is_opened) ?? [];
                    if (opened.length === 0) return '';
                    return `<div class="flex flex-wrap gap-1 mt-1.5">${opened.map(c =>
                        `<span class="inline-flex items-center px-1.5 py-0.5 rounded text-[10px] bg-sky-50 dark:bg-sky-900/20 text-sky-500 dark:text-sky-400 font-medium border border-sky-100 dark:border-sky-800/40">${c.name}</span>`
                    ).join('')}</div>`;
                })()}
            </td>
            <td class="px-4 py-3 text-center">${g.is_opened ? OPEN_BADGE : CLOSE_BADGE}</td>
            <td class="pr-3 py-3 w-10 text-right">
                ${hasRooms ? `
                <button type="button" onclick="toggleClassrooms(${g.id})"
                    class="w-6 h-6 rounded flex items-center justify-center text-gray-400 hover:text-gray-600 hover:bg-gray-100 dark:hover:bg-[#3a3b3c] transition-colors ml-auto">
                    <i id="chev-${g.id}" class="fas fa-chevron-right text-[10px] transition-transform duration-150"></i>
                </button>` : ''}
            </td>
        </tr>`;

    if (hasRooms) {
        const allChecked = g.classrooms.every(c => c.is_opened);

        // Check-all sub-header
        html += `
        <tr class="cr-${g.id}" style="display:none">
            <td colspan="4" class="pl-10 pr-4 py-2 bg-indigo-50/50 dark:bg-[#1e1e2e]/50 border-b border-indigo-100/50 dark:border-[#2a2a3a]">
                <label class="flex items-center gap-2 cursor-pointer select-none">
                    <input type="checkbox" id="ca-${g.id}"
                        ${allChecked ? 'checked' : ''}
                        onchange="toggleGradeClassrooms(${g.id}, this)"
                        class="rounded accent-indigo-600 cursor-pointer">
                    <span class="text-[11px] font-bold text-indigo-400 uppercase tracking-wider">เลือกทุกห้อง</span>
                </label>
            </td>
        </tr>`;

        g.classrooms.forEach(c => {
            html += `
        <tr class="cr-${g.id}" style="display:none">
            <td class="pl-10 py-2.5 w-10 bg-gray-50/40 dark:bg-[#1c1c1c]/60">
                <input type="checkbox"
                    class="classroom-cb rounded accent-indigo-600 cursor-pointer"
                    value="${c.id}"
                    data-grade-id="${g.id}"
                    data-was-opened="${c.is_opened ? '1' : '0'}"
                    ${c.is_opened ? 'checked' : ''}
                    onchange="onClassroomChange(${g.id})">
            </td>
            <td class="py-2.5 text-sm text-slate-600 dark:text-slate-300 bg-gray-50/40 dark:bg-[#1c1c1c]/60">
                <i class="fas fa-door-open text-gray-300 dark:text-gray-600 text-xs mr-1.5"></i>${c.name}
            </td>
            <td class="py-2.5 text-center bg-gray-50/40 dark:bg-[#1c1c1c]/60">${c.is_opened ? OPEN_BADGE : CLOSE_BADGE}</td>
            <td class="bg-gray-50/40 dark:bg-[#1c1c1c]/60 border-b border-gray-100 dark:border-[#2a2a2a]"></td>
        </tr>`;
        });
    }

    return html;
}

function toggleClassrooms(gradeId) {
    const rows    = document.querySelectorAll(`.cr-${gradeId}`);
    const chevron = document.getElementById(`chev-${gradeId}`);
    const show    = rows[0]?.style.display === 'none';
    rows.forEach(r => r.style.display = show ? '' : 'none');
    if (chevron) chevron.style.transform = show ? 'rotate(90deg)' : '';
}

function onGradeChange(gradeId, cb) {
    if (!cb.checked) {
        // Uncheck all classrooms for this grade (they'll be deleted with the grade)
        document.querySelectorAll(`.classroom-cb[data-grade-id="${gradeId}"]`)
            .forEach(room => room.checked = false);
        const masterCb = document.getElementById(`ca-${gradeId}`);
        if (masterCb) masterCb.checked = false;
        // Collapse classroom rows
        document.querySelectorAll(`.cr-${gradeId}`).forEach(r => r.style.display = 'none');
        const chevron = document.getElementById(`chev-${gradeId}`);
        if (chevron) chevron.style.transform = '';
    } else {
        // Check all classrooms and expand
        const roomCbs = document.querySelectorAll(`.classroom-cb[data-grade-id="${gradeId}"]`);
        roomCbs.forEach(room => room.checked = true);
        const masterCb = document.getElementById(`ca-${gradeId}`);
        if (masterCb) masterCb.checked = roomCbs.length > 0;
        document.querySelectorAll(`.cr-${gradeId}`).forEach(r => r.style.display = '');
        const chevron = document.getElementById(`chev-${gradeId}`);
        if (chevron) chevron.style.transform = 'rotate(90deg)';
    }
    updateSelectedCount();
}

function toggleGradeClassrooms(gradeId, master) {
    document.querySelectorAll(`.classroom-cb[data-grade-id="${gradeId}"]`)
        .forEach(cb => cb.checked = master.checked);
    updateSelectedCount();
}

function onClassroomChange(gradeId) {
    const all     = [...document.querySelectorAll(`.classroom-cb[data-grade-id="${gradeId}"]`)];
    const masterCb = document.getElementById(`ca-${gradeId}`);
    if (masterCb) masterCb.checked = all.length > 0 && all.every(cb => cb.checked);
    updateSelectedCount();
}

function toggleCheckAll(master) {
    document.querySelectorAll('.grade-cb').forEach(cb => cb.checked = master.checked);
    // Mirror to all classrooms and their check-all masters
    document.querySelectorAll('.classroom-cb').forEach(cb => cb.checked = master.checked);
    document.querySelectorAll('[id^="ca-"]').forEach(ca => ca.checked = master.checked);
    updateSelectedCount();
}

function updateSelectedCount() {
    const gradeCbs     = [...document.querySelectorAll('.grade-cb')];
    const gradeChanged = gradeCbs.filter(cb => (cb.checked ? '1' : '0') !== cb.dataset.wasOpened).length;

    // Only count classroom changes for grades that are currently open
    const openedGradeIds = new Set(gradeCbs.filter(cb => cb.checked).map(cb => cb.value));
    const classroomCbs   = [...document.querySelectorAll('.classroom-cb')]
        .filter(cb => openedGradeIds.has(cb.dataset.gradeId));
    const classChanged   = classroomCbs.filter(cb => (cb.checked ? '1' : '0') !== cb.dataset.wasOpened).length;
    const total = gradeChanged + classChanged;

    document.getElementById('selectedCount').textContent = `เปลี่ยนแปลง ${total} รายการ`;
    document.getElementById('saveGradeBtn').disabled = total === 0;

    const checkedG = gradeCbs.filter(cb => cb.checked).length;
    document.getElementById('checkAll').checked = gradeCbs.length > 0 && checkedG === gradeCbs.length;
}

function saveGrades() {
    const gradeCbs     = [...document.querySelectorAll('.grade-cb')];
    const classroomCbs = [...document.querySelectorAll('.classroom-cb')];
    const toOpen  = gradeCbs.filter(cb => cb.checked && cb.dataset.wasOpened === '0');
    const toClose = gradeCbs.filter(cb => !cb.checked && cb.dataset.wasOpened === '1');
    const classroomChanged = classroomCbs.some(cb => (cb.checked ? '1' : '0') !== cb.dataset.wasOpened);
    const openedGradeCbs   = gradeCbs.filter(cb => cb.checked);

    if (toOpen.length === 0 && toClose.length === 0 && !classroomChanged) return;

    const btn = document.getElementById('saveGradeBtn');
    btn.disabled = true;
    btn.textContent = 'กำลังบันทึก...';

    const openRequests = toOpen.map(cb =>
        fetch('{{ route('admin.dashboard.open-grade') }}', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': CSRF, 'X-Requested-With': 'XMLHttpRequest' },
            body: JSON.stringify({ academic_year_id: ACADEMIC_YEAR_ID, semester_id: SEMESTER_ID, grade_id: cb.value })
        }).then(r => r.json()).then(d => ({ type: 'open', success: d.success }))
    );

    const closeRequests = toClose.map(cb =>
        fetch(`/admin/dashboard/close-grade/${cb.dataset.openedId}`, {
            method: 'DELETE',
            headers: { 'X-CSRF-TOKEN': CSRF, 'X-Requested-With': 'XMLHttpRequest' }
        }).then(r => r.json()).then(d => ({ type: 'close', success: d.success }))
    );

    // Step 1: grade changes
    Promise.all([...openRequests, ...closeRequests])
    .then(gradeResults => {
        const opened = gradeResults.filter(d => d.success && d.type === 'open');
        const closed = gradeResults.filter(d => d.success && d.type === 'close');
        document.getElementById('openedGradeCount').textContent =
            parseInt(document.getElementById('openedGradeCount').textContent) + opened.length - closed.length;

        // Step 2: sync classrooms per grade (for all currently-opened grades)
        const hasClassroomWork = classroomChanged || opened.length > 0;
        if (!hasClassroomWork || openedGradeCbs.length === 0) return Promise.resolve(null);

        const grades = openedGradeCbs.map(gradeCb => ({
            grade_id: parseInt(gradeCb.value),
            classroom_ids: [...document.querySelectorAll(`.classroom-cb[data-grade-id="${gradeCb.value}"]:checked`)]
                .map(cb => parseInt(cb.value))
        }));

        return fetch('{{ route('admin.dashboard.sync-grade-classrooms') }}', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': CSRF, 'X-Requested-With': 'XMLHttpRequest' },
            body: JSON.stringify({ academic_year_id: ACADEMIC_YEAR_ID, semester_id: SEMESTER_ID, grades })
        }).then(r => r.json());
    })
    .then(() => {
        return fetch('{{ route('admin.dashboard.stats') }}', {
            headers: { 'X-Requested-With': 'XMLHttpRequest' }
        }).then(r => r.json());
    })
    .then(stats => {
        document.getElementById('openedGradeCount').textContent      = stats.grade_count;
        document.getElementById('openedClassroomCount').textContent   = stats.classroom_count;
        document.getElementById('openedCourseCount').textContent      = stats.course_distinct_count;
        document.getElementById('openedCourseTotalCount').textContent = stats.course_total_count;
        closeAddGradeModal();
        showFlash('บันทึกเรียบร้อยแล้ว');
    })
    .finally(() => {
        btn.disabled = false;
        btn.textContent = 'บันทึก';
    });
}

function showFlash(message) {
    const id = 'dashFlash';
    document.getElementById(id)?.remove();
    const el = document.createElement('div');
    el.id = id;
    el.style.cssText = 'position:fixed;bottom:1.5rem;right:1.5rem;z-index:99999;max-width:22rem;width:100%;animation:fadeInUp .3s ease';
    el.innerHTML = `
        <div style="display:flex;align-items:center;gap:.75rem;padding:1rem 1.25rem;border-radius:1rem;background:rgba(16,185,129,.88);backdrop-filter:blur(6px);box-shadow:0 8px 24px rgba(0,0,0,.15)">
            <i class="fas fa-check-circle" style="color:#fff;font-size:1.1rem;flex-shrink:0"></i>
            <span style="color:#fff;font-size:.85rem;font-weight:600;flex:1">${message}</span>
            <button onclick="this.closest('#${id}').remove()" style="background:none;border:0;color:rgba(255,255,255,.7);cursor:pointer;font-size:.8rem;padding:0"><i class="fas fa-times"></i></button>
        </div>`;
    document.body.appendChild(el);
    setTimeout(() => el.remove(), 3500);
}
</script>
@endpush
