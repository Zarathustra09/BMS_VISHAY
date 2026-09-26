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
            <p class="text-sm opacity-70 mt-2">Manage roles and the permissions each one grants</p>
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

    <!-- Roles Grid -->
    <div class="grid grid-cols-1 lg:grid-cols-2 xl:grid-cols-3 gap-6">
        @foreach($roles as $role)
        <div class="card bg-base-100 shadow-lg border border-base-300/50 hover:shadow-xl transition-all">
            <div class="card-body">
                <div class="flex items-start justify-between">
                    <h3 class="card-title capitalize">{{ str_replace('-', ' ', $role->name) }}</h3>
                    @if($role->name === 'admin')
                    <div class="badge badge-warning badge-sm gap-1">
                        <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"></path>
                        </svg>
                        Protected
                    </div>
                    @endif
                </div>

                <div class="flex gap-6 text-sm mt-2">
                    <div>
                        <span class="text-2xl font-bold">{{ $role->users_count }}</span>
                        <span class="opacity-70 block text-xs">user{{ $role->users_count === 1 ? '' : 's' }}</span>
                    </div>
                    <div>
                        <span class="text-2xl font-bold">{{ $role->permissions_count }}</span>
                        <span class="opacity-70 block text-xs">permission{{ $role->permissions_count === 1 ? '' : 's' }}</span>
                    </div>
                </div>

                <div class="card-actions justify-end mt-4">
                    <a href="{{ route('roles.show', $role) }}" class="btn btn-ghost btn-sm">View</a>
                    @can('roles.update')
                        @if($role->name !== 'admin')
                        <a href="{{ route('roles.edit', $role) }}" class="btn btn-outline btn-sm">Edit</a>
                        @endif
                    @endcan
                    @can('roles.delete')
                        @if($role->name !== 'admin')
                        <a href="{{ route('roles.confirm-delete', $role) }}" class="btn btn-error btn-outline btn-sm">Delete</a>
                        @endif
                    @endcan
                </div>
            </div>
        </div>
        @endforeach
    </div>
</div>
@endsection
