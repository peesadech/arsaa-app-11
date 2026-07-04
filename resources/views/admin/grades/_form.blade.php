@php
    $g = $grade ?? null;
    $levelOptions = $educationLevels->mapWithKeys(fn ($lvl) => [$lvl->id => $lvl->name_th.' ('.$lvl->name_en.')'])->toArray();
@endphp
<div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
    <div class="lg:col-span-2 space-y-6">
        <x-card :title="__('Basic info')">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <x-form.input :label="__('Name (Thai)')" name="name_th" :value="$g->name_th ?? null" required />
                <x-form.input :label="__('Name (English)')" name="name_en" :value="$g->name_en ?? null" required />
                <div class="md:col-span-2">
                    <x-form.textarea :label="__('Description')" name="description" rows="3" :value="$g->description ?? null" />
                </div>
            </div>
        </x-card>
    </div>

    <div class="space-y-6">
        <x-card :title="__('Education Level')">
            <x-form.select
                name="education_level_id"
                :options="$levelOptions"
                :selected="$g->education_level_id ?? null"
                :placeholder="__('Select Education Level')" />
        </x-card>

        <x-card :title="__('Status')">
            <x-form.select
                name="status"
                :options="[1 => __('Active'), 2 => __('Not Active')]"
                :selected="$g->status ?? 1" />
        </x-card>
    </div>
</div>
