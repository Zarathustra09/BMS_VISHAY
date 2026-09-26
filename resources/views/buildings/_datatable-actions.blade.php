<div class="flex items-center gap-2">
    <a href="{{ route('buildings.show', $building) }}" class="btn btn-ghost btn-xs">View</a>
    @can('buildings.update')
        <a href="{{ route('buildings.edit', $building) }}" class="btn btn-outline btn-xs">Edit</a>
    @endcan
    @can('buildings.delete')
        <a href="{{ route('buildings.confirm-delete', $building) }}" class="btn btn-error btn-outline btn-xs">Delete</a>
    @endcan
</div>
