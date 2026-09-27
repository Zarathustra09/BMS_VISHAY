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
                    <div class="form-control md:col-span-2">
                        <label class="label"><span class="label-text font-semibold">Title <span class="text-error">*</span></span></label>
                        <input type="text" name="title" value="{{ old('title') }}"
                               placeholder="e.g., Replace chiller compressor bearing"
                               class="input input-bordered @error('title') input-error @enderror" required maxlength="255"/>
                        @error('title')
                            <label class="label"><span class="label-text-alt text-error">{{ $message }}</span></label>
                        @enderror
                    </div>

                    <div class="form-control md:col-span-2">
                        <label class="label"><span class="label-text font-semibold">Description</span></label>
                        <textarea name="description" rows="3" class="textarea textarea-bordered @error('description') textarea-error @enderror"
                                  placeholder="What needs to be done?">{{ old('description') }}</textarea>
                        @error('description')
                            <label class="label"><span class="label-text-alt text-error">{{ $message }}</span></label>
                        @enderror
                    </div>

                    <div class="form-control">
                        <label class="label"><span class="label-text font-semibold">Machine <span class="text-error">*</span></span></label>
                        <select name="machine_id" class="select select-bordered @error('machine_id') select-error @enderror" required>
                            <option value="" disabled {{ old('machine_id', $machineId) ? '' : 'selected' }}>Select machine</option>
                            @foreach($machines as $machine)
                                <option value="{{ $machine->id }}" {{ (int) old('machine_id', $machineId) === $machine->id ? 'selected' : '' }}>{{ $machine->name }}</option>
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
                                <option value="{{ $val }}" {{ old('type', $type) === $val ? 'selected' : '' }}>{{ $label }}</option>
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
                                <option value="{{ $val }}" {{ old('priority', 'medium') === $val ? 'selected' : '' }}>{{ $label }}</option>
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
                                <option value="{{ $assignee->id }}" {{ (int) old('assigned_to') === $assignee->id ? 'selected' : '' }}>{{ $assignee->name }}</option>
                            @endforeach
                        </select>
                        @error('assigned_to')
                            <label class="label"><span class="label-text-alt text-error">{{ $message }}</span></label>
                        @enderror
                    </div>

                    <div class="form-control">
                        <label class="label"><span class="label-text font-semibold">Due Date</span></label>
                        <input type="date" name="due_date" value="{{ old('due_date') }}"
                               class="input input-bordered @error('due_date') input-error @enderror"/>
                        @error('due_date')
                            <label class="label"><span class="label-text-alt text-error">{{ $message }}</span></label>
                        @enderror
                    </div>
                </div>

                <div class="flex justify-end gap-3 pt-4 border-t border-base-300">
                    <a href="{{ route('work-orders.index') }}" class="btn btn-ghost">Cancel</a>
                    <button type="submit" class="btn btn-primary">Create Work Order</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
