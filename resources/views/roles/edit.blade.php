@extends('layouts.app')

@section('title', 'Edit Role — ' . $role->name)

@section('breadcrumbs')
    <li><a href="{{ route('roles.index') }}">Roles</a></li>
    <li><a href="{{ route('roles.show', $role) }}" class="capitalize">{{ $role->name }}</a></li>
    <li>Edit</li>
@endsection

@section('back', route('roles.show', $role))

@section('content')
<div class="space-y-6 max-w-5xl mx-auto">
    @if(session('error'))
    <div class="alert alert-error shadow-lg animate-fadeIn">
        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
        </svg>
        <span>{{ session('error') }}</span>
    </div>
    @endif

    <h1 class="text-3xl font-bold flex items-center gap-3">
        <div class="w-12 h-12 rounded-xl bg-gradient-to-br from-primary to-secondary flex items-center justify-center shadow-lg">
            <svg class="w-7 h-7 text-base-100" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
            </svg>
        </div>
        Edit Role
    </h1>

    <form action="{{ route('roles.update', $role) }}" method="POST" class="space-y-6">
        @csrf
        @method('PUT')

        <div class="card bg-base-100 shadow-lg border border-base-300/50">
            <div class="card-body">
                <div class="form-control max-w-sm">
                    <label class="label"><span class="label-text font-medium">Role Name</span></label>
                    <input type="text" name="name" value="{{ old('name', $role->name) }}"
                           class="input input-bordered @error('name') input-error @enderror" required />
                    @error('name')
                        <span class="text-error text-xs mt-1">{{ $message }}</span>
                    @else
                        <label class="label"><span class="label-text-alt">Lowercase letters, numbers, and hyphens only</span></label>
                    @enderror
                </div>
            </div>
        </div>

        <div class="card bg-base-100 shadow-lg border border-base-300/50">
            <div class="card-body">
                <h2 class="card-title">Permissions</h2>
                <p class="text-sm opacity-70">Choose what this role can view, create, update, and delete.</p>
                @include('roles._permission-matrix', ['groupedPermissions' => $groupedPermissions, 'checkedNames' => old('permissions', $rolePermissionNames)])
            </div>
        </div>

        <div class="flex justify-end gap-2">
            <a href="{{ route('roles.show', $role) }}" class="btn btn-ghost">Cancel</a>
            <button type="submit" class="btn btn-primary">Save Changes</button>
        </div>
    </form>
</div>
@endsection
