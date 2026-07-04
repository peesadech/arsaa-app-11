<x-layouts.admin :header="__('Generation Results')" :subheader="($generation->academicYear->year ?? '') . ' / ' . __('Semester') . ' ' . ($generation->semester->semester_number ?? '') . ' — ' . __('Created at') . ' ' . $generation->created_at->format('d/m/Y H:i')">
    <x-slot name="actions">
        <x-button variant="secondary" icon="arrow-left" :href="route('admin.timetable.index')">{{ __('Back') }}</x-button>
    </x-slot>

    {{-- Progress Bar (for running/pending) --}}
    @if(in_array($generation->status, ['pending', 'running']))
    <div id="progress-card" class="card card-body mb-8">
        <div class="flex items-center justify-between mb-3">
            <h3 class="text-sm font-semibold text-slate-700">{{ __('Generating...') }}</h3>
            <span id="progress-text" class="text-sm text-slate-500">0%</span>
        </div>
        <div class="w-full bg-slate-200 rounded-full h-3">
            <div id="progress-bar" class="bg-brand-600 h-3 rounded-full transition-all duration-500" style="width: 0%"></div>
        </div>
        <p id="progress-gen" class="text-xs text-slate-400 mt-2">Generation: 0/{{ $generation->max_generations }}</p>
    </div>

    <script>
    (function pollProgress() {
        const genId = {{ $generation->id }};
        const interval = setInterval(() => {
            fetch(`/admin/timetable/api/generations/${genId}/progress`)
                .then(r => r.json())
                .then(data => {
                    document.getElementById('progress-bar').style.width = data.progress_percent + '%';
                    document.getElementById('progress-text').textContent = data.progress_percent + '%';
                    document.getElementById('progress-gen').textContent = `Generation: ${data.current_generation}/${data.max_generations}`;

                    if (data.status === 'completed' || data.status === 'failed') {
                        clearInterval(interval);
                        location.reload();
                    }
                });
        }, 2000);
    })();
    </script>
    @endif

    {{-- Error --}}
    @if($generation->status === 'failed')
    <div class="mb-6 p-4 bg-red-50 border border-red-200 rounded-xl text-red-700 text-sm">
        {{ __('Generation failed') }}: {{ $generation->error_message }}
    </div>
    @endif

    {{-- Solutions --}}
    @if($generation->status === 'completed' && $generation->solutions->isNotEmpty())
    <div class="grid grid-cols-1 md:grid-cols-{{ min($generation->solutions->count(), 3) }} gap-6">
        @foreach($generation->solutions->sortBy('rank') as $solution)
        <div class="bg-white rounded-2xl shadow-card border-2 {{ $solution->is_selected ? 'border-brand-400' : 'border-slate-100' }} p-6 relative">
            @if($solution->is_selected)
            <div class="absolute -top-3 left-6 px-3 py-1 bg-brand-600 text-white text-xs font-bold rounded-full">{{ __('Active') }}</div>
            @endif

            <div class="text-center mb-4">
                <div class="text-lg font-semibold text-slate-800">{{ __('Rank') }} {{ $solution->rank }}</div>
            </div>

            <div class="space-y-3 mb-6">
                <div class="flex justify-between text-sm">
                    <span class="text-slate-500">{{ __('Fitness Score') }}</span>
                    <span class="font-bold text-brand-600">{{ number_format($solution->fitness_score, 0) }}</span>
                </div>
                <div class="flex justify-between text-sm">
                    <span class="text-slate-500">{{ __('Hard Violations') }}</span>
                    <span class="font-bold {{ $solution->hard_violations > 0 ? 'text-red-600' : 'text-emerald-600' }}">{{ $solution->hard_violations }}</span>
                </div>
                <div class="flex justify-between text-sm">
                    <span class="text-slate-500">{{ __('Soft Violations') }}</span>
                    <span class="font-bold text-amber-600">{{ $solution->soft_violations }}</span>
                </div>

                @if($solution->fitness_breakdown)
                <div class="pt-2 border-t border-slate-100">
                    <div class="text-xs font-semibold text-slate-500 mb-1">{{ __('Details') }}</div>
                    @foreach($solution->fitness_breakdown as $key => $val)
                        @if($val > 0)
                        <div class="flex justify-between text-xs text-slate-500">
                            <span>{{ str_replace('_', ' ', ucfirst($key)) }}</span>
                            <span class="font-mono">{{ $val }}</span>
                        </div>
                        @endif
                    @endforeach
                </div>
                @endif
            </div>

            <div class="space-y-2">
                <a href="{{ route('admin.timetable.solutions.show', $solution->id) }}"
                   class="block w-full text-center py-2.5 bg-brand-600 hover:bg-brand-700 text-white rounded-lg text-sm font-semibold transition-colors">
                    {{ __('View Timetable') }}
                </a>

                @if(!$solution->is_selected)
                <button onclick="selectSolution({{ $solution->id }})"
                        class="block w-full text-center py-2.5 bg-slate-100 hover:bg-slate-200 text-slate-700 rounded-lg text-sm font-semibold transition-colors">
                    {{ __('Use this Solution') }}
                </button>
                @endif

                <a href="{{ route('admin.timetable.conflicts', $solution->id) }}"
                   class="block w-full text-center py-2.5 text-red-600 hover:bg-red-50 rounded-lg text-sm font-medium transition-colors">
                    {{ __('View Conflicts') }} ({{ $solution->conflicts->count() }})
                </a>
            </div>
        </div>
        @endforeach
    </div>
    @endif

    <script>
    function selectSolution(id) {
        if (!confirm('{{ __('Use this Solution?') }}')) return;
        fetch(`/admin/timetable/api/solutions/${id}/select`, {
            method: 'POST',
            headers: {'X-CSRF-TOKEN': '{{ csrf_token() }}', 'Content-Type': 'application/json'},
        }).then(r => r.json()).then(data => {
            if (data.success) location.reload();
        });
    }
    </script>
</x-layouts.admin>
