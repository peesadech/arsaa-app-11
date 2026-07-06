<x-layouts.admin :header="__('Academic Results')"
    :subheader="__('Academic Year') . ' ' . ($academicYear->year ?? '?') . ' / ' . __('Semester') . ' ' . ($semester->semester_number ?? '?') . ' — ' . __('Class ranking and GPA')">
    <x-slot name="actions">
        @if($gradeId && $classroomId)
        <a href="{{ route('admin.student-reports.class-report-csv', ['grade_id' => $gradeId, 'classroom_id' => $classroomId]) }}" class="btn-secondary">
            <x-icon name="download" class="h-4 w-4" /> {{ __('Export') }} CSV
        </a>
        @endif
        <x-button variant="secondary" icon="arrow-left" :href="route('admin.student-reports.index')">{{ __('Back') }}</x-button>
    </x-slot>

    @if($openedClassrooms->isEmpty())
    <x-card class="text-center text-slate-400 py-10">
        <x-icon name="door" class="h-10 w-10 mx-auto mb-3" />
        <p class="text-sm font-medium">{{ __('No classrooms opened for this term yet') }}</p>
    </x-card>
    @else

    <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-5 gap-3 mb-6">
        @foreach($openedClassrooms as $oc)
        @php $isSelected = $gradeId == $oc->grade_id && $classroomId == $oc->classroom_id; @endphp
        <a href="{{ route('admin.student-reports.class-report', ['grade_id' => $oc->grade_id, 'classroom_id' => $oc->classroom_id]) }}"
           class="block p-4 rounded-2xl border transition {{ $isSelected ? 'bg-brand-50 border-brand-200' : 'bg-white border-slate-100 shadow-card hover:border-brand-200' }}">
            <div class="text-sm font-semibold {{ $isSelected ? 'text-brand-700' : 'text-slate-800' }}">
                {{ $oc->grade->name_th ?? '' }} / {{ $oc->classroom->name ?? '' }}
            </div>
        </a>
        @endforeach
    </div>

    @if(!$gradeId || !$classroomId)
    <x-card class="text-center text-slate-400 py-10">
        <x-icon name="filter" class="h-9 w-9 mx-auto mb-3" />
        <p class="text-sm font-medium">{{ __('Select a classroom above to see ranking') }}</p>
    </x-card>
    @else
    <x-card padded="false">
        <div class="overflow-x-auto">
            <table class="w-full text-sm" style="min-width:640px">
                <thead>
                    <tr class="text-left text-xs font-medium text-slate-500 uppercase tracking-wide bg-slate-50 border-b border-slate-100">
                        <th class="px-5 py-3 w-14 text-center">{{ __('Rank') }}</th>
                        <th class="px-5 py-3">{{ __('Student') }}</th>
                        <th class="px-5 py-3 text-right">{{ __('Weighted total') }}</th>
                        <th class="px-5 py-3 text-center">{{ __('Grade') }}</th>
                        <th class="px-5 py-3 text-center">GPA</th>
                        <th class="px-5 py-3 text-center">{{ __('Result') }}</th>
                        <th class="px-5 py-3 text-right"></th>
                    </tr>
                </thead>
                <tbody>
                @forelse($rows as $r)
                    <tr class="border-b border-slate-100 hover:bg-slate-50">
                        <td class="px-5 py-3 text-center font-bold text-slate-700">{{ $r['rank'] ?? '-' }}</td>
                        <td class="px-5 py-3">
                            <div class="font-medium text-slate-800">{{ $r['student']->name_th }}</div>
                            <div class="text-xs text-slate-400">{{ $r['student']->student_code }}</div>
                        </td>
                        <td class="px-5 py-3 text-right font-semibold text-slate-800">{{ $r['weighted_score'] ?? '-' }}</td>
                        <td class="px-5 py-3 text-center font-semibold text-brand-600">{{ $r['overall_grade'] ?? '-' }}</td>
                        <td class="px-5 py-3 text-center text-slate-600">{{ $r['gpa'] ?? '-' }}</td>
                        <td class="px-5 py-3 text-center">
                            @if($r['overall_pass'] === true)<x-badge color="green">{{ __('Pass') }}</x-badge>
                            @elseif($r['overall_pass'] === false)<x-badge color="red">{{ __('Fail') }}</x-badge>
                            @else <span class="text-slate-300">-</span>@endif
                        </td>
                        <td class="px-5 py-3 text-right">
                            <a href="{{ route('admin.student-reports.report-card', $r['student']->id) }}" target="_blank"
                               class="btn-secondary text-xs py-1.5 inline-flex items-center justify-center gap-1 w-24"><x-icon name="eye" class="h-4 w-4" /> {{ __('Semester') }}</a>
                            <a href="{{ route('admin.student-reports.report-card-year', $r['student']->id) }}" target="_blank"
                               class="btn-secondary text-xs py-1.5 inline-flex items-center justify-center gap-1 w-24"><x-icon name="eye" class="h-4 w-4" /> {{ __('Year') }}</a>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="7" class="text-center text-slate-400 py-16">{{ __('No students in this classroom yet') }}</td></tr>
                @endforelse
                </tbody>
            </table>
        </div>
    </x-card>
    @endif
    @endif
</x-layouts.admin>
