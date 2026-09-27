@props([
    'name',
    'label' => null,
    'help' => null,
    'required' => false,
    'span' => false,
    'id' => null,
])

{{--
    Base field wrapper — every <x-form.*> control renders through this. It owns the one
    thing that broke under daisyUI 5 (see docs/08-frontend.md): the label sits in its own
    block-level row above the control, and the error/help text is a block <p>, never an
    inline <span> that can end up sharing the control's line.
--}}
<div {{ $attributes->class(['flex flex-col gap-1.5', 'md:col-span-2' => $span]) }}>
    @if($label)
        <label class="label py-0" for="{{ $id ?? $name }}">
            <span class="text-sm font-medium">
                {{ $label }}
                @if($required)<span class="text-error">*</span>@endif
            </span>
        </label>
    @endif

    {{ $slot }}

    @error($name)
        <p class="text-error text-xs mt-0.5">{{ $message }}</p>
    @else
        @if($help)
            {{-- $help is always a literal string authored in the calling view, never user
                 input, so a <code> tag inside it (e.g. "see `app:foo`") is intentional. --}}
            <p class="text-xs opacity-60 mt-0.5">{!! $help !!}</p>
        @endif
    @enderror
</div>
