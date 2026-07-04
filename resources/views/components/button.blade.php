@props(['variant' => 'primary', 'type' => 'button', 'href' => null, 'icon' => null])

@php
    $map = [
        'primary'   => 'btn-primary',
        'secondary' => 'btn-secondary',
        'danger'    => 'btn-danger',
        'ghost'     => 'btn-ghost',
    ];
    $class = $map[$variant] ?? $map['primary'];
@endphp

@if ($href)
    <a href="{{ $href }}" {{ $attributes->merge(['class' => $class]) }}>
        @if ($icon)<x-icon :name="$icon" class="h-4 w-4" />@endif
        {{ $slot }}
    </a>
@else
    <button type="{{ $type }}" {{ $attributes->merge(['class' => $class]) }}>
        @if ($icon)<x-icon :name="$icon" class="h-4 w-4" />@endif
        {{ $slot }}
    </button>
@endif
