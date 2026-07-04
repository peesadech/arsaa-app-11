@php
    $language = $language ?? null;
@endphp
<div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
    <div class="lg:col-span-2 space-y-6">
        <x-card :title="__('Basic info')">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <x-form.input :label="__('Language Code')" name="code" :value="$language->code ?? null" placeholder="e.g. en, th, zh" required />
                <x-form.input :label="__('Flag')" name="flag" :value="$language->flag ?? null" placeholder="🇺🇸, 🇹🇭, 🇨🇳" />
                <x-form.input :label="__('Language Name')" name="name" :value="$language->name ?? null" placeholder="e.g. English, Thai" required />
                <x-form.input :label="__('Native Name')" name="native_name" :value="$language->native_name ?? null" placeholder="e.g. ภาษาไทย, 中文" required />
            </div>
        </x-card>

        <x-card :title="__('Display')">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <x-form.select
                    :label="__('Direction')"
                    name="direction"
                    :options="['ltr' => __('Left to Right') . ' (LTR)', 'rtl' => __('Right to Left') . ' (RTL)']"
                    :selected="$language->direction ?? 'ltr'"
                    required />
                <x-form.input :label="__('Sort Order')" name="sort_order" type="number" :value="$language->sort_order ?? 0" placeholder="0" />
            </div>
        </x-card>
    </div>

    <div class="space-y-6">
        <x-card :title="__('Status')">
            <x-form.select
                name="status"
                :options="[1 => __('Active'), 2 => __('Not Active')]"
                :selected="$language->status ?? 1" />
        </x-card>

        <x-card :title="__('Options')">
            <x-form.toggle
                name="is_default"
                :label="__('Set as Default')"
                :description="__('Use this language as the system default.')"
                :checked="$language->is_default ?? false" />
        </x-card>
    </div>
</div>
