@extends('layouts.app')

@section('title', $workOrder->code . ' — ' . $workOrder->title)

@section('breadcrumbs')
    <li><a href="{{ route('work-orders.index') }}">Work Orders</a></li>
    <li>{{ $workOrder->code }}</li>
@endsection

@section('back', route('work-orders.index'))

@php
    $canTouchStatus = auth()->user()->can('work-orders.update')
        || $workOrder->assigned_to === auth()->id();

    $priorityColor = match ($workOrder->priority) {
        'urgent' => 'error',
        'high' => 'warning',
        'medium' => 'info',
        default => 'ghost',
    };

    $statusColor = match ($workOrder->status) {
        'pending' => 'ghost',
        'in_progress' => 'info',
        'completed' => 'success',
        'cancelled' => 'error',
        default => 'ghost',
    };
@endphp

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

    <!-- Header -->
    <div class="flex flex-col lg:flex-row justify-between items-start gap-4">
        <div>
            <h1 class="text-3xl font-bold flex items-center gap-3 flex-wrap">
                {{ $workOrder->title }}
                <div class="badge badge-{{ $priorityColor }} badge-lg">{{ ucfirst($workOrder->priority) }}</div>
                <div class="badge badge-{{ $statusColor }} badge-lg">{{ ucfirst(str_replace('_', ' ', $workOrder->status)) }}</div>
            </h1>
            <p class="text-sm opacity-70 mt-2">
                <code class="font-mono">{{ $workOrder->code }}</code>
                · {{ ucfirst($workOrder->type) }}
                · @can('machines.view')<a href="{{ route('machines.show', $workOrder->machine) }}" class="link link-hover">{{ $workOrder->machine->name }}</a>@else{{ $workOrder->machine->name }}@endcan
            </p>
        </div>
        <div class="flex gap-2">
            @can('work-orders.update')
                <a href="{{ route('work-orders.edit', $workOrder) }}" class="btn btn-outline gap-2">Edit</a>
            @endcan
            @can('work-orders.delete')
                <a href="{{ route('work-orders.confirm-delete', $workOrder) }}" class="btn btn-error btn-outline gap-2">Delete</a>
            @endcan
        </div>
    </div>

    @if($workOrder->alert)
        <div class="alert alert-warning">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path>
            </svg>
            <div class="text-sm">
                <p class="font-semibold">Linked to alert #{{ $workOrder->alert->id }}</p>
                <p class="opacity-80">{{ $workOrder->alert->message }}</p>
                @if($workOrder->status !== 'completed')
                    <p class="opacity-60 text-xs mt-1">Completing this work order will resolve that alert.</p>
                @endif
            </div>
        </div>
    @endif

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- Details -->
        <div class="lg:col-span-2 card bg-base-100 shadow-lg border border-base-300/50">
            <div class="card-body">
                <h2 class="card-title">Details</h2>
                <p class="text-sm whitespace-pre-line">{{ $workOrder->description ?: 'No description provided.' }}</p>

                @if($workOrder->status === 'completed' && $workOrder->resolution_notes)
                    <div class="divider"></div>
                    <h3 class="font-semibold text-sm">Resolution Notes</h3>
                    <p class="text-sm whitespace-pre-line opacity-80">{{ $workOrder->resolution_notes }}</p>
                @endif
            </div>
        </div>

        <!-- Info sidebar -->
        <div class="card bg-base-100 shadow-lg border border-base-300/50">
            <div class="card-body space-y-3">
                <h2 class="card-title">Info</h2>

                <div class="flex flex-col gap-0.5">
                    <span class="text-xs font-semibold uppercase tracking-wide opacity-50">Assigned To</span>
                    <span class="font-medium">{{ $workOrder->assignedTo->name ?? 'Unassigned' }}</span>
                </div>

                <div class="flex flex-col gap-0.5">
                    <span class="text-xs font-semibold uppercase tracking-wide opacity-50">Created By</span>
                    <span class="font-medium">{{ $workOrder->createdBy->name ?? 'System' }} · {{ $workOrder->created_at->format('M d, Y') }}</span>
                </div>

                @if($workOrder->due_date)
                <div class="flex flex-col gap-0.5">
                    <span class="text-xs font-semibold uppercase tracking-wide opacity-50">Due Date</span>
                    <span class="font-medium">{{ $workOrder->due_date->format('M d, Y') }}</span>
                </div>
                @endif

                @if($workOrder->started_at)
                <div class="flex flex-col gap-0.5">
                    <span class="text-xs font-semibold uppercase tracking-wide opacity-50">Started</span>
                    <span class="font-medium">{{ $workOrder->started_at->format('M d, Y H:i') }}</span>
                </div>
                @endif

                @if($workOrder->completed_at)
                <div class="flex flex-col gap-0.5">
                    <span class="text-xs font-semibold uppercase tracking-wide opacity-50">Completed</span>
                    <span class="font-medium">{{ $workOrder->completed_at->format('M d, Y H:i') }}</span>
                </div>
                @endif
            </div>
        </div>
    </div>

    <!-- Status workflow -->
    @can('work-orders.update-status')
    @if($canTouchStatus && !in_array($workOrder->status, ['completed', 'cancelled']))
    <div class="card bg-base-100 shadow-lg border border-base-300/50" id="complete">
        <div class="card-body">
            <h2 class="card-title">Update Status</h2>

            <div class="flex flex-wrap gap-2 mt-2">
                @if($workOrder->status === 'pending')
                    <form action="{{ route('work-orders.update-status', $workOrder) }}" method="POST">
                        @csrf
                        @method('PATCH')
                        <input type="hidden" name="status" value="in_progress">
                        <button type="submit" class="btn btn-info gap-2">Start Work</button>
                    </form>
                @endif

                <form action="{{ route('work-orders.update-status', $workOrder) }}" method="POST">
                    @csrf
                    @method('PATCH')
                    <input type="hidden" name="status" value="cancelled">
                    <button type="submit" class="btn btn-ghost btn-outline gap-2">Cancel Work Order</button>
                </form>
            </div>

            @if($workOrder->status === 'in_progress')
                <div class="divider"></div>
                <form action="{{ route('work-orders.update-status', $workOrder) }}" method="POST" class="space-y-3">
                    @csrf
                    @method('PATCH')
                    <input type="hidden" name="status" value="completed">
                    <x-form.textarea name="resolution_notes" label="Resolution Notes" :rows="3"
                                      placeholder="What was done to resolve this?" />
                    <button type="submit" class="btn btn-success gap-2">Mark Complete</button>
                </form>
            @endif
        </div>
    </div>
    @endif
    @endcan
</div>
@endsection
