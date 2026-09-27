@extends('layouts.app')

@section('title', 'Work Orders')

@section('breadcrumbs')
    <li>Work Orders</li>
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
    <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
        <div>
            <h1 class="text-3xl font-bold flex items-center gap-3">
                <div class="w-12 h-12 rounded-xl bg-gradient-to-br from-primary to-secondary flex items-center justify-center shadow-lg">
                    <svg class="w-7 h-7 text-base-100" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01"></path>
                    </svg>
                </div>
                Work Orders
            </h1>
            <p class="text-sm opacity-70 mt-2">Maintenance and repair tasks across every machine</p>
        </div>
        @can('work-orders.create')
        <a href="{{ route('work-orders.create') }}" class="btn btn-primary gap-2 shadow-lg hover:shadow-primary/50">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
            </svg>
            Create Work Order
        </a>
        @endcan
    </div>

    <!-- Stats Overview -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-5 gap-4">
        <div class="stat bg-base-100 rounded-box shadow-lg border border-base-300/50 hover:shadow-xl transition-all">
            <div class="stat-title">Total</div>
            <div class="stat-value text-2xl">{{ $stats['total'] }}</div>
        </div>
        <div class="stat bg-base-100 rounded-box shadow-lg border border-base-300/50 hover:shadow-xl transition-all">
            <div class="stat-title">Pending</div>
            <div class="stat-value text-2xl">{{ $stats['pending'] }}</div>
        </div>
        <div class="stat bg-base-100 rounded-box shadow-lg border border-base-300/50 hover:shadow-xl transition-all">
            <div class="stat-title text-info">In Progress</div>
            <div class="stat-value text-2xl text-info">{{ $stats['in_progress'] }}</div>
        </div>
        <div class="stat bg-base-100 rounded-box shadow-lg border border-base-300/50 hover:shadow-xl transition-all">
            <div class="stat-title text-success">Completed</div>
            <div class="stat-value text-2xl text-success">{{ $stats['completed'] }}</div>
        </div>
        <div class="stat bg-base-100 rounded-box shadow-lg border border-base-300/50 hover:shadow-xl transition-all">
            <div class="stat-title text-error">Urgent (open)</div>
            <div class="stat-value text-2xl text-error">{{ $stats['urgent'] }}</div>
        </div>
    </div>

    <!-- Table -->
    <div class="card bg-base-100 shadow-lg border border-base-300/50">
        <div class="card-body">
            <div class="flex flex-col lg:flex-row gap-4 mb-4">
                <select id="statusFilter" class="select w-full lg:w-44">
                    <option value="">All Status</option>
                    <option value="pending">Pending</option>
                    <option value="in_progress">In Progress</option>
                    <option value="completed">Completed</option>
                    <option value="cancelled">Cancelled</option>
                </select>

                <select id="priorityFilter" class="select w-full lg:w-44">
                    <option value="">All Priorities</option>
                    <option value="low">Low</option>
                    <option value="medium">Medium</option>
                    <option value="high">High</option>
                    <option value="urgent">Urgent</option>
                </select>

                <select id="typeFilter" class="select w-full lg:w-44">
                    <option value="">All Types</option>
                    <option value="corrective">Corrective</option>
                    <option value="preventive">Preventive</option>
                    <option value="emergency">Emergency</option>
                    <option value="inspection">Inspection</option>
                </select>

                <select id="machineFilter" class="select w-full lg:w-64">
                    <option value="">All Machines</option>
                    @foreach($machines as $machine)
                        <option value="{{ $machine->id }}">{{ $machine->name }}</option>
                    @endforeach
                </select>
            </div>

            <div class="overflow-x-auto">
                <table id="workOrdersTable" class="table table-zebra w-full">
                    <thead>
                        <tr>
                            <th>Work Order</th>
                            <th>Type</th>
                            <th>Priority</th>
                            <th>Status</th>
                            <th>Assignee</th>
                            <th>Due</th>
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
        var table = $('#workOrdersTable').DataTable({
            processing: true,
            serverSide: true,
            autoWidth: false,
            pageLength: 25,
            order: [[5, 'asc']],
            ajax: {
                url: '{{ route('work-orders.data') }}',
                data: function (d) {
                    d.status = $('#statusFilter').val();
                    d.priority = $('#priorityFilter').val();
                    d.type = $('#typeFilter').val();
                    d.machine_id = $('#machineFilter').val();
                }
            },
            columns: [
                {data: 'work_order', name: 'title', orderable: false},
                {data: 'type', name: 'type'},
                {data: 'priority', name: 'priority'},
                {data: 'status', name: 'status'},
                {data: 'assignee', name: 'assignedTo.name', orderable: false},
                {data: 'due_date', name: 'due_date'},
                {data: 'action', name: 'action', orderable: false, searchable: false},
            ],
        });

        $('#statusFilter, #priorityFilter, #typeFilter, #machineFilter').on('change', function () {
            table.draw();
        });
    });
</script>
@endpush
@endsection
