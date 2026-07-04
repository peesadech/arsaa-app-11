@forelse ($rooms as $room)
    <tr class="border-t border-slate-100 hover:bg-slate-50">
        <td class="px-5 py-4">
            <a href="{{ route('admin.rooms.edit', $room->id) }}" class="font-medium text-slate-900 hover:text-brand-600">{{ $room->room_number }}</a>
        </td>
        <td class="px-5 py-4">
            @if ($room->building)
                <x-badge color="blue">{{ $room->building->name_th }}</x-badge>
            @else
                <span class="text-slate-400 text-xs italic">-</span>
            @endif
        </td>
        <td class="px-5 py-4">
            @if ($room->floor)
                <x-badge color="amber">{{ $room->floor->name_th }}</x-badge>
            @else
                <span class="text-slate-400 text-xs italic">-</span>
            @endif
        </td>
        <td class="px-5 py-4">
            @if ($room->courses->isNotEmpty())
                <div class="flex flex-wrap gap-1">
                    @foreach ($room->courses->take(3) as $course)
                        <x-badge color="gray">{{ $course->name }}</x-badge>
                    @endforeach
                    @if ($room->courses->count() > 3)
                        <span class="text-xs text-slate-400">+{{ $room->courses->count() - 3 }}</span>
                    @endif
                </div>
            @else
                <span class="text-slate-400 text-xs italic">-</span>
            @endif
        </td>
        <td class="px-5 py-4">
            <x-badge :color="$room->status == 1 ? 'green' : 'gray'">{{ $room->status == 1 ? __('Active') : __('Not Active') }}</x-badge>
        </td>
        <td class="px-5 py-4 text-right whitespace-nowrap">
            <a href="{{ route('admin.rooms.edit', $room->id) }}" class="btn-ghost p-2" title="{{ __('Edit') }}"><x-icon name="edit" class="h-4 w-4" /></a>
            <button type="button"
                    x-on:click="$dispatch('open-delete', { id: {{ $room->id }}, name: @js($room->room_number) })"
                    class="btn-ghost p-2 text-red-500 hover:bg-red-50" title="{{ __('Delete') }}">
                <x-icon name="trash" class="h-4 w-4" />
            </button>
        </td>
    </tr>
@empty
    <tr><td colspan="6" class="text-center text-slate-400 py-16">{{ __('No records') }}</td></tr>
@endforelse
