@extends('layouts.app')

@section('title', 'Edit Work Order — ' . $workOrder->code)

@section('breadcrumbs')
    <li><a href="{{ route('work-orders.index') }}">Work Orders</a></li>
    <li><a href="{{ route('work-orders.show', $workOrder) }}">{{ $workOrder->code }}</a></li>
    <li>Edit</li>
@endsection

@section('back', route('work-orders.show', $workOrder))

@section('content')
<div class="space-y-6">
    <div class="flex items-center gap-4">
        <div>
            <h1 class="text-3xl font-bold">Edit Work Order</h1>
            <p class="text-sm opacity-70 mt-1"><code class="font-mono">{{ $workOrder->code }}</code></p>
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
            <form action="{{ route('work-orders.update', $workOrder) }}" method="POST" class="space-y-6">
                @csrf
                @method('PUT')

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <x-form.input name="title" label="Title" required span maxlength="255"
                                  :value="$workOrder->title" />

                    <x-form.textarea name="description" label="Description" :rows="3" span
                                      :value="$workOrder->description" />

                    <x-form.select name="machine_id" label="Machine" required
                                    :options="$machineOptions" :selected="$workOrder->machine_id" />

                    <x-form.select name="type" label="Type" required
                                    :options="$typeOptions" :selected="$workOrder->type" />

                    <x-form.select name="priority" label="Priority" required
                                    :options="$priorityOptions" :selected="$workOrder->priority" />

                    <x-form.select name="assigned_to" label="Assign To"
                                    placeholder="— Unassigned —" :options="$assigneeOptions"
                                    :selected="$workOrder->assigned_to" />

                    <x-form.input type="date" name="due_date" label="Due Date"
                                  :value="$workOrder->due_date?->format('Y-m-d')" />
                </div>

                @if($workOrder->alert)
                    <div class="alert alert-info">
                        <span class="text-sm">Linked to alert: {{ $workOrder->alert->message }} (not editable here)</span>
                    </div>
                @endif

                <x-form.actions :cancel-href="route('work-orders.show', $workOrder)" submit-label="Save Changes" />
            </form>
        </div>
    </div>
</div>
@endsection
