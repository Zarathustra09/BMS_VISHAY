@extends('layouts.app')

@section('title', $machine->name)

@section('breadcrumbs')
    <li><a href="{{ route('machines.index') }}">Machines</a></li>
    <li>{{ $machine->name }}</li>
@endsection

@section('back', route('machines.index'))

@section('content')
<div class="space-y-6" x-data="sensorChart(@js($chart))">
    @if(session('device_key_plain'))
    <div class="alert alert-warning shadow-lg animate-fadeIn">
        <svg class="w-6 h-6 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"></path>
        </svg>
        <div>
            <p class="font-semibold">New device API key generated. The old key no longer works.</p>
            <code class="block mt-1 text-sm select-all bg-base-100/50 px-2 py-1 rounded">{{ session('device_key_plain') }}</code>
            <p class="text-xs opacity-70 mt-1">Send it in the <code>X-Device-Key</code> header on every reading. You can view it again below any time.</p>
        </div>
    </div>
    @endif

    <!-- Header -->
    <div class="flex flex-col lg:flex-row justify-between items-start gap-4">
        <div class="flex items-start gap-4">
            <div>
                <div class="flex items-center gap-3 mb-2">
                    <h1 class="text-3xl font-bold">{{ $machine->name }}</h1>
                    @php
                        $statusEnum = \App\Enums\MachineStatus::tryFrom($machine->status);
                        // badge-ghost/badge-error read poorly for a large "current status" pill on this
                        // page, so the colour classes stay a small local map (each written out as a
                        // literal string below — Tailwind's build only sees classes it can find in the
                        // source text, so `badge-{{ $x }}` would silently fail to compile); the label
                        // comes from the enum so the wording itself has one source of truth.
                        [$badgeClass, $dotClass] = match ($machine->status) {
                            'active' => ['badge-success', 'bg-success-content'],
                            'maintenance' => ['badge-warning', 'bg-warning-content'],
                            'fault' => ['badge-error', 'bg-error-content'],
                            // 'badge-base-300' isn't a real daisyUI class (only the semantic
                            // colors below have a badge-* modifier) — it silently rendered
                            // unstyled even before this page's build was fixed to compile
                            // Tailwind for real. 'badge-neutral' is the intended grey tone.
                            default => ['badge-neutral', 'bg-neutral-content'],
                        };
                    @endphp
                    <div class="badge {{ $badgeClass }} badge-lg gap-2">
                        @if($machine->status === 'active')
                            <span class="w-2 h-2 {{ $dotClass }} rounded-full animate-pulse"></span>
                        @endif
                        {{ $statusEnum?->label() ?? ucfirst($machine->status) }}
                    </div>
                    <div class="tooltip" :data-tip="last_seen_at ? 'Last reading ' + last_seen_human : 'No reading ever received'">
                        <div class="badge badge-lg gap-2"
                             :class="last_seen_at === null ? 'badge-ghost' : (online ? 'badge-success' : 'badge-error')">
                            <span class="w-2 h-2 rounded-full bg-current" :class="online ? 'animate-pulse' : ''"></span>
                            <span x-text="last_seen_at === null ? 'No readings' : (online ? 'Online' : 'Offline')"></span>
                        </div>
                    </div>
                    @if($machine->is_critical)
                        <div class="badge badge-error badge-lg gap-2">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path>
                            </svg>
                            Critical System
                        </div>
                    @endif
                </div>
                <p class="text-sm opacity-70 flex items-center gap-1.5">
                    {{ $machine->machine_code }} •
                    <x-machine-type-icon :icon="$machine->machineType?->icon" class="w-4 h-4 inline" />
                    {{ $machine->machineType?->name ?? 'Unknown Type' }}
                </p>
            </div>
        </div>
        <div class="flex gap-2">
            @can('work-orders.create')
            <a href="{{ route('work-orders.create', ['machine_id' => $machine->id]) }}" class="btn btn-outline gap-2">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01"></path>
                </svg>
                Create Work Order
            </a>
            @endcan
            @can('machines.update')
            <a href="{{ route('machines.edit', $machine) }}" class="btn btn-outline gap-2">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
                </svg>
                Edit
            </a>
            @endcan
            @can('machines.delete')
            <a href="{{ route('machines.confirm-delete', $machine) }}" class="btn btn-error btn-outline gap-2">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                </svg>
                Delete
            </a>
            @endcan
        </div>
    </div>

    {{-- At-a-glance numbers: always visible above the tabs --}}
    <div class="grid grid-cols-2 lg:grid-cols-4 gap-3">
        @can('alerts.view')
        <div class="stat bg-base-100 rounded-box shadow border border-base-300/50 py-3">
            <div class="stat-title text-xs">Open alerts</div>
            <div class="stat-value text-2xl {{ $openAlertCount > 0 ? 'text-error' : 'text-success' }}" data-open-alert-count>{{ $openAlertCount }}</div>
            <div class="stat-desc">{{ $openAlertCount > 0 ? 'Need attention' : 'All clear' }}</div>
        </div>
        @endcan
        <div class="stat bg-base-100 rounded-box shadow border border-base-300/50 py-3">
            <div class="stat-title text-xs">Last reading</div>
            <div class="stat-value text-lg leading-8" data-kpi="last_seen">{{ $machine->last_seen_at?->diffForHumans() ?? 'Never' }}</div>
            <div class="stat-desc" data-kpi="last_seen_at">{{ $machine->last_seen_at?->format('M d, H:i') ?? 'No data received yet' }}</div>
        </div>
        <div class="stat bg-base-100 rounded-box shadow border border-base-300/50 py-3">
            <div class="stat-title text-xs">Readings today</div>
            <div class="stat-value text-2xl text-info" data-kpi="logs.today">{{ number_format($logStats['today']) }}</div>
            <div class="stat-desc"><span data-kpi="logs.week">{{ number_format($logStats['week']) }}</span> this week</div>
        </div>
        <div class="stat bg-base-100 rounded-box shadow border border-base-300/50 py-3">
            <div class="stat-title text-xs">Next maintenance</div>
            @if($machine->next_maintenance_date)
                <div class="stat-value text-lg leading-8 {{ $machine->next_maintenance_date->isPast() ? 'text-error' : '' }}">{{ $machine->next_maintenance_date->format('M d, Y') }}</div>
                <div class="stat-desc {{ $machine->next_maintenance_date->isPast() ? 'text-error' : '' }}">{{ $machine->next_maintenance_date->isPast() ? 'Overdue' : $machine->next_maintenance_date->diffForHumans() }}</div>
            @else
                <div class="stat-value text-lg leading-8 opacity-60">Not scheduled</div>
                <div class="stat-desc">Set on the Edit page</div>
            @endif
        </div>
    </div>
    {{-- Tabbed sections. The active tab lives in the URL hash (#alerts) so links, refresh and Back keep it.
         #machineAlertsPanel carries what the live-alert script needs to refresh the alert fragments. --}}
    <div id="machineAlertsPanel" x-data="machineTabs()" data-machine-id="{{ $machine->id }}"
         @can('alerts.view') data-panel-url="{{ route('machines.alerts-panel', $machine) }}" @endcan>
        <div class="overflow-x-auto border-b border-base-300">
            <div role="tablist" aria-label="Machine sections" class="tabs tabs-border flex-nowrap" @keydown="keys($event)">
                <button type="button" role="tab" id="tab-overview" data-tab="overview" aria-controls="panel-overview"
                        :aria-selected="tab === 'overview'" :tabindex="tab === 'overview' ? 0 : -1"
                        class="tab gap-2 whitespace-nowrap" :class="tab === 'overview' && 'tab-active font-semibold'" @click="select('overview')">Overview</button>
                @can('alerts.view')
                <button type="button" role="tab" id="tab-alerts" data-tab="alerts" aria-controls="panel-alerts"
                        :aria-selected="tab === 'alerts'" :tabindex="tab === 'alerts' ? 0 : -1"
                        class="tab gap-2 whitespace-nowrap" :class="tab === 'alerts' && 'tab-active font-semibold'" @click="select('alerts')">Alerts
                    <span class="badge badge-error badge-sm {{ $openAlertCount > 0 ? '' : 'hidden' }}" data-open-alert-count>{{ $openAlertCount }}</span></button>
                @endcan
                <button type="button" role="tab" id="tab-logs" data-tab="logs" aria-controls="panel-logs"
                        :aria-selected="tab === 'logs'" :tabindex="tab === 'logs' ? 0 : -1"
                        class="tab gap-2 whitespace-nowrap" :class="tab === 'logs' && 'tab-active font-semibold'" @click="select('logs')">Logs</button>
                <button type="button" role="tab" id="tab-details" data-tab="details" aria-controls="panel-details"
                        :aria-selected="tab === 'details'" :tabindex="tab === 'details' ? 0 : -1"
                        class="tab gap-2 whitespace-nowrap" :class="tab === 'details' && 'tab-active font-semibold'" @click="select('details')">Details</button>
                <button type="button" role="tab" id="tab-integration" data-tab="integration" aria-controls="panel-integration"
                        :aria-selected="tab === 'integration'" :tabindex="tab === 'integration' ? 0 : -1"
                        class="tab gap-2 whitespace-nowrap" :class="tab === 'integration' && 'tab-active font-semibold'" @click="select('integration')">Integration</button>
                <button type="button" role="tab" id="tab-activity" data-tab="activity" aria-controls="panel-activity"
                        :aria-selected="tab === 'activity'" :tabindex="tab === 'activity' ? 0 : -1"
                        class="tab gap-2 whitespace-nowrap" :class="tab === 'activity' && 'tab-active font-semibold'" @click="select('activity')">Activity</button>
            </div>
        </div>
        <section id="panel-overview" role="tabpanel" aria-labelledby="tab-overview" tabindex="0" x-show="tab === 'overview'" x-cloak class="space-y-6 pt-5">
        <!-- Live Sensor Readings -->
        <div class="card bg-base-100 shadow-lg border border-base-300/50">
            <div class="card-body">
                <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 mb-4">
                    <h2 class="card-title">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 12l3-3 3 3 4-4M8 21l4-4 4 4M3 4h18M4 4h16v12a1 1 0 01-1 1H5a1 1 0 01-1-1V4z"></path>
                        </svg>
                        Live Sensor Readings
                    </h2>
                    <div class="flex items-center gap-2" x-show="availableMetrics.length > 0">
                        <span class="loading loading-spinner loading-xs" x-show="loading"></span>
                        <select class="select select-sm" x-model="metric">
                            <template x-for="m in availableMetrics" :key="m.value">
                                <option :value="m.value" x-text="m.label" :selected="m.value === metric"></option>
                            </template>
                        </select>
                        <select class="select select-sm" x-model="rangeChoice" aria-label="Time range">
                            <option value="auto" x-text="autoLabel">Auto</option>
                            <option value="5m">Last 5 minutes</option>
                            <option value="15m">Last 15 minutes</option>
                            <option value="1h">Last hour</option>
                            <option value="6h">Last 6 hours</option>
                            <option value="24h">Last 24 hours</option>
                            <option value="7d">Last 7 days</option>
                            <option value="30d">Last 30 days</option>
                            <option value="custom">Custom…</option>
                        </select>
                    </div>
                </div>

                {{-- Custom window: inputs are only applied on "Apply", so a half-edited range is never queried --}}
                <div class="flex flex-wrap items-end gap-2 mb-4" x-show="rangeChoice === 'custom'" x-cloak>
                    <label class="flex flex-col gap-1 text-xs opacity-80">From
                        <input type="datetime-local" class="input input-sm" x-model="customFrom" aria-label="From">
                    </label>
                    <label class="flex flex-col gap-1 text-xs opacity-80">To
                        <input type="datetime-local" class="input input-sm" x-model="customTo" aria-label="To">
                    </label>
                    <button type="button" class="btn btn-sm btn-primary" @click="applyCustom()">Apply</button>
                    <p class="text-error text-xs w-full" x-show="rangeError" x-text="rangeError"></p>
                </div>

                {{-- No-data state --}}
                <div class="h-64 flex flex-col items-center justify-center text-center gap-2 opacity-60"
                     x-show="availableMetrics.length === 0 || points.length === 0">
                    <svg class="w-10 h-10" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                    <p class="text-sm" x-text="availableMetrics.length === 0
                        ? 'This machine has no numeric parameters to chart yet — add one on its edit page.'
                        : 'No ' + (label || 'sensor') + ' readings in this range.'"></p>
                    <p class="text-sm" x-show="availableMetrics.length > 0 && latest_reading_at">
                        Latest reading was <span x-text="latest_reading_human"></span>.
                        <button type="button" class="link link-primary" @click="showLatest()">Show it</button>
                    </p>
                </div>

                {{-- Chart --}}
                <div class="flex gap-2" x-show="points.length > 0">
                    {{-- Y axis: five statically-classed spans, Alpine only sets their text — the
                         Tailwind v4 browser CDN only compiles classes present at page load, so
                         nothing here may be generated at runtime (see docs/08-frontend.md). --}}
                    <div class="flex flex-col justify-between h-64 text-xs opacity-60 w-14 shrink-0 text-right py-0.5">
                        <span x-text="yLabels[0]"></span>
                        <span x-text="yLabels[1]"></span>
                        <span x-text="yLabels[2]"></span>
                        <span x-text="yLabels[3]"></span>
                        <span x-text="yLabels[4]"></span>
                    </div>

                    <div class="flex-1 min-w-0">
                        <div class="relative h-64 sensor-chart" @mousemove="onHover($event)" @mouseleave="hover = null">
                            {{-- viewBox + preserveAspectRatio="none" means clientWidth is never
                                 measured, which the layout's 300ms reveal would report as 0. --}}
                            {{-- Threshold bands and limit lines are plain HTML overlays, not <rect>/<line> inside a
                                 <template x-for> in the <svg>: Alpine cannot clone a template into an SVG (it threw
                                 "reading 'children'" on every load). y values are in the 0-300 viewBox, so /3 = percent. --}}
                            <template x-for="b in bands" :key="b.key">
                                <div class="chart-hband" :class="b.cls === 'chart-band-crit' ? 'chart-hband-crit' : 'chart-hband-warn'" :style="`top:${b.y / 3}%; height:${b.h / 3}%`"></div>
                            </template>
                            <template x-for="l in bandLines" :key="l.key">
                                <div class="chart-hline" :class="l.cls === 'chart-edge-crit' ? 'chart-hline-crit' : 'chart-hline-warn'" :style="`top:${l.y / 3}%`"></div>
                            </template>
                            <svg class="relative w-full h-full block" viewBox="0 0 1000 300" preserveAspectRatio="none" aria-hidden="true">
                                <line x1="0" x2="1000" y1="0" y2="0" class="chart-grid" vector-effect="non-scaling-stroke"></line>
                                <line x1="0" x2="1000" y1="75" y2="75" class="chart-grid" vector-effect="non-scaling-stroke"></line>
                                <line x1="0" x2="1000" y1="150" y2="150" class="chart-grid" vector-effect="non-scaling-stroke"></line>
                                <line x1="0" x2="1000" y1="225" y2="225" class="chart-grid" vector-effect="non-scaling-stroke"></line>
                                <line x1="0" x2="1000" y1="300" y2="300" class="chart-grid" vector-effect="non-scaling-stroke"></line>
                                <path :d="spreadPath" class="chart-spread"></path>
                                <path :d="areaPath" class="chart-area"></path>
                                <path :d="linePath" class="chart-line" vector-effect="non-scaling-stroke"></path>
                                <line x-show="hover" :x1="hover?.x" :x2="hover?.x" y1="0" y2="300"
                                      class="chart-crosshair" vector-effect="non-scaling-stroke"></line>
                            </svg>

                            {{-- Dots are HTML, not <circle> — preserveAspectRatio="none" would
                                 squash a circle into an ellipse. Inline styles only. --}}
                            <template x-for="(p, i) in (points.length <= 120 ? points : [])" :key="p.t + '-' + i">
                                <span class="chart-dot" :style="`left:${pctX(i)}%; top:${pctY(p.avg)}%`"></span>
                            </template>

                            <div class="chart-tip" x-show="hover" :style="`left:${hover?.px}%`">
                                <div class="font-semibold" x-text="hover ? fmt(hover.point.avg) : ''"></div>
                                <div class="opacity-70" x-text="hover?.point.label"></div>
                                <div class="opacity-50" x-show="hover && hover.point.n > 1"
                                     x-text="hover ? `${fmt(hover.point.min)} – ${fmt(hover.point.max)} (${hover.point.n} readings)` : ''"></div>
                            </div>
                        </div>

                        <div class="flex justify-between text-xs opacity-60 mt-2">
                            <span x-text="xLabels[0]"></span>
                            <span x-text="xLabels[1]"></span>
                            <span x-text="xLabels[2]"></span>
                            <span x-text="xLabels[3]"></span>
                            <span x-text="xLabels[4]"></span>
                        </div>
                    </div>
                </div>

                <div class="flex flex-wrap items-center gap-4 mt-4 pt-4 border-t border-base-300" x-show="points.length > 0">
                    <div class="flex items-center gap-2"><div class="w-3 h-3 rounded-full bg-primary"></div><span class="text-sm" x-text="label"></span></div>
                    <div class="flex items-center gap-2" x-show="hasWarningBand"><div class="w-3 h-3 rounded-full bg-warning"></div><span class="text-sm">Warning range</span></div>
                    <div class="flex items-center gap-2" x-show="hasCriticalBand"><div class="w-3 h-3 rounded-full bg-error"></div><span class="text-sm">Critical range</span></div>
                    <div class="text-xs opacity-60 ml-auto flex items-center gap-2">
                        <button type="button" class="btn btn-xs btn-ghost" x-show="poll_seconds" @click="toggleLive()" x-text="live ? 'Pause' : 'Resume'"></button>
                        <span x-text="statusText"></span>
                    </div>
                </div>
            </div>
        </div>

            @include('machines._parameters-table')

            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                @can('alerts.view')
                <div id="machine-alerts-compact">@include('machines._alerts-compact')</div>
                @endcan
                <div class="card bg-base-100 shadow-lg border border-base-300/50">
                    <div class="card-body">
                        <div class="flex items-center justify-between gap-2">
                            <h2 class="card-title text-lg">At a glance</h2>
                            <button type="button" class="btn btn-ghost btn-xs" @click="select('details')">All details</button>
                        </div>
                        <dl class="text-sm divide-y divide-base-200">
                            <div class="flex justify-between py-2"><dt class="opacity-70">Type</dt><dd class="font-medium">{{ $machine->machineType?->name ?? 'Unknown' }}</dd></div>
                            @module('buildings')
                            <div class="flex justify-between py-2"><dt class="opacity-70">Location</dt><dd class="font-medium">{{ collect([$machine->building?->name, $machine->floor?->name])->filter()->implode(' · ') ?: 'N/A' }}</dd></div>
                            @endmodule
                            <div class="flex justify-between py-2"><dt class="opacity-70">Assignment group</dt><dd class="font-medium">
                                @if($machine->assignmentGroup && auth()->user()->can('groups.view'))
                                    <a href="{{ route('groups.show', $machine->assignmentGroup) }}" class="link link-hover">{{ $machine->assignmentGroup->name }}</a>
                                @else
                                    {{ $machine->assignmentGroup?->name ?? 'None' }}
                                @endif
                            </dd></div>
                            <div class="flex justify-between py-2"><dt class="opacity-70">IP address</dt><dd class="font-medium">{{ $machine->ip_address ?: 'N/A' }}</dd></div>
                            <div class="flex justify-between py-2"><dt class="opacity-70">Total logs</dt><dd class="font-medium" data-kpi="logs.total">{{ number_format($logStats['total']) }}</dd></div>
                        </dl>
                    </div>
                </div>
            </div>
        </section>
        @can('alerts.view')
        <section id="panel-alerts" role="tabpanel" aria-labelledby="tab-alerts" tabindex="0" x-show="tab === 'alerts'" x-cloak class="space-y-6 pt-5">
            <div id="machine-alerts-active">@include('machines._alerts-active')</div>
            <div id="machine-alerts-trail">@include('machines._alerts-trail')</div>
        </section>
        @endcan
        <section id="panel-logs" role="tabpanel" aria-labelledby="tab-logs" tabindex="0" x-show="tab === 'logs'" x-cloak class="space-y-6 pt-5">
        <!-- Log Statistics -->
        <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-6 gap-4">
            <div class="stat bg-base-100 rounded-box shadow-lg border border-base-300/50">
                <div class="stat-title text-xs">Total Logs</div>
                <div class="stat-value text-2xl" data-kpi="logs.total">{{ number_format($logStats['total']) }}</div>
            </div>
            <div class="stat bg-base-100 rounded-box shadow-lg border border-base-300/50">
                <div class="stat-title text-xs">Today</div>
                <div class="stat-value text-2xl text-info" data-kpi="logs.today">{{ $logStats['today'] }}</div>
            </div>
            <div class="stat bg-base-100 rounded-box shadow-lg border border-base-300/50">
                <div class="stat-title text-xs">This Week</div>
                <div class="stat-value text-2xl text-primary" data-kpi="logs.week">{{ $logStats['week'] }}</div>
            </div>
            <div class="stat bg-base-100 rounded-box shadow-lg border border-base-300/50">
                <div class="stat-title text-xs">Critical</div>
                <div class="stat-value text-2xl text-error" data-kpi="logs.critical">{{ $logStats['critical'] }}</div>
            </div>
            <div class="stat bg-base-100 rounded-box shadow-lg border border-base-300/50">
                <div class="stat-title text-xs">Errors</div>
                <div class="stat-value text-2xl text-warning" data-kpi="logs.errors">{{ $logStats['errors'] }}</div>
            </div>
            <div class="stat bg-base-100 rounded-box shadow-lg border border-base-300/50">
                <div class="stat-title text-xs">Warnings</div>
                <div class="stat-value text-2xl text-warning" data-kpi="logs.warnings">{{ $logStats['warnings'] }}</div>
            </div>
        </div>

        {{-- Recent Alert-Level Logs (raw log entries with severity error/critical — distinct from the Active Alerts above, which are AlertService-raised threshold breaches) --}}
        @if($recentAlerts->isNotEmpty())
            <div class="card bg-base-100 shadow-lg border border-base-300/50">
                <div class="card-body">
                    <h2 class="card-title">
                        <svg class="w-5 h-5 text-error" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path>
                        </svg>
                        Recent Alert-Level Logs
                    </h2>
                    <div class="space-y-2">
                        @foreach($recentAlerts as $alert)
                            <div class="alert alert-{{ $alert->severity === 'critical' ? 'error' : 'warning' }}">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path>
                                </svg>
                                <div class="flex-1">
                                    <div class="font-medium">{{ $alert->event }}</div>
                                    <div class="text-xs opacity-70">{{ $alert->logged_at->format('M d, Y H:i:s') }} ({{ $alert->logged_at->diffForHumans() }})</div>
                                </div>
                                <div class="badge badge-{{ $alert->severity === 'critical' ? 'error' : 'warning' }}">{{ ucfirst($alert->severity) }}</div>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>
        @endif

        <!-- Machine Logs -->
        <div class="card bg-base-100 shadow-lg border border-base-300/50">
            <div class="card-body">
                <h2 class="card-title mb-4">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                    </svg>
                    Machine Logs
                </h2>

                <div class="flex flex-col sm:flex-row gap-4 mb-4">
                    <select id="severityFilter" class="select select-sm w-full sm:w-40">
                        <option value="">All Severities</option>
                        <option value="info">Info</option>
                        <option value="warning">Warning</option>
                        <option value="error">Error</option>
                        <option value="critical">Critical</option>
                    </select>

                    <select id="logTypeFilter" class="select select-sm w-full sm:w-48">
                        <option value="">All Types</option>
                        <option value="status_change">Status Change</option>
                        <option value="maintenance">Maintenance</option>
                        <option value="alarm">Alarm</option>
                        <option value="sensor_reading">Sensor Reading</option>
                        <option value="operation">Operation</option>
                        <option value="error">Error</option>
                    </select>

                    <input type="date" id="dateFromFilter" class="input input-sm w-full sm:w-40" title="From date">
                    <input type="date" id="dateToFilter" class="input input-sm w-full sm:w-40" title="To date">
                </div>

                <div class="overflow-x-auto">
                    <table id="machineLogsTable" class="table table-zebra w-full">
                        <thead>
                            <tr>
                                <th>Timestamp</th>
                                <th>Type</th>
                                <th>Event</th>
                                <th>Severity</th>
                                <th>Sensors</th>
                                <th>Performed By</th>
                            </tr>
                        </thead>
                    </table>
                </div>
            </div>
        </div>
        </section>
        <section id="panel-details" role="tabpanel" aria-labelledby="tab-details" tabindex="0" x-show="tab === 'details'" x-cloak class="space-y-6 pt-5">
        <!-- Machine Info Cards -->
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            <!-- Basic Info -->
            <div class="card bg-base-100 shadow-lg border border-base-300/50">
                <div class="card-body">
                    <h2 class="card-title text-lg mb-4">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                        Basic Information
                    </h2>
                    <div class="space-y-3 text-sm">
                        <div class="flex justify-between">
                            <span class="opacity-70">Manufacturer:</span>
                            <span class="font-medium">{{ $machine->manufacturer ?? 'N/A' }}</span>
                        </div>
                        <div class="flex justify-between">
                            <span class="opacity-70">Model:</span>
                            <span class="font-medium">{{ $machine->model_number ?? 'N/A' }}</span>
                        </div>
                        <div class="flex justify-between">
                            <span class="opacity-70">Serial Number:</span>
                            <span class="font-medium">{{ $machine->serial_number ?? 'N/A' }}</span>
                        </div>
                        <div class="flex justify-between">
                            <span class="opacity-70">Assignment Group:</span>
                            <span class="font-medium">
                                @if($machine->assignmentGroup && auth()->user()->can('groups.view'))
                                    <a href="{{ route('groups.show', $machine->assignmentGroup) }}" class="link link-hover">{{ $machine->assignmentGroup->name }}</a>
                                @else
                                    {{ $machine->assignmentGroup?->name ?? 'N/A' }}
                                @endif
                            </span>
                        </div>
                        <div class="divider my-2"></div>
                        @module('buildings')
                        <div class="flex justify-between">
                            <span class="opacity-70">Building:</span>
                            <span class="font-medium">{{ $machine->building?->name ?? 'N/A' }}</span>
                        </div>
                        <div class="flex justify-between">
                            <span class="opacity-70">Floor:</span>
                            <span class="font-medium">{{ $machine->floor?->name ?? 'N/A' }}</span>
                        </div>
                        @endmodule
                        @if($machine->ip_address)
                            <div class="flex justify-between">
                                <span class="opacity-70">IP Address:</span>
                                <span class="font-medium font-mono text-xs">{{ $machine->ip_address }}</span>
                            </div>
                        @endif
                        @if($machine->description)
                            <div class="pt-2">
                                <span class="opacity-70 block mb-1">Description:</span>
                                <p class="text-sm">{{ $machine->description }}</p>
                            </div>
                        @endif
                    </div>
                </div>
            </div>

            <!-- Specifications -->
            <div class="card bg-base-100 shadow-lg border border-base-300/50">
                <div class="card-body">
                    <h2 class="card-title text-lg mb-4">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 3v2m6-2v2M9 19v2m6-2v2M5 9H3m2 6H3m18-6h-2m2 6h-2M7 19h10a2 2 0 002-2V7a2 2 0 00-2-2H7a2 2 0 00-2 2v10a2 2 0 002 2zM9 9h6v6H9V9z"></path>
                        </svg>
                        Specifications
                    </h2>
                    <div class="space-y-3 text-sm">
                        @if($machine->power_rating)
                            <div class="flex justify-between">
                                <span class="opacity-70">Power Rating:</span>
                                <span class="font-medium">{{ $machine->power_rating }} kW</span>
                            </div>
                        @endif
                        @if($machine->spec_voltage)
                            <div class="flex justify-between">
                                <span class="opacity-70">Voltage:</span>
                                <span class="font-medium">{{ $machine->spec_voltage }} V</span>
                            </div>
                        @endif
                        @if(count($specRows))
                            <div class="divider my-2"></div>
                        @endif
                        @foreach($specRows as $row)
                            <div class="flex justify-between gap-3">
                                <span class="opacity-70">{{ $row['label'] }}:</span>
                                @if($row['type'] === 'url')
                                    <a href="{{ $row['value'] }}" target="_blank" rel="noopener" class="font-medium link link-primary truncate">{{ $row['value'] }}</a>
                                @else
                                    <span class="font-medium">{{ $row['value'] }}@if($row['unit']) {{ $row['unit'] }}@endif</span>
                                @endif
                            </div>
                        @endforeach
                        @if(! $machine->power_rating && ! $machine->spec_voltage && count($specRows) === 0)
                            <p class="text-sm opacity-60">No specifications recorded.</p>
                        @endif
                    </div>
                </div>
            </div>

            <!-- Maintenance Info -->
            <div class="card bg-base-100 shadow-lg border border-base-300/50">
                <div class="card-body">
                    <h2 class="card-title text-lg mb-4">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                        Maintenance Schedule
                    </h2>
                    <div class="space-y-3 text-sm">
                        @if(! $machine->installation_date && ! $machine->last_maintenance_date && ! $machine->next_maintenance_date && ! $machine->maintenance_interval_days)
                            <p class="opacity-60">No installation or maintenance dates recorded.
                                @can('machines.update')
                                    <a href="{{ route('machines.edit', $machine) }}" class="link">Add them on the Edit page.</a>
                                @endcan
                            </p>
                        @endif
                        @if($machine->installation_date)
                            <div class="flex justify-between">
                                <span class="opacity-70">Installed:</span>
                                <span class="font-medium">{{ $machine->installation_date->format('M d, Y') }}</span>
                            </div>
                            <div class="flex justify-between">
                                <span class="opacity-70">Age:</span>
                                <span class="font-medium">{{ $machine->installation_date->diffForHumans(null, true) }}</span>
                            </div>
                        @endif
                        <div class="divider my-2"></div>
                        @if($machine->last_maintenance_date)
                            <div class="flex justify-between">
                                <span class="opacity-70">Last Maintenance:</span>
                                <span class="font-medium">{{ $machine->last_maintenance_date->format('M d, Y') }}</span>
                            </div>
                        @endif
                        @if($machine->next_maintenance_date)
                            <div class="flex justify-between">
                                <span class="opacity-70">Next Maintenance:</span>
                                <span class="font-medium">{{ $machine->next_maintenance_date->format('M d, Y') }}</span>
                            </div>
                            @php
                                $daysUntilMaintenance = now()->diffInDays($machine->next_maintenance_date, false);
                                $isOverdue = $daysUntilMaintenance < 0;
                            @endphp
                            <div class="alert {{ $isOverdue ? 'alert-error' : 'alert-info' }} py-2 mt-2">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                </svg>
                                <span class="text-xs">
                                    @if($isOverdue)
                                        Overdue by {{ abs($daysUntilMaintenance) }} days
                                    @else
                                        Due in {{ $daysUntilMaintenance }} days
                                    @endif
                                </span>
                            </div>
                        @endif
                        @if($machine->maintenance_interval_days)
                            <div class="flex justify-between">
                                <span class="opacity-70">Interval:</span>
                                <span class="font-medium">Every {{ $machine->maintenance_interval_days }} days</span>
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
        </section>
        <section id="panel-integration" role="tabpanel" aria-labelledby="tab-integration" tabindex="0" x-show="tab === 'integration'" x-cloak class="space-y-6 pt-5">
        <!-- Device API Key (requires machines.view-device-key permission) -->
        @can('machines.view-device-key')
        <div class="card bg-base-100 shadow-lg border border-base-300/50">
            <div class="card-body">
                <h2 class="card-title text-lg mb-2">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 7a2 2 0 012 2m4 0a6 6 0 01-7.743 5.743L11 17H9v2H7v2H4a1 1 0 01-1-1v-2.586a1 1 0 01.293-.707l5.964-5.964A6 6 0 1121 9z"></path>
                    </svg>
                    Device API Key
                </h2>
                <p class="text-sm opacity-70">
                    @if($deviceKeyPlain)
                        This key authenticates the machine's sensor device. Regenerating it immediately invalidates the old one.
                    @else
                        No device key set yet — this machine's sensor cannot send readings until one is generated.
                    @endif
                </p>
                <div class="flex items-center justify-between mt-2 text-sm">
                    <span class="opacity-70">Last reading received:</span>
                    <span class="font-medium">{{ $machine->last_seen_at?->diffForHumans() ?? 'Never' }}</span>
                </div>

                @if($deviceKeyPlain)
                <div x-data="{ visible: false, copied: false }" class="mt-4">
                    <label class="text-xs opacity-70 block mb-1">Current key:</label>
                    <div class="join w-full">
                        <input type="text" readonly value="{{ $deviceKeyPlain }}"
                               x-bind:type="visible ? 'text' : 'password'"
                               class="join-item input input-sm font-mono w-full">
                        <button type="button" class="join-item btn btn-sm btn-ghost" @click="visible = !visible">
                            <span x-text="visible ? 'Hide' : 'Show'"></span>
                        </button>
                        <button type="button" class="join-item btn btn-sm btn-ghost"
                                @click="navigator.clipboard.writeText('{{ $deviceKeyPlain }}'); copied = true; setTimeout(() => copied = false, 1500)">
                            <span x-text="copied ? 'Copied!' : 'Copy'"></span>
                        </button>
                    </div>
                    <p class="text-xs opacity-70 mt-1">Send it in the <code>X-Device-Key</code> header on every reading.</p>
                </div>
                @endif

                <form action="{{ route('machines.device-key.generate', $machine) }}" method="POST" class="mt-4"
                      onsubmit="return confirm('{{ $deviceKeyPlain ? 'This will invalidate the current key immediately. Continue?' : 'Generate a device key for this machine?' }}')">
                    @csrf
                    <button type="submit" class="btn btn-outline btn-warning gap-2">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path>
                        </svg>
                        {{ $deviceKeyPlain ? 'Regenerate Key' : 'Generate Key' }}
                    </button>
                </form>
            </div>
        </div>
        @endcan

        <!-- API Integration: endpoint, header and example requests built from this machine's own parameters -->
        @include('machines._api-integration')
        </section>
        <section id="panel-activity" role="tabpanel" aria-labelledby="tab-activity" tabindex="0" x-show="tab === 'activity'" x-cloak class="space-y-6 pt-5">
        <!-- Activity -->
        <div class="card bg-base-100 shadow-lg border border-base-300/50">
            <div class="card-body">
                <h2 class="card-title mb-4">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"></path>
                    </svg>
                    Activity
                </h2>

                @if($activityFeed->isEmpty())
                    <p class="text-sm opacity-70">No changes recorded yet.</p>
                @else
                    <ul class="timeline timeline-vertical timeline-compact">
                        @foreach($activityFeed as $activity)
                            <li>
                                @unless($loop->first)<hr/>@endunless
                                <div class="timeline-start text-xs opacity-60 whitespace-nowrap">{{ $activity->created_at->diffForHumans() }}</div>
                                <div class="timeline-middle">
                                    <svg class="w-4 h-4 text-primary" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path>
                                    </svg>
                                </div>
                                <div class="timeline-end timeline-box">
                                    <p class="text-sm">
                                        <span class="font-semibold">{{ $activity->causer->name ?? 'System' }}</span>
                                        {{ $activity->description }}
                                        <span class="opacity-70">{{ str_replace('_', ' ', \Illuminate\Support\Str::snake(class_basename($activity->subject_type))) }}</span>
                                        @if($activity->subject_type === \App\Models\WorkOrder::class && $activity->subject)
                                            <code class="text-xs">{{ $activity->subject->code }}</code>
                                        @elseif($activity->subject_type === \App\Models\Alert::class && $activity->subject)
                                            <span class="text-xs">#{{ $activity->subject->id }}</span>
                                        @endif
                                    </p>
                                    @if($activity->event === 'updated' && $activity->properties->get('attributes'))
                                        <ul class="text-xs opacity-70 mt-1 space-y-0.5">
                                            @foreach($activity->properties->get('attributes') as $key => $newValue)
                                                <li>
                                                    <span class="font-medium">{{ str_replace('_', ' ', $key) }}:</span>
                                                    {{ $activity->properties->get('old')[$key] ?? '—' }} → {{ $newValue }}
                                                </li>
                                            @endforeach
                                        </ul>
                                    @endif
                                </div>
                                @unless($loop->last)<hr/>@endunless
                            </li>
                        @endforeach
                    </ul>
                @endif
            </div>
        </div>
        </section>
    </div>


<script>
// The headline counters (last reading, readings today, log totals) and the open-alert count, kept
// current: called when a live reading arrives and whenever one of the alert events fires. Debounced,
// because a burst of readings would otherwise ask for the numbers once per reading.
(function () {
    const root = document.getElementById('machineAlertsPanel');
    let timer = null;

    window.refreshMachineSummary = function () {
        clearTimeout(timer);
        timer = setTimeout(() => {
            fetch('{{ route('machines.summary', $machine) }}', { headers: { Accept: 'application/json' } })
                .then((r) => (r.ok ? r.json() : Promise.reject()))
                .then((d) => {
                    const set = (key, value) => document.querySelectorAll('[data-kpi="' + key + '"]').forEach((el) => { el.textContent = value; });
                    set('last_seen', d.last_seen);
                    set('last_seen_at', d.last_seen_at);
                    Object.keys(d.logs).forEach((k) => set('logs.' + k, Number(d.logs[k]).toLocaleString()));

                    // Sensor parameters table: latest value, how long ago, and the state pill.
                    const pills = {
                        critical: ['badge badge-error gap-1 whitespace-nowrap', 'Critical'],
                        warning: ['badge badge-warning gap-1 whitespace-nowrap', 'Warning'],
                        normal: ['badge badge-success gap-1 whitespace-nowrap', 'Normal'],
                        no_limits: ['badge badge-ghost gap-1 whitespace-nowrap', 'No limits'],
                        no_data: ['badge badge-ghost gap-1 whitespace-nowrap', 'No data'],
                    };
                    (d.parameters || []).forEach((p) => {
                        const value = document.querySelector('[data-param-value="' + p.key + '"]');
                        const ago = document.querySelector('[data-param-ago="' + p.key + '"]');
                        const pill = document.querySelector('[data-param-state="' + p.key + '"]');
                        if (value) value.textContent = p.display;
                        if (ago) ago.textContent = p.ago ?? 'No data yet';
                        if (pill && pills[p.state]) { pill.className = pills[p.state][0]; pill.textContent = pills[p.state][1]; }
                    });
                })
                .catch(() => {});
        }, 800);
    };

    window.addEventListener('alert:changed', () => window.refreshMachineSummary());
})();
</script>

<script>
document.addEventListener('alpine:init', () => {
    // Tab state for the machine page. The hash keeps the tab across refresh/links/Back; arrow keys,
    // Home and End move between tabs (WAI-ARIA tabs pattern).
    Alpine.data('machineTabs', () => ({
        tab: 'overview',
        tabs: [],
        init() {
            this.tabs = Array.from(this.$root.querySelectorAll('[role=tab][data-tab]')).map(b => b.dataset.tab);
            const fromHash = () => {
                const h = location.hash.slice(1);
                if (this.tabs.includes(h)) this.show(h);
            };
            fromHash();
            window.addEventListener('hashchange', fromHash);
        },
        show(t) {
            this.tab = t;
            // DataTables sized itself while hidden; recompute its columns once it is visible.
            this.$nextTick(() => { if (t === 'logs') window.machineLogsTable?.columns.adjust(); });
        },
        select(t) {
            if (!this.tabs.includes(t)) return;
            this.show(t);
            history.replaceState(null, '', '#' + t);
        },
        keys(e) {
            const i = this.tabs.indexOf(this.tab);
            const next = { ArrowRight: i + 1, ArrowLeft: i - 1, Home: 0, End: this.tabs.length - 1 }[e.key];
            if (next === undefined) return;
            e.preventDefault();
            const t = this.tabs[(next + this.tabs.length) % this.tabs.length];
            this.select(t);
            this.$root.querySelector('#tab-' + t)?.focus();
        },
    }));
});
</script>
</div>

@include('partials._datatables-assets')

@push('scripts')
<script>
    $(function () {
        var logsTable = $('#machineLogsTable').DataTable({
            processing: true,
            serverSide: true,
            autoWidth: false,
            pageLength: 25,
            order: [[0, 'desc']],
            ajax: {
                url: '{{ route('machines.logs.data', $machine) }}',
                data: function (d) {
                    d.severity = $('#severityFilter').val();
                    d.log_type = $('#logTypeFilter').val();
                    d.date_from = $('#dateFromFilter').val();
                    d.date_to = $('#dateToFilter').val();
                }
            },
            columns: [
                {data: 'logged_at', name: 'logged_at'},
                {data: 'log_type', name: 'log_type'},
                {data: 'event', name: 'event'},
                {data: 'severity', name: 'severity'},
                {data: 'sensors', name: 'sensors', orderable: false, searchable: false},
                {data: 'performed_by', name: 'performed_by'},
            ],
        });

        // The sensor chart reloads this when a live reading is pushed (see sensorChart).
        window.machineLogsTable = logsTable;

        $('#severityFilter, #logTypeFilter, #dateFromFilter, #dateToFilter').on('change', function () {
            logsTable.draw();
        });
    });
</script>
@endpush

<!-- Floating Chat Widget -->
<div class="fixed bottom-6 right-6 z-50" x-data="chatWidget()" x-cloak>

    <!-- Chat Window -->
    <div x-show="isOpen"
         x-transition:enter="transition ease-out duration-300 transform"
         x-transition:enter-start="opacity-0 translate-y-4 scale-95"
         x-transition:enter-end="opacity-100 translate-y-0 scale-100"
         x-transition:leave="transition ease-in duration-200 transform"
         x-transition:leave-start="opacity-100 translate-y-0 scale-100"
         x-transition:leave-end="opacity-0 translate-y-4 scale-95"
         class="mb-4 w-96 max-w-[calc(100vw-3rem)] h-[600px] max-h-[calc(100vh-8rem)] flex flex-col bg-base-100 rounded-2xl shadow-2xl border border-base-300 overflow-hidden">

        <!-- Chat Header -->
        <div class="bg-gradient-to-r from-primary to-secondary p-4 flex items-center justify-between">
            <div class="flex items-center gap-3">
                <div class="avatar online placeholder">
                    <div class="bg-base-100 text-primary rounded-full w-10 h-10 flex items-center justify-center">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.663 17h4.673M12 3v1m6.364 1.636l-.707.707M21 12h-1M4 12H3m3.343-5.657l-.707-.707m2.828 9.9a5 5 0 117.072 0l-.548.547A3.374 3.374 0 0014 18.469V19a2 2 0 11-4 0v-.531c0-.895-.356-1.754-.988-2.386l-.548-.547z"></path>
                        </svg>
                    </div>
                </div>
                <div>
                    <div class="font-bold text-base-100">LogXense AI Assistant</div>
                    <div class="text-xs text-base-100/80 flex items-center gap-1">
                        <span class="w-2 h-2 bg-success rounded-full animate-pulse"></span>
                        Powered by OpenAI GPT
                    </div>
                </div>
            </div>
            <button @click="closeChat()" class="btn btn-ghost btn-sm btn-circle text-base-100 hover:bg-base-100/20">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                </svg>
            </button>
        </div>

        <!-- Chat Messages -->
        <div id="chat-messages"
             class="flex-1 overflow-y-auto p-4 space-y-4 bg-base-200/30"
             x-ref="messagesContainer">
            <template x-for="(message, index) in messages" :key="index">
                <div :class="message.type === 'user' ? 'chat chat-end' : 'chat chat-start'">
                    <div class="chat-image avatar">
                        <div class="w-10 rounded-full" :class="message.type === 'user' ? 'bg-primary' : 'bg-secondary'">
                            <div class="w-full h-full flex items-center justify-center text-base-100">
                                <svg x-show="message.type === 'user'" class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                                </svg>
                                <svg x-show="message.type === 'bot'" class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.663 17h4.673M12 3v1m6.364 1.636l-.707.707M21 12h-1M4 12H3m3.343-5.657l-.707-.707m2.828 9.9a5 5 0 117.072 0l-.548.547A3.374 3.374 0 0014 18.469V19a2 2 0 11-4 0v-.531c0-.895-.356-1.754-.988-2.386l-.548-.547z"></path>
                                </svg>
                            </div>
                        </div>
                    </div>
                    <div class="chat-header text-xs opacity-70 mb-1" x-text="message.type === 'user' ? 'You' : 'AI Assistant'"></div>
                    <div class="chat-bubble text-sm whitespace-pre-wrap"
                         :class="message.type === 'user' ? 'chat-bubble-primary' : 'chat-bubble-secondary'"
                         x-html="formatMessage(message.text)"></div>
                    <div class="chat-footer opacity-50 text-xs mt-1" x-text="message.time"></div>
                </div>
            </template>

            <!-- Typing Indicator -->
            <div x-show="isTyping" class="chat chat-start">
                <div class="chat-image avatar">
                    <div class="w-10 rounded-full bg-secondary">
                        <div class="w-full h-full flex items-center justify-center text-base-100">
                            <svg class="w-5 h-5 animate-spin" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                      d="M9.663 17h4.673M12 3v1m6.364 1.636l-.707.707M21 12h-1M4 12H3
                                         m3.343-5.657l-.707-.707m2.828 9.9a5 5 0 117.072 0l-.548.547
                                         A3.374 3.374 0 0014 18.469V19a2 2 0 11-4 0v-.531
                                         c0-.895-.356-1.754-.988-2.386l-.548-.547z"></path>
                            </svg>
                        </div>
                    </div>
                </div>
                <div class="chat-bubble chat-bubble-secondary">
                    <span class="loading loading-dots loading-sm"></span>
                    <span class="text-xs ml-2">Analyzing machine data...</span>
                </div>
            </div>
        </div>

        <!-- Suggested Questions -->
        <div x-show="suggestedQuestions.length > 0" class="px-4 py-3 border-t border-base-300 bg-base-100">
            <div class="text-xs font-medium mb-2 opacity-70">💡 Quick Insights:</div>
            <div class="flex flex-wrap gap-2 max-h-32 overflow-y-auto">
                <template x-for="(q, index) in suggestedQuestions" :key="index">
                    <button @click="sendQuickAction(q.question)"
                            class="btn btn-xs btn-outline gap-1 normal-case"
                            :disabled="isTyping">
                        <span x-text="q.icon"></span>
                        <span x-text="q.question" class="truncate max-w-[200px]"></span>
                    </button>
                </template>
            </div>
        </div>

        <!-- Chat Input -->
        <div class="p-4 border-t border-base-300 bg-base-100">
            <form @submit.prevent="sendMessage()" class="flex gap-2">
                <input type="text"
                       x-model="messageInput"
                       placeholder="Ask about machine insights..."
                       class="input flex-1 focus:outline-none focus:ring-2 focus:ring-primary text-sm"
                       :disabled="isTyping">
                <button type="submit"
                        class="btn btn-primary btn-circle"
                        :disabled="messageInput.trim() === '' || isTyping">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8"></path>
                    </svg>
                </button>
            </form>
            <div class="text-xs opacity-50 mt-2 text-center">
                Press Enter to send • Powered by OpenAI GPT
            </div>
        </div>
    </div>

    <!-- Floating Button -->
    <button @click="toggleChat()"
            class="btn btn-circle btn-lg shadow-2xl relative group transition-all duration-300"
            :class="isOpen ? 'bg-error hover:bg-error/90' : 'bg-gradient-to-br from-primary to-secondary hover:shadow-primary/50 hover:scale-110'">

        <svg x-show="!isOpen"
             class="w-7 h-7 text-base-100 transition-transform group-hover:scale-110"
             fill="none"
             stroke="currentColor"
             viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8.228 9c.549-1.165 2.03-2 3.772-2 2.21 0 4 1.343 4 3 0 1.4-1.278 2.575-3.006 2.907-.542.104-.994.54-.994 1.093m0 3h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
        </svg>

        <svg x-show="isOpen"
             class="w-7 h-7 text-base-100"
             fill="none"
             stroke="currentColor"
             viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
        </svg>

        <div x-show="!isOpen && hasUnread"
             class="absolute -top-1 -right-1 w-5 h-5 bg-error rounded-full flex items-center justify-center animate-pulse">
            <span class="text-xs font-bold text-base-100">AI</span>
        </div>

        <span class="absolute inset-0 rounded-full bg-primary opacity-75 animate-ping"
              x-show="!isOpen"></span>
    </button>
</div>

<!-- Alpine.js Component Script -->
<script>
document.addEventListener('alpine:init', () => {
    Alpine.data('chatWidget', () => ({
        isOpen: false,
        isTyping: false,
        messageInput: '',
        hasUnread: true,
        currentCacheKey: null,
        checkInterval: null,
        suggestedQuestions: [],
        messages: [
            {
                type: 'bot',
                text: '👋 Hello! I\'m your AI assistant powered by OpenAI GPT.\n\nI can analyze {{ $machine->name }} logs and provide insights about:\n\n• Health & performance trends\n• Anomaly detection\n• Maintenance recommendations\n• Power consumption analysis\n• Risk assessment\n\nClick on a suggested question or ask me anything!',
                time: 'just now'
            }
        ],

        async init() {
            this.$watch('messages', () => {
                this.$nextTick(() => {
                    if (this.$refs.messagesContainer) {
                        this.$refs.messagesContainer.scrollTop = this.$refs.messagesContainer.scrollHeight;
                    }
                });
            });

            // Load suggested questions immediately
            await this.loadSuggestedQuestions();
        },

        async loadSuggestedQuestions() {
            try {
                // Fixed: /api -> /ai routes
                const response = await fetch('{{ route('ai.questions', $machine) }}');
                const data = await response.json();
                if (data.success && data.questions) {
                    this.suggestedQuestions = data.questions;
                }
            } catch (error) {
                console.error('Failed to load questions:', error);
            }
        },

        toggleChat() {
            this.isOpen = !this.isOpen;
            if (this.isOpen) {
                this.hasUnread = false;
                // Reload questions when opening chat
                if (this.suggestedQuestions.length === 0) {
                    this.loadSuggestedQuestions();
                }
            }
        },

        closeChat() {
            this.isOpen = false;
            if (this.checkInterval) {
                clearInterval(this.checkInterval);
            }
        },

        async sendMessage() {
            if (this.messageInput.trim() === '' || this.isTyping) return;

            const userMessage = this.messageInput.trim();

            this.messages.push({
                type: 'user',
                text: userMessage,
                time: 'just now'
            });

            this.messageInput = '';
            this.isTyping = true;

            try {
                const response = await fetch('{{ route('ai.chat') }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                    },
                    body: JSON.stringify({
                        machine_id: {{ $machine->id }},
                        message: userMessage
                    })
                });

                const data = await response.json();

                if (data.processing) {
                    this.currentCacheKey = data.cache_key;
                    this.startStatusCheck();
                } else if (data.cached) {
                    this.addBotMessage(data.message);
                    this.isTyping = false;
                }

            } catch (error) {
                console.error('Chat error:', error);
                this.addBotMessage('❌ Sorry, I encountered an error. Please try again.');
                this.isTyping = false;
            }
        },

        startStatusCheck() {
            this.checkInterval = setInterval(async () => {
                try {
                    const response = await fetch('{{ route('ai.check-status') }}', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                        },
                        body: JSON.stringify({
                            cache_key: this.currentCacheKey
                        })
                    });

                    const data = await response.json();

                    if (data.ready) {
                        this.addBotMessage(data.message);
                        this.isTyping = false;
                        clearInterval(this.checkInterval);
                        this.currentCacheKey = null;
                        // Reload questions after getting response
                        this.loadSuggestedQuestions();
                    }
                } catch (error) {
                    console.error('Status check error:', error);
                    clearInterval(this.checkInterval);
                    this.isTyping = false;
                }
            }, 2000);
        },

        sendQuickAction(question) {
            this.messageInput = question;
            this.sendMessage();
        },

        addBotMessage(text) {
            this.messages.push({
                type: 'bot',
                text: text,
                time: 'just now'
            });
        },

        formatMessage(text) {
            return text
                .replace(/\*\*(.*?)\*\*/g, '<strong>$1</strong>')
                .replace(/\*(.*?)\*/g, '<em>$1</em>')
                .replace(/\n/g, '<br>');
        }
    }));

    Alpine.data('sensorChart', (initial) => ({
        ...initial,
        availableMetrics: initial.available_metrics,
        // What the user picked (auto/preset/custom); `range` is what the server resolved it to.
        rangeChoice: initial.requested_range,
        customFrom: initial.window_start_input,
        customTo: initial.window_end_input,
        appliedFrom: null,
        appliedTo: null,
        rangeError: null,
        live: true,
        loading: false,
        failures: 0,
        hover: null,
        lastUpdated: 'just now',
        timer: null,
        controller: null,
        // Live push (Laravel Reverb). socketLive is whether the WebSocket is connected right now;
        // while it is, polling drops to a slow safety net and a pushed reading is drawn instantly.
        socketLive: false,
        channelBound: false,
        reloadTimer: null,
        logsTimer: null,
        windowSeconds: { '5m': 300, '15m': 900, '1h': 3600, '6h': 21600, '24h': 86400, '7d': 604800, '30d': 2592000 },
        rangeLabels: { '5m': '5 min', '15m': '15 min', '1h': '1 hour', '6h': '6 hours', '24h': '24 hours', '7d': '7 days', '30d': '30 days', custom: 'custom' },

        init() {
            this.$watch('metric', () => this.load());
            this.$watch('rangeChoice', (choice) => {
                this.rangeError = null;
                // Custom waits for Apply — its inputs are still being edited.
                if (choice !== 'custom') this.load();
            });
            this.schedule();
            this.connectLive();
        },

        // The server decides how often a window is worth re-polling (poll_seconds): every few
        // seconds for a short window someone is watching, slower for long ones, never for a
        // custom window that has already ended.
        schedule() {
            clearTimeout(this.timer);
            if (!this.live || !this.poll_seconds) return;
            // With the socket connected, pushed readings do the real work; this slower poll only
            // slides the window, refreshes thresholds/auto range and covers a missed message.
            const seconds = this.socketLive ? Math.max(this.poll_seconds, 60) : this.poll_seconds;
            this.timer = setTimeout(() => {
                if (document.visibilityState === 'visible') this.load(true); else this.schedule();
            }, seconds * 1000);
        },

        toggleLive() {
            this.live = !this.live;
            this.failures = 0;
            if (this.live) this.load(true); else clearTimeout(this.timer);
        },

        applyCustom() {
            if (!this.customFrom || !this.customTo) { this.rangeError = 'Pick both a start and an end.'; return; }
            this.appliedFrom = this.customFrom;
            this.appliedTo = this.customTo;
            this.load();
        },

        // "Show it" on the empty state: auto already looks back 30 days, so if auto is what's
        // showing, the reading is older than that and gets a custom window around it.
        showLatest() {
            if (this.rangeChoice !== 'auto') { this.rangeChoice = 'auto'; return; }
            this.customFrom = this.latest_reading_from_input;
            this.customTo = this.latest_reading_to_input;
            this.rangeChoice = 'custom';
            this.applyCustom();
        },

        // Subscribes to this machine's private channel once Echo exists (it is created by a
        // deferred Vite module, so it can appear after Alpine starts; with live updates not
        // configured it never does, and the chart just keeps polling).
        connectLive() {
            if (!window.Echo) {
                window.addEventListener('echo:ready', () => this.connectLive(), { once: true });
                return;
            }
            if (this.channelBound) return;
            this.channelBound = true;

            const connection = window.Echo.connector.pusher.connection;
            this.socketLive = connection.state === 'connected';
            connection.bind('state_change', ({ current }) => {
                const wasLive = this.socketLive;
                this.socketLive = current === 'connected';
                // Back online: catch up on whatever was missed while the socket was down.
                if (this.socketLive && !wasLive) this.load(true); else this.schedule();
            });

            window.Echo.private('machines.{{ $machine->id }}')
                .listen('.reading.recorded', (e) => this.onLiveReading(e));
        },

        onLiveReading(e) {
            if (!this.live) return;                                  // paused: the chart stays put
            if (this.rangeChoice === 'custom' && !this.poll_seconds) return; // a finished window can't grow

            // Whatever was charted, the machine just proved it's alive.
            this.online = true;
            this.last_seen_at = new Date().toISOString();
            this.last_seen_human = 'just now';
            this.lastUpdated = new Date().toLocaleTimeString();
            this.reloadLogsSoon();

            const value = this.metric ? e.readings?.[this.metric] : undefined;
            if (typeof value !== 'number') return;                   // not the charted parameter

            this.latest_reading_at = new Date(e.t * 1000).toISOString();
            this.latest_reading_human = 'just now';

            // A replayed, older-than-the-window reading isn't part of this view.
            if (e.t * 1000 < Date.parse(this.window_start)) return;

            // Raw mode (or an empty chart) can take the point as-is. Bucketed data would need
            // the server's averaging, so it just re-fetches (debounced).
            if (this.points.length >= 300 || (this.points.length && this.resolution !== 'Raw readings')) {
                this.loadSoon();
                return;
            }

            const first = this.points.length ? this.points[0].t : e.t;
            const spanBefore = this.points.length ? this.points[this.points.length - 1].t - first : 0;
            const span = Math.max(e.t, this.points.length ? this.points[this.points.length - 1].t : e.t) - first;

            let points = [...this.points, {
                t: e.t, avg: value, min: value, max: value, n: 1,
                label: span <= 3600 ? e.labels.time : (span <= 86400 ? e.labels.hour : e.labels.date),
            }];
            if (points.length > 1 && points[points.length - 2].t > e.t) points.sort((a, b) => a.t - b.t);

            // A preset window slides: drop what has fallen off its far end.
            const length = this.windowSeconds[this.range];
            if (this.range !== 'custom' && length) points = points.filter(p => p.t >= e.t - length);

            this.points = points;
            this.resolution = 'Raw readings';
            this.reading_count = points.length;
            this.latest = value;

            // The data's span just grew past a label-format boundary, so earlier labels are
            // now in a finer format than this one: re-fetch to relabel consistently.
            if ((spanBefore <= 3600 && span > 3600) || (spanBefore <= 86400 && span > 86400)) this.loadSoon();
        },

        loadSoon() {
            clearTimeout(this.reloadTimer);
            this.reloadTimer = setTimeout(() => this.load(true), 2000);
        },

        reloadLogsSoon() {
            clearTimeout(this.logsTimer);
            this.logsTimer = setTimeout(() => window.machineLogsTable?.ajax.reload(null, false), 2000);
            window.refreshMachineSummary?.();
        },

        get autoLabel() {
            return this.rangeChoice === 'auto' ? `Auto (${this.rangeLabels[this.range] ?? this.range})` : 'Auto';
        },

        get statusText() {
            const parts = [];
            if (this.poll_seconds) parts.push(this.live ? (this.socketLive ? 'Live · instant' : `Live · every ${this.poll_seconds}s`) : 'Paused');
            else parts.push('Fixed window');
            if (this.points.length) parts.push(`${this.resolution} (${this.reading_count})`);
            parts.push(`updated ${this.lastUpdated}`);
            return parts.join(' · ');
        },

        async load(silent = false) {
            if (this.rangeChoice === 'custom' && !this.appliedFrom) return;
            if (this.controller) this.controller.abort();
            this.controller = new AbortController();
            if (!silent) this.loading = true;
            let reschedule = true;
            let aborted = false;
            try {
                const url = new URL('{{ route('machines.readings', $machine) }}', window.location.origin);
                url.searchParams.set('metric', this.metric ?? '');
                url.searchParams.set('range', this.rangeChoice);
                if (this.rangeChoice === 'custom') {
                    url.searchParams.set('from', this.appliedFrom);
                    url.searchParams.set('to', this.appliedTo);
                }
                const res = await fetch(url, { signal: this.controller.signal, headers: { 'Accept': 'application/json' } });
                if (res.status === 422) {
                    const body = await res.json();
                    this.rangeError = Object.values(body.errors || {}).flat()[0] || 'That range is not valid.';
                    reschedule = false;
                    return;
                }
                if (!res.ok) throw new Error(res.status);
                const d = await res.json();
                this.rangeError = null;
                Object.assign(this, d, { availableMetrics: d.available_metrics });
                this.lastUpdated = new Date().toLocaleTimeString();
                this.failures = 0;
            } catch (e) {
                if (e.name === 'AbortError') { aborted = true; reschedule = false; return; }
                if (++this.failures >= 5) { this.live = false; this.lastUpdated = 'live updates paused'; reschedule = false; }
            } finally {
                if (!aborted) this.loading = false;
                if (reschedule) this.schedule();
            }
        },
        get scale() {
            const vals = [];
            this.points.forEach(p => vals.push(p.min, p.max));
            Object.values(this.thresholds).forEach(v => { if (v !== null) vals.push(v); });
            if (!vals.length) return { lo: 0, hi: 1 };
            let lo = Math.min(...vals), hi = Math.max(...vals);
            if (hi === lo) { hi = lo + (Math.abs(lo) * 0.1 || 1); lo -= (Math.abs(lo) * 0.1 || 1); }
            const pad = (hi - lo) * 0.08;
            return { lo: lo - pad, hi: hi + pad };
        },
        yAt(v) { const { lo, hi } = this.scale; return 300 - ((v - lo) / (hi - lo)) * 300; },
        // Position by time, not by index, so an uneven reporting interval or a gap is drawn at its
        // true width (falling back to even spacing when every point shares one timestamp).
        xAt(i) {
            const n = this.points.length;
            if (n <= 1) return 500;
            const span = this.points[n - 1].t - this.points[0].t;
            return span > 0 ? ((this.points[i].t - this.points[0].t) / span) * 1000 : (i / (n - 1)) * 1000;
        },
        nearestIndex(t) {
            let best = 0, dist = Infinity;
            this.points.forEach((p, i) => { const d = Math.abs(p.t - t); if (d < dist) { dist = d; best = i; } });
            return best;
        },
        pctX(i) { return this.xAt(i) / 10; },
        pctY(v) { return this.yAt(v) / 3; },
        fmt(v) { return v === null || v === undefined ? '—' : (Math.round(v * 100) / 100).toString(); },

        get segments() {
            const segs = []; let cur = [];
            this.points.forEach((p, i) => {
                const gap = cur.length && (p.t - cur[cur.length - 1].p.t) > this.gap_seconds;
                if (gap) { segs.push(cur); cur = []; }
                cur.push({ p, i });
            });
            if (cur.length) segs.push(cur);
            return segs;
        },
        get linePath() {
            return this.segments.map(seg =>
                seg.map(({ p, i }, k) => `${k ? 'L' : 'M'}${this.xAt(i).toFixed(1)} ${this.yAt(p.avg).toFixed(1)}`).join(' ')
            ).join(' ');
        },
        get areaPath() {
            return this.segments.map(seg => {
                const top = seg.map(({ p, i }, k) => `${k ? 'L' : 'M'}${this.xAt(i).toFixed(1)} ${this.yAt(p.avg).toFixed(1)}`).join(' ');
                return `${top} L${this.xAt(seg[seg.length - 1].i).toFixed(1)} 300 L${this.xAt(seg[0].i).toFixed(1)} 300 Z`;
            }).join(' ');
        },
        get spreadPath() {
            return this.segments.map(seg => {
                const up = seg.map(({ p, i }, k) => `${k ? 'L' : 'M'}${this.xAt(i).toFixed(1)} ${this.yAt(p.max).toFixed(1)}`).join(' ');
                const down = [...seg].reverse().map(({ p, i }) => `L${this.xAt(i).toFixed(1)} ${this.yAt(p.min).toFixed(1)}`).join(' ');
                return `${up} ${down} Z`;
            }).join(' ');
        },

        get bands() {
            const t = this.thresholds, out = [];
            const push = (key, yA, yB, cls) => {
                const y = Math.max(0, Math.min(yA, yB)), h = Math.min(300, Math.max(yA, yB)) - y;
                if (h > 0) out.push({ key, y, h, cls });
            };
            if (t.critical_max !== null) push('cmax', 0, this.yAt(t.critical_max), 'chart-band-crit');
            if (t.critical_min !== null) push('cmin', this.yAt(t.critical_min), 300, 'chart-band-crit');
            if (t.warning_max !== null) push('wmax', t.critical_max !== null ? this.yAt(t.critical_max) : 0, this.yAt(t.warning_max), 'chart-band-warn');
            if (t.warning_min !== null) push('wmin', this.yAt(t.warning_min), t.critical_min !== null ? this.yAt(t.critical_min) : 300, 'chart-band-warn');
            return out;
        },
        get bandLines() {
            const t = this.thresholds, out = [];
            [['warning_min', 'chart-edge-warn'], ['warning_max', 'chart-edge-warn'],
             ['critical_min', 'chart-edge-crit'], ['critical_max', 'chart-edge-crit']]
                .forEach(([k, cls]) => { if (t[k] !== null) out.push({ key: k, y: this.yAt(t[k]), cls }); });
            return out;
        },
        get hasWarningBand() { return this.thresholds.warning_min !== null || this.thresholds.warning_max !== null; },
        get hasCriticalBand() { return this.thresholds.critical_min !== null || this.thresholds.critical_max !== null; },

        get yLabels() {
            const { lo, hi } = this.scale;
            return [0, 1, 2, 3, 4].map(i => this.fmt(hi - ((hi - lo) / 4) * i));
        },
        get xLabels() {
            const n = this.points.length;
            if (!n) return ['', '', '', '', ''];
            const t0 = this.points[0].t, span = this.points[n - 1].t - t0;
            return [0, 1, 2, 3, 4].map(i => this.points[this.nearestIndex(t0 + span * i / 4)].label);
        },

        onHover(e) {
            if (!this.points.length) return;
            const r = e.currentTarget.getBoundingClientRect();
            const pct = Math.min(1, Math.max(0, (e.clientX - r.left) / r.width));
            const n = this.points.length, t0 = this.points[0].t, span = this.points[n - 1].t - t0;
            const i = span > 0 ? this.nearestIndex(t0 + span * pct) : Math.round(pct * (n - 1));
            this.hover = { point: this.points[i], x: this.xAt(i), px: this.pctX(i) };
        },
    }));
});
</script>

<!-- Styles -->
<style>
    [x-cloak] {
        display: none !important;
    }

    #chat-messages::-webkit-scrollbar {
        width: 6px;
    }

    #chat-messages::-webkit-scrollbar-track {
        background: transparent;
    }

    #chat-messages::-webkit-scrollbar-thumb {
        background: color-mix(in oklab, var(--color-base-content) 20%, transparent);
        border-radius: 3px;
    }

    #chat-messages::-webkit-scrollbar-thumb:hover {
        background: color-mix(in oklab, var(--color-base-content) 30%, transparent);
    }

    .chat {
        animation: fadeInUp 0.3s ease-out;
    }

    @keyframes fadeInUp {
        from {
            opacity: 0;
            transform: translateY(10px);
        }
        to {
            opacity: 1;
            transform: translateY(0);
        }
    }

    /* Live Sensor Readings chart — colours MUST live here, not as fill=/stroke= attributes
       on the SVG elements themselves: CSS custom properties are only substituted inside CSS
       declarations, not SVG presentation attributes, so var(--color-primary) silently does
       nothing written as fill="var(--color-primary)". Using classes here means the whole
       chart recolours instantly on a light/forest theme switch with no JS. */
    .chart-grid      { stroke: color-mix(in oklab, var(--color-base-content) 12%, transparent); stroke-width: 1; }
    .chart-crosshair { stroke: color-mix(in oklab, var(--color-base-content) 40%, transparent); stroke-width: 1; stroke-dasharray: 3 3; }
    .chart-line      { fill: none; stroke: var(--color-primary); stroke-width: 2; stroke-linejoin: round; stroke-linecap: round; }
    .chart-area      { fill: var(--color-primary); fill-opacity: .10; stroke: none; }
    .chart-spread    { fill: var(--color-primary); fill-opacity: .18; stroke: none; }
    .chart-band-warn { fill: var(--color-warning); fill-opacity: .10; }
    .chart-band-crit { fill: var(--color-error); fill-opacity: .12; }
    .chart-edge-warn { stroke: var(--color-warning); stroke-width: 1; stroke-dasharray: 6 4; }
    .chart-edge-crit { stroke: var(--color-error); stroke-width: 1; stroke-dasharray: 6 4; }

    /* Threshold bands + limit lines: HTML overlays behind the SVG (see the chart markup). */
    .chart-hband { position: absolute; left: 0; right: 0; pointer-events: none; }
    .chart-hband-warn { background: color-mix(in oklab, var(--color-warning) 10%, transparent); }
    .chart-hband-crit { background: color-mix(in oklab, var(--color-error) 12%, transparent); }
    .chart-hline { position: absolute; left: 0; right: 0; height: 0; border-top: 1px dashed; pointer-events: none; }
    .chart-hline-warn { border-color: var(--color-warning); }
    .chart-hline-crit { border-color: var(--color-error); }

    .chart-dot {
        position: absolute; width: 6px; height: 6px; border-radius: 9999px;
        background: var(--color-primary);
        border: 1px solid var(--color-base-100);
        transform: translate(-50%, -50%); pointer-events: none;
    }

    .chart-tip {
        position: absolute; top: .5rem; transform: translateX(-50%);
        background: var(--color-base-100); color: var(--color-base-content);
        border: 1px solid color-mix(in oklab, var(--color-base-content) 20%, transparent);
        border-radius: .5rem; padding: .25rem .5rem; font-size: .75rem;
        white-space: nowrap; pointer-events: none; z-index: 10;
        box-shadow: 0 4px 12px color-mix(in oklab, var(--color-base-content) 15%, transparent);
    }
</style>
@endsection
