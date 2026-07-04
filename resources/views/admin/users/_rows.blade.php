@forelse ($users as $user)
    <tr class="border-t border-slate-100 hover:bg-slate-50">
        <td class="px-5 py-4">
            <a href="{{ route('admin.users.edit', $user->id) }}" class="font-medium text-slate-900 hover:text-brand-600">{{ $user->name }}</a>
            <div class="text-xs text-slate-500">{{ $user->email }}</div>
        </td>
        <td class="px-5 py-4">
            <div class="flex flex-wrap gap-1">
                @forelse ($user->roles as $role)
                    <x-badge :color="$role->name === 'SuperAdmin' ? 'red' : 'blue'">{{ $role->name }}</x-badge>
                @empty
                    <span class="text-slate-400 text-xs italic">{{ __('None') }}</span>
                @endforelse
            </div>
        </td>
        <td class="px-5 py-4">
            <x-badge :color="$user->status == 1 ? 'green' : 'gray'">{{ $user->status == 1 ? __('Active') : __('Not Active') }}</x-badge>
        </td>
        <td class="px-5 py-4 text-right whitespace-nowrap">
            <a href="{{ route('admin.users.edit', $user->id) }}" class="btn-ghost p-2" title="{{ __('Edit') }}"><x-icon name="edit" class="h-4 w-4" /></a>
            @if ($user->id !== auth()->id())
                <button type="button"
                        x-on:click="$dispatch('open-delete', { id: {{ $user->id }}, name: @js($user->name) })"
                        class="btn-ghost p-2 text-red-500 hover:bg-red-50" title="{{ __('Delete') }}">
                    <x-icon name="trash" class="h-4 w-4" />
                </button>
            @endif
        </td>
    </tr>
@empty
    <tr><td colspan="4" class="text-center text-slate-400 py-16">{{ __('No records') }}</td></tr>
@endforelse
