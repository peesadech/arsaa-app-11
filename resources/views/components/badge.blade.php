@props(['color' => 'gray'])

@php
    $map = [
        'gray'   => 'badge-gray',
        'blue'   => 'badge-blue',
        'green'  => 'badge-green',
        'amber'  => 'badge-amber',
        'red'    => 'badge-red',
    ];
    $class = $map[$color] ?? $map['gray'];
@endphp

<span {{ $attributes->merge(['class' => $class]) }}>{{ $slot }}</span>
