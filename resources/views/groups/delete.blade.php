@extends('layouts.app')

@section('title', 'Delete Group — ' . $group->name)

@section('breadcrumbs')
    <li><a href="{{ route('groups.index') }}">Groups</a></li>
    <li><a href="{{ route('groups.show', $group) }}">{{ $group->name }}</a></li>
    <li class="text-error">Delete</li>
@endsection

@section('back', route('groups.show', $group))

@section('content')
<div class="space-y-6 max-w-2xl mx-auto">
    <div class="alert alert-error shadow-lg">
        <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path>
        </svg>
        <div>
            <h3 class="font-bold text-lg">Delete group "{{ $group->name }}"?</h3>
            <p class="text-sm">Its members lose any access they only had through this group. This cannot be undone.</p>
        </div>
    </div>

    @if($machines->isNotEmpty())
        <div class="card bg-base-100 shadow-lg border-2 border-warning/50">
            <div class="card-body">
                <h3 class="card-title text-warning">Can't delete yet</h3>
                <p class="text-sm">
                    {{ $machines->count() }} machine{{ $machines->count() === 1 ? '' : 's' }}
                    {{ $machines->count() === 1 ? 'uses' : 'use' }} this group for alert emails.
                    Give {{ $machines->count() === 1 ? 'it' : 'them' }} another group (or none) first.
                </p>
                <ul class="mt-3 space-y-1">
                    @foreach($machines as $machine)
                        <li class="text-sm">{{ $machine->name }} <span class="opacity-50 font-mono text-xs">{{ $machine->machine_code }}</span></li>
                    @endforeach
                </ul>
                <div class="card-actions justify-end mt-4">
                    <a href="{{ route('groups.show', $group) }}" class="btn btn-ghost">Back to Group</a>
                </div>
            </div>
        </div>
    @else
        <div class="card bg-base-100 shadow-lg border-2 border-error/50">
            <div class="card-body">
                <p class="text-sm">No machine uses this group - it's safe to delete.</p>
                <form action="{{ route('groups.destroy', $group) }}" method="POST" class="card-actions justify-end mt-4">
                    @csrf
                    @method('DELETE')
                    <a href="{{ route('groups.show', $group) }}" class="btn btn-ghost">Cancel</a>
                    <button type="submit" class="btn btn-error">Permanently Delete Group</button>
                </form>
            </div>
        </div>
    @endif
</div>
@endsection
