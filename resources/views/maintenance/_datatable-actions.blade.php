@php
    $hasOpenPreventiveWorkOrder = $machine->workOrders()->where('type', 'preventive')->open()->exists();
@endphp
<div class="flex items-center gap-2">
    <a href="{{ route('machines.show', $machine) }}" class="btn btn-ghost btn-xs">View</a>
    @can('work-orders.create')
        @if($hasOpenPreventiveWorkOrder)
            <span class="badge badge-ghost badge-sm">WO open</span>
        @else
            <a href="{{ route('work-orders.create', ['machine_id' => $machine->id, 'type' => 'preventive']) }}" class="btn btn-outline btn-xs">Create Work Order</a>
        @endif
    @endcan
</div>
