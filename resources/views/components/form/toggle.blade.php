@props(['label' => null, 'description' => null, 'name', 'checked' => false])

<label class="flex items-start justify-between gap-4 py-3">
    <div class="min-w-0">
        @if ($label)<span class="block text-sm font-medium text-slate-900">{{ $label }}</span>@endif
        @if ($description)<span class="block text-xs text-slate-500 mt-0.5">{{ $description }}</span>@endif
    </div>
    <span class="relative inline-flex items-center">
        <input type="hidden" name="{{ $name }}" value="0">
        <input type="checkbox" name="{{ $name }}" value="1"
               @checked(old($name, $checked))
               class="peer sr-only" />
        <span class="toggle">
            <span class="toggle-dot"></span>
        </span>
    </span>
</label>
