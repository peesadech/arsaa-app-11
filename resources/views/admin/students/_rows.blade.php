@php
    $statusColors = [
        'studying'  => 'green',
        'suspended' => 'amber',
        'resigned'  => 'red',
        'graduated' => 'blue',
    ];
    $statusLabels = [
        'studying'  => __('Studying'),
        'suspended' => __('Suspended'),
        'resigned'  => __('Resigned'),
        'graduated' => __('Graduated'),
    ];
@endphp
@forelse ($students as $student)
    @php $enrollment = $student->enrollments->first(); @endphp
    <tr class="border-t border-slate-100 hover:bg-slate-50">
        <td class="px-5 py-4 font-medium text-slate-700">{{ $student->student_code }}</td>
        <td class="px-5 py-4">
            <a href="{{ route('admin.students.edit', $student->id) }}" class="font-medium text-slate-900 hover:text-brand-600">{{ $student->name_th }}</a>
            @if ($student->name_cn)
                <div class="text-xs text-slate-500">{{ $student->name_cn }}</div>
            @endif
        </td>
        <td class="px-5 py-4 text-slate-600">
            @if ($enrollment)
                <div class="text-sm">{{ ($enrollment->grade->name_th ?? '') . ' / ' . ($enrollment->classroom->name ?? '') }}</div>
                <div class="text-xs text-slate-400">{{ ($enrollment->academicYear->year ?? '') . ' / ' . ($enrollment->semester->semester_number ?? '') }}</div>
            @else
                <span class="text-slate-400 text-xs">—</span>
            @endif
        </td>
        <td class="px-5 py-4 text-slate-600 text-sm">{{ $student->mobile ?: '—' }}</td>
        <td class="px-5 py-4">
            <x-badge :color="$statusColors[$student->status] ?? 'gray'">{{ $statusLabels[$student->status] ?? $student->status }}</x-badge>
        </td>
        <td class="px-5 py-4 text-right whitespace-nowrap">
            <a href="{{ route('admin.student-reports.profile', $student->id) }}" target="_blank" class="btn-ghost p-2" title="{{ __('Student Profile') }}"><x-icon name="eye" class="h-4 w-4" /></a>
            <a href="{{ route('admin.students.edit', $student->id) }}" class="btn-ghost p-2" title="{{ __('Edit') }}"><x-icon name="edit" class="h-4 w-4" /></a>
            <button type="button"
                    x-on:click="$dispatch('open-delete', { id: {{ $student->id }}, name: @js($student->name_th) })"
                    class="btn-ghost p-2 text-red-500 hover:bg-red-50" title="{{ __('Delete') }}">
                <x-icon name="trash" class="h-4 w-4" />
            </button>
        </td>
    </tr>
@empty
    <tr><td colspan="6" class="text-center text-slate-400 py-16">{{ __('No records') }}</td></tr>
@endforelse
