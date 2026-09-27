@extends('layouts.app')

@section('title', 'Alerts')

@section('breadcrumbs')
    <li>Alerts</li>
@endsection

@section('content')
<div class="space-y-6">
    @if(session('success'))
    <div class="alert alert-success shadow-lg animate-fadeIn">
        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
        </svg>
        <span>{{ session('success') }}</span>
    </div>
    @endif

    <!-- Page Header -->
    <div>
        <h1 class="text-3xl font-bold flex items-center gap-3">
            <div class="w-12 h-12 rounded-xl bg-gradient-to-br from-primary to-secondary flex items-center justify-center shadow-lg">
                <svg class="w-7 h-7 text-base-100" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"></path>
                </svg>
            </div>
            Alerts
        </h1>
        <p class="text-sm opacity-70 mt-2">Sensor threshold breaches and offline devices — configure thresholds on each machine's edit page</p>
    </div>

    <!-- Stats Overview -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
        <div class="stat bg-base-100 rounded-box shadow-lg border border-base-300/50 hover:shadow-xl transition-all">
            <div class="stat-figure text-error">
                <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path>
                </svg>
            </div>
            <div class="stat-title">Open</div>
            <div class="stat-value text-error">{{ $stats['open'] }}</div>
            <div class="stat-desc">Need attention</div>
        </div>

        <div class="stat bg-base-100 rounded-box shadow-lg border border-base-300/50 hover:shadow-xl transition-all">
            <div class="stat-figure text-error">
                <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"></path>
                </svg>
            </div>
            <div class="stat-title">Critical (open)</div>
            <div class="stat-value text-error">{{ $stats['critical'] }}</div>
            <div class="stat-desc">Highest priority</div>
        </div>

        <div class="stat bg-base-100 rounded-box shadow-lg border border-base-300/50 hover:shadow-xl transition-all">
            <div class="stat-figure text-warning">
                <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4M7.835 4.697a3.42 3.42 0 001.946-.806 3.42 3.42 0 014.438 0 3.42 3.42 0 001.946.806 3.42 3.42 0 013.138 3.138 3.42 3.42 0 00.806 1.946 3.42 3.42 0 010 4.438 3.42 3.42 0 00-.806 1.946 3.42 3.42 0 01-3.138 3.138 3.42 3.42 0 00-1.946.806 3.42 3.42 0 01-4.438 0 3.42 3.42 0 00-1.946-.806 3.42 3.42 0 01-3.138-3.138 3.42 3.42 0 00-.806-1.946 3.42 3.42 0 010-4.438 3.42 3.42 0 00.806-1.946 3.42 3.42 0 013.138-3.138z"></path>
                </svg>
            </div>
            <div class="stat-title">Acknowledged</div>
            <div class="stat-value text-warning">{{ $stats['acknowledged'] }}</div>
            <div class="stat-desc">Being handled</div>
        </div>

        <div class="stat bg-base-100 rounded-box shadow-lg border border-base-300/50 hover:shadow-xl transition-all">
            <div class="stat-figure text-success">
                <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                </svg>
            </div>
            <div class="stat-title">Resolved today</div>
            <div class="stat-value text-success">{{ $stats['resolved_today'] }}</div>
            <div class="stat-desc">Back to normal</div>
        </div>
    </div>

    <!-- Table -->
    <div class="card bg-base-100 shadow-lg border border-base-300/50">
        <div class="card-body">
            <div class="flex flex-col lg:flex-row gap-4 mb-4">
                <select id="statusFilter" class="select w-full lg:w-48">
                    <option value="">All Status</option>
                    <option value="open">Open</option>
                    <option value="acknowledged">Acknowledged</option>
                    <option value="resolved">Resolved</option>
                </select>

                <select id="severityFilter" class="select w-full lg:w-48">
                    <option value="">All Severities</option>
                    <option value="warning">Warning</option>
                    <option value="critical">Critical</option>
                </select>

                <select id="machineFilter" class="select w-full lg:w-64">
                    <option value="">All Machines</option>
                    @foreach($machines as $machine)
                        <option value="{{ $machine->id }}">{{ $machine->name }}</option>
                    @endforeach
                </select>

                <select id="typeFilter" class="select w-full lg:w-48">
                    <option value="">All Types</option>
                    <option value="threshold">Threshold breach</option>
                    <option value="offline">Device offline</option>
                </select>
            </div>

            <div class="overflow-x-auto">
                <table id="alertsTable" class="table table-zebra w-full">
                    <thead>
                        <tr>
                            <th>Machine</th>
                            <th>Metric / Type</th>
                            <th>Severity</th>
                            <th>Status</th>
                            <th>Raised</th>
                            <th></th>
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
        var table = $('#alertsTable').DataTable({
            processing: true,
            serverSide: true,
            autoWidth: false,
            pageLength: 25,
            order: [[4, 'desc']],
            ajax: {
                url: '{{ route('alerts.data') }}',
                data: function (d) {
                    d.status = $('#statusFilter').val();
                    d.severity = $('#severityFilter').val();
                    d.machine_id = $('#machineFilter').val();
                    d.type = $('#typeFilter').val();
                }
            },
            columns: [
                {data: 'machine', name: 'machine.name', orderable: false},
                {data: 'metric', name: 'metric'},
                {data: 'severity', name: 'severity'},
                {data: 'status', name: 'status'},
                {data: 'created_at', name: 'created_at'},
                {data: 'action', name: 'action', orderable: false, searchable: false},
            ],
        });

        $('#statusFilter, #severityFilter, #machineFilter, #typeFilter').on('change', function () {
            table.draw();
        });
    });
</script>
@endpush
@endsection
