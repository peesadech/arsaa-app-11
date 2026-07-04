<x-layouts.admin :header="__('Generate Timetable')" :subheader="__('Genetic Algorithm Timetable Generator')">
    <x-slot name="actions">
        <x-button variant="secondary" icon="arrow-left" :href="route('admin.timetable.index')">{{ __('Back') }}</x-button>
    </x-slot>

    <div class="max-w-3xl">
        <form action="{{ route('admin.timetable.generate.store') }}" method="POST">
            @csrf
            <x-card>
                <div class="space-y-6">
                    {{-- Algorithm Settings --}}
                    <div>
                        <h3 class="text-sm font-semibold text-slate-700 uppercase tracking-wide mb-4">{{ __('Algorithm Settings') }}</h3>
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                            <div>
                                <label class="form-label">{{ __('Population Size') }}</label>
                                <input type="number" name="population_size" value="30" min="10" max="100" class="form-input">
                            </div>
                            <div>
                                <label class="form-label">{{ __('Max Generations') }}</label>
                                <input type="number" name="max_generations" value="500" min="50" max="2000" class="form-input">
                            </div>
                            <div>
                                <label class="form-label">{{ __('Number of Solutions') }}</label>
                                <input type="number" name="solutions_requested" value="3" min="1" max="10" class="form-input">
                            </div>
                        </div>
                    </div>

                    {{-- Scope --}}
                    <div>
                        <h3 class="text-sm font-semibold text-slate-700 uppercase tracking-wide mb-4">{{ __('Scope (none = all)') }}</h3>

                        <div class="mb-4">
                            <label class="form-label">{{ __('Grade Level') }}</label>
                            <div class="grid grid-cols-2 md:grid-cols-3 gap-2">
                                @foreach(\App\Models\Grade::where('status', 1)->with('educationLevel')->get() as $grade)
                                <label class="flex items-center space-x-2 p-2 bg-slate-50 rounded-lg cursor-pointer hover:bg-slate-100 transition-colors">
                                    <input type="checkbox" name="scope_grade_ids[]" value="{{ $grade->id }}"
                                           class="rounded border-slate-300 text-brand-600 focus:ring-brand-400">
                                    <span class="text-sm text-slate-700">{{ $grade->name_th }}</span>
                                </label>
                                @endforeach
                            </div>
                        </div>

                        <div>
                            <label class="form-label">{{ __('Classroom') }}</label>
                            <div class="grid grid-cols-2 md:grid-cols-4 gap-2">
                                @foreach(\App\Models\Classroom::where('status', 1)->get() as $classroom)
                                <label class="flex items-center space-x-2 p-2 bg-slate-50 rounded-lg cursor-pointer hover:bg-slate-100 transition-colors">
                                    <input type="checkbox" name="scope_classroom_ids[]" value="{{ $classroom->id }}"
                                           class="rounded border-slate-300 text-brand-600 focus:ring-brand-400">
                                    <span class="text-sm text-slate-700">{{ $classroom->name }}</span>
                                </label>
                                @endforeach
                            </div>
                        </div>
                    </div>

                    {{-- Info box --}}
                    <div class="p-4 bg-brand-50 border border-brand-100 rounded-xl text-brand-700 text-sm flex items-start gap-2">
                        <x-icon name="chart" class="h-4 w-4 mt-0.5 shrink-0" />
                        <span>
                            {{ __('Generate info line 1') }}
                            {{ __('Generate info line 2') }}
                        </span>
                    </div>

                    <div class="flex justify-end">
                        <button type="submit" class="btn-primary px-8"
                                onclick="return confirm('{{ __('Confirm generate timetable?') }}')">
                            <x-icon name="check" class="h-4 w-4" /> {{ __('Start Generate') }}
                        </button>
                    </div>
                </div>
            </x-card>
        </form>
    </div>
</x-layouts.admin>
