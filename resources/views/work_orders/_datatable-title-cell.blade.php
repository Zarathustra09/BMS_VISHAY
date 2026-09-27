<div>
    <a href="{{ route('work-orders.show', $workOrder) }}" class="link link-hover font-medium">{{ $workOrder->title }}</a>
    <div class="text-xs opacity-60">
        <code class="font-mono">{{ $workOrder->code }}</code>
        @if($workOrder->machine)
            · <a href="{{ route('machines.show', $workOrder->machine) }}" class="link link-hover">{{ $workOrder->machine->name }}</a>
        @endif
    </div>
</div>
