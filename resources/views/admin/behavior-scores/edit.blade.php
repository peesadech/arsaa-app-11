@php $isMerit = $type === 'merit'; @endphp
<x-layouts.admin :header="$isMerit ? __('Edit Merit Score') : __('Edit Demerit Score')" :subheader="__('Update behavior score details')">
    <x-slot name="actions">
        <x-button variant="secondary" icon="arrow-left" :href="route('admin.behavior-scores.index', $type)">{{ __('Back to List') }}</x-button>
    </x-slot>

    <form action="{{ route('admin.behavior-scores.update', [$type, $item->id]) }}" method="POST" class="space-y-6">
        @csrf
        @method('PUT')
        @include('admin.behavior-scores._form', ['item' => $item, 'type' => $type])

        <div class="flex items-center justify-end gap-2">
            <x-button variant="secondary" :href="route('admin.behavior-scores.index', $type)">{{ __('Cancel') }}</x-button>
            <x-button type="submit" icon="check">{{ __('Save Changes') }}</x-button>
        </div>
    </form>
</x-layouts.admin>
