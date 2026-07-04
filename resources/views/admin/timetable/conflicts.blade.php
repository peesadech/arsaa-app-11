<x-layouts.admin :header="__('Conflict Report')" :subheader="__('Solution') . ' #' . $solution->rank . ' — ' . $conflicts->count() . ' ' . __('conflicts')">
    <x-slot name="actions">
        <x-button variant="secondary" icon="arrow-left" :href="route('admin.timetable.generations.show', $solution->generation_id)">{{ __('Back') }}</x-button>
    </x-slot>

    {{-- Summary --}}
    @php
        $hardCount = $conflicts->where('severity', 'hard')->count();
        $softCount = $conflicts->where('severity', 'soft')->count();
        $byType = $conflicts->groupBy('type');
    @endphp
    <div class="grid grid-cols-2 md:grid-cols-4 gap-4 mb-6">
        <x-card>
            <div class="text-center">
                <div class="text-2xl font-bold {{ $hardCount > 0 ? 'text-red-600' : 'text-emerald-600' }}">{{ $hardCount }}</div>
                <div class="text-xs text-slate-500">{{ __('Hard') }}</div>
            </div>
        </x-card>
        <x-card>
            <div class="text-center">
                <div class="text-2xl font-bold text-amber-600">{{ $softCount }}</div>
                <div class="text-xs text-slate-500">{{ __('Soft') }}</div>
            </div>
        </x-card>
        @foreach($byType->take(2) as $type => $items)
        <x-card>
            <div class="text-center">
                <div class="text-2xl font-bold text-slate-700">{{ $items->count() }}</div>
                <div class="text-xs text-slate-500">{{ str_replace('_', ' ', ucfirst($type)) }}</div>
            </div>
        </x-card>
        @endforeach
    </div>

    {{-- Conflict List --}}
    <x-card>
        @if($conflicts->isEmpty())
        <p class="text-center text-emerald-600 py-8 text-lg font-bold flex items-center justify-center gap-2">
            <x-icon name="check" class="h-6 w-6" /> {{ __('No Conflicts') }}
        </p>
        @else
        <div class="space-y-3">
            @foreach($conflicts as $conflict)
            @php
                $isSoft = $conflict->severity === 'soft';
                $details = $conflict->details ?? [];
                $dayNames = [1=>__('Monday'), 2=>__('Tuesday'), 3=>__('Wednesday'), 4=>__('Thursday'), 5=>__('Friday'), 6=>__('Saturday'), 7=>__('Sunday')];
            @endphp
            <div class="flex items-start space-x-3 p-3 rounded-xl {{ $isSoft ? 'bg-amber-50 border border-amber-200' : 'bg-red-50 border border-red-200' }}">
                <div class="flex-shrink-0 mt-0.5">
                    <span class="inline-flex px-2 py-0.5 rounded-full text-[10px] font-bold {{ $isSoft ? 'bg-amber-200 text-amber-800' : 'bg-red-200 text-red-800' }}">
                        {{ strtoupper($conflict->severity) }}
                    </span>
                </div>
                <div class="flex-1 min-w-0">
                    <div class="text-sm font-medium {{ $isSoft ? 'text-amber-800' : 'text-red-800' }}">
                        {{ $details['message'] ?? str_replace('_', ' ', $conflict->type) }}
                    </div>
                    @if($conflict->day)
                    <div class="text-xs {{ $isSoft ? 'text-amber-600' : 'text-red-600' }} mt-0.5">
                        {{ $dayNames[$conflict->day] ?? $conflict->day }} {{ __('Period') }} {{ $conflict->period }}
                    </div>
                    @endif
                    <div class="text-[10px] text-slate-500 mt-0.5">
                        {{ __('Type') }}: {{ str_replace('_', ' ', $conflict->type) }}
                    </div>
                </div>
            </div>
            @endforeach
        </div>
        @endif
    </x-card>
</x-layouts.admin>
