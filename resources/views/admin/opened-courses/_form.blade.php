@php
    $oc = $openedCourse ?? null;
    $gradeOptions = ($openedGrades ?? collect())
        ->mapWithKeys(fn ($og) => [$og->grade_id => $og->grade?->name_th])
        ->filter()
        ->toArray();
    $classroomOptions = ($classrooms ?? collect())
        ->mapWithKeys(fn ($c) => [$c->classroom_id => $c->classroom?->name])
        ->filter()
        ->toArray();
    $courseOptions = ($courses ?? collect())
        ->mapWithKeys(fn ($c) => [$c->id => $c->name])
        ->filter()
        ->toArray();
@endphp
<div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
    <div class="lg:col-span-2 space-y-6">
        <x-card :title="__('Course information')">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <x-form.select
                    :label="__('Grade Level')"
                    name="grade_id"
                    :options="$gradeOptions"
                    :selected="$oc->grade_id ?? null"
                    :placeholder="__('-- Select Grade Level --')"
                    required />
                <x-form.select
                    :label="__('Classroom')"
                    name="classroom_id"
                    :options="$classroomOptions"
                    :selected="$oc->classroom_id ?? null"
                    :placeholder="__('-- Select Classroom --')"
                    required />
                <div class="md:col-span-2">
                    <x-form.select
                        :label="__('Course')"
                        name="course_id"
                        :options="$courseOptions"
                        :selected="$oc->course_id ?? null"
                        :placeholder="__('-- Select Course --')"
                        required />
                </div>
            </div>
        </x-card>
    </div>

    <div class="space-y-6">
        <x-card :title="__('Academic Period')">
            <dl class="space-y-3">
                <div>
                    <dt class="text-sm text-slate-500">{{ __('Academic Year') }}</dt>
                    <dd class="text-sm font-medium text-slate-900">{{ $currentYear?->year ?? '—' }}</dd>
                </div>
                <div>
                    <dt class="text-sm text-slate-500">{{ __('Semester') }}</dt>
                    <dd class="text-sm font-medium text-slate-900">{{ $currentSemester?->semester_number ?? '—' }}</dd>
                </div>
            </dl>
            <p class="form-help mt-3">{{ __('Courses are opened for the current academic year and semester.') }}</p>
        </x-card>
    </div>
</div>
