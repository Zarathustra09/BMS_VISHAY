@php
    $color = match ($alert->status) {
        'open' => 'error',
        'acknowledged' => 'warning',
        'resolved' => 'success',
        default => 'ghost',
    };
    $resolutionLabel = match ($alert->resolution) {
        'auto' => 'auto-resolved',
        'work_order' => 'work order',
        'device_back_online' => 'device back online',
        default => null,
    };
@endphp
<div class="badge badge-{{ $color }} badge-outline gap-1">
    {{ ucfirst($alert->status) }}
</div>
@if($alert->status === 'acknowledged' && $alert->acknowledged_at)
    <div class="text-xs opacity-60 mt-1">by {{ $alert->acknowledgedBy?->name ?? 'unknown' }} · {{ $alert->acknowledged_at->diffForHumans() }}</div>
@elseif($alert->status === 'resolved' && $alert->resolved_at)
    <div class="text-xs opacity-60 mt-1">
        by {{ $alert->resolvedBy?->name ?? 'System' }}@if($resolutionLabel) ({{ $resolutionLabel }})@endif · {{ $alert->resolved_at->diffForHumans() }}
    </div>
@endif