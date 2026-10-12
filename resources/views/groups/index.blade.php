@extends('layouts.app')

@section('title', 'Groups')

@section('breadcrumbs')
    <li><a href="{{ route('groups.index') }}">Groups</a></li>
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

    <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
        <div>
            <h1 class="text-3xl font-bold flex items-center gap-3">
                <div class="w-12 h-12 rounded-xl bg-gradient-to-br from-primary to-secondary flex items-center justify-center shadow-lg">
                    <svg class="w-7 h-7 text-base-100" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"></path>
                    </svg>
                </div>
                Groups
            </h1>
            <p class="text-sm opacity-70 mt-2">Put users in a group to give them its access in one go, and to email them together when a machine raises an alert</p>
        </div>
        @can('groups.create')
        <a href="{{ route('groups.create') }}" class="btn btn-primary gap-2 shadow-lg hover:shadow-primary/50">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
            </svg>
            Add Group
        </a>
        @endcan
    </div>

    <div class="card bg-base-100 shadow-lg border border-base-300/50">
        <div class="card-body">
            <div class="overflow-x-auto">
                <table id="groupsTable" class="table table-zebra w-full">
                    <thead>
                        <tr>
                            <th>Group</th>
                            <th>Members</th>
                            <th>Roles</th>
                            <th>Extra permissions</th>
                            <th>Machines</th>
                            <th></th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($groups as $group)
                        <tr>
                            <td>
                                <div class="font-medium">{{ $group->name }}</div>
                                @if($group->description)
                                <div class="text-xs opacity-60 max-w-xs truncate">{{ $group->description }}</div>
                                @endif
                            </td>
                            <td>{{ $group->users_count }}</td>
                            <td>{{ $group->roles_count }}</td>
                            <td>{{ $group->permissions_count }}</td>
                            <td>{{ $group->machines_count }}</td>
                            <td>
                                <div class="flex items-center gap-2">
                                    <a href="{{ route('groups.show', $group) }}" class="btn btn-ghost btn-xs">View</a>
                                    @can('groups.update')
                                    <a href="{{ route('groups.edit', $group) }}" class="btn btn-outline btn-xs">Edit</a>
                                    @endcan
                                    @can('groups.delete')
                                    <a href="{{ route('groups.confirm-delete', $group) }}" class="btn btn-error btn-outline btn-xs">Delete</a>
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
        $('#groupsTable').DataTable({
            autoWidth: false,
            pageLength: 25,
            columnDefs: [{targets: -1, orderable: false, searchable: false}],
        });
    });
</script>
@endpush
@endsection
