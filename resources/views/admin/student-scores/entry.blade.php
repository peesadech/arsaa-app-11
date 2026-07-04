@php
    $subheader = __('Academic Year') . ' ' . ($openedCourse->academicYear->year ?? '?') . ' / ' . __('Semester') . ' ' . ($openedCourse->semester->semester_number ?? '?') . ' · ' . ($openedCourse->grade->name_th ?? '') . ' / ' . ($openedCourse->classroom->name ?? '');
    $scoreInputClass = 'w-20 px-2 py-1.5 rounded-lg border border-slate-200 bg-white text-xs text-right text-slate-800 shadow-sm focus:border-brand-400 focus:ring-brand-200 focus:outline-none';
@endphp
<x-layouts.admin :header="__('Record Scores') . ' — ' . ($openedCourse->course->name ?? '?')" :subheader="$subheader">
    <x-slot name="actions">
        <x-button variant="secondary" icon="arrow-left"
                  :href="route('admin.student-scores.index', ['grade_id' => $openedCourse->grade_id, 'classroom_id' => $openedCourse->classroom_id])">{{ __('Back') }}</x-button>
    </x-slot>

    @if(session('status'))
    <div class="mb-6 badge-green w-full rounded-xl px-4 py-3 text-sm justify-start">{{ session('status') }}</div>
    @endif

    {{-- Grade criteria hint --}}
    <div class="mb-6 flex flex-wrap items-center gap-2 text-xs text-slate-500">
        <x-icon name="clipboard" class="h-4 w-4 text-slate-400" />
        <span class="font-semibold">{{ __('Grade Criteria') }}:</span>
        @foreach($gradeSettings as $gs)
        <span class="{{ $gs->is_pass ? 'badge-green' : 'badge-red' }}">{{ $gs->grade }} = {{ $gs->min_score + 0 }}-{{ $gs->max_score + 0 }}</span>
        @endforeach
        <a href="{{ route('admin.student-master.index', ['type' => 'grade_setting']) }}" class="text-brand-600 hover:underline font-medium">{{ __('Edit') }}</a>
    </div>

    @if($enrollments->isEmpty())
    <x-card>
        <div class="py-10 text-center text-slate-400">
            <x-icon name="users" class="h-8 w-8 mx-auto mb-3" />
            <p class="text-sm font-medium">{{ __('No students enrolled in this classroom yet') }}</p>
            <div class="mt-4">
                <x-button icon="plus" :href="route('admin.student-enrollments.index', ['grade_id' => $openedCourse->grade_id, 'classroom_id' => $openedCourse->classroom_id])">{{ __('Add students') }}</x-button>
            </div>
        </div>
    </x-card>
    @else

    <form action="{{ route('admin.student-scores.save', $openedCourse->id) }}" method="POST">
        @csrf
        <x-card>
            {{-- Teacher --}}
            <div class="flex flex-wrap items-center gap-3 mb-5">
                <label class="form-label mb-0">{{ __('Teacher') }}:</label>
                <select name="teacher_id" class="form-select rounded-lg w-full sm:w-64">
                    <option value="">-</option>
                    @foreach($openedCourse->course->teachers ?? [] as $teacher)
                    <option value="{{ $teacher->id }}" {{ $scores->first()?->teacher_id == $teacher->id ? 'selected' : '' }}>{{ $teacher->name }}</option>
                    @endforeach
                </select>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full" style="min-width:800px">
                    <thead>
                        <tr class="text-left text-xs font-medium text-slate-500 uppercase tracking-wide border-b border-slate-100">
                            <th class="py-2 pr-3 w-10">#</th>
                            <th class="py-2 pr-3">{{ __('Student') }}</th>
                            <th class="py-2 pr-3 text-right">{{ __('Collect') }}</th>
                            <th class="py-2 pr-3 text-right">{{ __('Midterm') }}</th>
                            <th class="py-2 pr-3 text-right">{{ __('Final') }}</th>
                            <th class="py-2 pr-3 text-right">{{ __('Total') }}</th>
                            <th class="py-2 pr-3 text-center">{{ __('Grade') }}</th>
                            <th class="py-2">{{ __('Remark') }}</th>
                        </tr>
                    </thead>
                    <tbody>
                    @foreach($enrollments as $i => $enrollment)
                        @php
                            $student = $enrollment->student;
                            $score = $scores->get($student->id);
                        @endphp
                        <tr class="border-b border-slate-50" data-score-row>
                            <td class="py-2 pr-3 text-xs text-slate-400">{{ $i + 1 }}</td>
                            <td class="py-2 pr-3">
                                <div class="text-sm font-semibold text-slate-800">{{ $student->name_th }}</div>
                                @if($student->name_cn)
                                    <div class="text-xs text-slate-500">{{ $student->name_cn }}</div>
                                @endif
                                <div class="text-xs text-slate-400">{{ $student->student_code }}</div>
                            </td>
                            <td class="py-2 pr-3 text-right"><input type="number" name="scores[{{ $student->id }}][score_collect]" value="{{ $score->score_collect ?? '' }}" step="0.01" min="0" max="100" class="{{ $scoreInputClass }}" data-part></td>
                            <td class="py-2 pr-3 text-right"><input type="number" name="scores[{{ $student->id }}][score_midterm]" value="{{ $score->score_midterm ?? '' }}" step="0.01" min="0" max="100" class="{{ $scoreInputClass }}" data-part></td>
                            <td class="py-2 pr-3 text-right"><input type="number" name="scores[{{ $student->id }}][score_final]" value="{{ $score->score_final ?? '' }}" step="0.01" min="0" max="100" class="{{ $scoreInputClass }}" data-part></td>
                            <td class="py-2 pr-3 text-right font-semibold text-sm text-slate-800" data-total>{{ $score->total_score ?? '-' }}</td>
                            <td class="py-2 pr-3 text-center font-semibold text-sm text-brand-600" data-grade>{{ $score->grade ?? '-' }}</td>
                            <td class="py-2"><input type="text" name="scores[{{ $student->id }}][remark]" value="{{ $score->remark ?? '' }}" class="w-full px-3 py-1.5 rounded-lg border border-slate-200 bg-white text-xs text-slate-800 shadow-sm focus:border-brand-400 focus:ring-brand-200 focus:outline-none"></td>
                        </tr>
                    @endforeach
                    </tbody>
                </table>
            </div>

            <div class="mt-5 flex justify-end">
                <x-button type="submit" icon="check">{{ __('Save Scores') }}</x-button>
            </div>
        </x-card>
    </form>
    @endif
</x-layouts.admin>

@push('scripts')
<script>
// คำนวณ total + เกรด แบบ live ตามเกณฑ์จาก grade_settings
const GRADE_SETTINGS = {!! json_encode($gradeSettings->map(fn($g) => ['grade' => $g->grade, 'min' => (float) $g->min_score, 'max' => (float) $g->max_score])) !!};

document.querySelectorAll('[data-score-row]').forEach(row => {
    const parts = row.querySelectorAll('[data-part]');
    const totalCell = row.querySelector('[data-total]');
    const gradeCell = row.querySelector('[data-grade]');

    function recalc() {
        let hasValue = false, total = 0;
        parts.forEach(input => {
            if (input.value !== '') { hasValue = true; total += parseFloat(input.value) || 0; }
        });
        if (!hasValue) { totalCell.textContent = '-'; gradeCell.textContent = '-'; return; }
        total = Math.round(total * 100) / 100;
        totalCell.textContent = total;
        const setting = GRADE_SETTINGS.find(g => total >= g.min && total <= g.max);
        gradeCell.textContent = setting ? setting.grade : '-';
    }

    parts.forEach(input => input.addEventListener('input', recalc));
});
</script>
@endpush
