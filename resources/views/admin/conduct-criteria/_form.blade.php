@php $it = $item ?? null; @endphp
<div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
    <div class="lg:col-span-2 space-y-6">
        <x-card :title="__('Basic info')">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <x-form.input :label="__('Name')" name="name" :value="$it->name ?? null" required :placeholder="__('e.g. Manners')" />
                <x-form.input :label="__('Name (CN)')" name="name_cn" :value="$it->name_cn ?? null" placeholder="礼貌" />
                <x-form.input :label="__('Full Score')" name="max_score" type="number" step="0.01" min="1" :value="$it ? ($it->max_score + 0) : 100" required />
                <x-form.input :label="__('Sort order')" name="sort_order" type="number" :value="$it->sort_order ?? 0" />
            </div>
        </x-card>
    </div>
    <div class="space-y-6">
        <x-card :title="__('Status')">
            <x-form.toggle name="is_active" :label="__('Active')" :description="__('Only active items appear in pickers.')" :checked="$it->is_active ?? true" />
        </x-card>
    </div>
</div>
