@extends('layouts.app')

@section('title', 'User Management')

@section('breadcrumbs')
    <li><a href="{{ route('users.index') }}">User Management</a></li>
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
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"></path>
                    </svg>
                </div>
                User Management
            </h1>
            <p class="text-sm opacity-70 mt-2">Manage user accounts and role assignments</p>
        </div>
        @can('users.create')
        <button class="btn btn-primary gap-2 shadow-lg hover:shadow-primary/50" onclick="add_user_modal.showModal()">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z"></path>
            </svg>
            Add New User
        </button>
        @endcan
    </div>

    <!-- Stats Overview -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-{{ min(count($stats), 5) }} gap-4">
        <div class="stat bg-base-100 rounded-box shadow-lg border border-base-300/50">
            <div class="stat-figure text-primary">
                <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"></path>
                </svg>
            </div>
            <div class="stat-title">Total Users</div>
            <div class="stat-value text-primary">{{ $stats['total'] }}</div>
        </div>
        @foreach($roles as $role)
        <div class="stat bg-base-100 rounded-box shadow-lg border border-base-300/50">
            <div class="stat-title capitalize">{{ $role }}</div>
            <div class="stat-value text-2xl">{{ $stats[$role] ?? 0 }}</div>
        </div>
        @endforeach
    </div>

    <!-- Table -->
    <div class="card bg-base-100 shadow-lg border border-base-300/50">
        <div class="card-body">
            <div class="flex flex-col lg:flex-row gap-4 mb-4">
                <select id="roleFilter" class="select select-bordered w-full lg:w-48">
                    <option value="">All Roles</option>
                    @foreach($roles as $role)
                        <option value="{{ $role }}" class="capitalize">{{ ucfirst($role) }}</option>
                    @endforeach
                </select>
            </div>

            <div class="overflow-x-auto">
                <table id="usersTable" class="table table-zebra w-full">
                    <thead>
                        <tr>
                            <th>Name</th>
                            <th>Email</th>
                            <th>Role</th>
                            <th>Joined</th>
                            <th></th>
                        </tr>
                    </thead>
                </table>
            </div>
        </div>
    </div>
</div>

@can('users.create')
<!-- Add User Modal -->
<dialog id="add_user_modal" class="modal modal-bottom sm:modal-middle">
    <div class="modal-box">
        <h3 class="font-bold text-lg flex items-center gap-2">
            <svg class="w-6 h-6 text-primary" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z"></path>
            </svg>
            Add New User
        </h3>
        <form action="{{ route('users.store') }}" method="POST" class="py-4 space-y-3">
            @csrf
            <div class="form-control">
                <label class="label"><span class="label-text">Full Name</span></label>
                <input type="text" name="name" placeholder="John Doe" class="input input-bordered" required />
            </div>
            <div class="form-control">
                <label class="label"><span class="label-text">Email</span></label>
                <input type="email" name="email" placeholder="john@vishay.com" class="input input-bordered" required />
            </div>
            <div class="form-control">
                <label class="label"><span class="label-text">Initial Password</span></label>
                <input type="password" name="password" class="input input-bordered" required minlength="8" />
            </div>
            <div class="form-control">
                <label class="label"><span class="label-text">Role</span></label>
                <select name="role" class="select select-bordered capitalize" required>
                    <option value="">Select role</option>
                    @foreach($roles as $role)
                        <option value="{{ $role }}" class="capitalize">{{ ucfirst($role) }}</option>
                    @endforeach
                </select>
            </div>
            <div class="modal-action">
                <button type="button" class="btn" onclick="add_user_modal.close()">Cancel</button>
                <button type="submit" class="btn btn-primary">Create User</button>
            </div>
        </form>
    </div>
    <form method="dialog" class="modal-backdrop"><button>close</button></form>
</dialog>
@endcan

@can('users.update')
<!-- Edit User Modal (shared — populated via JS from the clicked row) -->
<dialog id="edit_user_modal" class="modal modal-bottom sm:modal-middle">
    <div class="modal-box">
        <h3 class="font-bold text-lg">Edit <span id="editUserName"></span></h3>
        <form id="editUserForm" method="POST" class="py-4 space-y-3">
            @csrf
            @method('PUT')
            <div class="form-control">
                <label class="label"><span class="label-text">Full Name</span></label>
                <input type="text" name="name" id="editUserNameInput" class="input input-bordered" required />
            </div>
            <div class="form-control">
                <label class="label"><span class="label-text">Email</span></label>
                <input type="email" name="email" id="editUserEmailInput" class="input input-bordered" required />
            </div>
            <div class="form-control">
                <label class="label"><span class="label-text">Role</span></label>
                <select name="role" id="editUserRoleInput" class="select select-bordered capitalize" required>
                    @foreach($roles as $role)
                        <option value="{{ $role }}" class="capitalize">{{ ucfirst($role) }}</option>
                    @endforeach
                </select>
            </div>
            <div class="modal-action">
                <button type="button" class="btn" onclick="edit_user_modal.close()">Cancel</button>
                <button type="submit" class="btn btn-primary">Save Changes</button>
            </div>
        </form>
    </div>
    <form method="dialog" class="modal-backdrop"><button>close</button></form>
</dialog>

<!-- Reset Password Modal (shared) -->
<dialog id="reset_password_modal" class="modal modal-bottom sm:modal-middle">
    <div class="modal-box">
        <h3 class="font-bold text-lg">Reset Password — <span id="resetPasswordUserName"></span></h3>
        <form id="resetPasswordForm" method="POST" class="py-4 space-y-3">
            @csrf
            @method('PUT')
            <div class="form-control">
                <label class="label"><span class="label-text">New Password</span></label>
                <input type="password" name="password" class="input input-bordered" required minlength="8" />
            </div>
            <div class="form-control">
                <label class="label"><span class="label-text">Confirm New Password</span></label>
                <input type="password" name="password_confirmation" class="input input-bordered" required minlength="8" />
            </div>
            <div class="modal-action">
                <button type="button" class="btn" onclick="reset_password_modal.close()">Cancel</button>
                <button type="submit" class="btn btn-primary">Update Password</button>
            </div>
        </form>
    </div>
    <form method="dialog" class="modal-backdrop"><button>close</button></form>
</dialog>
@endcan

@can('users.delete')
<!-- Delete User Modal (shared) -->
<dialog id="delete_user_modal" class="modal modal-bottom sm:modal-middle">
    <div class="modal-box">
        <h3 class="font-bold text-lg text-error">Delete <span id="deleteUserName"></span>?</h3>
        <p class="py-2 text-sm opacity-80">This permanently deletes the account for <strong id="deleteUserEmail"></strong>. This cannot be undone.</p>
        <form id="deleteUserForm" method="POST" class="modal-action">
            @csrf
            @method('DELETE')
            <button type="button" class="btn" onclick="delete_user_modal.close()">Cancel</button>
            <button type="submit" class="btn btn-error">Delete Permanently</button>
        </form>
    </div>
    <form method="dialog" class="modal-backdrop"><button>close</button></form>
</dialog>
@endcan

@include('partials._datatables-assets')

@push('scripts')
<script>
    $(function () {
        var table = $('#usersTable').DataTable({
            processing: true,
            serverSide: true,
            autoWidth: false,
            pageLength: 25,
            order: [[0, 'asc']],
            ajax: {
                url: '{{ route('users.data') }}',
                data: function (d) {
                    d.role = $('#roleFilter').val();
                }
            },
            columns: [
                {data: 'name', name: 'name'},
                {data: 'email', name: 'email'},
                {data: 'role', name: 'role', orderable: false, searchable: false},
                {data: 'joined', name: 'created_at'},
                {data: 'action', name: 'action', orderable: false, searchable: false},
            ],
        });

        $('#roleFilter').on('change', function () {
            table.draw();
        });

        // Edit / Reset Password / Delete use one shared modal each, populated from the
        // clicked row's data-* attributes (see users/_datatable-actions.blade.php) — the
        // table is ajax-rendered, so we can't pre-render a modal per row.
        var editUrlTemplate = '{{ route('users.update', ['user' => ':id']) }}';
        var resetUrlTemplate = '{{ route('users.reset-password', ['user' => ':id']) }}';
        var deleteUrlTemplate = '{{ route('users.destroy', ['user' => ':id']) }}';

        $(document).on('click', '.js-edit-user', function () {
            var d = $(this).data();
            $('#editUserForm').attr('action', editUrlTemplate.replace(':id', d.id));
            $('#editUserName').text(d.name);
            $('#editUserNameInput').val(d.name);
            $('#editUserEmailInput').val(d.email);
            $('#editUserRoleInput').val(d.role);
            document.getElementById('edit_user_modal').showModal();
        });

        $(document).on('click', '.js-reset-password', function () {
            var d = $(this).data();
            $('#resetPasswordForm').attr('action', resetUrlTemplate.replace(':id', d.id));
            $('#resetPasswordUserName').text(d.name);
            document.getElementById('reset_password_modal').showModal();
        });

        $(document).on('click', '.js-delete-user', function () {
            var d = $(this).data();
            $('#deleteUserForm').attr('action', deleteUrlTemplate.replace(':id', d.id));
            $('#deleteUserName').text(d.name);
            $('#deleteUserEmail').text(d.email);
            document.getElementById('delete_user_modal').showModal();
        });
    });
</script>
@endpush
@endsection
