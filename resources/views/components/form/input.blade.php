@props(['label' => null, 'name', 'type' => 'text', 'value' => null, 'help' => null, 'required' => false])

<div>
    @if ($label)
        <label for="{{ $name }}" class="form-label">
            {{ $label }} @if($required)<span class="text-red-500">*</span>@endif
        </label>
    @endif
    <input
        type="{{ $type }}"
        name="{{ $name }}"
        id="{{ $name }}"
        value="{{ old($name, $value) }}"
        {{ $attributes->merge(['class' => 'form-input']) }}
    />
    @if ($help)<p class="form-help">{{ $help }}</p>@endif
    @error($name)<p class="form-error">{{ $message }}</p>@enderror
</div>
