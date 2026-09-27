@extends('layouts.app')

@section('title', 'Buildings')

@section('breadcrumbs')
    <li>Buildings</li>
@endsection

@section('content')
<div class="space-y-6">
    <!-- Success/Error Messages -->
    @if(session('success'))
    <div class="alert alert-success shadow-lg animate-fadeIn">
        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
        </svg>
        <span>{{ session('success') }}</span>
    </div>
    @endif

    @if(session('error'))
    <div class="alert alert-error shadow-lg animate-fadeIn">
        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
        </svg>
        <span>{{ session('error') }}</span>
    </div>
    @endif

    <!-- Page Header -->
    <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
        <div>
            <h1 class="text-3xl font-bold flex items-center gap-3">
                <div class="w-12 h-12 rounded-xl bg-gradient-to-br from-primary to-secondary flex items-center justify-center shadow-lg">
                    <svg class="w-7 h-7 text-base-100" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"></path>
                    </svg>
                </div>
                Building Management
            </h1>
            <p class="text-sm opacity-70 mt-2">Manage all manufacturing facilities and infrastructure</p>
        </div>
        @can('buildings.create')
        <a href="{{ route('buildings.create') }}" class="btn btn-primary gap-2 shadow-lg hover:shadow-primary/50">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
            </svg>
            Add Building
        </a>
        @endcan
    </div>

    <!-- Stats Overview -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
        <div class="stat bg-base-100 rounded-box shadow-lg border border-base-300/50 hover:shadow-xl transition-all">
            <div class="stat-figure text-primary">
                <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"></path>
                </svg>
            </div>
            <div class="stat-title">Total Buildings</div>
            <div class="stat-value text-primary">{{ $stats['total'] }}</div>
            <div class="stat-desc">Managed facilities</div>
        </div>

        <div class="stat bg-base-100 rounded-box shadow-lg border border-base-300/50 hover:shadow-xl transition-all">
            <div class="stat-figure text-success">
                <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                </svg>
            </div>
            <div class="stat-title">Operational</div>
            <div class="stat-value text-success">{{ $stats['operational'] }}</div>
            <div class="stat-desc">Full capacity</div>
        </div>

        <div class="stat bg-base-100 rounded-box shadow-lg border border-base-300/50 hover:shadow-xl transition-all">
            <div class="stat-figure text-info">
                <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.75 17L9 20l-1 1h8l-1-1-.75-3M3 13h18M5 17h14a2 2 0 002-2V5a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"></path>
                </svg>
            </div>
            <div class="stat-title">Total Machines</div>
            <div class="stat-value text-info">{{ $stats['machines'] }}</div>
            <div class="stat-desc">Across all buildings</div>
        </div>

        <div class="stat bg-base-100 rounded-box shadow-lg border border-base-300/50 hover:shadow-xl transition-all">
            <div class="stat-figure text-warning">
                <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21a4 4 0 01-4-4V5a2 2 0 012-2h4a2 2 0 012 2v12a4 4 0 01-4 4zm0 0h12a2 2 0 002-2v-4a2 2 0 00-2-2h-2.343M11 7.343l1.657-1.657a2 2 0 012.828 0l2.829 2.829a2 2 0 010 2.828l-8.486 8.485M7 17h.01"></path>
                </svg>
            </div>
            <div class="stat-title">Total Floors</div>
            <div class="stat-value text-warning">{{ $stats['floors'] }}</div>
            <div class="stat-desc">Floor spaces</div>
        </div>
    </div>

    <!-- Table -->
    <div class="card bg-base-100 shadow-lg border border-base-300/50">
        <div class="card-body">
            <div class="flex flex-col lg:flex-row gap-4 mb-4">
                <select id="statusFilter" class="select w-full lg:w-48">
                    <option value="">All Status</option>
                    <option value="Operational">Operational</option>
                    <option value="Under Construction">Under Construction</option>
                    <option value="Maintenance">Maintenance</option>
                    <option value="Inactive">Inactive</option>
                </select>

                <select id="typeFilter" class="select w-full lg:w-48">
                    <option value="">All Types</option>
                    <option value="Production Facility">Production Facility</option>
                    <option value="Warehouse">Warehouse</option>
                    <option value="Office Building">Office Building</option>
                    <option value="R&D Center">R&D Center</option>
                    <option value="Distribution Center">Distribution Center</option>
                </select>
            </div>

            <div class="overflow-x-auto">
                <table id="buildingsTable" class="table table-zebra w-full">
                    <thead>
                        <tr>
                            <th>Building</th>
                            <th>Type</th>
                            <th>Status</th>
                            <th>City</th>
                            <th>Floors</th>
                            <th>Machines</th>
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
        var table = $('#buildingsTable').DataTable({
            processing: true,
            serverSide: true,
            autoWidth: false,
            pageLength: 25,
            order: [[0, 'asc']],
            ajax: {
                url: '{{ route('buildings.data') }}',
                data: function (d) {
                    d.status = $('#statusFilter').val();
                    d.type = $('#typeFilter').val();
                }
            },
            columns: [
                {data: 'building', name: 'name'},
                {data: 'type', name: 'type'},
                {data: 'status', name: 'status'},
                {data: 'city', name: 'city'},
                {data: 'floors_count', name: 'floors_count', orderable: true, searchable: false, className: 'text-center'},
                {data: 'machines_count', name: 'machines_count', orderable: true, searchable: false, className: 'text-center'},
                {data: 'action', name: 'action', orderable: false, searchable: false},
            ],
        });

        $('#statusFilter, #typeFilter').on('change', function () {
            table.draw();
        });
    });
</script>
@endpush
@endsection
