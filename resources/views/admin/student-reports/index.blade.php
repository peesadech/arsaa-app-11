<x-layouts.admin :header="__('Student Reports')" :subheader="__('Export and print student reports')">
    <x-slot name="actions">
        <x-button variant="secondary" icon="arrow-left" :href="route('admin.students.index')">{{ __('Back') }}</x-button>
    </x-slot>

    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">

        {{-- Student list export --}}
        <x-card>
            <div class="flex items-center gap-3 mb-4">
                <div class="w-10 h-10 rounded-xl bg-brand-50 text-brand-600 flex items-center justify-center"><x-icon name="download" class="h-5 w-5" /></div>
                <h2 class="text-base font-semibold text-slate-900">{{ __('Student list (Excel/CSV)') }}</h2>
            </div>
            <form action="{{ route('admin.student-reports.students-csv') }}" method="GET" class="space-y-3">
                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="form-label">{{ __('Academic Year') }}</label>
                        <select name="academic_year_id" class="form-select rounded-lg">
                            <option value="">{{ __('All Years') }}</option>
                            @foreach($academicYears as $y)<option value="{{ $y->id }}" {{ $yearId == $y->id ? 'selected' : '' }}>{{ $y->year }}</option>@endforeach
                        </select>
                    </div>
                    <div>
                        <label class="form-label">{{ __('Semester') }}</label>
                        <select name="semester_id" class="form-select rounded-lg">
                            <option value="">{{ __('All Semesters') }}</option>
                            @foreach($semesters as $s)<option value="{{ $s->id }}" {{ $semesterId == $s->id ? 'selected' : '' }}>{{ __('Semester') }} {{ $s->semester_number }}</option>@endforeach
                        </select>
                    </div>
                    <div>
                        <label class="form-label">{{ __('Classroom') }}</label>
                        <select name="classroom_id" class="form-select rounded-lg">
                            <option value="">{{ __('All Classrooms') }}</option>
                            @foreach($classrooms as $c)<option value="{{ $c->id }}">{{ $c->name }}</option>@endforeach
                        </select>
                    </div>
                    <div>
                        <label class="form-label">{{ __('Status') }}</label>
                        <select name="status" class="form-select rounded-lg">
                            <option value="">{{ __('All Status') }}</option>
                            <option value="studying">{{ __('Studying') }}</option>
                            <option value="suspended">{{ __('Suspended') }}</option>
                            <option value="resigned">{{ __('Resigned') }}</option>
                            <option value="graduated">{{ __('Graduated') }}</option>
                        </select>
                    </div>
                </div>
                <x-button type="submit" icon="download" class="w-full">{{ __('Export CSV') }}</x-button>
            </form>
        </x-card>

        {{-- Class scores report --}}
        <x-card>
            <div class="flex items-center gap-3 mb-4">
                <div class="w-10 h-10 rounded-xl bg-emerald-50 text-emerald-600 flex items-center justify-center"><x-icon name="clipboard" class="h-5 w-5" /></div>
                <h2 class="text-base font-semibold text-slate-900">{{ __('Class scores report') }}</h2>
            </div>
            <p class="text-xs text-slate-500 mb-3">{{ __('Score summary of every student and course in a classroom') }}</p>
            <form action="{{ route('admin.student-reports.class-scores') }}" method="GET" target="_blank" class="space-y-3" id="classScoresForm">
                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="form-label">{{ __('Academic Year') }} *</label>
                        <select name="academic_year_id" required class="form-select rounded-lg">
                            @foreach($academicYears as $y)<option value="{{ $y->id }}" {{ $yearId == $y->id ? 'selected' : '' }}>{{ $y->year }}</option>@endforeach
                        </select>
                    </div>
                    <div>
                        <label class="form-label">{{ __('Semester') }} *</label>
                        <select name="semester_id" required class="form-select rounded-lg">
                            @foreach($semesters as $s)<option value="{{ $s->id }}" {{ $semesterId == $s->id ? 'selected' : '' }}>{{ __('Semester') }} {{ $s->semester_number }}</option>@endforeach
                        </select>
                    </div>
                    <div>
                        <label class="form-label">{{ __('Grade Level') }} *</label>
                        <select name="grade_id" required class="form-select rounded-lg">
                            @foreach(\App\Models\Grade::where('status', 1)->get() as $g)<option value="{{ $g->id }}">{{ $g->name_th }}</option>@endforeach
                        </select>
                    </div>
                    <div>
                        <label class="form-label">{{ __('Classroom') }} *</label>
                        <select name="classroom_id" required class="form-select rounded-lg">
                            @foreach($classrooms as $c)<option value="{{ $c->id }}">{{ $c->name }}</option>@endforeach
                        </select>
                    </div>
                </div>
                <x-button type="submit" icon="printer" class="w-full">{{ __('Open report') }}</x-button>
            </form>
        </x-card>

        {{-- Incomplete documents --}}
        <x-card>
            <div class="flex items-center gap-3 mb-4">
                <div class="w-10 h-10 rounded-xl bg-amber-50 text-amber-600 flex items-center justify-center"><x-icon name="clipboard" class="h-5 w-5" /></div>
                <h2 class="text-base font-semibold text-slate-900">{{ __('Students with incomplete documents') }}</h2>
            </div>
            <p class="text-xs text-slate-500 mb-3">{{ __('List of studying students whose application documents are not complete') }}</p>
            <x-button icon="printer" :href="route('admin.student-reports.incomplete-documents')" class="w-full">{{ __('Open report') }}</x-button>
        </x-card>

        {{-- Per-student reports hint --}}
        <x-card>
            <div class="flex items-center gap-3 mb-4">
                <div class="w-10 h-10 rounded-xl bg-brand-50 text-brand-600 flex items-center justify-center"><x-icon name="academic" class="h-5 w-5" /></div>
                <h2 class="text-base font-semibold text-slate-900">{{ __('Student profile & Transcript') }}</h2>
            </div>
            <p class="text-xs text-slate-500 mb-3">{{ __('Open from the student list — use the profile icon, or the Transcript button on the student page') }}</p>
            <x-button variant="secondary" icon="users" :href="route('admin.students.index')" class="w-full">{{ __('Go to student list') }}</x-button>
        </x-card>

    </div>
</x-layouts.admin>
