@extends('layouts.app')

@section('content')
@php
    $scoreInputClass = 'w-20 px-2 py-1.5 bg-gray-50 dark:bg-[#3a3b3c] border-2 border-transparent rounded-lg text-xs text-right text-gray-800 dark:text-white focus:border-indigo-500 focus:outline-none transition-all';
@endphp
<div class="min-h-screen bg-gray-50 dark:bg-[#18191a] py-10 px-4 sm:px-6 lg:px-8 transition-colors duration-300">
    <div class="max-w-6xl mx-auto">

        {{-- Header --}}
        <div class="flex items-center space-x-4 mb-8">
            <a href="{{ route('admin.student-scores.index', ['grade_id' => $openedCourse->grade_id, 'classroom_id' => $openedCourse->classroom_id]) }}"
               class="group flex items-center justify-center w-10 h-10 rounded-full bg-white dark:bg-[#242526] shadow-sm border border-gray-200 dark:border-[#3a3b3c] text-gray-500 dark:text-gray-400 hover:text-indigo-600 dark:hover:text-indigo-400 hover:border-indigo-300 transition-all duration-200">
                <i class="fas fa-arrow-left group-hover:-translate-x-0.5 transition-transform"></i>
            </a>
            <div>
                <h1 class="text-2xl font-extrabold text-gray-900 dark:text-white tracking-tight">{{ __('Record Scores') }} — {{ $openedCourse->course->name ?? '?' }}</h1>
                <p class="text-sm text-gray-500 dark:text-gray-400 font-medium px-1">
                    {{ __('Academic Year') }} {{ $openedCourse->academicYear->year ?? '?' }} / {{ __('Semester') }} {{ $openedCourse->semester->semester_number ?? '?' }}
                    · {{ $openedCourse->grade->name_th ?? '' }} / {{ $openedCourse->classroom->name ?? '' }}
                </p>
            </div>
        </div>

        @if(session('status'))
        <div class="mb-6 p-4 bg-emerald-50 dark:bg-emerald-900/20 border border-emerald-200 dark:border-emerald-800 rounded-2xl text-emerald-700 dark:text-emerald-300 text-sm">{{ session('status') }}</div>
        @endif

        {{-- Grade criteria hint --}}
        <div class="mb-6 flex flex-wrap items-center gap-2 text-xs text-gray-400">
            <i class="fas fa-info-circle"></i>
            <span class="font-bold">{{ __('Grade Criteria') }}:</span>
            @foreach($gradeSettings as $gs)
            <span class="px-2 py-0.5 rounded-lg {{ $gs->is_pass ? 'bg-emerald-50 text-emerald-600' : 'bg-rose-50 text-rose-600' }} font-bold">{{ $gs->grade }} = {{ $gs->min_score + 0 }}-{{ $gs->max_score + 0 }}</span>
            @endforeach
            <a href="{{ route('admin.student-master.index', ['type' => 'grade_setting']) }}" class="text-indigo-400 hover:underline">{{ __('Edit') }}</a>
        </div>

        @if($enrollments->isEmpty())
        <div class="bg-white dark:bg-[#242526] rounded-[2rem] shadow-sm border border-gray-100 dark:border-[#3a3b3c] p-10 text-center text-gray-400">
            <i class="fas fa-user-slash text-3xl mb-3"></i>
            <p class="text-sm font-medium">{{ __('No students enrolled in this classroom yet') }}</p>
            <a href="{{ route('admin.student-enrollments.index', ['grade_id' => $openedCourse->grade_id, 'classroom_id' => $openedCourse->classroom_id]) }}" class="btn-app mt-4">
                <i class="fas fa-user-plus text-[10px]"></i> {{ __('Add students') }}
            </a>
        </div>
        @else

        <form action="{{ route('admin.student-scores.save', $openedCourse->id) }}" method="POST">
            @csrf
            <div class="bg-white dark:bg-[#242526] rounded-[2rem] shadow-sm border border-gray-100 dark:border-[#3a3b3c] p-6">
                {{-- Teacher --}}
                <div class="flex flex-wrap items-center gap-3 mb-5">
                    <label class="text-xs font-medium text-gray-500 dark:text-gray-400">{{ __('Teacher') }}:</label>
                    <select name="teacher_id" class="px-4 py-2 bg-gray-50 dark:bg-[#3a3b3c] border-2 border-transparent rounded-xl text-sm text-gray-800 dark:text-white focus:border-indigo-500 focus:outline-none">
                        <option value="">-</option>
                        @foreach($openedCourse->course->teachers ?? [] as $teacher)
                        <option value="{{ $teacher->id }}" {{ $scores->first()?->teacher_id == $teacher->id ? 'selected' : '' }}>{{ $teacher->name }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="overflow-x-auto">
                    <table class="w-full" style="min-width:800px">
                        <thead>
                            <tr class="text-left text-[10px] font-bold text-gray-400 uppercase tracking-widest border-b border-gray-100 dark:border-[#3a3b3c]">
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
                            <tr class="border-b border-gray-50 dark:border-[#3a3b3c]/50" data-score-row>
                                <td class="py-2 pr-3 text-xs text-gray-400">{{ $i + 1 }}</td>
                                <td class="py-2 pr-3">
                                    <div class="text-sm font-bold text-gray-800 dark:text-gray-200">{{ $student->name_th }}</div>
                                    <div class="text-[10px] text-gray-400">{{ $student->student_code }}</div>
                                </td>
                                <td class="py-2 pr-3 text-right"><input type="number" name="scores[{{ $student->id }}][score_collect]" value="{{ $score->score_collect ?? '' }}" step="0.01" min="0" max="100" class="{{ $scoreInputClass }}" data-part></td>
                                <td class="py-2 pr-3 text-right"><input type="number" name="scores[{{ $student->id }}][score_midterm]" value="{{ $score->score_midterm ?? '' }}" step="0.01" min="0" max="100" class="{{ $scoreInputClass }}" data-part></td>
                                <td class="py-2 pr-3 text-right"><input type="number" name="scores[{{ $student->id }}][score_final]" value="{{ $score->score_final ?? '' }}" step="0.01" min="0" max="100" class="{{ $scoreInputClass }}" data-part></td>
                                <td class="py-2 pr-3 text-right font-bold text-sm text-gray-800 dark:text-gray-200" data-total>{{ $score->total_score ?? '-' }}</td>
                                <td class="py-2 pr-3 text-center font-bold text-sm text-indigo-500" data-grade>{{ $score->grade ?? '-' }}</td>
                                <td class="py-2"><input type="text" name="scores[{{ $student->id }}][remark]" value="{{ $score->remark ?? '' }}" class="w-full px-3 py-1.5 bg-gray-50 dark:bg-[#3a3b3c] border-2 border-transparent rounded-lg text-xs text-gray-800 dark:text-white focus:border-indigo-500 focus:outline-none"></td>
                            </tr>
                        @endforeach
                        </tbody>
                    </table>
                </div>

                <div class="mt-5 flex justify-end">
                    <button type="submit" class="inline-flex items-center px-8 py-3 border border-transparent text-sm font-bold rounded-2xl shadow-lg text-white bg-indigo-600 hover:bg-indigo-700 transition-all transform hover:-translate-y-0.5 active:scale-95">
                        <i class="fas fa-save mr-2 opacity-75"></i> {{ __('Save Scores') }}
                    </button>
                </div>
            </div>
        </form>
        @endif

    </div>
</div>

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
@endsection
