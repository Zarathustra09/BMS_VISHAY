@props([
    'name',
    'label' => null,
    'options' => [],
    'placeholder' => null,
    'selected' => null,
    'help' => null,
    'required' => false,
    'span' => false,
    'id' => null,
    'oldKey' => null,
    'errorKey' => null,
])

@php
    $id = $id ?? $name;
    $oldKey = $oldKey ?? $name;
    $errorKey = $errorKey ?? $oldKey;
    $currentValue = old($oldKey, $selected);
@endphp

<x-form.field :name="$errorKey" :label="$label" :help="$help" :required="$required" :span="$span" :id="$id">
    <select
        name="{{ $name }}"
        id="{{ $id }}"
        @if($required) required @endif
        {{ $attributes->class(['select w-full', 'select-error' => $errors->has($errorKey)]) }}
    >
        @if($placeholder)
            <option value="">{{ $placeholder }}</option>
        @endif

        {{-- Pass `options` as a label=>value array, or fall back to the slot for a
             dynamically-rendered list (e.g. Alpine's <template x-for> floor options). --}}
        @if(count($options))
            @foreach($options as $optionValue => $optionLabel)
                <option value="{{ $optionValue }}" @selected((string) $currentValue === (string) $optionValue)>
                    {{ $optionLabel }}
                </option>
            @endforeach
        @else
            {{ $slot }}
        @endif
    </select>
</x-form.field>
