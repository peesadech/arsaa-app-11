@props(['title' => null, 'description' => null, 'padded' => true])

<div {{ $attributes->merge(['class' => 'card']) }}>
    @if ($title || $description || isset($actions))
        <div class="flex items-start justify-between gap-4 px-6 py-5 border-b border-slate-100">
            <div>
                @if ($title)<h3 class="text-base font-semibold text-slate-900">{{ $title }}</h3>@endif
                @if ($description)<p class="text-sm text-slate-500 mt-0.5">{{ $description }}</p>@endif
            </div>
            @isset($actions)
                <div class="flex items-center gap-2">{{ $actions }}</div>
            @endisset
        </div>
    @endif
    <div class="{{ $padded ? 'card-body' : '' }}">{{ $slot }}</div>
</div>
