@extends('layouts.app')

@section('title', 'Delete Machine Type — ' . $machineType->name)

@section('breadcrumbs')
    <li><a href="{{ route('machine-types.index') }}">Machine Types</a></li>
    <li class="text-error">Delete {{ $machineType->name }}</li>
@endsection

@section('back', route('machine-types.index'))

@section('content')
<div class="space-y-6 max-w-2xl mx-auto">
    <div class="alert alert-error shadow-lg">
        <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path>
        </svg>
        <div>
            <h3 class="font-bold text-lg">Delete machine type "{{ $machineType->icon }} {{ $machineType->name }}"?</h3>
            <p class="text-sm">This cannot be undone.</p>
        </div>
    </div>

    @if($inUse)
        <div class="card bg-base-100 shadow-lg border-2 border-warning/50">
            <div class="card-body">
                <h3 class="card-title text-warning">Can't delete yet</h3>
                <p class="text-sm">
                    {{ $machineType->machines()->withTrashed()->count() }} machine(s) still use this type.
                    Reassign them to a different type first.
                </p>
                <div class="card-actions justify-end mt-4">
                    <a href="{{ route('machine-types.edit', $machineType) }}" class="btn btn-ghost">Back to Type</a>
                </div>
            </div>
        </div>
    @else
        <div class="card bg-base-100 shadow-lg border-2 border-error/50">
            <div class="card-body">
                <p class="text-sm">No machines currently use this type — it's safe to delete.</p>
                <form action="{{ route('machine-types.destroy', $machineType) }}" method="POST" class="card-actions justify-end mt-4">
                    @csrf
                    @method('DELETE')
                    <a href="{{ route('machine-types.edit', $machineType) }}" class="btn btn-ghost">Cancel</a>
                    <button type="submit" class="btn btn-error">Permanently Delete Type</button>
                </form>
            </div>
        </div>
    @endif
</div>
@endsection
