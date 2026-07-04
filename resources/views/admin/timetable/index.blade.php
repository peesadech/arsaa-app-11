<x-layouts.admin :header="__('Timetable Scheduling')" :subheader="__('Timetable Scheduling Subtitle')">
    <x-slot name="actions">
        <x-button variant="secondary" icon="edit" :href="route('admin.timetable.manual.select')">{{ __('Manual Scheduling') }}</x-button>
        <button type="button" class="btn-primary opacity-50 cursor-not-allowed" disabled>
            <x-icon name="cog" class="h-4 w-4" /> {{ __('Auto Generate') }}
        </button>
    </x-slot>

    {{-- Active Solution Summary --}}
    @if($activeSolution)
    <x-card class="mb-6">
        <div class="flex items-center justify-between mb-4">
            <h2 class="text-lg font-semibold text-slate-900">{{ __('Current Timetable') }}</h2>
            <x-button variant="secondary" icon="eye" :href="route('admin.timetable.solutions.show', $activeSolution->id)">{{ __('View Timetable') }}</x-button>
        </div>
        <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
            <div class="text-center p-4 bg-slate-50 rounded-xl">
                <div class="text-2xl font-bold {{ $activeSolution->hard_violations > 0 ? 'text-red-600' : 'text-emerald-600' }}">{{ $activeSolution->hard_violations }}</div>
                <div class="text-xs text-slate-500 mt-1">{{ __('Hard Violations') }}</div>
            </div>
            <div class="text-center p-4 bg-slate-50 rounded-xl">
                <div class="text-2xl font-bold text-amber-600">{{ $activeSolution->soft_violations }}</div>
                <div class="text-xs text-slate-500 mt-1">{{ __('Soft Violations') }}</div>
            </div>
            <div class="text-center p-4 bg-slate-50 rounded-xl">
                <div class="text-2xl font-bold text-brand-600">{{ number_format($activeSolution->fitness_score, 0) }}</div>
                <div class="text-xs text-slate-500 mt-1">{{ __('Fitness Score') }}</div>
            </div>
            <div class="text-center p-4 bg-slate-50 rounded-xl">
                <div class="text-2xl font-bold text-slate-700">{{ $activeSolution->conflicts->count() }}</div>
                <div class="text-xs text-slate-500 mt-1">{{ __('Total Conflicts') }}</div>
            </div>
        </div>
    </x-card>
    @endif

    {{-- Generation History --}}
    <x-card title="{{ __('Generation History') }}" padded="false">
        @if($generations->isEmpty())
        <p class="text-center text-slate-400 py-10 px-6">{{ __('No generation history') }}</p>
        @else
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead>
                    <tr class="bg-slate-50">
                        <th class="text-left px-5 py-3 text-slate-500 font-medium uppercase tracking-wide text-xs">{{ __('Date') }}</th>
                        <th class="text-left px-5 py-3 text-slate-500 font-medium uppercase tracking-wide text-xs">{{ __('Status') }}</th>
                        <th class="text-left px-5 py-3 text-slate-500 font-medium uppercase tracking-wide text-xs">{{ __('Progress') }}</th>
                        <th class="text-left px-5 py-3 text-slate-500 font-medium uppercase tracking-wide text-xs">{{ __('Solutions') }}</th>
                        <th class="text-left px-5 py-3 text-slate-500 font-medium uppercase tracking-wide text-xs">{{ __('Created By') }}</th>
                        <th class="text-right px-5 py-3"></th>
                    </tr>
                </thead>
                <tbody>
                @foreach($generations as $gen)
                    <tr class="border-t border-slate-100 hover:bg-slate-50">
                        <td class="px-5 py-4 text-slate-700">{{ $gen->created_at->format('d/m/Y H:i') }}</td>
                        <td class="px-5 py-4">
                            @php
                                $badgeColors = ['pending' => 'gray', 'running' => 'blue', 'completed' => 'green', 'failed' => 'red'];
                            @endphp
                            <x-badge :color="$badgeColors[$gen->status] ?? 'gray'">{{ ucfirst($gen->status) }}</x-badge>
                        </td>
                        <td class="px-5 py-4 text-slate-600">{{ $gen->current_generation }}/{{ $gen->max_generations }}</td>
                        <td class="px-5 py-4 text-slate-600">{{ $gen->solutions->count() }}</td>
                        <td class="px-5 py-4 text-slate-600">{{ $gen->user->name ?? '-' }}</td>
                        <td class="px-5 py-4 text-right">
                            @if($gen->status === 'completed')
                            <a href="{{ route('admin.timetable.generations.show', $gen->id) }}" class="text-brand-600 hover:underline text-sm font-medium">
                                {{ __('View Results') }}
                            </a>
                            @elseif($gen->status === 'running' || $gen->status === 'pending')
                            <a href="{{ route('admin.timetable.generations.show', $gen->id) }}" class="text-brand-600 hover:underline text-sm font-medium">
                                {{ __('View Progress') }}
                            </a>
                            @endif
                        </td>
                    </tr>
                @endforeach
                </tbody>
            </table>
        </div>
        @endif
    </x-card>
</x-layouts.admin>
