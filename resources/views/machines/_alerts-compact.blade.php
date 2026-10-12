{{--
    A short "needs attention" card for the Overview tab: the most urgent active alerts, with a link
    to the Alerts tab. Re-fetched with the rest of the alert fragments (machines.alerts-panel).
    Variables: $activeAlerts.
--}}
@php
    $shown = $activeAlerts->sortByDesc(fn ($a) => [$a->severity === 'critical', $a->created_at])->take(3);
    $hidden = $activeAlerts->count() - $shown->count();
@endphp
<div class="card bg-base-100 shadow-lg border {{ $activeAlerts->isNotEmpty() ? 'border-error/40' : 'border-base-300/50' }} h-full">
    <div class="card-body">
        <div class="flex items-center justify-between gap-2">
            <h2 class="card-title text-lg">Needs attention</h2>
            <button type="button" class="btn btn-ghost btn-xs" @click="select('alerts')">Open alerts tab</button>
        </div>

        @if($activeAlerts->isEmpty())
            <div class="flex items-center gap-3 text-success">
                <svg class="w-6 h-6 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                </svg>
                <span class="text-sm">All clear — nothing is out of range.</span>
            </div>
        @else
            <ul class="space-y-2">
                @foreach($shown as $alert)
                    <li class="flex items-start gap-3 text-sm">
                        <span class="mt-1.5 w-2.5 h-2.5 rounded-full shrink-0 {{ $alert->severity === 'critical' ? 'bg-error' : 'bg-warning' }}" aria-hidden="true"></span>
                        <div class="min-w-0">
                            <div class="font-medium truncate">{{ $alert->message }}</div>
                            <div class="text-xs opacity-60">
                                {{ ucfirst($alert->severity) }} · {{ $alert->created_at->diffForHumans() }} · {{ ucfirst($alert->status) }}
                            </div>
                        </div>
                    </li>
                @endforeach
            </ul>
            @if($hidden > 0)
                <p class="text-xs opacity-60">+ {{ $hidden }} more in the Alerts tab</p>
            @endif
        @endif
    </div>
</div>
