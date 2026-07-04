@php
    $group = $subjectGroup ?? null;
@endphp
<div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
    <div class="lg:col-span-2 space-y-6">
        <x-card :title="__('Basic info')">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <x-form.input :label="__('Name (TH)')" name="name_th" :value="$group->name_th ?? null" required />
                <x-form.input :label="__('Name (EN)')" name="name_en" :value="$group->name_en ?? null" required />
                <div class="md:col-span-2">
                    <x-form.textarea :label="__('Description')" name="description" rows="3" :value="$group->description ?? null" />
                </div>
            </div>
        </x-card>
    </div>

    <div class="space-y-6">
        <x-card :title="__('Status')">
            <x-form.select
                name="status"
                :options="[1 => __('Active'), 2 => __('Not Active')]"
                :selected="$group->status ?? 1" />
        </x-card>
    </div>
</div>
