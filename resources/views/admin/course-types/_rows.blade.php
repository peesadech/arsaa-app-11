@forelse ($courseTypes as $ct)
    <tr class="border-t border-slate-100 hover:bg-slate-50">
        <td class="px-5 py-4">
            <a href="{{ route('admin.course-types.edit', $ct->id) }}" class="font-medium text-slate-900 hover:text-brand-600">{{ $ct->name_th }}</a>
            <div class="text-xs text-slate-500">{{ $ct->name_en }}</div>
        </td>
        <td class="px-5 py-4">
            @if ($ct->gradingScheme)
                <x-badge color="blue">{{ $ct->gradingScheme->name }}</x-badge>
            @else
                <span class="text-slate-400 text-xs italic">{{ __('Grading not set') }}</span>
            @endif
        </td>
        <td class="px-5 py-4">
            <x-badge :color="$ct->status == 1 ? 'green' : 'gray'">{{ $ct->status == 1 ? __('Active') : __('Not Active') }}</x-badge>
        </td>
        <td class="px-5 py-4 text-right whitespace-nowrap">
            <a href="{{ route('admin.course-types.show', $ct->id) }}" class="btn-ghost p-2" title="{{ __('View') }}"><x-icon name="eye" class="h-4 w-4" /></a>
            <a href="{{ route('admin.course-types.edit', $ct->id) }}" class="btn-ghost p-2" title="{{ __('Edit') }}"><x-icon name="edit" class="h-4 w-4" /></a>
            <button type="button"
                    x-on:click="$dispatch('open-delete', { id: {{ $ct->id }}, name: @js($ct->name_th.' / '.$ct->name_en) })"
                    class="btn-ghost p-2 text-red-500 hover:bg-red-50" title="{{ __('Delete') }}">
                <x-icon name="trash" class="h-4 w-4" />
            </button>
        </td>
    </tr>
@empty
    <tr><td colspan="4" class="text-center text-slate-400 py-16">{{ __('No records') }}</td></tr>
@endforelse
