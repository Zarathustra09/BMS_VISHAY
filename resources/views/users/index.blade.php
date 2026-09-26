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

    <!-- Filters -->
    <div class="card bg-base-100 shadow-lg border border-base-300/50">
        <div class="card-body">
            <form method="GET" action="{{ route('users.index') }}" class="flex flex-col lg:flex-row gap-4">
                <div class="form-control flex-1">
                    <label class="input input-bordered flex items-center gap-2">
                        <svg class="w-4 h-4 opacity-70" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                        </svg>
                        <input type="text" name="search" class="grow" placeholder="Search by name or email..." value="{{ request('search') }}" />
                    </label>
                </div>

                <select name="role" class="select select-bordered w-full lg:w-48" onchange="this.form.submit()">
                    <option value="all" {{ request('role', 'all') === 'all' ? 'selected' : '' }}>All Roles</option>
                    @foreach($roles as $role)
                        <option value="{{ $role }}" {{ request('role') === $role ? 'selected' : '' }} class="capitalize">{{ ucfirst($role) }}</option>
                    @endforeach
                </select>

                <button type="submit" class="btn btn-primary">Filter</button>
            </form>
        </div>
    </div>

    <!-- Users Grid -->
    <div class="grid grid-cols-1 lg:grid-cols-2 xl:grid-cols-3 gap-6">
        @forelse($users as $user)
        <div class="card bg-base-100 shadow-lg border border-base-300/50 hover:shadow-2xl transition-all group">
            <div class="card-body">
                <div class="flex items-start justify-between">
                    <div class="flex items-center gap-4">
                        <div class="avatar placeholder">
                            <div class="bg-primary text-primary-content rounded-full w-16">
                                <span class="text-2xl font-bold">{{ strtoupper(substr($user->name, 0, 2)) }}</span>
                            </div>
                        </div>
                        <div>
                            <h3 class="card-title text-lg">{{ $user->name }}</h3>
                            <p class="text-sm opacity-70">{{ $user->email }}</p>
                            @if($user->id === auth()->id())
                                <div class="badge badge-ghost badge-sm gap-1 mt-2">You</div>
                            @endif
                        </div>
                    </div>
                    @if(auth()->user()->can('users.update') || (auth()->user()->can('users.delete') && $user->id !== auth()->id()))
                    <div class="dropdown dropdown-end">
                        <div tabindex="0" role="button" class="btn btn-ghost btn-sm btn-circle">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 5v.01M12 12v.01M12 19v.01M12 6a1 1 0 110-2 1 1 0 010 2zm0 7a1 1 0 110-2 1 1 0 010 2zm0 7a1 1 0 110-2 1 1 0 010 2z"></path>
                            </svg>
                        </div>
                        <ul tabindex="0" class="dropdown-content z-[1] menu p-2 shadow-lg bg-base-100 rounded-box w-52 border border-base-300/50">
                            @can('users.update')
                            <li><a onclick="edit_user_modal_{{ $user->id }}.showModal()">Edit User</a></li>
                            <li><a onclick="reset_password_modal_{{ $user->id }}.showModal()">Reset Password</a></li>
                            @endcan
                            @can('users.delete')
                                @if($user->id !== auth()->id())
                                <div class="divider my-0"></div>
                                <li><a class="text-error" onclick="delete_user_modal_{{ $user->id }}.showModal()">Delete</a></li>
                                @endif
                            @endcan
                        </ul>
                    </div>
                    @endif
                </div>

                <div class="divider my-2"></div>

                <div class="space-y-3">
                    <div class="flex items-center justify-between">
                        <span class="opacity-70 text-sm">Role:</span>
                        <div class="flex flex-wrap gap-1 justify-end">
                            @forelse($user->roles as $role)
                                <div class="badge badge-primary badge-outline capitalize">{{ $role->name }}</div>
                            @empty
                                <div class="badge badge-ghost">No role</div>
                            @endforelse
                        </div>
                    </div>
                    <div class="flex items-center gap-2 text-sm">
                        <svg class="w-4 h-4 opacity-70" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                        </svg>
                        <span class="opacity-70">Joined:</span>
                        <span class="font-medium">{{ $user->created_at->format('M d, Y') }}</span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Edit User Modal -->
        <dialog id="edit_user_modal_{{ $user->id }}" class="modal modal-bottom sm:modal-middle">
            <div class="modal-box">
                <h3 class="font-bold text-lg">Edit {{ $user->name }}</h3>
                <form action="{{ route('users.update', $user) }}" method="POST" class="py-4 space-y-3">
                    @csrf
                    @method('PUT')
                    <div class="form-control">
                        <label class="label"><span class="label-text">Full Name</span></label>
                        <input type="text" name="name" value="{{ $user->name }}" class="input input-bordered" required />
                    </div>
                    <div class="form-control">
                        <label class="label"><span class="label-text">Email</span></label>
                        <input type="email" name="email" value="{{ $user->email }}" class="input input-bordered" required />
                    </div>
                    <div class="form-control">
                        <label class="label"><span class="label-text">Role</span></label>
                        <select name="role" class="select select-bordered capitalize" required>
                            @foreach($roles as $role)
                                <option value="{{ $role }}" class="capitalize" {{ $user->hasRole($role) ? 'selected' : '' }}>{{ ucfirst($role) }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="modal-action">
                        <button type="button" class="btn" onclick="edit_user_modal_{{ $user->id }}.close()">Cancel</button>
                        <button type="submit" class="btn btn-primary">Save Changes</button>
                    </div>
                </form>
            </div>
            <form method="dialog" class="modal-backdrop"><button>close</button></form>
        </dialog>

        <!-- Reset Password Modal -->
        <dialog id="reset_password_modal_{{ $user->id }}" class="modal modal-bottom sm:modal-middle">
            <div class="modal-box">
                <h3 class="font-bold text-lg">Reset Password — {{ $user->name }}</h3>
                <form action="{{ route('users.reset-password', $user) }}" method="POST" class="py-4 space-y-3">
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
                        <button type="button" class="btn" onclick="reset_password_modal_{{ $user->id }}.close()">Cancel</button>
                        <button type="submit" class="btn btn-primary">Update Password</button>
                    </div>
                </form>
            </div>
            <form method="dialog" class="modal-backdrop"><button>close</button></form>
        </dialog>

        <!-- Delete User Modal -->
        <dialog id="delete_user_modal_{{ $user->id }}" class="modal modal-bottom sm:modal-middle">
            <div class="modal-box">
                <h3 class="font-bold text-lg text-error">Delete {{ $user->name }}?</h3>
                <p class="py-2 text-sm opacity-80">This permanently deletes the account for <strong>{{ $user->email }}</strong>. This cannot be undone.</p>
                <form action="{{ route('users.destroy', $user) }}" method="POST" class="modal-action">
                    @csrf
                    @method('DELETE')
                    <button type="button" class="btn" onclick="delete_user_modal_{{ $user->id }}.close()">Cancel</button>
                    <button type="submit" class="btn btn-error">Delete Permanently</button>
                </form>
            </div>
            <form method="dialog" class="modal-backdrop"><button>close</button></form>
        </dialog>
        @empty
        <div class="col-span-full text-center py-12 opacity-70">
            No users found.
        </div>
        @endforelse
    </div>

    <div class="flex justify-center">
        {{ $users->appends(request()->query())->links() }}
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
    <form method="dialog" class="modal-backdrop">
        <button>close</button>
    </form>
</dialog>
@endcan

@endsection
