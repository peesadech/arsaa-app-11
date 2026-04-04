@extends('layouts.app')

@section('content')
<div class="min-h-screen bg-gray-50 dark:bg-[#18191a] py-10 px-4 sm:px-6 lg:px-8 transition-colors duration-300">
    <div class="max-w-6xl mx-auto">

        {{-- Header --}}
        <div class="flex items-center space-x-4 mb-8">
            <a href="{{ route('admin.timetable.index') }}"
               class="group flex items-center justify-center w-10 h-10 rounded-full bg-white dark:bg-[#242526] shadow-sm border border-gray-200 dark:border-[#3a3b3c] text-gray-500 dark:text-gray-400 hover:text-indigo-600 dark:hover:text-indigo-400 hover:border-indigo-300 transition-all duration-200">
                <i class="fas fa-arrow-left group-hover:-translate-x-0.5 transition-transform"></i>
            </a>
            <div>
                <h1 class="text-2xl font-extrabold text-gray-900 dark:text-white tracking-tight">{{ __('Generation Results') }}</h1>
                <p class="text-sm text-gray-500 dark:text-gray-400 font-medium px-1">
                    {{ $generation->academicYear->year ?? '' }} / {{ __('Semester') }} {{ $generation->semester->semester_number ?? '' }}
                    — {{ __('Created at') }} {{ $generation->created_at->format('d/m/Y H:i') }}
                </p>
            </div>
        </div>

        {{-- Progress Bar (for running/pending) --}}
        @if(in_array($generation->status, ['pending', 'running']))
        <div id="progress-card" class="bg-white dark:bg-[#242526] rounded-[2rem] shadow-sm border border-gray-100 dark:border-[#3a3b3c] p-6 mb-8">
            <div class="flex items-center justify-between mb-3">
                <h3 class="text-sm font-bold text-gray-700 dark:text-gray-300">{{ __('Generating...') }}</h3>
                <span id="progress-text" class="text-sm text-gray-500 dark:text-gray-400">0%</span>
            </div>
            <div class="w-full bg-gray-200 dark:bg-[#3a3b3c] rounded-full h-3">
                <div id="progress-bar" class="bg-indigo-600 h-3 rounded-full transition-all duration-500" style="width: 0%"></div>
            </div>
            <p id="progress-gen" class="text-xs text-gray-400 dark:text-gray-500 mt-2">Generation: 0/{{ $generation->max_generations }}</p>
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
        <div class="mb-6 p-4 bg-rose-50 dark:bg-rose-900/20 border border-rose-200 dark:border-rose-800 rounded-2xl text-rose-700 dark:text-rose-300 text-sm">
            <i class="fas fa-exclamation-triangle mr-1"></i> {{ __('Generation failed') }}: {{ $generation->error_message }}
        </div>
        @endif

        {{-- Solutions --}}
        @if($generation->status === 'completed' && $generation->solutions->isNotEmpty())
        <div class="grid grid-cols-1 md:grid-cols-{{ min($generation->solutions->count(), 3) }} gap-6">
            @foreach($generation->solutions->sortBy('rank') as $solution)
            <div class="bg-white dark:bg-[#242526] rounded-[2rem] shadow-sm border-2 {{ $solution->is_selected ? 'border-indigo-400 dark:border-indigo-500' : 'border-gray-100 dark:border-[#3a3b3c]' }} p-6 relative">
                @if($solution->is_selected)
                <div class="absolute -top-3 left-6 px-3 py-1 bg-indigo-600 text-white text-xs font-bold rounded-full">{{ __('Active') }}</div>
                @endif

                <div class="text-center mb-4">
                    <div class="text-lg font-bold text-gray-800 dark:text-white">{{ __('Rank') }} {{ $solution->rank }}</div>
                </div>

                <div class="space-y-3 mb-6">
                    <div class="flex justify-between text-sm">
                        <span class="text-gray-500 dark:text-gray-400">{{ __('Fitness Score') }}</span>
                        <span class="font-bold text-indigo-600">{{ number_format($solution->fitness_score, 0) }}</span>
                    </div>
                    <div class="flex justify-between text-sm">
                        <span class="text-gray-500 dark:text-gray-400">{{ __('Hard Violations') }}</span>
                        <span class="font-bold {{ $solution->hard_violations > 0 ? 'text-rose-600' : 'text-emerald-600' }}">{{ $solution->hard_violations }}</span>
                    </div>
                    <div class="flex justify-between text-sm">
                        <span class="text-gray-500 dark:text-gray-400">{{ __('Soft Violations') }}</span>
                        <span class="font-bold text-amber-600">{{ $solution->soft_violations }}</span>
                    </div>

                    @if($solution->fitness_breakdown)
                    <div class="pt-2 border-t border-gray-100 dark:border-[#3a3b3c]">
                        <div class="text-xs font-semibold text-gray-500 dark:text-gray-400 mb-1">{{ __('Details') }}</div>
                        @foreach($solution->fitness_breakdown as $key => $val)
                            @if($val > 0)
                            <div class="flex justify-between text-xs text-gray-500 dark:text-gray-400">
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
                       class="block w-full text-center py-2.5 bg-indigo-600 hover:bg-indigo-700 text-white rounded-xl text-sm font-semibold transition-colors">
                        {{ __('View Timetable') }}
                    </a>

                    @if(!$solution->is_selected)
                    <button onclick="selectSolution({{ $solution->id }})"
                            class="block w-full text-center py-2.5 bg-gray-100 dark:bg-[#3a3b3c] hover:bg-gray-200 dark:hover:bg-[#4a4b4c] text-gray-700 dark:text-gray-300 rounded-xl text-sm font-semibold transition-colors">
                        {{ __('Use this Solution') }}
                    </button>
                    @endif

                    <a href="{{ route('admin.timetable.conflicts', $solution->id) }}"
                       class="block w-full text-center py-2.5 text-rose-600 dark:text-rose-400 hover:bg-rose-50 dark:hover:bg-rose-900/20 rounded-xl text-sm font-medium transition-colors">
                        {{ __('View Conflicts') }} ({{ $solution->conflicts->count() }})
                    </a>
                </div>
            </div>
            @endforeach
        </div>
        @endif
    </div>
</div>

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
@endsection
