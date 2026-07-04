@props(['label' => null, 'name', 'options' => [], 'selected' => null, 'placeholder' => null, 'help' => null, 'required' => false])

<div>
    @if ($label)
        <label for="{{ $name }}" class="form-label">
            {{ $label }} @if($required)<span class="text-red-500">*</span>@endif
        </label>
    @endif
    <select name="{{ $name }}" id="{{ $name }}" {{ $attributes->merge(['class' => 'form-select']) }}>
        @if ($placeholder)<option value="">{{ $placeholder }}</option>@endif
        @foreach ($options as $key => $optLabel)
            <option value="{{ $key }}" @selected(old($name, $selected) == $key)>{{ $optLabel }}</option>
        @endforeach
    </select>
    @if ($help)<p class="form-help">{{ $help }}</p>@endif
    @error($name)<p class="form-error">{{ $message }}</p>@enderror
</div>
