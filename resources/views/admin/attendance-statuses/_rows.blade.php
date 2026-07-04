@forelse ($statuses as $s)
    <tr class="border-t border-slate-100 hover:bg-slate-50">
        <td class="px-5 py-4">
            <div class="flex items-center gap-3">
                <span style="display:inline-block;width:14px;height:14px;border-radius:4px;background: {{ $s->color ?: '#cbd5e1' }}"></span>
                <div>
                    <a href="{{ route('admin.attendance-statuses.edit', $s->id) }}" class="font-medium text-slate-900 hover:text-brand-600">{{ $s->name_th }}</a>
                    @if ($s->name_en)<div class="text-xs text-slate-500">{{ $s->name_en }}</div>@endif
                </div>
            </div>
        </td>
        <td class="px-5 py-4">
            <span class="font-mono text-xs text-slate-600">{{ $s->code }}</span>
        </td>
        <td class="px-5 py-4">
            @if ($s->status_type)
                <x-badge color="gray">{{ $s->status_type }}</x-badge>
            @else
                <span class="text-slate-400 text-xs italic">—</span>
            @endif
        </td>
        <td class="px-5 py-4">
            <div class="flex flex-wrap gap-1">
                @if ($s->is_count_as_present)<x-badge color="green">{{ __('Present') }}</x-badge>@endif
                @if ($s->is_count_as_absent)<x-badge color="red">{{ __('Absent') }}</x-badge>@endif
                @if ($s->is_late)<x-badge color="amber">{{ __('Late') }}</x-badge>@endif
                @if ($s->is_leave)<x-badge color="blue">{{ __('Leave') }}</x-badge>@endif
                @if ($s->is_require_remark)<x-badge color="gray">{{ __('Remark') }}</x-badge>@endif
            </div>
        </td>
        <td class="px-5 py-4">
            <x-badge :color="$s->is_active ? 'green' : 'gray'">{{ $s->is_active ? __('Active') : __('Inactive') }}</x-badge>
        </td>
        <td class="px-5 py-4 text-right whitespace-nowrap">
            <a href="{{ route('admin.attendance-statuses.edit', $s->id) }}" class="btn-ghost p-2" title="{{ __('Edit') }}"><x-icon name="edit" class="h-4 w-4" /></a>
            <button type="button"
                    x-on:click="$dispatch('open-delete', { id: {{ $s->id }}, name: @js($s->name_th) })"
                    class="btn-ghost p-2 text-red-500 hover:bg-red-50" title="{{ __('Delete') }}">
                <x-icon name="trash" class="h-4 w-4" />
            </button>
        </td>
    </tr>
@empty
    <tr><td colspan="6" class="text-center text-slate-400 py-16">{{ __('No records') }}</td></tr>
@endforelse
