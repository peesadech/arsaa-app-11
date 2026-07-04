@php
    $classroom = $classroom ?? null;
@endphp
<div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
    <div class="lg:col-span-2 space-y-6">
        <x-card :title="__('Basic info')">
            <x-form.input :label="__('Name (Classroom)')" name="name" :value="$classroom->name ?? null" :placeholder="__('e.g. 1/1')" required />
        </x-card>
    </div>

    <div class="space-y-6">
        <x-card :title="__('Status')">
            <x-form.select
                name="status"
                :options="[1 => __('Active'), 2 => __('Not Active')]"
                :selected="$classroom->status ?? 1" />
        </x-card>
    </div>
</div>
