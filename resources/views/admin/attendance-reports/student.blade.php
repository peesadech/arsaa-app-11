@php
    $activeFilters = array_filter($filters, fn ($v) => $v !== null && $v !== '');
@endphp
<x-layouts.admin :header="$student->name_th . ($student->name_cn ? ' · ' . $student->name_cn : '')" :subheader="__('Attendance detail') . ' · ' . $student->student_code">
    <x-slot name="actions">
        <x-button variant="secondary" icon="arrow-left" :href="route('admin.attendance-reports.index', $activeFilters)">{{ __('Back') }}</x-button>
    </x-slot>

    {{-- Summary --}}
    <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-6 gap-3 mb-5">
        @php
            $tiles = [
                ['label' => __('Sessions'), 'value' => $tally['sessions'], 'color' => 'text-slate-800'],
                ['label' => __('Present'),  'value' => $tally['present'],  'color' => 'text-emerald-600'],
                ['label' => __('Late'),     'value' => $tally['late'],     'color' => 'text-amber-600'],
                ['label' => __('Leave'),    'value' => $tally['leave'],    'color' => 'text-blue-600'],
                ['label' => __('Absent'),   'value' => $tally['absent'],   'color' => 'text-red-600'],
                ['label' => __('Attendance %'), 'value' => $tally['percent'].'%', 'color' => 'text-brand-700'],
            ];
        @endphp
        @foreach($tiles as $tile)
            <div class="card px-4 py-3">
                <div class="text-[11px] font-bold uppercase tracking-wide text-slate-400">{{ $tile['label'] }}</div>
                <div class="text-xl font-extrabold mt-1 {{ $tile['color'] }}">{{ $tile['value'] }}</div>
            </div>
        @endforeach
    </div>

    {{-- Session-by-session records --}}
    <x-card padded="false">
        <div class="overflow-x-auto">
            <table class="w-full text-sm" style="min-width:640px">
                <thead>
                    <tr class="text-left text-[10px] font-bold text-slate-400 uppercase tracking-widest bg-slate-50">
                        <th class="px-4 py-3">{{ __('Date') }}</th>
                        <th class="px-4 py-3">{{ __('Course') }}</th>
                        <th class="px-4 py-3">{{ __('Classroom') }}</th>
                        <th class="px-4 py-3">{{ __('Status') }}</th>
                        <th class="px-4 py-3">{{ __('Remark') }}</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($records as $rec)
                        @php $cs = $rec->classSession; $st = $rec->attendanceStatus; @endphp
                        <tr class="border-t border-slate-100">
                            <td class="px-4 py-3 whitespace-nowrap">{{ optional($cs?->session_date)->format('d M Y') }}</td>
                            <td class="px-4 py-3">{{ $cs?->course?->name ?? '—' }}</td>
                            <td class="px-4 py-3">{{ $cs?->grade?->name_th }} / {{ $cs?->classroom?->name }}</td>
                            <td class="px-4 py-3">
                                @if($st)
                                    <span class="inline-flex items-center px-2.5 py-1 rounded-lg text-xs font-bold"
                                          style="background: {{ $st->color }}1a; color: {{ $st->color }}; border:1px solid {{ $st->color }}66;">
                                        {{ $st->name }}
                                    </span>
                                @else
                                    <span class="text-slate-400 text-xs">—</span>
                                @endif
                            </td>
                            <td class="px-4 py-3 text-slate-500">{{ $rec->remark }}</td>
                        </tr>
                    @empty
                        <tr><td colspan="5" class="text-center text-slate-400 py-16">{{ __('No records') }}</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </x-card>
</x-layouts.admin>
