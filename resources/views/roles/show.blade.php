@extends('layouts.app')

@section('title', 'Role — ' . $role->name)

@section('breadcrumbs')
    <li><a href="{{ route('roles.index') }}">Roles</a></li>
    <li class="capitalize">{{ $role->name }}</li>
@endsection

@section('back', route('roles.index'))

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

    <!-- Header -->
    <div class="flex flex-col lg:flex-row justify-between items-start gap-4">
        <div>
            <h1 class="text-3xl font-bold flex items-center gap-3 capitalize">
                {{ str_replace('-', ' ', $role->name) }}
                @if($role->name === 'admin')
                <div class="badge badge-warning gap-1">
                    <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"></path>
                    </svg>
                    Protected
                </div>
                @endif
            </h1>
            <p class="text-sm opacity-70 mt-2">{{ $users->count() }} user{{ $users->count() === 1 ? '' : 's' }} · {{ count($rolePermissionNames) }} permission{{ count($rolePermissionNames) === 1 ? '' : 's' }}</p>
        </div>
        <div class="flex gap-2">
            @can('roles.update')
                @if($role->name !== 'admin')
                <a href="{{ route('roles.edit', $role) }}" class="btn btn-outline gap-2">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
                    </svg>
                    Edit
                </a>
                @endif
            @endcan
            @can('roles.delete')
                @if($role->name !== 'admin')
                <a href="{{ route('roles.confirm-delete', $role) }}" class="btn btn-error btn-outline gap-2">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                    </svg>
                    Delete
                </a>
                @endif
            @endcan
        </div>
    </div>

    <!-- Permissions (read-only) -->
    <div class="card bg-base-100 shadow-lg border border-base-300/50">
        <div class="card-body">
            <h2 class="card-title">Permissions</h2>
            @include('roles._permission-matrix', ['groupedPermissions' => $groupedPermissions, 'checkedNames' => $rolePermissionNames, 'readonly' => true])
        </div>
    </div>

    <!-- Users with this role -->
    <div class="card bg-base-100 shadow-lg border border-base-300/50">
        <div class="card-body">
            <h2 class="card-title">Users with this role</h2>
            @if($users->isEmpty())
                <p class="text-sm opacity-70">No users currently hold this role.</p>
            @else
                @php $showLink = Route::has('users.show') && auth()->user()->can('users.view'); @endphp
                <div class="overflow-x-auto">
                    <table id="roleUsersTable" class="table table-zebra w-full">
                        <thead>
                            <tr>
                                <th>Name</th>
                                <th>Email</th>
                                <th>Joined</th>
                                @if($showLink)
                                <th></th>
                                @endif
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($users as $user)
                            <tr>
                                <td>{{ $user->name }}</td>
                                <td>{{ $user->email }}</td>
                                <td>{{ $user->created_at->format('M d, Y') }}</td>
                                @if($showLink)
                                <td><a href="{{ route('users.show', $user) }}" class="link link-primary text-sm">View</a></td>
                                @endif
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                @include('partials._datatables-assets')

                @push('scripts')
                <script>
                    $(function () {
                        // Small, fixed-size list — rendered server-side already, DataTables just
                        // adds client-side sort/search/paging on top. No ajax endpoint needed.
                        $('#roleUsersTable').DataTable({
                            autoWidth: false,
                            pageLength: 10,
                        });
                    });
                </script>
                @endpush
            @endif
        </div>
    </div>
</div>
@endsection
