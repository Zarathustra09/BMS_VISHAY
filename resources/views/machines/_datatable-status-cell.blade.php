@php
    $color = match($machine->status) {
        'active' => 'success',
        'maintenance' => 'warning',
        'fault' => 'error',
        default => 'ghost',
    };
@endphp
<div class="badge badge-{{ $color }} gap-1">
    @if($machine->status === 'active')
        <span class="w-2 h-2 bg-{{ $color }}-content rounded-full animate-pulse"></span>
    @endif
    {{ ucfirst($machine->status) }}
</div>
