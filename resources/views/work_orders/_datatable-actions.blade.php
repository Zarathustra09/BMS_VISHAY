@php
    $canTouchStatus = auth()->user()->can('work-orders.update')
        || $workOrder->assigned_to === auth()->id();
@endphp
<div class="flex items-center gap-2">
    <a href="{{ route('work-orders.show', $workOrder) }}" class="btn btn-ghost btn-xs">View</a>
    @can('work-orders.update')
        <a href="{{ route('work-orders.edit', $workOrder) }}" class="btn btn-outline btn-xs">Edit</a>
    @endcan
    @can('work-orders.update-status')
        @if($canTouchStatus && $workOrder->status === 'pending')
            <form action="{{ route('work-orders.update-status', $workOrder) }}" method="POST" class="inline">
                @csrf
                @method('PATCH')
                <input type="hidden" name="status" value="in_progress">
                <button type="submit" class="btn btn-info btn-outline btn-xs">Start</button>
            </form>
        @elseif($canTouchStatus && $workOrder->status === 'in_progress')
            <a href="{{ route('work-orders.show', $workOrder) }}#complete" class="btn btn-success btn-outline btn-xs">Complete</a>
        @endif
    @endcan
    @can('work-orders.delete')
        <a href="{{ route('work-orders.confirm-delete', $workOrder) }}" class="btn btn-error btn-outline btn-xs">Delete</a>
    @endcan
</div>
