<x-layouts.admin
    :header="__('Record Scores') . ' — ' . ($openedCourse->course->name ?? '?')"
    :subheader="__('Academic Year') . ' ' . ($openedCourse->academicYear->year ?? '?') . ' / ' . __('Semester') . ' ' . ($openedCourse->semester->semester_number ?? '?') . ' · ' . ($openedCourse->grade->name_th ?? '') . ' / ' . ($openedCourse->classroom->name ?? '') . ' · ' . $teacher->name">

    <x-slot name="actions">
        <x-button variant="secondary" icon="arrow-left" :href="route('teacher.scores.index')">{{ __('Back') }}</x-button>
    </x-slot>

    @php
        $scoreInputClass = 'form-input w-20 text-right text-sm';
    @endphp

    @if(session('status'))
    <div class="mb-6 flex items-center gap-2 p-4 bg-emerald-50 border border-emerald-200 rounded-xl text-emerald-700 text-sm">
        <x-icon name="check" class="h-4 w-4" />{{ session('status') }}
    </div>
    @endif

    {{-- Grade criteria hint --}}
    <div class="mb-6 flex flex-wrap items-center gap-2 text-xs text-slate-500">
        <span class="font-medium">{{ __('Grade Criteria') }}:</span>
        @foreach($gradeSettings as $gs)
        <x-badge :color="$gs->is_pass ? 'green' : 'red'">{{ $gs->grade }} = {{ $gs->min_score + 0 }}-{{ $gs->max_score + 0 }}</x-badge>
        @endforeach
    </div>

    @if($enrollments->isEmpty())
    <x-card>
        <div class="py-10 text-center text-slate-400">
            <x-icon name="user" class="h-8 w-8 mx-auto mb-3" />
            <p class="text-sm font-medium">{{ __('No students enrolled in this classroom yet') }}</p>
        </div>
    </x-card>
    @else

    <form action="{{ route('teacher.scores.save', $openedCourse->id) }}" method="POST">
        @csrf
        <x-card padded="false">
            <div class="overflow-x-auto">
                <table class="w-full text-sm" style="min-width:800px">
                    <thead>
                        <tr class="text-left text-xs font-medium text-slate-500 uppercase tracking-wide bg-slate-50 border-b border-slate-100">
                            <th class="px-5 py-3 w-10">#</th>
                            <th class="px-5 py-3">{{ __('Student') }}</th>
                            <th class="px-5 py-3 text-right">{{ __('Collect') }}</th>
                            <th class="px-5 py-3 text-right">{{ __('Midterm') }}</th>
                            <th class="px-5 py-3 text-right">{{ __('Final') }}</th>
                            <th class="px-5 py-3 text-right">{{ __('Total') }}</th>
                            <th class="px-5 py-3 text-center">{{ __('Grade') }}</th>
                            <th class="px-5 py-3">{{ __('Remark') }}</th>
                        </tr>
                    </thead>
                    <tbody>
                    @foreach($enrollments as $i => $enrollment)
                        @php
                            $student = $enrollment->student;
                            $score = $scores->get($student->id);
                        @endphp
                        <tr class="border-b border-slate-100 hover:bg-slate-50" data-score-row>
                            <td class="px-5 py-3 text-xs text-slate-400">{{ $i + 1 }}</td>
                            <td class="px-5 py-3">
                                <div class="text-sm font-medium text-slate-800">{{ $student->name_th }}</div>
                                <div class="text-xs text-slate-400">{{ $student->student_code }}</div>
                            </td>
                            <td class="px-5 py-3 text-right"><input type="number" name="scores[{{ $student->id }}][score_collect]" value="{{ $score->score_collect ?? '' }}" step="0.01" min="0" max="100" class="{{ $scoreInputClass }}" data-part></td>
                            <td class="px-5 py-3 text-right"><input type="number" name="scores[{{ $student->id }}][score_midterm]" value="{{ $score->score_midterm ?? '' }}" step="0.01" min="0" max="100" class="{{ $scoreInputClass }}" data-part></td>
                            <td class="px-5 py-3 text-right"><input type="number" name="scores[{{ $student->id }}][score_final]" value="{{ $score->score_final ?? '' }}" step="0.01" min="0" max="100" class="{{ $scoreInputClass }}" data-part></td>
                            <td class="px-5 py-3 text-right font-semibold text-sm text-slate-800" data-total>{{ $score->total_score ?? '-' }}</td>
                            <td class="px-5 py-3 text-center font-semibold text-sm text-brand-600" data-grade>{{ $score->grade ?? '-' }}</td>
                            <td class="px-5 py-3"><input type="text" name="scores[{{ $student->id }}][remark]" value="{{ $score->remark ?? '' }}" class="form-input text-sm"></td>
                        </tr>
                    @endforeach
                    </tbody>
                </table>
            </div>

            <div class="p-5 flex justify-end border-t border-slate-100">
                <button type="submit" class="btn-primary">
                    <x-icon name="check" class="h-4 w-4" /> {{ __('Save Scores') }}
                </button>
            </div>
        </x-card>
    </form>
    @endif

    @push('scripts')
    <script>
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
</x-layouts.admin>
