@php
    $sem = $semester ?? null;
@endphp
<div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
    <div class="lg:col-span-2 space-y-6">
        <x-card :title="__('Basic info')">
            <x-form.input :label="__('Semester Number')" name="semester_number" type="number" :value="$sem->semester_number ?? null" required />
        </x-card>
    </div>

    <div class="space-y-6">
        <x-card :title="__('Status')">
            <x-form.select
                name="status"
                :options="[1 => __('Active'), 2 => __('Not Active')]"
                :selected="$sem->status ?? 1" />
        </x-card>
    </div>
</div>
