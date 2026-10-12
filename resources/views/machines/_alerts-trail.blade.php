{{--
    The machine's Alert trail card (Alerts tab): who raised / acknowledged / resolved what, and when.
    Re-fetched with the active alerts (machines.alerts-panel). Variables: $alertTrail.
--}}
<div class="card bg-base-100 shadow-lg border border-base-300/50">
    <div class="card-body">
        <div class="flex items-center justify-between gap-2">
            <h2 class="card-title">Alert trail</h2>
            <span class="text-xs opacity-60">Who did what with this machine's alerts</span>
        </div>

        @if($alertTrail->isEmpty())
            <p class="text-sm opacity-60">Nothing has happened to this machine's alerts yet. Raised, acknowledged and resolved alerts will appear here with the person who did it.</p>
        @else
            @include('alerts._timeline', ['events' => $alertTrail, 'showAlert' => true])
        @endif
    </div>
</div>
