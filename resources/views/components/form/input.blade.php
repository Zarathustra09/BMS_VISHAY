@props([
    'name',
    'label' => null,
    'type' => 'text',
    'value' => null,
    'help' => null,
    'required' => false,
    'span' => false,
    'id' => null,
    // Use when the html `name` isn't a valid old()/$errors key on its own, e.g. an array
    // name like `thresholds[temperature][warning_min]` (old key `thresholds.temperature.warning_min`).
    'oldKey' => null,
    'errorKey' => null,
])

@php
    $id = $id ?? $name;
    $oldKey = $oldKey ?? $name;
    $errorKey = $errorKey ?? $oldKey;
@endphp

<x-form.field :name="$errorKey" :label="$label" :help="$help" :required="$required" :span="$span" :id="$id">
    <input
        type="{{ $type }}"
        name="{{ $name }}"
        id="{{ $id }}"
        value="{{ old($oldKey, $value) }}"
        @if($required) required @endif
        {{ $attributes->class(['input w-full', 'input-error' => $errors->has($errorKey)]) }}
    />
</x-form.field>
