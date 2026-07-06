@forelse ($teachers as $teacher)
    <tr class="border-t border-slate-100 hover:bg-slate-50">
        <td class="px-5 py-4 w-14">
            @php
                $avatar = $teacher->image_path
                    ? asset($teacher->image_path)
                    : 'https://ui-avatars.com/api/?name=' . urlencode($teacher->name) . '&color=7F9CF5&background=EBF4FF';
            @endphp
            <img src="{{ $avatar }}" class="w-10 h-10 rounded-xl object-cover shadow-sm border border-slate-100" alt="">
        </td>
        <td class="px-5 py-4">
            <a href="{{ route('admin.teacher-term-status.edit', $teacher->id) }}"
               class="font-medium text-slate-900 hover:text-brand-600">{{ $teacher->name }}</a>
        </td>
        <td class="px-5 py-4 text-center">
            <x-badge :color="$teacher->status == 1 ? 'green' : 'red'">
                {{ $teacher->status == 1 ? __('Active') : __('Not Active') }}
            </x-badge>
        </td>
        <td class="px-5 py-4 text-center">
            @php $status = $teacher->term_status; @endphp
            @if (! $status)
                <x-badge color="gray">{{ __('No Record') }}</x-badge>
            @else
                @php
                    $colors = [
                        'available'     => 'bg-emerald-50 text-emerald-600',
                        'unavailable'   => 'bg-rose-50 text-rose-600',
                        'leave'         => 'bg-amber-50 text-amber-600',
                        'partial'       => 'bg-blue-50 text-blue-600',
                        'transferred'   => 'bg-purple-50 text-purple-600',
                        'resigned_term' => 'bg-gray-100 text-gray-600',
                    ];
                    $color = $colors[$status] ?? 'bg-gray-50 text-gray-500';
                @endphp
                <span class="inline-flex items-center px-2.5 py-1 rounded-full {{ $color }} text-xs font-medium">
                    {{ __(ucfirst(str_replace('_', ' ', $status))) }}
                </span>
            @endif
        </td>
        <td class="px-5 py-4 text-center">
            @php
                $can = $teacher->term_status === null ? ($teacher->status == 1) : (bool) $teacher->can_be_scheduled;
            @endphp
            <span class="font-semibold text-xs {{ $can ? 'text-emerald-600' : 'text-rose-600' }}">
                {{ $can ? __('Yes') : __('No') }}
            </span>
        </td>
        <td class="px-5 py-4 text-center">
            @php
                $parts = [];
                if ($teacher->max_periods_per_day) $parts[] = $teacher->max_periods_per_day . '/' . __('Day');
                if ($teacher->max_periods_per_week) $parts[] = $teacher->max_periods_per_week . '/' . __('Week');
            @endphp
            @if ($parts)
                <span class="text-xs text-slate-500">{{ implode(', ', $parts) }}</span>
            @else
                <span class="text-xs text-slate-300">—</span>
            @endif
        </td>
        <td class="px-5 py-4 text-right whitespace-nowrap">
            <a href="{{ route('admin.teacher-term-status.edit', $teacher->id) }}"
               class="btn-ghost p-2 text-amber-500 hover:bg-amber-50" title="{{ __('Edit') }}">
                <x-icon name="edit" class="h-4 w-4" />
            </a>
        </td>
    </tr>
@empty
    <tr><td colspan="7" class="text-center text-slate-400 py-16">{{ __('No records') }}</td></tr>
@endforelse
