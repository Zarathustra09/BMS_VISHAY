@extends('layouts.app')

@section('title', 'Add Role')

@section('breadcrumbs')
    <li><a href="{{ route('roles.index') }}">Roles</a></li>
    <li>Add Role</li>
@endsection

@section('content')
<div class="space-y-6 max-w-5xl mx-auto">
    <h1 class="text-3xl font-bold flex items-center gap-3">
        <div class="w-12 h-12 rounded-xl bg-gradient-to-br from-primary to-secondary flex items-center justify-center shadow-lg">
            <svg class="w-7 h-7 text-base-100" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
            </svg>
        </div>
        Add Role
    </h1>

    <form action="{{ route('roles.store') }}" method="POST" class="space-y-6">
        @csrf

        <div class="card bg-base-100 shadow-lg border border-base-300/50">
            <div class="card-body">
                <div class="form-control max-w-sm">
                    <label class="label"><span class="label-text font-medium">Role Name</span></label>
                    <input type="text" name="name" value="{{ old('name') }}" placeholder="e.g. shift-supervisor"
                           class="input input-bordered @error('name') input-error @enderror" required autofocus />
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
                @include('roles._permission-matrix', ['groupedPermissions' => $groupedPermissions, 'checkedNames' => old('permissions', [])])
            </div>
        </div>

        <div class="flex justify-end gap-2">
            <a href="{{ route('roles.index') }}" class="btn btn-ghost">Cancel</a>
            <button type="submit" class="btn btn-primary">Create Role</button>
        </div>
    </form>
</div>
@endsection
