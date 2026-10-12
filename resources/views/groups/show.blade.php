@extends('layouts.app')

@section('title', 'Group — ' . $group->name)

@section('breadcrumbs')
    <li><a href="{{ route('groups.index') }}">Groups</a></li>
    <li>{{ $group->name }}</li>
@endsection

@section('back', route('groups.index'))

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

    <div class="flex flex-col lg:flex-row justify-between items-start gap-4">
        <div>
            <h1 class="text-3xl font-bold">{{ $group->name }}</h1>
            @if($group->description)
            <p class="text-sm opacity-70 mt-2">{{ $group->description }}</p>
            @endif
        </div>
        <div class="flex gap-2">
            @can('groups.update')
            <a href="{{ route('groups.edit', $group) }}" class="btn btn-primary btn-sm">Edit</a>
            @endcan
            @can('groups.delete')
            <a href="{{ route('groups.confirm-delete', $group) }}" class="btn btn-error btn-outline btn-sm">Delete</a>
            @endcan
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <div class="card bg-base-100 shadow-lg border border-base-300/50">
            <div class="card-body">
                <h2 class="card-title">Members <span class="badge badge-ghost">{{ $group->users->count() }}</span></h2>
                @forelse($group->users as $user)
                    <div class="text-sm flex justify-between border-b border-base-200 py-1">
                        <span>{{ $user->name }}</span>
                        <span class="opacity-60">{{ $user->email }}</span>
                    </div>
                @empty
                    <p class="text-sm opacity-60">No members yet.</p>
                @endforelse
            </div>
        </div>

        <div class="card bg-base-100 shadow-lg border border-base-300/50">
            <div class="card-body">
                <h2 class="card-title">Roles <span class="badge badge-ghost">{{ $group->roles->count() }}</span></h2>
                <div class="flex flex-wrap gap-2">
                    @forelse($group->roles as $role)
                        <span class="badge badge-primary capitalize">{{ str_replace('-', ' ', $role->name) }}</span>
                    @empty
                        <p class="text-sm opacity-60">No roles - members get only what the extra permissions below grant.</p>
                    @endforelse
                </div>

                <h2 class="card-title mt-4">Assigned machines <span class="badge badge-ghost">{{ $group->machines->count() }}</span></h2>
                <p class="text-xs opacity-60">Members are emailed when one of these machines raises an alert.</p>
                @forelse($group->machines as $machine)
                    <div class="text-sm border-b border-base-200 py-1">
                        @can('machines.view')
                            <a href="{{ route('machines.show', $machine) }}" class="link link-hover">{{ $machine->name }}</a>
                        @else
                            {{ $machine->name }}
                        @endcan
                        <span class="opacity-50 font-mono text-xs">{{ $machine->machine_code }}</span>
                    </div>
                @empty
                    <p class="text-sm opacity-60">No machine uses this group yet.</p>
                @endforelse
            </div>
        </div>
    </div>

    <div class="card bg-base-100 shadow-lg border border-base-300/50">
        <div class="card-body">
            <h2 class="card-title">Extra permissions</h2>
            @include('roles._permission-matrix', ['groupedPermissions' => $groupedPermissions, 'checkedNames' => $groupPermissionNames, 'readonly' => true])
        </div>
    </div>
</div>
@endsection
