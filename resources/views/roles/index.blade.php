@extends('layouts.app')

@section('title', 'Roles')

@section('breadcrumbs')
    <li><a href="{{ route('roles.index') }}">Roles</a></li>
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
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"></path>
                    </svg>
                </div>
                Roles
            </h1>
            <p class="text-sm opacity-70 mt-2">Manage roles, the permissions each one grants, and who holds them</p>
        </div>
        @can('roles.create')
        <a href="{{ route('roles.create') }}" class="btn btn-primary gap-2 shadow-lg hover:shadow-primary/50">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
            </svg>
            Add Role
        </a>
        @endcan
    </div>

    <!-- Table -->
    <div class="card bg-base-100 shadow-lg border border-base-300/50">
        <div class="card-body">
            <div class="overflow-x-auto">
                <table id="rolesTable" class="table table-zebra w-full">
                    <thead>
                        <tr>
                            <th>Role</th>
                            <th>Users</th>
                            <th>Permissions</th>
                            <th></th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($roles as $role)
                        <tr>
                            <td>
                                <span class="capitalize font-medium">{{ str_replace('-', ' ', $role->name) }}</span>
                                @if($role->name === 'admin')
                                <div class="badge badge-warning badge-sm gap-1 ml-2">
                                    <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"></path>
                                    </svg>
                                    Protected
                                </div>
                                @endif
                            </td>
                            <td>{{ $role->users_count }}</td>
                            <td>{{ $role->permissions_count }}</td>
                            <td>
                                <div class="flex items-center gap-2">
                                    <a href="{{ route('roles.show', $role) }}" class="btn btn-ghost btn-xs">View</a>
                                    @can('roles.update')
                                    <button type="button" class="btn btn-outline btn-xs js-assign-users"
                                            data-role-id="{{ $role->id }}"
                                            data-role-name="{{ str_replace('-', ' ', $role->name) }}">Add Users</button>
                                        @if($role->name !== 'admin')
                                        <a href="{{ route('roles.edit', $role) }}" class="btn btn-outline btn-xs">Edit</a>
                                        @endif
                                    @endcan
                                    @can('roles.delete')
                                        @if($role->name !== 'admin')
                                        <a href="{{ route('roles.confirm-delete', $role) }}" class="btn btn-error btn-outline btn-xs">Delete</a>
                                        @endif
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

@can('roles.update')
<!-- Add Users Modal (shared — target role set via JS from the clicked row) -->
<dialog id="assign_users_modal" class="modal modal-bottom sm:modal-middle">
    <div class="modal-box max-w-lg">
        <h3 class="font-bold text-lg capitalize">Add users to <span id="assignUsersRoleName"></span></h3>
        <p class="text-xs opacity-70 mt-1">Each user holds exactly one role — picking someone here moves them off their current role.</p>

        <form id="assignUsersForm" method="POST" class="py-4 space-y-3">
            @csrf
            <input type="text" id="assignUsersSearch" class="input input-sm w-full" placeholder="Search users..." autocomplete="off">

            <div class="max-h-72 overflow-y-auto space-y-1 border border-base-300 rounded-lg p-2">
                @forelse($users as $user)
                    @if($user->id !== auth()->id())
                    <label class="assign-user-row label cursor-pointer justify-start gap-3 hover:bg-base-200 rounded-lg px-2" data-search="{{ strtolower($user->name . ' ' . $user->email) }}">
                        <input type="checkbox" name="user_ids[]" value="{{ $user->id }}" class="checkbox checkbox-sm" />
                        <span class="flex-1">
                            <span class="block text-sm">{{ $user->name }}</span>
                            <span class="block text-xs opacity-60">{{ $user->email }} @if($user->roles->first()) &middot; currently {{ $user->roles->first()->name }} @endif</span>
                        </span>
                    </label>
                    @endif
                @empty
                    <p class="text-sm opacity-60 px-2">No other users exist yet.</p>
                @endforelse
            </div>

            <div class="modal-action">
                <button type="button" class="btn" onclick="assign_users_modal.close()">Cancel</button>
                <button type="submit" class="btn btn-primary">Add Selected Users</button>
            </div>
        </form>
    </div>
    <form method="dialog" class="modal-backdrop"><button>close</button></form>
</dialog>
@endcan

@include('partials._datatables-assets')

@push('scripts')
<script>
    $(function () {
        // All roles are already rendered above (a handful at most) — DataTables just adds
        // sort/search/paging on top, no ajax endpoint needed (see 02-architecture.md).
        $('#rolesTable').DataTable({
            autoWidth: false,
            pageLength: 25,
            columnDefs: [{targets: -1, orderable: false, searchable: false}],
        });

        var assignUrlTemplate = '{{ route('roles.assign-users', ['role' => ':id']) }}';

        $(document).on('click', '.js-assign-users', function () {
            var roleId = $(this).data('role-id');
            var roleName = $(this).data('role-name');

            $('#assignUsersForm').attr('action', assignUrlTemplate.replace(':id', roleId));
            $('#assignUsersRoleName').text(roleName);
            $('#assignUsersSearch').val('');
            $('.assign-user-row').show();
            $('#assignUsersForm input[type=checkbox]').prop('checked', false);

            document.getElementById('assign_users_modal').showModal();
        });

        $('#assignUsersSearch').on('input', function () {
            var term = $(this).val().toLowerCase();
            $('.assign-user-row').each(function () {
                var match = $(this).data('search').indexOf(term) !== -1;
                $(this).toggle(match);
            });
        });
    });
</script>
@endpush
@endsection
