@php
    $color = match ($workOrder->priority) {
        'urgent' => 'error',
        'high' => 'warning',
        'medium' => 'info',
        default => 'ghost',
    };
@endphp
<div class="badge badge-{{ $color }} badge-sm">{{ ucfirst($workOrder->priority) }}</div>
