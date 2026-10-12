{{--
    The machine's Active Alerts card (Alerts tab). Re-fetched as an HTML fragment
    (machines.alerts-panel) whenever one of this machine's alerts changes.
    Variables: $machine, $activeAlerts. Only ever rendered for users with alerts.view.
--}}
<div class="card bg-base-100 shadow-lg border {{ $activeAlerts->isNotEmpty() ? 'border-error/40' : 'border-base-300/50' }}">
    <div class="card-body">
        <div class="flex items-center justify-between gap-2">
            <h2 class="card-title">
                <svg class="w-5 h-5 {{ $activeAlerts->isNotEmpty() ? 'text-error' : 'text-success' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"></path>
                </svg>
                Active Alerts
                <span class="badge badge-ghost badge-sm">{{ $activeAlerts->count() }}</span>
            </h2>
            <a href="{{ route('alerts.index') }}" class="btn btn-ghost btn-xs">All alerts</a>
        </div>

        @if($activeAlerts->isEmpty())
            <div class="flex items-center gap-3 rounded-lg bg-success/10 text-success px-4 py-3">
                <svg class="w-6 h-6 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                </svg>
                <div class="text-sm">
                    <div class="font-medium">No active alerts</div>
                    <div class="opacity-80">This machine is within its limits. Alert limits are set on the machine's Edit page.</div>
                </div>
            </div>
        @else
            <div class="space-y-2">
                @foreach($activeAlerts as $alert)
                    <div class="alert alert-{{ $alert->severity === 'critical' ? 'error' : 'warning' }}">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path>
                        </svg>
                        <div class="flex-1">
                            <div class="font-medium">{{ $alert->message }}</div>
                            <div class="text-xs opacity-70">
                                {{ ucfirst($alert->severity) }} · {{ $alert->created_at->diffForHumans() }} · {{ ucfirst($alert->status) }}
                                @if($alert->status === 'acknowledged' && $alert->acknowledgedBy)
                                    by {{ $alert->acknowledgedBy->name }}
                                @endif
                                · <a href="{{ route('alerts.show', $alert) }}" class="link">trail</a>
                            </div>
                        </div>
                        <div class="flex flex-wrap items-center gap-2">
                            @if($alert->status === 'open')
                                @can('alerts.acknowledge')
                                    <form action="{{ route('alerts.acknowledge', $alert) }}" method="POST">
                                        @csrf
                                        <button type="submit" class="btn btn-xs bg-base-100 text-base-content border-base-300 hover:bg-base-200">Acknowledge</button>
                                    </form>
                                @endcan
                            @endif
                            @can('alerts.resolve')
                                <form action="{{ route('alerts.resolve', $alert) }}" method="POST">
                                    @csrf
                                    <button type="submit" class="btn btn-xs btn-success">Resolve</button>
                                </form>
                            @endcan
                            @can('work-orders.create')
                                <a href="{{ route('work-orders.create', ['alert_id' => $alert->id, 'machine_id' => $machine->id]) }}" class="btn btn-xs bg-base-100 text-base-content border-base-300 hover:bg-base-200">Create Work Order</a>
                            @endcan
                        </div>
                    </div>
                @endforeach
            </div>
        @endif
    </div>
</div>
