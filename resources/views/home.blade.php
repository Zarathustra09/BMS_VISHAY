@extends('layouts.app')

@section('title', 'Dashboard')

@section('content')
<div class="space-y-6">
    <!-- Welcome Section -->
    <div class="card bg-gradient-to-br from-primary/10 via-secondary/5 to-accent/10 border border-primary/20 shadow-lg">
        <div class="card-body">
            <div class="flex flex-col md:flex-row items-start md:items-center justify-between gap-4">
                <div>
                    <h1 class="text-3xl font-bold mb-2">Welcome back, {{ Auth::user()->name }}! 👋</h1>
                    <p class="text-sm opacity-70">Here's what's happening in your facilities today</p>
                </div>
                <div class="flex gap-2">
                    @can('work-orders.create')
                    <a href="{{ route('work-orders.create') }}" class="btn btn-primary gap-2 shadow-lg hover:shadow-primary/50">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
                        </svg>
                        New Work Order
                    </a>
                    @endcan
                    @can('reports.view')
                    <a href="{{ route('reports.index') }}" class="btn btn-ghost gap-2">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                        </svg>
                        View Reports
                    </a>
                    @endcan
                </div>
            </div>
        </div>
    </div>

    <!-- Key Metrics -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
        <!-- Equipment Availability -->
        <div class="stat bg-base-100 rounded-box shadow-lg border border-base-300/50 hover:shadow-xl transition-all group cursor-pointer">
            <div class="stat-figure text-primary">
                <div class="w-12 h-12 rounded-xl bg-primary/20 flex items-center justify-center group-hover:scale-110 transition-transform">
                    <svg class="w-7 h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"></path>
                    </svg>
                </div>
            </div>
            <div class="stat-title">Equipment Availability</div>
            <div class="stat-value text-primary">{{ $summary['equipment_availability'] }}%</div>
            <div class="stat-desc">{{ $summary['machines']['active'] }} of {{ $summary['machines']['total'] }} machines active</div>
        </div>

        <!-- Active Equipment -->
        <div class="stat bg-base-100 rounded-box shadow-lg border border-base-300/50 hover:shadow-xl transition-all group cursor-pointer">
            <div class="stat-figure text-success">
                <div class="w-12 h-12 rounded-xl bg-success/20 flex items-center justify-center group-hover:scale-110 transition-transform">
                    <svg class="w-7 h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                </div>
            </div>
            <div class="stat-title">Active Equipment</div>
            <div class="stat-value text-success">{{ $summary['machines']['active'] }}/{{ $summary['machines']['total'] }}</div>
            <div class="stat-desc">{{ $summary['machines']['maintenance'] }} under maintenance</div>
        </div>

        {{-- Pending Work Orders --}}
        @module('work-orders')
        <div class="stat bg-base-100 rounded-box shadow-lg border border-base-300/50 hover:shadow-xl transition-all group cursor-pointer">
            <div class="stat-figure text-warning">
                <div class="w-12 h-12 rounded-xl bg-warning/20 flex items-center justify-center group-hover:scale-110 transition-transform">
                    <svg class="w-7 h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"></path>
                    </svg>
                </div>
            </div>
            <div class="stat-title">Pending Work Orders</div>
            <div class="stat-value text-warning">{{ $summary['work_orders']['pending'] }}</div>
            <div class="stat-desc">{{ $summary['work_orders']['urgent'] }} urgent priority</div>
        </div>
        @endmodule

        <!-- Open Alerts -->
        <div class="stat bg-base-100 rounded-box shadow-lg border border-base-300/50 hover:shadow-xl transition-all group cursor-pointer">
            <div class="stat-figure text-error">
                <div class="w-12 h-12 rounded-xl bg-error/20 flex items-center justify-center group-hover:scale-110 transition-transform">
                    <svg class="w-7 h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"></path>
                    </svg>
                </div>
            </div>
            <div class="stat-title">Open Alerts</div>
            <div class="stat-value text-error">{{ $summary['alerts']['open'] }}</div>
            <div class="stat-desc">{{ $summary['alerts']['critical'] }} critical</div>
        </div>
    </div>

    <!-- Charts and Analytics -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- Alerts Trend -->
        <div class="lg:col-span-2 card bg-base-100 shadow-lg border border-base-300/50">
            <div class="card-body">
                <div class="flex items-center justify-between mb-4">
                    <h2 class="card-title">Alerts Raised — Last 7 Days</h2>
                </div>

                @php
                    $maxAlertCount = max(1, collect($alertsTrend)->max('count'));
                    $alertHeightSteps = [4, 8, 12, 16, 20, 24, 28, 32, 36, 40, 44, 48, 52, 56, 60, 64];
                @endphp
                <div class="h-64 flex items-end justify-around gap-2">
                    @foreach($alertsTrend as $day)
                        @php
                            $ratio = $day['count'] / $maxAlertCount;
                            $stepIndex = $day['count'] > 0 ? max(1, (int) round($ratio * (count($alertHeightSteps) - 1))) : 0;
                            $heightClass = 'h-' . $alertHeightSteps[$stepIndex];
                        @endphp
                        <div class="tooltip flex-1" data-tip="{{ $day['label'] }}: {{ $day['count'] }} alert(s)">
                            <div class="{{ $heightClass }} bg-gradient-to-t from-primary to-primary/50 rounded-t hover:from-primary hover:to-primary/70 transition-all cursor-pointer"></div>
                            <div class="text-center text-xs mt-2 opacity-70">{{ $day['label'] }}</div>
                        </div>
                    @endforeach
                </div>

                <div class="flex justify-between items-center mt-4 pt-4 border-t border-base-300">
                    <div class="flex items-center gap-2">
                        <div class="w-3 h-3 rounded-full bg-primary"></div>
                        <span class="text-sm">Alerts per day</span>
                    </div>
                    <div class="stats stats-horizontal shadow">
                        <div class="stat py-2 px-4">
                            <div class="stat-title text-xs">Total (7 days)</div>
                            <div class="stat-value text-lg">{{ collect($alertsTrend)->sum('count') }}</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Equipment Status -->
        <div class="card bg-base-100 shadow-lg border border-base-300/50">
            <div class="card-body">
                <h2 class="card-title mb-4">Equipment Status</h2>

                @php
                    $total = max(1, $summary['machines']['total']);
                    $pct = fn ($n) => round($n / $total * 100, 1);
                @endphp
                <div class="space-y-4">
                    <div>
                        <div class="flex justify-between text-sm mb-2">
                            <span class="font-medium">Active</span>
                            <span class="text-success font-bold">{{ $pct($summary['machines']['active']) }}%</span>
                        </div>
                        <progress class="progress progress-success" value="{{ $pct($summary['machines']['active']) }}" max="100"></progress>
                    </div>

                    <div>
                        <div class="flex justify-between text-sm mb-2">
                            <span class="font-medium">Maintenance</span>
                            <span class="text-warning font-bold">{{ $pct($summary['machines']['maintenance']) }}%</span>
                        </div>
                        <progress class="progress progress-warning" value="{{ $pct($summary['machines']['maintenance']) }}" max="100"></progress>
                    </div>

                    <div>
                        <div class="flex justify-between text-sm mb-2">
                            <span class="font-medium">Fault</span>
                            <span class="text-error font-bold">{{ $pct($summary['machines']['fault']) }}%</span>
                        </div>
                        <progress class="progress progress-error" value="{{ $pct($summary['machines']['fault']) }}" max="100"></progress>
                    </div>
                </div>

                <div class="divider"></div>

                <div class="grid grid-cols-2 gap-3">
                    <div class="stat bg-success/10 rounded-lg p-3 border border-success/20">
                        <div class="stat-value text-2xl text-success">{{ $summary['machines']['active'] }}</div>
                        <div class="stat-desc text-xs">Active Now</div>
                    </div>
                    <div class="stat bg-warning/10 rounded-lg p-3 border border-warning/20">
                        <div class="stat-value text-2xl text-warning">{{ $summary['machines']['maintenance'] }}</div>
                        <div class="stat-desc text-xs">In Maintenance</div>
                    </div>
                    <div class="stat bg-error/10 rounded-lg p-3 border border-error/20">
                        <div class="stat-value text-2xl text-error">{{ $summary['machines']['fault'] }}</div>
                        <div class="stat-desc text-xs">Fault</div>
                    </div>
                    <div class="stat bg-info/10 rounded-lg p-3 border border-info/20">
                        <div class="stat-value text-2xl text-info">{{ $summary['machines']['total'] }}</div>
                        <div class="stat-desc text-xs">Total Units</div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Building Health & Recent Alerts --}}
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        {{-- Building Health --}}
        @module('buildings')
        <div class="card bg-base-100 shadow-lg border border-base-300/50">
            <div class="card-body">
                <h2 class="card-title">Building Health</h2>

                <div class="space-y-3 mt-4">
                    @forelse($buildingHealth as $building)
                        @php
                            $buildingPct = $building->machines_count > 0
                                ? round($building->active_count / $building->machines_count * 100)
                                : 0;
                            $radialColor = match (true) {
                                $buildingPct >= 90 => 'text-success',
                                $buildingPct >= 70 => 'text-primary',
                                default => 'text-warning',
                            };
                        @endphp
                        <a href="{{ route('buildings.show', $building) }}" class="flex items-center gap-3 p-3 rounded-lg bg-base-200 hover:bg-base-300 transition-all">
                            <div class="radial-progress {{ $radialColor }}" style="--value:{{ $buildingPct }}; --size:3.5rem; --thickness:4px">
                                <span class="text-sm font-bold">{{ $buildingPct }}%</span>
                            </div>
                            <div class="flex-1">
                                <div class="font-medium">{{ $building->name }}</div>
                                <div class="text-xs opacity-70">{{ $building->machines_count }} machine{{ $building->machines_count === 1 ? '' : 's' }} · {{ $building->active_count }} active</div>
                            </div>
                            <svg class="w-5 h-5 opacity-50" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
                            </svg>
                        </a>
                    @empty
                        <p class="text-sm opacity-70">No buildings yet.</p>
                    @endforelse
                </div>
            </div>
        </div>
        @endmodule

        {{-- Recent Alerts — takes the whole row when the Building Health card beside it is off --}}
        <div class="card bg-base-100 shadow-lg border border-base-300/50 {{ \App\Enums\AppModule::Buildings->isEnabled() ? '' : 'lg:col-span-2' }}">
            <div class="card-body">
                <div class="flex items-center justify-between mb-4">
                    <h2 class="card-title">Recent Alerts</h2>
                    @can('alerts.view')
                    <a href="{{ route('alerts.index') }}" class="btn btn-ghost btn-sm">View All</a>
                    @endcan
                </div>

                <div class="space-y-3">
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
                            @if($alert->machine)
                                <a href="{{ route('machines.show', $alert->machine) }}" class="btn btn-sm btn-ghost">Details</a>
                            @endif
                        </div>
                    @empty
                        <p class="text-sm opacity-70">No alerts yet.</p>
                    @endforelse
                </div>
            </div>
        </div>
    </div>

    {{-- Recent Work Orders & Quick Actions --}}
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        {{-- Recent Work Orders --}}
        @module('work-orders')
        <div class="lg:col-span-2 card bg-base-100 shadow-lg border border-base-300/50">
            <div class="card-body">
                <div class="flex items-center justify-between mb-4">
                    <h2 class="card-title">Recent Work Orders</h2>
                    @can('work-orders.view')
                    <a href="{{ route('work-orders.index') }}" class="btn btn-ghost btn-sm gap-2">
                        View All
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
                        </svg>
                    </a>
                    @endcan
                </div>

                <div class="overflow-x-auto">
                    <table class="table table-zebra">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Equipment</th>
                                <th>Type</th>
                                <th>Status</th>
                                <th>Priority</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($recentWorkOrders as $workOrder)
                                @php
                                    $priorityColor = match ($workOrder->priority) {
                                        'urgent' => 'error', 'high' => 'warning', 'medium' => 'info', default => 'ghost',
                                    };
                                    $statusColor = match ($workOrder->status) {
                                        'pending' => 'ghost', 'in_progress' => 'info', 'completed' => 'success', 'cancelled' => 'error', default => 'ghost',
                                    };
                                @endphp
                                <tr class="hover">
                                    <td class="font-mono text-xs">{{ $workOrder->code }}</td>
                                    <td>
                                        <div class="font-medium">{{ $workOrder->machine->name ?? '—' }}</div>
                                        <div class="text-xs opacity-70">{{ $workOrder->machine->building->name ?? '' }}</div>
                                    </td>
                                    <td><span class="badge badge-outline badge-sm">{{ ucfirst($workOrder->type) }}</span></td>
                                    <td><span class="badge badge-{{ $statusColor }} badge-sm">{{ ucfirst(str_replace('_', ' ', $workOrder->status)) }}</span></td>
                                    <td><span class="badge badge-{{ $priorityColor }} badge-sm">{{ ucfirst($workOrder->priority) }}</span></td>
                                    <td>
                                        <a href="{{ route('work-orders.show', $workOrder) }}" class="btn btn-ghost btn-xs">View</a>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" class="text-center text-sm opacity-70 py-6">No work orders yet.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
        @endmodule

        {{-- Quick Actions — takes the whole row when the Recent Work Orders card beside it is off --}}
        <div class="card bg-base-100 shadow-lg border border-base-300/50 {{ \App\Enums\AppModule::WorkOrders->isEnabled() ? '' : 'lg:col-span-3' }}">
            <div class="card-body">
                <h2 class="card-title mb-4">Quick Actions</h2>

                <div class="space-y-2">
                    @can('work-orders.create')
                    <a href="{{ route('work-orders.create') }}" class="btn btn-primary w-full justify-start gap-3">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
                        </svg>
                        Create Work Order
                    </a>
                    @endcan

                    @can('maintenance.view')
                    <a href="{{ route('maintenance.index') }}" class="btn btn-outline btn-primary w-full justify-start gap-3">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                        </svg>
                        Maintenance Dashboard
                    </a>
                    @endcan

                    @can('reports.view')
                    <a href="{{ route('reports.index') }}" class="btn btn-outline btn-secondary w-full justify-start gap-3">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path>
                        </svg>
                        View Reports
                    </a>
                    @endcan

                    @can('alerts.view')
                    <a href="{{ route('alerts.index') }}" class="btn btn-outline btn-accent w-full justify-start gap-3">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"></path>
                        </svg>
                        View Alerts
                    </a>
                    @endcan
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
