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

    <div class="card bg-base-100 shadow-xl border border-base-300/50">
        <div class="card-body">
            <form action="{{ route('work-orders.update', $workOrder) }}" method="POST" class="space-y-6">
                @csrf
                @method('PUT')

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div class="form-control md:col-span-2">
                        <label class="label"><span class="label-text font-semibold">Title <span class="text-error">*</span></span></label>
                        <input type="text" name="title" value="{{ old('title', $workOrder->title) }}"
                               class="input input-bordered @error('title') input-error @enderror" required maxlength="255"/>
                        @error('title')
                            <label class="label"><span class="label-text-alt text-error">{{ $message }}</span></label>
                        @enderror
                    </div>

                    <div class="form-control md:col-span-2">
                        <label class="label"><span class="label-text font-semibold">Description</span></label>
                        <textarea name="description" rows="3" class="textarea textarea-bordered @error('description') textarea-error @enderror">{{ old('description', $workOrder->description) }}</textarea>
                        @error('description')
                            <label class="label"><span class="label-text-alt text-error">{{ $message }}</span></label>
                        @enderror
                    </div>

                    <div class="form-control">
                        <label class="label"><span class="label-text font-semibold">Machine <span class="text-error">*</span></span></label>
                        <select name="machine_id" class="select select-bordered @error('machine_id') select-error @enderror" required>
                            @foreach($machines as $machine)
                                <option value="{{ $machine->id }}" {{ (int) old('machine_id', $workOrder->machine_id) === $machine->id ? 'selected' : '' }}>{{ $machine->name }}</option>
                            @endforeach
                        </select>
                        @error('machine_id')
                            <label class="label"><span class="label-text-alt text-error">{{ $message }}</span></label>
                        @enderror
                    </div>

                    <div class="form-control">
                        <label class="label"><span class="label-text font-semibold">Type <span class="text-error">*</span></span></label>
                        <select name="type" class="select select-bordered @error('type') select-error @enderror" required>
                            @foreach(['corrective' => 'Corrective', 'preventive' => 'Preventive', 'emergency' => 'Emergency', 'inspection' => 'Inspection'] as $val => $label)
                                <option value="{{ $val }}" {{ old('type', $workOrder->type) === $val ? 'selected' : '' }}>{{ $label }}</option>
                            @endforeach
                        </select>
                        @error('type')
                            <label class="label"><span class="label-text-alt text-error">{{ $message }}</span></label>
                        @enderror
                    </div>

                    <div class="form-control">
                        <label class="label"><span class="label-text font-semibold">Priority <span class="text-error">*</span></span></label>
                        <select name="priority" class="select select-bordered @error('priority') select-error @enderror" required>
                            @foreach(['low' => 'Low', 'medium' => 'Medium', 'high' => 'High', 'urgent' => 'Urgent'] as $val => $label)
                                <option value="{{ $val }}" {{ old('priority', $workOrder->priority) === $val ? 'selected' : '' }}>{{ $label }}</option>
                            @endforeach
                        </select>
                        @error('priority')
                            <label class="label"><span class="label-text-alt text-error">{{ $message }}</span></label>
                        @enderror
                    </div>

                    <div class="form-control">
                        <label class="label"><span class="label-text font-semibold">Assign To</span></label>
                        <select name="assigned_to" class="select select-bordered @error('assigned_to') select-error @enderror">
                            <option value="">— Unassigned —</option>
                            @foreach($assignees as $assignee)
                                <option value="{{ $assignee->id }}" {{ (int) old('assigned_to', $workOrder->assigned_to) === $assignee->id ? 'selected' : '' }}>{{ $assignee->name }}</option>
                            @endforeach
                        </select>
                        @error('assigned_to')
                            <label class="label"><span class="label-text-alt text-error">{{ $message }}</span></label>
                        @enderror
                    </div>

                    <div class="form-control">
                        <label class="label"><span class="label-text font-semibold">Due Date</span></label>
                        <input type="date" name="due_date" value="{{ old('due_date', $workOrder->due_date?->format('Y-m-d')) }}"
                               class="input input-bordered @error('due_date') input-error @enderror"/>
                        @error('due_date')
                            <label class="label"><span class="label-text-alt text-error">{{ $message }}</span></label>
                        @enderror
                    </div>
                </div>

                @if($workOrder->alert)
                    <div class="alert alert-info">
                        <span class="text-sm">Linked to alert: {{ $workOrder->alert->message }} (not editable here)</span>
                    </div>
                @endif

                <div class="flex justify-end gap-3 pt-4 border-t border-base-300">
                    <a href="{{ route('work-orders.show', $workOrder) }}" class="btn btn-ghost">Cancel</a>
                    <button type="submit" class="btn btn-primary">Save Changes</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
