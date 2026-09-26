@php
    $color = match ($alert->status) {
        'open' => 'error',
        'acknowledged' => 'warning',
        'resolved' => 'success',
        default => 'ghost',
    };
@endphp
<div class="badge badge-{{ $color }} badge-outline gap-1">
    {{ ucfirst($alert->status) }}
</div>
