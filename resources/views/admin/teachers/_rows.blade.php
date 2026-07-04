@forelse ($teachers as $teacher)
    <tr class="border-t border-slate-100 hover:bg-slate-50">
        <td class="px-5 py-4">
            <a href="{{ route('admin.teachers.edit', $teacher->id) }}" class="font-medium text-slate-900 hover:text-brand-600">{{ $teacher->name }}</a>
            <div class="text-xs text-slate-500">{{ $teacher->email }}</div>
            @if ($teacher->phone)
                <div class="text-xs text-slate-400">{{ $teacher->phone }}</div>
            @endif
        </td>
        <td class="px-5 py-4">
            @if ($teacher->courses->isEmpty())
                <span class="text-slate-400 text-xs italic">{{ __('No courses') }}</span>
            @else
                <div class="flex flex-wrap gap-1">
                    @foreach ($teacher->courses->take(3) as $course)
                        <x-badge color="blue">{{ $course->name }}</x-badge>
                    @endforeach
                    @if ($teacher->courses->count() > 3)
                        <x-badge color="gray">+{{ $teacher->courses->count() - 3 }} {{ __('more') }}</x-badge>
                    @endif
                </div>
            @endif
        </td>
        <td class="px-5 py-4">
            @if ($teacher->user_id)
                <x-badge color="green">{{ __('Has account') }}</x-badge>
            @else
                <x-badge color="gray">{{ __('No account') }}</x-badge>
            @endif
        </td>
        <td class="px-5 py-4">
            <x-badge :color="$teacher->status == 1 ? 'green' : 'gray'">{{ $teacher->status == 1 ? __('Active') : __('Not Active') }}</x-badge>
        </td>
        <td class="px-5 py-4 text-right whitespace-nowrap">
            <a href="{{ route('admin.teachers.edit', $teacher->id) }}" class="btn-ghost p-2" title="{{ __('Edit') }}"><x-icon name="edit" class="h-4 w-4" /></a>
            <button type="button"
                    x-on:click="$dispatch('open-delete', { id: {{ $teacher->id }}, name: @js($teacher->name) })"
                    class="btn-ghost p-2 text-red-500 hover:bg-red-50" title="{{ __('Delete') }}">
                <x-icon name="trash" class="h-4 w-4" />
            </button>
        </td>
    </tr>
@empty
    <tr><td colspan="5" class="text-center text-slate-400 py-16">{{ __('No records') }}</td></tr>
@endforelse
