@extends('layouts.app')

@section('title', 'Machine Types')

@section('breadcrumbs')
    <li><a href="{{ route('machine-types.index') }}">Machine Types</a></li>
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
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 10.83V6a3 3 0 013-3z"></path>
                    </svg>
                </div>
                Machine Types
            </h1>
            <p class="text-sm opacity-70 mt-2">Manage machine types and the specification fields each one collects</p>
        </div>
        @can('machine-types.create')
        <a href="{{ route('machine-types.create') }}" class="btn btn-primary gap-2 shadow-lg hover:shadow-primary/50">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
            </svg>
            Add Type
        </a>
        @endcan
    </div>

    <!-- Table -->
    <div class="card bg-base-100 shadow-lg border border-base-300/50">
        <div class="card-body">
            <div class="overflow-x-auto">
                <table id="machineTypesTable" class="table table-zebra w-full">
                    <thead>
                        <tr>
                            <th>Type</th>
                            <th>Specification Fields</th>
                            <th>Machines</th>
                            <th></th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($machineTypes as $machineType)
                        <tr>
                            <td>
                                <span class="font-medium inline-flex items-center gap-2">
                                    <x-machine-type-icon :icon="$machineType->icon" class="w-5 h-5 text-primary" />
                                    {{ $machineType->name }}
                                </span>
                            </td>
                            <td>
                                @forelse($machineType->specFields() as $field)
                                    <span class="badge badge-ghost badge-sm">{{ $field['label'] }}</span>
                                @empty
                                    <span class="text-xs opacity-50">No fields</span>
                                @endforelse
                            </td>
                            <td>{{ $machineType->machines_count }}</td>
                            <td>
                                <div class="flex items-center gap-2">
                                    @can('machine-types.update')
                                    <a href="{{ route('machine-types.edit', $machineType) }}" class="btn btn-outline btn-xs">Edit</a>
                                    @endcan
                                    @can('machine-types.delete')
                                    <a href="{{ route('machine-types.confirm-delete', $machineType) }}" class="btn btn-error btn-outline btn-xs">Delete</a>
                                    @endcan
                                </div>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

@include('partials._datatables-assets')

@push('scripts')
<script>
    $(function () {
        // Every type is already rendered above (a small, admin-maintained list) — DataTables
        // just adds sort/search/paging on top, no ajax endpoint needed (see 02-architecture.md).
        $('#machineTypesTable').DataTable({
            autoWidth: false,
            pageLength: 25,
            columnDefs: [{targets: -1, orderable: false, searchable: false}],
        });
    });
</script>
@endpush
@endsection
