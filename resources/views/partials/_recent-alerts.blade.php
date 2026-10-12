{{-- The dashboard's Recent Alerts list. Rendered inline by home and re-fetched (home.alerts-live) when an alert changes. --}}
    @forelse($recentAlerts as $alert)
        <div class="alert {{ $alert->severity === 'critical' ? 'alert-error' : 'alert-warning' }}">
            <div class="flex-1">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path>
                </svg>
                <div>
                    <div class="font-bold">{{ $alert->machine->name ?? 'Unknown machine' }}</div>
                    <div class="text-xs">{{ $alert->message }} · {{ $alert->created_at->diffForHumans() }}</div>
                </div>
            </div>
            @if($alert->machine && auth()->user()->can('machines.view'))
                <a href="{{ route('machines.show', $alert->machine) }}" class="btn btn-sm btn-ghost">Details</a>
            @endif
        </div>
    @empty
        <p class="text-sm opacity-70">No alerts yet.</p>
    @endforelse
