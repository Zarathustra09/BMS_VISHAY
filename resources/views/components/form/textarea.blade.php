@props([
    'name',
    'label' => null,
    'value' => null,
    'rows' => 3,
    'help' => null,
    'required' => false,
    'span' => false,
    'id' => null,
])

@php $id = $id ?? $name; @endphp

<x-form.field :name="$name" :label="$label" :help="$help" :required="$required" :span="$span" :id="$id">
    <textarea
        name="{{ $name }}"
        id="{{ $id }}"
        rows="{{ $rows }}"
        @if($required) required @endif
        {{ $attributes->class(['textarea w-full', 'textarea-error' => $errors->has($name)]) }}
    >{{ old($name, $value) }}</textarea>
</x-form.field>
