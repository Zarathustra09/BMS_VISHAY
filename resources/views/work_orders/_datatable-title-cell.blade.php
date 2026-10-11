<div>
    <a href="{{ route('work-orders.show', $workOrder) }}" class="link link-hover font-medium">{{ $workOrder->title }}</a>
    <div class="text-xs opacity-60">
        <code class="font-mono">{{ $workOrder->code }}</code>
        @if($workOrder->machine)
            · @can('machines.view')<a href="{{ route('machines.show', $workOrder->machine) }}" class="link link-hover">{{ $workOrder->machine->name }}</a>@else{{ $workOrder->machine->name }}@endcan
        @endif
    </div>
</div>
