@forelse ($schemes as $scheme)
    <tr class="border-t border-slate-100 hover:bg-slate-50">
        <td class="px-5 py-4">
            <a href="{{ route('admin.grading-schemes.edit', $scheme->id) }}" class="font-medium text-slate-900 hover:text-brand-600">{{ $scheme->name }}</a>
            @if ($scheme->details_count)
                <div class="text-xs text-slate-500">{{ __(':count grade rows', ['count' => $scheme->details_count]) }}</div>
            @endif
        </td>
        <td class="px-5 py-4">
            @if ($scheme->result_type === \App\Models\GradingScheme::RESULT_TYPE_GRADE)
                <x-badge color="blue">{{ __('Grade (A-F)') }}</x-badge>
            @else
                <x-badge color="amber">{{ __('Pass / Fail') }}</x-badge>
            @endif
        </td>
        <td class="px-5 py-4">
            <x-badge :color="$scheme->status == 1 ? 'green' : 'gray'">{{ $scheme->status == 1 ? __('Active') : __('Not Active') }}</x-badge>
        </td>
        <td class="px-5 py-4 text-right whitespace-nowrap">
            <a href="{{ route('admin.grading-schemes.edit', $scheme->id) }}" class="btn-ghost p-2" title="{{ __('Edit') }}"><x-icon name="edit" class="h-4 w-4" /></a>
            <button type="button"
                    x-on:click="$dispatch('open-delete', { id: {{ $scheme->id }}, name: @js($scheme->name) })"
                    class="btn-ghost p-2 text-red-500 hover:bg-red-50" title="{{ __('Delete') }}">
                <x-icon name="trash" class="h-4 w-4" />
            </button>
        </td>
    </tr>
@empty
    <tr><td colspan="4" class="text-center text-slate-400 py-16">{{ __('No records') }}</td></tr>
@endforelse
