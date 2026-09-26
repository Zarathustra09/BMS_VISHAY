<div class="flex items-center gap-2">
    @if($alert->machine)
        <a href="{{ route('machines.show', $alert->machine) }}" class="btn btn-ghost btn-xs">Machine</a>
    @endif
    @if($alert->status === 'open')
        @can('alerts.acknowledge')
            <form action="{{ route('alerts.acknowledge', $alert) }}" method="POST" class="inline">
                @csrf
                <button type="submit" class="btn btn-outline btn-xs">Acknowledge</button>
            </form>
        @endcan
    @endif
    @if($alert->status !== 'resolved')
        @can('alerts.resolve')
            <form action="{{ route('alerts.resolve', $alert) }}" method="POST" class="inline">
                @csrf
                <button type="submit" class="btn btn-success btn-outline btn-xs">Resolve</button>
            </form>
        @endcan
    @endif
</div>
