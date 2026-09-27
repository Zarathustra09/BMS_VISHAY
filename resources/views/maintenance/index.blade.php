@extends('layouts.app')

@section('title', 'Maintenance')

@section('breadcrumbs')
    <li>Maintenance</li>
@endsection

@section('content')
<div class="space-y-6">
    <!-- Page Header -->
    <div>
        <h1 class="text-3xl font-bold flex items-center gap-3">
            <div class="w-12 h-12 rounded-xl bg-gradient-to-br from-primary to-secondary flex items-center justify-center shadow-lg">
                <svg class="w-7 h-7 text-base-100" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"></path>
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                </svg>
            </div>
            Maintenance Dashboard
        </h1>
        <p class="text-sm opacity-70 mt-2">Which machines need preventive maintenance, and when</p>
    </div>

    <!-- Stats Overview -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
        <div class="stat bg-base-100 rounded-box shadow-lg border border-base-300/50 hover:shadow-xl transition-all">
            <div class="stat-figure text-error">
                <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path>
                </svg>
            </div>
            <div class="stat-title">Overdue</div>
            <div class="stat-value text-error">{{ $stats['overdue'] }}</div>
            <div class="stat-desc">Past their next maintenance date</div>
        </div>

        <div class="stat bg-base-100 rounded-box shadow-lg border border-base-300/50 hover:shadow-xl transition-all">
            <div class="stat-figure text-warning">
                <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                </svg>
            </div>
            <div class="stat-title">Due in 7 Days</div>
            <div class="stat-value text-warning">{{ $stats['due_soon'] }}</div>
            <div class="stat-desc">Needs scheduling soon</div>
        </div>

        <div class="stat bg-base-100 rounded-box shadow-lg border border-base-300/50 hover:shadow-xl transition-all">
            <div class="stat-figure text-info">
                <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                </svg>
            </div>
            <div class="stat-title">Due in 30 Days</div>
            <div class="stat-value text-info">{{ $stats['due_month'] }}</div>
            <div class="stat-desc">This month's workload</div>
        </div>

        <div class="stat bg-base-100 rounded-box shadow-lg border border-base-300/50 hover:shadow-xl transition-all">
            <div class="stat-figure text-success">
                <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                </svg>
            </div>
            <div class="stat-title">Completed This Month</div>
            <div class="stat-value text-success">{{ $stats['completed_this_month'] }}</div>
            <div class="stat-desc">Preventive work orders</div>
        </div>
    </div>

    <!-- Per-building breakdown -->
    <div class="card bg-base-100 shadow-lg border border-base-300/50">
        <div class="card-body">
            <h2 class="card-title">By Building</h2>
            @if($buildingBreakdown->isEmpty())
                <p class="text-sm opacity-70">No buildings yet.</p>
            @else
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-3 mt-2">
                    @foreach($buildingBreakdown as $building)
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
                    @endforeach
                </div>
            @endif
        </div>
    </div>

    <!-- Machines Due for Maintenance -->
    <div class="card bg-base-100 shadow-lg border border-base-300/50">
        <div class="card-body">
            <h2 class="card-title mb-4">Machines Due for Maintenance</h2>

            <div class="flex flex-col sm:flex-row gap-4 mb-4">
                <select id="statusFilter" class="select select-bordered select-sm w-full sm:w-44">
                    <option value="">All</option>
                    <option value="overdue">Overdue</option>
                    <option value="due_soon">Due in 7 Days</option>
                    <option value="due_month">Due in 30 Days</option>
                </select>

                <select id="buildingFilter" class="select select-bordered select-sm w-full sm:w-56">
                    <option value="">All Buildings</option>
                    @foreach($buildings as $building)
                        <option value="{{ $building->id }}">{{ $building->name }}</option>
                    @endforeach
                </select>
            </div>

            <div class="overflow-x-auto">
                <table id="maintenanceTable" class="table table-zebra w-full">
                    <thead>
                        <tr>
                            <th>Machine</th>
                            <th>Building</th>
                            <th>Last Maintenance</th>
                            <th>Next Maintenance</th>
                            <th>Status</th>
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
        var table = $('#maintenanceTable').DataTable({
            processing: true,
            serverSide: true,
            autoWidth: false,
            pageLength: 25,
            order: [[3, 'asc']],
            ajax: {
                url: '{{ route('maintenance.data') }}',
                data: function (d) {
                    d.status = $('#statusFilter').val();
                    d.building_id = $('#buildingFilter').val();
                }
            },
            columns: [
                {data: 'machine', name: 'name'},
                {data: 'building_name', name: 'building.name', orderable: false},
                {data: 'last_maintenance_date', name: 'last_maintenance_date'},
                {data: 'next_maintenance_date', name: 'next_maintenance_date'},
                {data: 'status', name: 'next_maintenance_date', orderable: false, searchable: false},
                {data: 'action', name: 'action', orderable: false, searchable: false},
            ],
        });

        $('#statusFilter, #buildingFilter').on('change', function () {
            table.draw();
        });
    });
</script>
@endpush
@endsection
