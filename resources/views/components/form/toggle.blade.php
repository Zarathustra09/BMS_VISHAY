@props([
    'name',
    'label',
    'description' => null,
    'checked' => false,
    'color' => 'primary',
])

{{--
    The hidden-0 + checkbox-1 pattern from settings/index.blade.php — the only idiom in the
    app that correctly persists an unchecked boolean (an unchecked <input type=checkbox> never
    submits at all, so the hidden field is what supplies the "0" when unticked).
--}}
<div class="flex items-center gap-4 p-4 rounded-xl border border-base-300 bg-base-200/50">
    <div class="flex-1 min-w-0">
        <p class="font-medium text-sm">{{ $label }}</p>
        @if($description)
            <p class="text-xs opacity-60 mt-0.5">{{ $description }}</p>
        @endif
    </div>
    <input type="hidden" name="{{ $name }}" value="0" />
    <input
        type="checkbox"
        name="{{ $name }}"
        value="1"
        @checked(old($name, $checked))
        {{ $attributes->class(['toggle', 'toggle-'.$color, 'toggle-lg']) }}
    />
</div>
