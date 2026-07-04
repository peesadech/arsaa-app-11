<x-layouts.admin :header="$courseType->name_th" :subheader="$courseType->name_en">
    <x-slot name="actions">
        <x-button variant="secondary" icon="arrow-left" :href="route('admin.course-types.index')">{{ __('Back to List') }}</x-button>
        <x-button icon="edit" :href="route('admin.course-types.edit', $courseType->id)">{{ __('Edit') }}</x-button>
    </x-slot>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <div class="lg:col-span-2 space-y-6">
            <x-card :title="__('Basic info')">
                <dl class="divide-y divide-slate-100">
                    <div class="grid grid-cols-3 gap-4 py-3">
                        <dt class="text-sm text-slate-500">{{ __('Name (TH)') }}</dt>
                        <dd class="col-span-2 text-sm font-medium text-slate-900">{{ $courseType->name_th }}</dd>
                    </div>
                    <div class="grid grid-cols-3 gap-4 py-3">
                        <dt class="text-sm text-slate-500">{{ __('Name (EN)') }}</dt>
                        <dd class="col-span-2 text-sm font-medium text-slate-900">{{ $courseType->name_en }}</dd>
                    </div>
                    <div class="grid grid-cols-3 gap-4 py-3">
                        <dt class="text-sm text-slate-500">{{ __('Description') }}</dt>
                        <dd class="col-span-2 text-sm text-slate-700">{{ $courseType->description ?: '—' }}</dd>
                    </div>
                </dl>
            </x-card>
        </div>

        <div class="space-y-6">
            <x-card :title="__('Grading Scheme')">
                @if ($courseType->gradingScheme)
                    <x-badge color="blue">{{ $courseType->gradingScheme->name }}</x-badge>
                @else
                    <span class="text-slate-400 text-sm italic">{{ __('Grading not set') }}</span>
                @endif
            </x-card>

            <x-card :title="__('Status')">
                <x-badge :color="$courseType->status == 1 ? 'green' : 'gray'">{{ $courseType->status == 1 ? __('Active') : __('Not Active') }}</x-badge>
            </x-card>
        </div>
    </div>
</x-layouts.admin>
