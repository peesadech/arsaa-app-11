@php
    $ct = $courseType ?? null;
    $schemeOptions = $gradingSchemes->pluck('name', 'id')->toArray();
@endphp
<div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
    <div class="lg:col-span-2 space-y-6">
        <x-card :title="__('Basic info')">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <x-form.input :label="__('Name (TH)')" name="name_th" :value="$ct->name_th ?? null" required />
                <x-form.input :label="__('Name (EN)')" name="name_en" :value="$ct->name_en ?? null" required />
                <div class="md:col-span-2">
                    <x-form.textarea :label="__('Description')" name="description" rows="3" :value="$ct->description ?? null" />
                </div>
            </div>
        </x-card>
    </div>

    <div class="space-y-6">
        <x-card :title="__('Grading Scheme')">
            <x-form.select
                name="grading_scheme_id"
                :options="$schemeOptions"
                :selected="$ct->grading_scheme_id ?? null"
                :placeholder="__('Use course type default')" />
            <p class="form-help mt-2">{{ __('Default grading scheme for courses of this type. Courses can override it.') }}</p>
        </x-card>

        <x-card :title="__('Status')">
            <x-form.select
                name="status"
                :options="[1 => __('Active'), 2 => __('Not Active')]"
                :selected="$ct->status ?? 1" />
        </x-card>
    </div>
</div>
