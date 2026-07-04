<x-layouts.admin
    :header="__('My Courses')"
    :subheader="$teacher->name . ' — ' . __('Academic Year') . ' ' . ($academicYear->year ?? '?') . ' / ' . __('Semester') . ' ' . ($semester->semester_number ?? '?')">

    @if(session('status'))
    <div class="mb-6 flex items-center gap-2 p-4 bg-emerald-50 border border-emerald-200 rounded-xl text-emerald-700 text-sm">
        <x-icon name="check" class="h-4 w-4" />{{ session('status') }}
    </div>
    @endif

    @if($openedCourses->isEmpty())
    <x-card>
        <div class="py-10 text-center text-slate-400">
            <x-icon name="book" class="h-8 w-8 mx-auto mb-3" />
            <p class="text-sm font-medium">{{ __('You have no courses in the current term') }}</p>
        </div>
    </x-card>
    @else
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
        @foreach($openedCourses as $oc)
        <a href="{{ route('teacher.scores.entry', $oc->id) }}"
           class="card card-body hover:border-brand-300 hover:shadow-soft transition-all">
            <div class="text-sm font-semibold text-slate-900">{{ $oc->course->name ?? '?' }}</div>
            <div class="text-xs text-slate-400 mt-1">
                {{ $oc->grade->name_th ?? '' }} / {{ $oc->classroom->name ?? '' }}
                @if($oc->course?->subjectGroup) · {{ $oc->course->subjectGroup->name_th }}@endif
            </div>
            <div class="mt-3 text-xs font-semibold flex items-center gap-1 {{ $oc->scored_count > 0 ? 'text-emerald-600' : 'text-slate-300' }}">
                <x-icon name="clipboard" class="h-4 w-4" />{{ $oc->scored_count }} {{ __('scores recorded') }}
            </div>
        </a>
        @endforeach
    </div>
    @endif
</x-layouts.admin>
