@extends('layouts.app')

@section('title', 'Create Work Order')

@section('breadcrumbs')
    <li><a href="{{ route('work-orders.index') }}">Work Orders</a></li>
    <li>Create</li>
@endsection

@section('back', route('work-orders.index'))

@section('content')
<div class="space-y-6">
    <div class="flex items-center gap-4">
        <div>
            <h1 class="text-3xl font-bold">Create Work Order</h1>
            <p class="text-sm opacity-70 mt-1">Log a maintenance or repair task</p>
        </div>
    </div>

    <x-form.errors />

    @php
        $typeOptions = ['corrective' => 'Corrective', 'preventive' => 'Preventive', 'emergency' => 'Emergency', 'inspection' => 'Inspection'];
        $priorityOptions = ['low' => 'Low', 'medium' => 'Medium', 'high' => 'High', 'urgent' => 'Urgent'];
        $machineOptions = $machines->pluck('name', 'id')->all();
        $assigneeOptions = $assignees->pluck('name', 'id')->all();
    @endphp

    <div class="card bg-base-100 shadow-xl border border-base-300/50">
        <div class="card-body">
            <form action="{{ route('work-orders.store') }}" method="POST" class="space-y-6">
                @csrf

                @if($alert)
                    <input type="hidden" name="alert_id" value="{{ $alert->id }}">
                    <div class="alert alert-warning">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path>
                        </svg>
                        <div class="text-sm">
                            <p class="font-semibold">Linked to an open alert</p>
                            <p class="opacity-80">{{ $alert->message }}</p>
                            <p class="opacity-60 text-xs mt-1">Completing this work order will resolve that alert automatically.</p>
                        </div>
                    </div>
                @endif

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <x-form.input name="title" label="Title" required span maxlength="255"
                                  placeholder="e.g., Replace chiller compressor bearing" />

                    <x-form.textarea name="description" label="Description" :rows="3" span
                                      placeholder="What needs to be done?" />

                    <x-form.select name="machine_id" label="Machine" required
                                    placeholder="Select machine" :options="$machineOptions"
                                    :selected="$machineId" />

                    <x-form.select name="type" label="Type" required
                                    :options="$typeOptions" :selected="$type" />

                    <x-form.select name="priority" label="Priority" required
                                    :options="$priorityOptions" selected="medium" />

                    <x-form.select name="assigned_to" label="Assign To"
                                    placeholder="— Unassigned —" :options="$assigneeOptions" />

                    <x-form.input type="date" name="due_date" label="Due Date" />
                </div>

                <x-form.actions :cancel-href="route('work-orders.index')" submit-label="Create Work Order" />
            </form>
        </div>
    </div>
</div>
@endsection
