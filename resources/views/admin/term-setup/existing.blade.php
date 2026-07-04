<x-layouts.admin :header="__('Existing terms')" :subheader="__('Select a term to open it')">
    <x-slot name="actions">
        <x-button variant="secondary" icon="arrow-left" :href="route('admin.dashboard')">{{ __('Back') }}</x-button>
        <x-button icon="plus" :href="route('admin.term-setup.index')">{{ __('New Term Setup') }}</x-button>
    </x-slot>

    {{-- Flash --}}
    @if(session('status'))
    <div class="mb-6 rounded-xl bg-emerald-50 border border-emerald-200 text-emerald-700 text-sm px-4 py-3">
        {{ session('status') }}
    </div>
    @endif
    @if(session('error'))
    <div class="mb-6 rounded-xl bg-red-50 border border-red-200 text-red-700 text-sm px-4 py-3">
        {{ session('error') }}
    </div>
    @endif

    @if($existingTerms->isEmpty())
    <x-card>
        <div class="py-10 text-center text-slate-400">
            <i class="fas fa-calendar-times text-3xl mb-3"></i>
            <p class="text-sm font-medium">{{ __('No previous term with data found') }}</p>
        </div>
    </x-card>
    @else
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
        @foreach($existingTerms as $term)
        @php $isCurrent = $term['academic_year_id'] == $yearId && $term['semester_id'] == $semesterId; @endphp
        <div class="card {{ $isCurrent ? 'border-brand-200 ring-1 ring-brand-100' : '' }}">
            <div class="card-body">
                <div class="flex items-center justify-between mb-3">
                    <div class="text-base font-semibold text-slate-800">
                        {{ __('Academic Year') }} {{ $term['year'] }} / {{ __('Semester') }} {{ $term['semester_number'] }}
                    </div>
                    @if($isCurrent)
                    <span class="badge-blue uppercase">{{ __('Currently viewing') }}</span>
                    @endif
                </div>
                <div class="grid grid-cols-2 gap-2 text-[11px] text-slate-500 mb-4">
                    <div><i class="fas fa-layer-group mr-1 text-slate-300"></i>{{ __('Grade Levels') }}: <span class="font-bold text-slate-700">{{ $term['summary']['opened_grades']['count'] }}</span></div>
                    <div><i class="fas fa-school mr-1 text-slate-300"></i>{{ __('Classrooms') }}: <span class="font-bold text-slate-700">{{ $term['summary']['opened_classrooms']['count'] }}</span></div>
                    <div><i class="fas fa-book mr-1 text-slate-300"></i>{{ __('Courses') }}: <span class="font-bold text-slate-700">{{ $term['summary']['opened_courses']['count'] }}</span></div>
                    <div><i class="fas fa-user-check mr-1 text-slate-300"></i>{{ __('Teachers') }}: <span class="font-bold text-slate-700">{{ $term['summary']['teacher_term_statuses']['count'] }}</span></div>
                    <div><i class="fas fa-calendar-alt mr-1 text-slate-300"></i>{{ __('Schedules') }}: <span class="font-bold text-slate-700">{{ $term['summary']['yearly_schedules']['count'] }}</span></div>
                </div>
                @unless($isCurrent)
                <form action="{{ route('admin.academic-years.select-current') }}" method="POST">
                    @csrf
                    <input type="hidden" name="academic_year_id" value="{{ $term['academic_year_id'] }}">
                    <input type="hidden" name="semester_id" value="{{ $term['semester_id'] }}">
                    <input type="hidden" name="redirect_dashboard" value="1">
                    <button type="submit" class="btn-primary w-full">
                        <i class="fas fa-sign-in-alt text-xs"></i> {{ __('Open this term') }}
                    </button>
                </form>
                @endunless
            </div>
        </div>
        @endforeach
    </div>
    @endif
</x-layouts.admin>
