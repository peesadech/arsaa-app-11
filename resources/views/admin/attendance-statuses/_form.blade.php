@php
    $s = $status ?? null;
@endphp
<div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
    <div class="lg:col-span-2 space-y-6">
        <x-card :title="__('Basic info')">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <x-form.input :label="__('Code')" name="code" :value="$s->code ?? null" required />
                <x-form.input :label="__('Type')" name="status_type" :value="$s->status_type ?? null"
                    :help="__('e.g. PRESENT, LATE, LEAVE, ABSENT, ACTIVITY')" />
                <x-form.input :label="__('Name (TH)')" name="name_th" :value="$s->name_th ?? null" required />
                <x-form.input :label="__('Name (EN)')" name="name_en" :value="$s->name_en ?? null" />
                <x-form.input :label="__('Color')" name="color" type="color" :value="$s->color ?? '#22c55e'" />
                <x-form.input :label="__('Sort order')" name="sort_order" type="number" :value="$s->sort_order ?? 0" />
            </div>
        </x-card>

        <x-card :title="__('Behavior')">
            <div class="divide-y divide-slate-100">
                <x-form.toggle name="is_count_as_present" :label="__('Count as present')"
                    :description="__('Records marked with this status are counted as present.')"
                    :checked="$s->is_count_as_present ?? false" />
                <x-form.toggle name="is_count_as_absent" :label="__('Count as absent')"
                    :description="__('Records marked with this status are counted as absent.')"
                    :checked="$s->is_count_as_absent ?? false" />
                <x-form.toggle name="is_late" :label="__('Marks as late')"
                    :description="__('This status indicates the student was late.')"
                    :checked="$s->is_late ?? false" />
                <x-form.toggle name="is_leave" :label="__('Marks as leave')"
                    :description="__('This status indicates an approved or requested leave.')"
                    :checked="$s->is_leave ?? false" />
                <x-form.toggle name="is_require_remark" :label="__('Requires remark')"
                    :description="__('A remark must be provided when using this status.')"
                    :checked="$s->is_require_remark ?? false" />
            </div>
        </x-card>
    </div>

    <div class="space-y-6">
        <x-card :title="__('Status')">
            <x-form.toggle name="is_active" :label="__('Active')"
                :description="__('Only active statuses appear in pickers.')"
                :checked="$s->is_active ?? true" />
        </x-card>
    </div>
</div>
