@extends('layouts.app')

@section('title', 'Delete Role — ' . $role->name)

@section('breadcrumbs')
    <li><a href="{{ route('roles.index') }}">Roles</a></li>
    <li><a href="{{ route('roles.show', $role) }}" class="capitalize">{{ $role->name }}</a></li>
    <li class="text-error">Delete</li>
@endsection

@section('content')
<div class="space-y-6 max-w-2xl mx-auto">
    <div class="alert alert-error shadow-lg">
        <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path>
        </svg>
        <div>
            <h3 class="font-bold text-lg">Delete role "{{ $role->name }}"?</h3>
            <p class="text-sm">This cannot be undone.</p>
        </div>
    </div>

    @if($users->isNotEmpty())
        <div class="card bg-base-100 shadow-lg border-2 border-warning/50">
            <div class="card-body">
                <h3 class="card-title text-warning">Can't delete yet</h3>
                <p class="text-sm">
                    {{ $users->count() }} user{{ $users->count() === 1 ? '' : 's' }} still {{ $users->count() === 1 ? 'holds' : 'hold' }} this role.
                    Reassign {{ $users->count() === 1 ? 'them' : 'them all' }} to a different role first.
                </p>
                <ul class="mt-3 space-y-1">
                    @foreach($users as $user)
                        <li class="text-sm flex items-center gap-2">
                            <svg class="w-4 h-4 opacity-60" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                            </svg>
                            {{ $user->name }} ({{ $user->email }})
                        </li>
                    @endforeach
                </ul>
                <div class="card-actions justify-end mt-4">
                    <a href="{{ route('roles.show', $role) }}" class="btn btn-ghost">Back to Role</a>
                </div>
            </div>
        </div>
    @else
        <div class="card bg-base-100 shadow-lg border-2 border-error/50">
            <div class="card-body">
                <p class="text-sm">No users currently hold this role — it's safe to delete.</p>
                <form action="{{ route('roles.destroy', $role) }}" method="POST" class="card-actions justify-end mt-4">
                    @csrf
                    @method('DELETE')
                    <a href="{{ route('roles.show', $role) }}" class="btn btn-ghost">Cancel</a>
                    <button type="submit" class="btn btn-error">Permanently Delete Role</button>
                </form>
            </div>
        </div>
    @endif
</div>
@endsection
