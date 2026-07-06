@php $isMerit = $type === 'merit'; @endphp
<x-layouts.admin :header="$isMerit ? __('Create Merit Score') : __('Create Demerit Score')" :subheader="__('Behavior score registration')">
    <x-slot name="actions">
        <x-button variant="secondary" icon="arrow-left" :href="route('admin.behavior-scores.index', $type)">{{ __('Back to List') }}</x-button>
    </x-slot>

    <form action="{{ route('admin.behavior-scores.store', $type) }}" method="POST" class="space-y-6">
        @csrf
        @include('admin.behavior-scores._form', ['item' => null, 'type' => $type])

        <div class="flex items-center justify-end gap-2">
            <x-button variant="secondary" :href="route('admin.behavior-scores.index', $type)">{{ __('Cancel') }}</x-button>
            <x-button type="submit" icon="check">{{ __('Create') }}</x-button>
        </div>
    </form>
</x-layouts.admin>
