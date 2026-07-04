@php
    $c = $course ?? null;

    $subjectGroupOptions = $subjectGroups->mapWithKeys(fn ($sg) => [$sg->id => $sg->name_th . ' / ' . $sg->name_en])->toArray();
    $courseTypeOptions   = $courseTypes->mapWithKeys(fn ($ct) => [$ct->id => $ct->name_th . ' / ' . $ct->name_en])->toArray();
    $gradeOptions        = $grades->mapWithKeys(fn ($g) => [$g->id => $g->name_th . ' / ' . $g->name_en])->toArray();
    $semesterOptions     = $semesters->mapWithKeys(fn ($s) => [$s->id => __('Semester') . ' ' . $s->semester_number])->toArray();
    $schemeOptions       = $gradingSchemes->pluck('name', 'id')->toArray();

    $savedDays = old('preferred_days', $c->preferred_days ?? []);
    $dayOptions = [
        1 => __('Mon'),
        2 => __('Tue'),
        3 => __('Wed'),
        4 => __('Thu'),
        5 => __('Fri'),
        6 => __('Sat'),
        7 => __('Sun'),
    ];
@endphp

<div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
    <div class="lg:col-span-2 space-y-6">
        <x-card :title="__('Basic info')">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div class="md:col-span-2">
                    <x-form.input :label="__('Name (Course)')" name="name" :value="$c->name ?? null" required />
                </div>
                <x-form.select
                    :label="__('Subject Group')"
                    name="subject_group_id"
                    :options="$subjectGroupOptions"
                    :selected="$c->subject_group_id ?? null"
                    :placeholder="__('-- Please Select --')"
                    required />
                <x-form.select
                    :label="__('Course Type')"
                    name="course_type_id"
                    :options="$courseTypeOptions"
                    :selected="$c->course_type_id ?? null"
                    :placeholder="__('-- Please Select --')"
                    required />
                <x-form.select
                    :label="__('Select Grade')"
                    name="grade_id"
                    :options="$gradeOptions"
                    :selected="$c->grade_id ?? null"
                    :placeholder="__('-- Please Select --')"
                    required />
                <x-form.select
                    :label="__('Select Semester')"
                    name="semester_id"
                    :options="$semesterOptions"
                    :selected="$c->semester_id ?? null"
                    :placeholder="__('-- Please Select --')"
                    required />
            </div>
        </x-card>

        <x-card :title="__('Scheduling')">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <x-form.input
                    type="number"
                    :label="__('Periods per Week')"
                    name="periods_per_week"
                    :value="$c->periods_per_week ?? 1"
                    :help="__('Total periods in a week')"
                    min="1" max="20" required />
                <x-form.input
                    type="number"
                    :label="__('Periods per Session')"
                    name="periods_per_session"
                    :value="$c->periods_per_session ?? 1"
                    :help="__('Consecutive periods per class')"
                    min="1" max="10" required />
            </div>

            <div class="mt-4">
                <label class="form-label">{{ __('Preferred Days') }}</label>
                <p class="form-help mb-2">{{ __('Leave empty for any day') }}</p>
                <div class="flex flex-wrap gap-2">
                    @foreach ($dayOptions as $dayNum => $dayLabel)
                        <label class="relative cursor-pointer">
                            <input type="checkbox" name="preferred_days[]" value="{{ $dayNum }}" class="peer sr-only" {{ in_array($dayNum, $savedDays) ? 'checked' : '' }}>
                            <span class="inline-block px-3.5 py-2 rounded-lg border border-slate-200 bg-white text-sm font-medium text-slate-500 transition peer-checked:border-brand-500 peer-checked:bg-brand-50 peer-checked:text-brand-600">
                                {{ $dayLabel }}
                            </span>
                        </label>
                    @endforeach
                </div>
                @error('preferred_days')<p class="form-error">{{ $message }}</p>@enderror
            </div>
        </x-card>
    </div>

    <div class="space-y-6">
        <x-card :title="__('Grading Scheme')">
            <x-form.select
                name="grading_scheme_id"
                :options="$schemeOptions"
                :selected="$c->grading_scheme_id ?? null"
                :placeholder="__('Use course type default')" />
            <p class="form-help mt-2">{{ __('leave empty = use course type default') }}</p>
        </x-card>

        <x-card :title="__('Status')">
            <x-form.select
                name="status"
                :options="[1 => __('Active'), 2 => __('Not Active')]"
                :selected="$c->status ?? 1" />
        </x-card>
    </div>
</div>
