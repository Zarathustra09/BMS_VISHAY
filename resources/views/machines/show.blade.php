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
                        $statusConfig = [
                            'active' => ['color' => 'success', 'text' => 'Active'],
                            'maintenance' => ['color' => 'warning', 'text' => 'Maintenance'],
                            'fault' => ['color' => 'error', 'text' => 'Fault'],
                            'inactive' => ['color' => 'base-300', 'text' => 'Inactive'],
                        ];
                        $config = $statusConfig[$machine->status] ?? $statusConfig['inactive'];
                    @endphp
                    <div class="badge badge-{{ $config['color'] }} badge-lg gap-2">
                        @if($machine->status === 'active')
                            <span class="w-2 h-2 bg-{{ $config['color'] }}-content rounded-full animate-pulse"></span>
                        @endif
                        {{ $config['text'] }}
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
                <p class="text-sm opacity-70">{{ $machine->machine_code }} • {{ $machine->type }}</p>
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
                    <div class="divider my-2"></div>
                    <div class="flex justify-between">
                        <span class="opacity-70">Building:</span>
                        <span class="font-medium">{{ $machine->building?->name ?? 'N/A' }}</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="opacity-70">Floor:</span>
                        <span class="font-medium">{{ $machine->floor?->name ?? 'N/A' }}</span>
                    </div>
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
                    @if($machine->capacity_tons)
                        <div class="flex justify-between">
                            <span class="opacity-70">Capacity:</span>
                            <span class="font-medium">{{ $machine->capacity_tons }} tons</span>
                        </div>
                    @endif
                    @if($machine->flow_lpm)
                        <div class="flex justify-between">
                            <span class="opacity-70">Flow Rate:</span>
                            <span class="font-medium">{{ $machine->flow_lpm }} L/min</span>
                        </div>
                    @endif
                    @if($machine->fuel)
                        <div class="flex justify-between">
                            <span class="opacity-70">Fuel Type:</span>
                            <span class="font-medium">{{ ucfirst(str_replace('_', ' ', $machine->fuel)) }}</span>
                        </div>
                    @endif
                    @if($machine->capacity_persons)
                        <div class="flex justify-between">
                            <span class="opacity-70">Capacity:</span>
                            <span class="font-medium">{{ $machine->capacity_persons }} persons</span>
                        </div>
                    @endif
                    @if($machine->speed_m_s)
                        <div class="flex justify-between">
                            <span class="opacity-70">Speed:</span>
                            <span class="font-medium">{{ $machine->speed_m_s }} m/s</span>
                        </div>
                    @endif
                    @if($machine->head_m)
                        <div class="flex justify-between">
                            <span class="opacity-70">Head:</span>
                            <span class="font-medium">{{ $machine->head_m }} m</span>
                        </div>
                    @endif
                    @if($machine->cells)
                        <div class="flex justify-between">
                            <span class="opacity-70">Cells:</span>
                            <span class="font-medium">{{ $machine->cells }}</span>
                        </div>
                    @endif
                    <div class="divider my-2"></div>
                    <div class="flex justify-between">
                        <span class="opacity-70">Autostart:</span>
                        <span class="font-medium">{{ $machine->autostart ? 'Yes' : 'No' }}</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="opacity-70">Makeup Valve:</span>
                        <span class="font-medium">{{ $machine->makeup_valve ? 'Yes' : 'No' }}</span>
                    </div>
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
                           class="join-item input input-bordered input-sm font-mono w-full">
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

    <!-- Log Statistics -->
    <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-6 gap-4">
        <div class="stat bg-base-100 rounded-box shadow-lg border border-base-300/50">
            <div class="stat-title text-xs">Total Logs</div>
            <div class="stat-value text-2xl">{{ number_format($logStats['total']) }}</div>
        </div>
        <div class="stat bg-base-100 rounded-box shadow-lg border border-base-300/50">
            <div class="stat-title text-xs">Today</div>
            <div class="stat-value text-2xl text-info">{{ $logStats['today'] }}</div>
        </div>
        <div class="stat bg-base-100 rounded-box shadow-lg border border-base-300/50">
            <div class="stat-title text-xs">This Week</div>
            <div class="stat-value text-2xl text-primary">{{ $logStats['week'] }}</div>
        </div>
        <div class="stat bg-base-100 rounded-box shadow-lg border border-base-300/50">
            <div class="stat-title text-xs">Critical</div>
            <div class="stat-value text-2xl text-error">{{ $logStats['critical'] }}</div>
        </div>
        <div class="stat bg-base-100 rounded-box shadow-lg border border-base-300/50">
            <div class="stat-title text-xs">Errors</div>
            <div class="stat-value text-2xl text-warning">{{ $logStats['errors'] }}</div>
        </div>
        <div class="stat bg-base-100 rounded-box shadow-lg border border-base-300/50">
            <div class="stat-title text-xs">Warnings</div>
            <div class="stat-value text-2xl text-warning">{{ $logStats['warnings'] }}</div>
        </div>
    </div>

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
                    <select class="select select-bordered select-sm" x-model="metric">
                        <template x-for="m in availableMetrics" :key="m.value">
                            <option :value="m.value" x-text="m.label" :selected="m.value === metric"></option>
                        </template>
                    </select>
                    <select class="select select-bordered select-sm" x-model="range">
                        <option value="1h">Last hour</option>
                        <option value="24h">Last 24 hours</option>
                        <option value="7d">Last 7 days</option>
                    </select>
                </div>
            </div>

            {{-- No-data state --}}
            <div class="h-64 flex flex-col items-center justify-center text-center gap-2 opacity-60"
                 x-show="availableMetrics.length === 0 || points.length === 0">
                <svg class="w-10 h-10" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                </svg>
                <p class="text-sm" x-text="availableMetrics.length === 0
                    ? 'This machine has not sent any sensor readings in the last 7 days.'
                    : 'No ' + (label || 'sensor') + ' readings in this range.'"></p>
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
                        <svg class="w-full h-full block" viewBox="0 0 1000 300" preserveAspectRatio="none" aria-hidden="true">
                            <template x-for="b in bands" :key="b.key">
                                <rect x="0" width="1000" :y="b.y" :height="b.h" :class="b.cls"></rect>
                            </template>
                            <template x-for="y in [0, 75, 150, 225, 300]" :key="y">
                                <line x1="0" x2="1000" :y1="y" :y2="y" class="chart-grid" vector-effect="non-scaling-stroke"></line>
                            </template>
                            <template x-for="l in bandLines" :key="l.key">
                                <line x1="0" x2="1000" :y1="l.y" :y2="l.y" :class="l.cls" vector-effect="non-scaling-stroke"></line>
                            </template>
                            <path :d="spreadPath" class="chart-spread"></path>
                            <path :d="areaPath" class="chart-area"></path>
                            <path :d="linePath" class="chart-line" vector-effect="non-scaling-stroke"></path>
                            <line x-show="hover" :x1="hover?.x" :x2="hover?.x" y1="0" y2="300"
                                  class="chart-crosshair" vector-effect="non-scaling-stroke"></line>
                        </svg>

                        {{-- Dots are HTML, not <circle> — preserveAspectRatio="none" would
                             squash a circle into an ellipse. Inline styles only. --}}
                        <template x-for="(p, i) in (points.length <= 60 ? points : [])" :key="p.t">
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
                <div class="text-xs opacity-60 ml-auto">Updates every 30s · <span x-text="lastUpdated"></span></div>
            </div>
        </div>
    </div>

    <!-- Active Alerts (threshold breaches raised by AlertService — see Alert Thresholds on the edit page) -->
    @if($activeAlerts->isNotEmpty())
        <div class="card bg-base-100 shadow-lg border border-error/40">
            <div class="card-body">
                <div class="flex items-center justify-between">
                    <h2 class="card-title">
                        <svg class="w-5 h-5 text-error" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"></path>
                        </svg>
                        Active Alerts
                    </h2>
                    <a href="{{ route('alerts.index') }}" class="btn btn-ghost btn-xs">View all</a>
                </div>
                <div class="space-y-2">
                    @foreach($activeAlerts as $alert)
                        <div class="alert alert-{{ $alert->severity === 'critical' ? 'error' : 'warning' }}">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path>
                            </svg>
                            <div class="flex-1">
                                <div class="font-medium">{{ $alert->message }}</div>
                                <div class="text-xs opacity-70">{{ $alert->created_at->diffForHumans() }} · {{ ucfirst($alert->status) }}</div>
                            </div>
                            <div class="flex items-center gap-2">
                                @if($alert->status === 'open')
                                    @can('alerts.acknowledge')
                                        <form action="{{ route('alerts.acknowledge', $alert) }}" method="POST">
                                            @csrf
                                            <button type="submit" class="btn btn-xs btn-outline">Acknowledge</button>
                                        </form>
                                    @endcan
                                @endif
                                @can('alerts.resolve')
                                    <form action="{{ route('alerts.resolve', $alert) }}" method="POST">
                                        @csrf
                                        <button type="submit" class="btn btn-xs btn-outline btn-success">Resolve</button>
                                    </form>
                                @endcan
                                @can('work-orders.create')
                                    <a href="{{ route('work-orders.create', ['alert_id' => $alert->id, 'machine_id' => $machine->id]) }}" class="btn btn-xs btn-outline">Create Work Order</a>
                                @endcan
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>
    @endif

    <!-- Recent Alert-Level Logs (raw log entries with severity error/critical — distinct from the Active Alerts above, which are AlertService-raised threshold breaches) -->
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
                <select id="severityFilter" class="select select-bordered select-sm w-full sm:w-40">
                    <option value="">All Severities</option>
                    <option value="info">Info</option>
                    <option value="warning">Warning</option>
                    <option value="error">Error</option>
                    <option value="critical">Critical</option>
                </select>

                <select id="logTypeFilter" class="select select-bordered select-sm w-full sm:w-48">
                    <option value="">All Types</option>
                    <option value="status_change">Status Change</option>
                    <option value="maintenance">Maintenance</option>
                    <option value="alarm">Alarm</option>
                    <option value="sensor_reading">Sensor Reading</option>
                    <option value="operation">Operation</option>
                    <option value="error">Error</option>
                </select>

                <input type="date" id="dateFromFilter" class="input input-bordered input-sm w-full sm:w-40" title="From date">
                <input type="date" id="dateToFilter" class="input input-bordered input-sm w-full sm:w-40" title="To date">
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
                    <div class="font-bold text-base-100">BMS AI Assistant</div>
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
                       class="input input-bordered flex-1 focus:outline-none focus:ring-2 focus:ring-primary text-sm"
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
        bucketSeconds: initial.bucket_seconds,
        loading: false,
        failures: 0,
        hover: null,
        lastUpdated: 'just now',
        timer: null,
        controller: null,

        init() {
            this.$watch('metric', () => this.load());
            this.$watch('range', () => this.load());
            this.timer = setInterval(() => {
                if (document.visibilityState === 'visible') this.load(true);
            }, 30000);
        },

        async load(silent = false) {
            if (this.controller) this.controller.abort();
            this.controller = new AbortController();
            if (!silent) this.loading = true;
            try {
                const url = new URL('{{ route('machines.readings', $machine) }}', window.location.origin);
                url.searchParams.set('metric', this.metric ?? '');
                url.searchParams.set('range', this.range);
                const res = await fetch(url, { signal: this.controller.signal, headers: { 'Accept': 'application/json' } });
                if (!res.ok) throw new Error(res.status);
                const d = await res.json();
                Object.assign(this, d, { availableMetrics: d.available_metrics, bucketSeconds: d.bucket_seconds });
                this.lastUpdated = new Date().toLocaleTimeString();
                this.failures = 0;
            } catch (e) {
                if (e.name === 'AbortError') return;
                if (++this.failures >= 5) { clearInterval(this.timer); this.lastUpdated = 'live updates paused'; }
            } finally {
                this.loading = false;
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
        xAt(i) { return this.points.length <= 1 ? 500 : (i / (this.points.length - 1)) * 1000; },
        pctX(i) { return this.xAt(i) / 10; },
        pctY(v) { return this.yAt(v) / 3; },
        fmt(v) { return v === null || v === undefined ? '—' : (Math.round(v * 100) / 100).toString(); },

        get segments() {
            const segs = []; let cur = [];
            this.points.forEach((p, i) => {
                const gap = cur.length && (p.t - cur[cur.length - 1].p.t) > this.bucketSeconds * 1.5;
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
            return [0, 1, 2, 3, 4].map(i => this.points[Math.round((n - 1) * i / 4)].label);
        },

        onHover(e) {
            if (!this.points.length) return;
            const r = e.currentTarget.getBoundingClientRect();
            const pct = Math.min(1, Math.max(0, (e.clientX - r.left) / r.width));
            const i = Math.round(pct * (this.points.length - 1));
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
       chart recolours instantly on a corporate/forest theme switch with no JS. */
    .chart-grid      { stroke: color-mix(in oklab, var(--color-base-content) 12%, transparent); stroke-width: 1; }
    .chart-crosshair { stroke: color-mix(in oklab, var(--color-base-content) 40%, transparent); stroke-width: 1; stroke-dasharray: 3 3; }
    .chart-line      { fill: none; stroke: var(--color-primary); stroke-width: 2; stroke-linejoin: round; stroke-linecap: round; }
    .chart-area      { fill: var(--color-primary); fill-opacity: .10; stroke: none; }
    .chart-spread    { fill: var(--color-primary); fill-opacity: .18; stroke: none; }
    .chart-band-warn { fill: var(--color-warning); fill-opacity: .10; }
    .chart-band-crit { fill: var(--color-error); fill-opacity: .12; }
    .chart-edge-warn { stroke: var(--color-warning); stroke-width: 1; stroke-dasharray: 6 4; }
    .chart-edge-crit { stroke: var(--color-error); stroke-width: 1; stroke-dasharray: 6 4; }

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
