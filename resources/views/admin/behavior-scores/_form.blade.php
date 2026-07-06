@php
    $it = $item ?? null;
    $isMerit = $type === 'merit';
    $hint = $isMerit
        ? __('Score must be greater than 0 (e.g. 0.1, 0.3)')
        : __('Score must be less than 0 (e.g. -0.1, -0.2)');
@endphp
<div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
    <div class="lg:col-span-2 space-y-6">
        <x-card :title="__('Basic info')">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div class="md:col-span-2">
                    <x-form.input :label="__('Name')" name="name" :value="$it->name ?? null" required
                        :placeholder="$isMerit ? __('e.g. Volunteer help') : __('e.g. Late arrival')" />
                </div>
                @if($isMerit)
                <x-form.input :label="__('Score')" name="score" type="number" step="0.01" min="0.01" placeholder="0.10"
                    :value="$it ? ($it->score + 0) : null" :help="$hint" required />
                @else
                <x-form.input :label="__('Score')" name="score" type="number" step="0.01" max="-0.01" placeholder="-0.10"
                    :value="$it ? ($it->score + 0) : null" :help="$hint" required />
                @endif
            </div>
        </x-card>
    </div>

    <div class="space-y-6">
        <x-card :title="__('Status')">
            <x-form.toggle name="is_active" :label="__('Active')"
                :description="__('Only active items appear in pickers.')"
                :checked="$it->is_active ?? true" />
        </x-card>
    </div>
</div>
