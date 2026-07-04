@forelse ($floors as $floor)
    <tr class="border-t border-slate-100 hover:bg-slate-50">
        <td class="px-5 py-4">
            <a href="{{ route('admin.floors.edit', $floor->id) }}" class="font-medium text-slate-900 hover:text-brand-600">{{ $floor->name_th }}</a>
            <div class="text-xs text-slate-500">{{ $floor->name_en }}</div>
        </td>
        <td class="px-5 py-4">
            @if ($floor->building)
                <x-badge color="blue">{{ $floor->building->name_th }}</x-badge>
            @else
                <span class="text-slate-400 text-xs italic">{{ __('No building') }}</span>
            @endif
        </td>
        <td class="px-5 py-4">
            <x-badge :color="$floor->status == 1 ? 'green' : 'gray'">{{ $floor->status == 1 ? __('Active') : __('Not Active') }}</x-badge>
        </td>
        <td class="px-5 py-4 text-right whitespace-nowrap">
            <a href="{{ route('admin.floors.edit', $floor->id) }}" class="btn-ghost p-2" title="{{ __('Edit') }}"><x-icon name="edit" class="h-4 w-4" /></a>
            <button type="button"
                    x-on:click="$dispatch('open-delete', { id: {{ $floor->id }}, name: @js($floor->name_th.' / '.$floor->name_en) })"
                    class="btn-ghost p-2 text-red-500 hover:bg-red-50" title="{{ __('Delete') }}">
                <x-icon name="trash" class="h-4 w-4" />
            </button>
        </td>
    </tr>
@empty
    <tr><td colspan="4" class="text-center text-slate-400 py-16">{{ __('No records') }}</td></tr>
@endforelse
