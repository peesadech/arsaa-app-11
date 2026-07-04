@forelse ($semesters as $semester)
    <tr class="border-t border-slate-100 hover:bg-slate-50">
        <td class="px-5 py-4">
            <a href="{{ route('admin.semesters.edit', $semester->id) }}" class="font-medium text-slate-900 hover:text-brand-600">{{ $semester->semester_number }}</a>
        </td>
        <td class="px-5 py-4">
            <x-badge :color="$semester->status == 1 ? 'green' : 'gray'">{{ $semester->status == 1 ? __('Active') : __('Not Active') }}</x-badge>
        </td>
        <td class="px-5 py-4 text-right whitespace-nowrap">
            <a href="{{ route('admin.semesters.edit', $semester->id) }}" class="btn-ghost p-2" title="{{ __('Edit') }}"><x-icon name="edit" class="h-4 w-4" /></a>
            <button type="button"
                    x-on:click="$dispatch('open-delete', { id: {{ $semester->id }}, name: @js($semester->semester_number) })"
                    class="btn-ghost p-2 text-red-500 hover:bg-red-50" title="{{ __('Delete') }}">
                <x-icon name="trash" class="h-4 w-4" />
            </button>
        </td>
    </tr>
@empty
    <tr><td colspan="3" class="text-center text-slate-400 py-16">{{ __('No records') }}</td></tr>
@endforelse
