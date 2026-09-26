<div class="flex items-center gap-2">
    <a href="{{ route('machines.show', $machine) }}" class="btn btn-ghost btn-xs">View</a>
    @can('machines.update')
        <a href="{{ route('machines.edit', $machine) }}" class="btn btn-outline btn-xs">Edit</a>
    @endcan
    @can('machines.delete')
        <a href="{{ route('machines.confirm-delete', $machine) }}" class="btn btn-error btn-outline btn-xs">Delete</a>
    @endcan
</div>
