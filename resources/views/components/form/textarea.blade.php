@props(['label' => null, 'name', 'value' => null, 'rows' => 4, 'help' => null, 'required' => false])

<div>
    @if ($label)
        <label for="{{ $name }}" class="form-label">
            {{ $label }} @if($required)<span class="text-red-500">*</span>@endif
        </label>
    @endif
    <textarea
        name="{{ $name }}"
        id="{{ $name }}"
        rows="{{ $rows }}"
        {{ $attributes->merge(['class' => 'form-textarea']) }}
    >{{ old($name, $value) }}</textarea>
    @if ($help)<p class="form-help">{{ $help }}</p>@endif
    @error($name)<p class="form-error">{{ $message }}</p>@enderror
</div>
