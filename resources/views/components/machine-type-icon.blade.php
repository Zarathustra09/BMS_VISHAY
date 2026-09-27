@props(['icon' => null])

@php
    $resolved = \App\Enums\MachineTypeIcon::tryFrom($icon) ?? \App\Enums\MachineTypeIcon::Cog;
@endphp

<svg {{ $attributes->class(['w-5 h-5']) }} fill="none" stroke="currentColor" viewBox="0 0 24 24">
    @foreach($resolved->paths() as $d)
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="{{ $d }}"></path>
    @endforeach
</svg>
