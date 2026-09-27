@extends('layouts.app')

@section('title', 'Machines')

@section('breadcrumbs')
    <li>Machines</li>
@endsection

@section('content')
<div class="space-y-6">
    <!-- Page Header -->
    <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
        <div>
            <h1 class="text-3xl font-bold flex items-center gap-3">
                <div class="w-12 h-12 rounded-xl bg-gradient-to-br from-primary to-secondary flex items-center justify-center shadow-lg">
                    <svg class="w-7 h-7 text-base-100" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.663 17h4.673M12 3v1m6.364 1.636l-.707.707M21 12h-1M4 12H3m3.343-5.657l-.707-.707m2.828 9.9a5 5 0 117.072 0l-.548.547A3.374 3.374 0 0014 18.469V19a2 2 0 11-4 0v-.531c0-.895-.356-1.754-.988-2.386l-.548-.547z"></path>
                    </svg>
                </div>
                Machine Management
            </h1>
            <p class="text-sm opacity-70 mt-2">Monitor and manage all manufacturing equipment</p>
        </div>
        @can('machines.create')
        <a href="{{ route('machines.create') }}" class="btn btn-primary gap-2 shadow-lg hover:shadow-primary/50">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
            </svg>
            Add Machine
        </a>
        @endcan
    </div>

    <!-- Stats Overview -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
        <div class="stat bg-base-100 rounded-box shadow-lg border border-base-300/50 hover:shadow-xl transition-all">
            <div class="stat-figure text-success">
                <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                </svg>
            </div>
            <div class="stat-title">Active Machines</div>
            <div class="stat-value text-success">{{ $stats['active'] }}</div>
            <div class="stat-desc">{{ number_format(($stats['active'] / max($stats['total'], 1)) * 100, 1) }}% of total</div>
        </div>

        <div class="stat bg-base-100 rounded-box shadow-lg border border-base-300/50 hover:shadow-xl transition-all">
            <div class="stat-figure text-warning">
                <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                </svg>
            </div>
            <div class="stat-title">In Maintenance</div>
            <div class="stat-value text-warning">{{ $stats['maintenance'] }}</div>
            <div class="stat-desc">Scheduled repairs</div>
        </div>

        <div class="stat bg-base-100 rounded-box shadow-lg border border-base-300/50 hover:shadow-xl transition-all">
            <div class="stat-figure text-error">
                <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path>
                </svg>
            </div>
            <div class="stat-title">Faults</div>
            <div class="stat-value text-error">{{ $stats['fault'] }}</div>
            <div class="stat-desc">Require attention</div>
        </div>

        <div class="stat bg-base-100 rounded-box shadow-lg border border-base-300/50 hover:shadow-xl transition-all">
            <div class="stat-figure text-info">
                <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0121 12a11.955 11.955 0 01-1.382 5.984M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0121 12c0 2.485-.59 4.843-1.618 6.984M15.75 12a3.75 3.75 0 11-7.5 0 3.75 3.75 0 017.5 0z"></path>
                </svg>
            </div>
            <div class="stat-title">Critical Systems</div>
            <div class="stat-value text-info">{{ $stats['critical'] }}</div>
            <div class="stat-desc">High priority</div>
        </div>
    </div>

    <!-- Table -->
    <div class="card bg-base-100 shadow-lg border border-base-300/50">
        <div class="card-body">
            <div class="flex flex-col lg:flex-row gap-4 mb-4">
                <select id="statusFilter" class="select w-full lg:w-48">
                    <option value="">All Status</option>
                    <option value="active">Active</option>
                    <option value="maintenance">Maintenance</option>
                    <option value="fault">Fault</option>
                    <option value="inactive">Inactive</option>
                </select>

                <select id="typeFilter" class="select w-full lg:w-48">
                    <option value="">All Types</option>
                    @foreach($machineTypes as $type)
                        <option value="{{ $type->id }}">{{ $type->name }}</option>
                    @endforeach
                </select>

                <select id="buildingFilter" class="select w-full lg:w-48">
                    <option value="">All Buildings</option>
                    @foreach($buildings as $building)
                        <option value="{{ $building->id }}">{{ $building->name }}</option>
                    @endforeach
                </select>
            </div>

            <div class="overflow-x-auto">
                <table id="machinesTable" class="table table-zebra w-full">
                    <thead>
                        <tr>
                            <th>Machine</th>
                            <th>Status</th>
                            <th>Type</th>
                            <th>Building</th>
                            <th>Floor</th>
                            <th>Manufacturer</th>
                            <th>Next Maintenance</th>
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
        var table = $('#machinesTable').DataTable({
            processing: true,
            serverSide: true,
            autoWidth: false,
            pageLength: 25,
            order: [[0, 'asc']],
            ajax: {
                url: '{{ route('machines.data') }}',
                data: function (d) {
                    d.status = $('#statusFilter').val();
                    d.machine_type_id = $('#typeFilter').val();
                    d.building_id = $('#buildingFilter').val();
                }
            },
            columns: [
                {data: 'machine', name: 'name'},
                {data: 'status', name: 'status'},
                {data: 'type', name: 'type', orderable: false, searchable: false},
                {data: 'building_name', name: 'building_name', orderable: false, searchable: false},
                {data: 'floor_name', name: 'floor_name', orderable: false, searchable: false},
                {data: 'manufacturer', name: 'manufacturer'},
                {data: 'maintenance', name: 'next_maintenance_date', orderable: false, searchable: false},
                {data: 'action', name: 'action', orderable: false, searchable: false},
            ],
        });

        $('#statusFilter, #typeFilter, #buildingFilter').on('change', function () {
            table.draw();
        });
    });
</script>
@endpush
@endsection
