@forelse ($courses as $course)
    <tr class="border-t border-slate-100 hover:bg-slate-50">
        <td class="px-5 py-4">
            <a href="{{ route('admin.courses.edit', $course->id) }}" class="font-medium text-slate-900 hover:text-brand-600">{{ $course->name }}</a>
        </td>
        <td class="px-5 py-4">
            @if ($course->subjectGroup)
                <x-badge color="blue">{{ $course->subjectGroup->name_th }}</x-badge>
            @else
                <span class="text-slate-400 text-xs italic">—</span>
            @endif
        </td>
        <td class="px-5 py-4 text-slate-600">
            {{ $course->grade ? ($course->grade->name_th . ' / ' . $course->grade->name_en) : '—' }}
        </td>
        <td class="px-5 py-4 text-slate-600">
            {{ $course->semester ? $course->semester->semester_number : '—' }}
        </td>
        <td class="px-5 py-4">
            @php $scheme = $course->resolveGradingScheme(); @endphp
            @if (! $scheme)
                <x-badge color="red">{{ __('Grading not set') }}</x-badge>
            @else
                @php
                    $fromCourse = $course->grading_scheme_id !== null;
                    $isPassFail = $scheme->result_type === \App\Models\GradingScheme::RESULT_TYPE_PASS_FAIL;
                @endphp
                <div class="flex flex-col gap-1">
                    <x-badge :color="$isPassFail ? 'green' : 'blue'" class="w-fit">{{ $scheme->name }}</x-badge>
                    <span class="text-[10px] font-semibold uppercase tracking-wide {{ $fromCourse ? 'text-brand-500' : 'text-slate-400' }}">
                        {{ $fromCourse ? __('From subject course') : __('From course type') }}
                    </span>
                </div>
            @endif
        </td>
        <td class="px-5 py-4">
            <x-badge :color="$course->status == 1 ? 'green' : 'gray'">{{ $course->status == 1 ? __('Active') : __('Not Active') }}</x-badge>
        </td>
        <td class="px-5 py-4 text-right whitespace-nowrap">
            <a href="{{ route('admin.courses.edit', $course->id) }}" class="btn-ghost p-2" title="{{ __('Edit') }}"><x-icon name="edit" class="h-4 w-4" /></a>
            <button type="button"
                    x-on:click="$dispatch('open-delete', { id: {{ $course->id }}, name: @js($course->name) })"
                    class="btn-ghost p-2 text-red-500 hover:bg-red-50" title="{{ __('Delete') }}">
                <x-icon name="trash" class="h-4 w-4" />
            </button>
        </td>
    </tr>
@empty
    <tr><td colspan="7" class="text-center text-slate-400 py-16">{{ __('No records') }}</td></tr>
@endforelse
