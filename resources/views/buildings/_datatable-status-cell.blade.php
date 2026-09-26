@php
    $color = match($building->status) {
        'Operational' => 'success',
        'Under Construction' => 'warning',
        'Maintenance' => 'info',
        default => 'ghost',
    };
@endphp
<div class="badge badge-{{ $color }} gap-1">
    @if($building->status === 'Operational')
        <span class="w-2 h-2 bg-{{ $color }}-content rounded-full animate-pulse"></span>
    @endif
    {{ $building->status ?? 'Unknown' }}
</div>
