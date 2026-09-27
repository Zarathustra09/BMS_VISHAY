@extends('layouts.app')

@section('title', 'Reports')

@section('breadcrumbs')
    <li>Reports</li>
@endsection

@php
    $reportLabels = [
        'sensor_summary' => 'Sensor Summary',
        'alert_history' => 'Alert History',
        'work_order_summary' => 'Work Order Summary',
        'maintenance_compliance' => 'Maintenance Compliance',
    ];
@endphp

@section('content')
<div class="space-y-6">
    <!-- Page Header -->
    <div>
        <h1 class="text-3xl font-bold flex items-center gap-3">
            <div class="w-12 h-12 rounded-xl bg-gradient-to-br from-primary to-secondary flex items-center justify-center shadow-lg">
                <svg class="w-7 h-7 text-base-100" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path>
                </svg>
            </div>
            Reports
        </h1>
        <p class="text-sm opacity-70 mt-2">Filter, review, and export operational data as CSV</p>
    </div>

    <!-- Filters -->
    <div class="card bg-base-100 shadow-lg border border-base-300/50">
        <div class="card-body">
            <form action="{{ route('reports.index') }}" method="GET" class="flex flex-col lg:flex-row gap-4 items-end">
                <div class="flex flex-col gap-1.5 flex-1 min-w-[10rem]">
                    <label class="label py-0"><span class="text-sm font-medium">Report</span></label>
                    <select name="type" class="select w-full">
                        @foreach($reportLabels as $val => $label)
                            <option value="{{ $val }}" {{ $type === $val ? 'selected' : '' }}>{{ $label }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="flex flex-col gap-1.5">
                    <label class="label py-0"><span class="text-sm font-medium">From</span></label>
                    <input type="date" name="date_from" value="{{ $filters['date_from'] }}" class="input w-full" {{ $type === 'maintenance_compliance' ? 'disabled' : '' }}/>
                </div>

                <div class="flex flex-col gap-1.5">
                    <label class="label py-0"><span class="text-sm font-medium">To</span></label>
                    <input type="date" name="date_to" value="{{ $filters['date_to'] }}" class="input w-full" {{ $type === 'maintenance_compliance' ? 'disabled' : '' }}/>
                </div>

                <div class="flex flex-col gap-1.5 flex-1 min-w-[10rem]">
                    <label class="label py-0"><span class="text-sm font-medium">Building</span></label>
                    <select name="building_id" class="select w-full">
                        <option value="">All Buildings</option>
                        @foreach($buildings as $building)
                            <option value="{{ $building->id }}" {{ (string) $filters['building_id'] === (string) $building->id ? 'selected' : '' }}>{{ $building->name }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="flex gap-2">
                    <button type="submit" class="btn btn-primary gap-2">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                        </svg>
                        Apply
                    </button>
                    <a href="{{ route('reports.export', array_merge(['type' => $type], $filters)) }}" class="btn btn-outline gap-2">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                        </svg>
                        Download CSV
                    </a>
                </div>
            </form>
            @if($type === 'maintenance_compliance')
                <p class="text-xs opacity-60 mt-2">Maintenance compliance is a point-in-time snapshot — the date range doesn't apply to it, only the building filter does.</p>
            @endif
        </div>
    </div>

    @if($type === 'sensor_summary')
        <div class="card bg-base-100 shadow-lg border border-base-300/50">
            <div class="card-body">
                <h2 class="card-title">Sensor Summary</h2>
                <p class="text-sm opacity-70">Reading volume and severity breakdown per machine, {{ $filters['date_from'] }} to {{ $filters['date_to'] }}</p>
                <div class="overflow-x-auto mt-4">
                    <table class="table table-zebra">
                        <thead>
                            <tr>
                                <th>Machine</th>
                                <th>Code</th>
                                <th class="text-right">Total Readings</th>
                                <th class="text-right">Critical</th>
                                <th class="text-right">Error</th>
                                <th class="text-right">Warning</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($report['rows'] as $row)
                                <tr>
                                    <td class="font-medium">{{ $row->machine_name }}</td>
                                    <td><code class="text-xs">{{ $row->machine_code }}</code></td>
                                    <td class="text-right">{{ number_format($row->total_readings) }}</td>
                                    <td class="text-right text-error">{{ $row->critical_count }}</td>
                                    <td class="text-right text-warning">{{ $row->error_count }}</td>
                                    <td class="text-right">{{ $row->warning_count }}</td>
                                </tr>
                            @empty
                                <tr><td colspan="6" class="text-center text-sm opacity-70 py-6">No sensor readings in this range.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    @endif

    @if($type === 'alert_history')
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
            <div class="stat bg-base-100 rounded-box shadow-lg border border-base-300/50">
                <div class="stat-title">Total</div>
                <div class="stat-value text-2xl">{{ $report['stats']['total'] }}</div>
            </div>
            <div class="stat bg-base-100 rounded-box shadow-lg border border-base-300/50">
                <div class="stat-title text-error">Critical</div>
                <div class="stat-value text-2xl text-error">{{ $report['stats']['critical'] }}</div>
            </div>
            <div class="stat bg-base-100 rounded-box shadow-lg border border-base-300/50">
                <div class="stat-title">Open</div>
                <div class="stat-value text-2xl">{{ $report['stats']['open'] }}</div>
            </div>
            <div class="stat bg-base-100 rounded-box shadow-lg border border-base-300/50">
                <div class="stat-title text-success">Resolved</div>
                <div class="stat-value text-2xl text-success">{{ $report['stats']['resolved'] }}</div>
            </div>
        </div>

        <div class="card bg-base-100 shadow-lg border border-base-300/50">
            <div class="card-body">
                <h2 class="card-title">Alert History</h2>
                <p class="text-sm opacity-70">Showing the latest {{ $report['preview']->count() }} of {{ $report['stats']['total'] }} matching alerts — download the CSV for the full list.</p>
                <div class="overflow-x-auto mt-4">
                    <table class="table table-zebra">
                        <thead>
                            <tr>
                                <th>Date</th>
                                <th>Machine</th>
                                <th>Type</th>
                                <th>Severity</th>
                                <th>Status</th>
                                <th>Message</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($report['preview'] as $alert)
                                <tr>
                                    <td class="whitespace-nowrap">{{ $alert->created_at->format('M d, Y H:i') }}</td>
                                    <td>{{ $alert->machine->name ?? '—' }}</td>
                                    <td><span class="badge badge-outline badge-sm">{{ ucfirst($alert->type) }}</span></td>
                                    <td><span class="badge badge-{{ $alert->severity === 'critical' ? 'error' : 'warning' }} badge-sm">{{ ucfirst($alert->severity) }}</span></td>
                                    <td><span class="badge badge-{{ $alert->status === 'resolved' ? 'success' : ($alert->status === 'acknowledged' ? 'warning' : 'error') }} badge-outline badge-sm">{{ ucfirst($alert->status) }}</span></td>
                                    <td class="text-sm opacity-80">{{ $alert->message }}</td>
                                </tr>
                            @empty
                                <tr><td colspan="6" class="text-center text-sm opacity-70 py-6">No alerts in this range.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    @endif

    @if($type === 'work_order_summary')
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-5 gap-4">
            <div class="stat bg-base-100 rounded-box shadow-lg border border-base-300/50">
                <div class="stat-title">Total</div>
                <div class="stat-value text-2xl">{{ $report['stats']['total'] }}</div>
            </div>
            <div class="stat bg-base-100 rounded-box shadow-lg border border-base-300/50">
                <div class="stat-title">Pending</div>
                <div class="stat-value text-2xl">{{ $report['stats']['pending'] }}</div>
            </div>
            <div class="stat bg-base-100 rounded-box shadow-lg border border-base-300/50">
                <div class="stat-title text-info">In Progress</div>
                <div class="stat-value text-2xl text-info">{{ $report['stats']['in_progress'] }}</div>
            </div>
            <div class="stat bg-base-100 rounded-box shadow-lg border border-base-300/50">
                <div class="stat-title text-success">Completed</div>
                <div class="stat-value text-2xl text-success">{{ $report['stats']['completed'] }}</div>
            </div>
            <div class="stat bg-base-100 rounded-box shadow-lg border border-base-300/50">
                <div class="stat-title">Preventive</div>
                <div class="stat-value text-2xl">{{ $report['stats']['preventive'] }}</div>
            </div>
        </div>

        <div class="card bg-base-100 shadow-lg border border-base-300/50">
            <div class="card-body">
                <h2 class="card-title">Work Order Summary</h2>
                <p class="text-sm opacity-70">Showing the latest {{ $report['preview']->count() }} of {{ $report['stats']['total'] }} matching work orders — download the CSV for the full list.</p>
                <div class="overflow-x-auto mt-4">
                    <table class="table table-zebra">
                        <thead>
                            <tr>
                                <th>Code</th>
                                <th>Title</th>
                                <th>Machine</th>
                                <th>Status</th>
                                <th>Priority</th>
                                <th>Assigned To</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($report['preview'] as $workOrder)
                                <tr>
                                    <td class="font-mono text-xs">{{ $workOrder->code }}</td>
                                    <td>{{ $workOrder->title }}</td>
                                    <td>{{ $workOrder->machine->name ?? '—' }}</td>
                                    <td><span class="badge badge-outline badge-sm">{{ ucfirst(str_replace('_', ' ', $workOrder->status)) }}</span></td>
                                    <td><span class="badge badge-outline badge-sm">{{ ucfirst($workOrder->priority) }}</span></td>
                                    <td>{{ $workOrder->assignedTo->name ?? 'Unassigned' }}</td>
                                </tr>
                            @empty
                                <tr><td colspan="6" class="text-center text-sm opacity-70 py-6">No work orders in this range.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    @endif

    @if($type === 'maintenance_compliance')
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
            <div class="stat bg-base-100 rounded-box shadow-lg border border-base-300/50">
                <div class="stat-title text-error">Overdue</div>
                <div class="stat-value text-2xl text-error">{{ $report['stats']['overdue'] }}</div>
            </div>
            <div class="stat bg-base-100 rounded-box shadow-lg border border-base-300/50">
                <div class="stat-title text-warning">Due in 7 Days</div>
                <div class="stat-value text-2xl text-warning">{{ $report['stats']['due_soon'] }}</div>
            </div>
            <div class="stat bg-base-100 rounded-box shadow-lg border border-base-300/50">
                <div class="stat-title text-info">Due in 30 Days</div>
                <div class="stat-value text-2xl text-info">{{ $report['stats']['due_month'] }}</div>
            </div>
            <div class="stat bg-base-100 rounded-box shadow-lg border border-base-300/50">
                <div class="stat-title text-success">Completed This Month</div>
                <div class="stat-value text-2xl text-success">{{ $report['stats']['completed_this_month'] }}</div>
            </div>
        </div>

        <div class="card bg-base-100 shadow-lg border border-base-300/50">
            <div class="card-body">
                <h2 class="card-title">By Building</h2>
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-3 mt-2">
                    @forelse($report['breakdown'] as $building)
                        <div class="flex items-center justify-between p-3 rounded-xl border border-base-300 bg-base-200/40">
                            <span class="font-medium text-sm">{{ $building->name }}</span>
                            <div class="flex items-center gap-2">
                                @if($building->overdue_count > 0)
                                    <div class="badge badge-error badge-sm">{{ $building->overdue_count }} overdue</div>
                                @endif
                                @if($building->due_soon_count > 0)
                                    <div class="badge badge-warning badge-sm">{{ $building->due_soon_count }} due soon</div>
                                @endif
                                @if($building->overdue_count === 0 && $building->due_soon_count === 0)
                                    <div class="badge badge-success badge-sm">On track</div>
                                @endif
                            </div>
                        </div>
                    @empty
                        <p class="text-sm opacity-70">No buildings yet.</p>
                    @endforelse
                </div>
            </div>
        </div>

        <div class="card bg-base-100 shadow-lg border border-base-300/50">
            <div class="card-body">
                <h2 class="card-title">Machines Due for Maintenance</h2>
                <p class="text-sm opacity-70">Soonest due first — download the CSV for the full list.</p>
                <div class="overflow-x-auto mt-4">
                    <table class="table table-zebra">
                        <thead>
                            <tr>
                                <th>Machine</th>
                                <th>Building</th>
                                <th>Last Maintenance</th>
                                <th>Next Maintenance</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($report['preview'] as $machine)
                                <tr>
                                    <td class="font-medium">{{ $machine->name }}</td>
                                    <td>{{ $machine->building->name ?? '—' }}</td>
                                    <td>{{ $machine->last_maintenance_date?->format('M d, Y') ?? '—' }}</td>
                                    <td>{{ $machine->next_maintenance_date?->format('M d, Y') ?? '—' }}</td>
                                </tr>
                            @empty
                                <tr><td colspan="4" class="text-center text-sm opacity-70 py-6">No machines with a maintenance schedule.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    @endif
</div>
@endsection
