@props([
    'name',
    'label',
    'checked' => false,
    'value' => '1',
])

{{-- `label.label cursor-pointer` happens to still be correct daisyUI 5 markup — inline-flex
     is exactly what a checkbox-and-caption row wants — so this pattern is kept as-is. --}}
<label class="label cursor-pointer justify-start gap-3 py-0">
    <input
        type="checkbox"
        name="{{ $name }}"
        value="{{ $value }}"
        @checked(old($name, $checked))
        {{ $attributes->class(['checkbox checkbox-sm']) }}
    />
    <span class="text-sm">{{ $label }}</span>
</label>
