@php
    $color = match ($workOrder->status) {
        'pending' => 'ghost',
        'in_progress' => 'info',
        'completed' => 'success',
        'cancelled' => 'error',
        default => 'ghost',
    };
@endphp
<div class="badge badge-{{ $color }} gap-1">{{ ucfirst(str_replace('_', ' ', $workOrder->status)) }}</div>
