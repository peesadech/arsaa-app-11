@php
    $activeFilters = array_filter($filters, fn ($v) => $v !== null && $v !== '');
@endphp
<x-layouts.admin :header="__('Attendance Report')" :subheader="__('Attendance summary by student with drill-down')">

    {{-- Filters --}}
    <x-card class="mb-5">
        <form method="GET" action="{{ route('admin.attendance-reports.index') }}"
              class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
            <div>
                <label class="form-label">{{ __('Academic Year') }}</label>
                <select name="academic_year_id" class="form-select">
                    <option value="">{{ __('All Years') }}</option>
                    @foreach($academicYears as $y)
                        <option value="{{ $y->id }}" {{ (string)($filters['academic_year_id'] ?? '') === (string)$y->id ? 'selected' : '' }}>{{ $y->year }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="form-label">{{ __('Semester') }}</label>
                <select name="semester_id" class="form-select">
                    <option value="">{{ __('All Semesters') }}</option>
                    @foreach($semesters as $s)
                        <option value="{{ $s->id }}" {{ (string)($filters['semester_id'] ?? '') === (string)$s->id ? 'selected' : '' }}>{{ __('Semester') }} {{ $s->semester_number }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="form-label">{{ __('Grade Level') }}</label>
                <select name="grade_id" class="form-select">
                    <option value="">{{ __('All Grade Levels') }}</option>
                    @foreach($grades as $g)
                        <option value="{{ $g->id }}" {{ (string)($filters['grade_id'] ?? '') === (string)$g->id ? 'selected' : '' }}>{{ $g->name_th }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="form-label">{{ __('Classroom') }}</label>
                <select name="classroom_id" class="form-select">
                    <option value="">{{ __('All Classrooms') }}</option>
                    @foreach($classrooms as $c)
                        <option value="{{ $c->id }}" {{ (string)($filters['classroom_id'] ?? '') === (string)$c->id ? 'selected' : '' }}>{{ $c->name }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="form-label">{{ __('Course') }}</label>
                <select name="course_id" class="form-select">
                    <option value="">{{ __('All Courses') }}</option>
                    @foreach($courses as $co)
                        <option value="{{ $co->id }}" {{ (string)($filters['course_id'] ?? '') === (string)$co->id ? 'selected' : '' }}>{{ $co->name }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="form-label">{{ __('Teacher') }}</label>
                <select name="teacher_id" class="form-select">
                    <option value="">{{ __('All Teachers') }}</option>
                    @foreach($teachers as $t)
                        <option value="{{ $t->id }}" {{ (string)($filters['teacher_id'] ?? '') === (string)$t->id ? 'selected' : '' }}>{{ $t->name }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="form-label">{{ __('From') }}</label>
                <input type="date" name="date_from" value="{{ $filters['date_from'] ?? '' }}" class="form-input">
            </div>
            <div>
                <label class="form-label">{{ __('To') }}</label>
                <input type="date" name="date_to" value="{{ $filters['date_to'] ?? '' }}" class="form-input">
            </div>

            <div class="sm:col-span-2 lg:col-span-4 flex items-center gap-2">
                <button type="submit" class="btn-primary"><x-icon name="search" class="h-4 w-4" /> {{ __('Search') }}</button>
                <a href="{{ route('admin.attendance-reports.index') }}" class="btn-secondary">{{ __('Reset') }}</a>
            </div>
        </form>
    </x-card>

    {{-- Summary tiles --}}
    <div class="grid grid-cols-2 sm:grid-cols-4 lg:grid-cols-8 gap-3 mb-5">
        @php
            $tiles = [
                ['label' => __('Sessions'),   'value' => $summary['sessions'], 'color' => 'text-slate-800'],
                ['label' => __('Students'),   'value' => $summary['students'], 'color' => 'text-slate-800'],
                ['label' => __('Present'),    'value' => $summary['present'],  'color' => 'text-emerald-600'],
                ['label' => __('Late'),       'value' => $summary['late'],     'color' => 'text-amber-600'],
                ['label' => __('Leave'),      'value' => $summary['leave'],    'color' => 'text-blue-600'],
                ['label' => __('Absent'),     'value' => $summary['absent'],   'color' => 'text-red-600'],
                ['label' => __('Activity'),   'value' => $summary['activity'], 'color' => 'text-sky-600'],
                ['label' => __('Attendance %'), 'value' => $summary['percent'].'%', 'color' => 'text-brand-700'],
                ['label' => __('At risk') . ' (<' . rtrim(rtrim(number_format($threshold, 1), '0'), '.') . '%)', 'value' => $summary['below'] ?? 0, 'color' => ($summary['below'] ?? 0) > 0 ? 'text-red-600' : 'text-slate-800'],
            ];
        @endphp
        @foreach($tiles as $tile)
            <div class="card px-4 py-3">
                <div class="text-[11px] font-bold uppercase tracking-wide text-slate-400">{{ $tile['label'] }}</div>
                <div class="text-xl font-extrabold mt-1 {{ $tile['color'] }}">{{ $tile['value'] }}</div>
            </div>
        @endforeach
    </div>

    {{-- Per-student table --}}
    <x-card padded="false">
        <div class="overflow-x-auto">
            <table class="w-full text-sm" style="min-width:720px">
                <thead>
                    <tr class="text-left text-[10px] font-bold text-slate-400 uppercase tracking-widest bg-slate-50">
                        <th class="px-4 py-3 w-10">#</th>
                        <th class="px-4 py-3">{{ __('Student') }}</th>
                        <th class="px-4 py-3 text-center">{{ __('Sessions') }}</th>
                        <th class="px-4 py-3 text-center">{{ __('Present') }}</th>
                        <th class="px-4 py-3 text-center">{{ __('Late') }}</th>
                        <th class="px-4 py-3 text-center">{{ __('Leave') }}</th>
                        <th class="px-4 py-3 text-center">{{ __('Absent') }}</th>
                        <th class="px-4 py-3 text-center">{{ __('Activity') }}</th>
                        <th class="px-4 py-3 text-center">{{ __('Attendance %') }}</th>
                        <th class="px-4 py-3"></th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($rows as $i => $row)
                        <tr class="border-t border-slate-100 hover:bg-slate-50">
                            <td class="px-4 py-3 text-slate-400">{{ $i + 1 }}</td>
                            <td class="px-4 py-3">
                                <div class="font-semibold text-slate-800">{{ $row['student']->name_th }}</div>
                                @if($row['student']->name_cn)
                                    <div class="text-[11px] text-slate-500">{{ $row['student']->name_cn }}</div>
                                @endif
                                <div class="text-[11px] text-slate-400">{{ $row['student']->student_code }}</div>
                            </td>
                            <td class="px-4 py-3 text-center font-medium">{{ $row['sessions'] }}</td>
                            <td class="px-4 py-3 text-center text-emerald-600 font-semibold">{{ $row['present'] }}</td>
                            <td class="px-4 py-3 text-center text-amber-600">{{ $row['late'] }}</td>
                            <td class="px-4 py-3 text-center text-blue-600">{{ $row['leave'] }}</td>
                            <td class="px-4 py-3 text-center text-red-600">{{ $row['absent'] }}</td>
                            <td class="px-4 py-3 text-center text-sky-600">{{ $row['activity'] }}</td>
                            <td class="px-4 py-3 text-center">
                                <span class="badge {{ $row['percent'] >= $threshold ? 'badge-green' : ($row['percent'] >= 60 ? 'badge-amber' : 'badge-red') }}">{{ $row['percent'] }}%</span>
                                @if($row['sessions'] > 0 && $row['percent'] < $threshold)
                                    <div class="text-[10px] font-bold text-red-500 mt-1">{{ __('Exam eligibility risk') }}</div>
                                @endif
                            </td>
                            <td class="px-4 py-3 text-right">
                                <a href="{{ route('admin.attendance-reports.student', array_merge(['studentId' => $row['student_id']], $activeFilters)) }}"
                                   class="btn-ghost p-2" title="{{ __('View detail') }}"><x-icon name="eye" class="h-4 w-4" /></a>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="10" class="text-center text-slate-400 py-16">{{ __('No records') }}</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </x-card>
</x-layouts.admin>
