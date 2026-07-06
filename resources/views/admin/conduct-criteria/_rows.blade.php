@forelse ($items as $item)
    <tr class="border-t border-slate-100 hover:bg-slate-50">
        <td class="px-5 py-4">
            <a href="{{ route('admin.conduct-criteria.edit', $item->id) }}" class="font-medium text-slate-900 hover:text-brand-600">{{ $item->name }}</a>
            @if($item->name_cn)<div class="text-xs text-slate-500">{{ $item->name_cn }}</div>@endif
        </td>
        <td class="px-5 py-4 text-right text-slate-700">{{ $item->max_score + 0 }}</td>
        <td class="px-5 py-4"><x-badge :color="$item->is_active ? 'green' : 'gray'">{{ $item->is_active ? __('Active') : __('Inactive') }}</x-badge></td>
        <td class="px-5 py-4 text-right whitespace-nowrap">
            <a href="{{ route('admin.conduct-criteria.edit', $item->id) }}" class="btn-ghost p-2" title="{{ __('Edit') }}"><x-icon name="edit" class="h-4 w-4" /></a>
            <button type="button" x-on:click="$dispatch('open-delete', { id: {{ $item->id }}, name: @js($item->name) })"
                    class="btn-ghost p-2 text-red-500 hover:bg-red-50" title="{{ __('Delete') }}"><x-icon name="trash" class="h-4 w-4" /></button>
        </td>
    </tr>
@empty
    <tr><td colspan="4" class="text-center text-slate-400 py-16">{{ __('No records') }}</td></tr>
@endforelse
