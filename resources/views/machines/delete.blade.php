@extends('layouts.app')

@section('title', 'Delete Machine — ' . $machine->name)

@section('breadcrumbs')
    <li><a href="{{ route('machines.index') }}">Machines</a></li>
    <li><a href="{{ route('machines.show', $machine) }}">{{ $machine->name }}</a></li>
    <li>Delete</li>
@endsection

@section('back', route('machines.show', $machine))

@section('content')
<div class="max-w-2xl mx-auto space-y-6">

    <!-- Header -->
    <div class="flex items-center gap-4">
        <div>
            <h1 class="text-3xl font-bold text-error">Delete Machine</h1>
            <p class="text-sm opacity-70 mt-1">This action cannot be undone</p>
        </div>
    </div>

    <!-- Warning Banner -->
    <div class="alert alert-error shadow-lg">
        <svg class="w-6 h-6 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                  d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
        </svg>
        <div>
            <p class="font-bold text-base">You are about to permanently delete this machine</p>
            <p class="text-sm opacity-90 mt-0.5">
                All associated logs and records linked to
                <span class="font-semibold">{{ $machine->name }}</span> will also be removed.
                This cannot be reversed.
            </p>
        </div>
    </div>

    <!-- Machine Summary Card -->
    <div class="card bg-base-100 shadow-xl border border-error/30">
        <div class="card-body space-y-5">

            <!-- Machine Identity -->
            <div class="flex items-start gap-4">
                <div class="w-14 h-14 rounded-2xl bg-error/10 border border-error/20 flex items-center justify-center shrink-0">
                    <x-machine-type-icon :icon="$machine->machineType?->icon" class="w-7 h-7 text-error" />
                </div>
                <div class="flex-1 min-w-0">
                    <div class="flex items-center gap-2 flex-wrap">
                        <h2 class="text-xl font-bold truncate">{{ $machine->name }}</h2>
                        @if($machine->is_critical)
                            <span class="badge badge-error badge-sm gap-1">
                                <svg class="w-3 h-3" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/>
                                </svg>
                                Critical
                            </span>
                        @endif
                    </div>
                    <div class="flex items-center gap-2 mt-1 flex-wrap">
                        <code class="text-xs bg-base-200 px-2 py-0.5 rounded font-mono">{{ $machine->machine_code }}</code>
                        <span class="text-sm opacity-60">{{ $machine->machineType?->name ?? 'Unknown Type' }}</span>
                        @php $statusEnum = \App\Enums\MachineStatus::tryFrom($machine->status); @endphp
                        <span class="badge {{ $statusEnum?->badgeClass() ?? 'badge-ghost' }} badge-sm">{{ $statusEnum?->label() ?? $machine->status }}</span>
                    </div>
                </div>
            </div>

            <div class="divider my-0"></div>

            <!-- Details Grid -->
            <div class="grid grid-cols-2 gap-3 text-sm">

                @if($machine->manufacturer)
                <div class="flex flex-col gap-0.5">
                    <span class="text-xs font-semibold uppercase tracking-wide opacity-50">Manufacturer</span>
                    <span class="font-medium">{{ $machine->manufacturer }}</span>
                </div>
                @endif

                @if($machine->model_number)
                <div class="flex flex-col gap-0.5">
                    <span class="text-xs font-semibold uppercase tracking-wide opacity-50">Model</span>
                    <span class="font-medium">{{ $machine->model_number }}</span>
                </div>
                @endif

                @module('buildings')
                @if($machine->building)
                <div class="flex flex-col gap-0.5">
                    <span class="text-xs font-semibold uppercase tracking-wide opacity-50">Building</span>
                    <span class="font-medium">{{ $machine->building->name }}</span>
                </div>
                @endif

                @if($machine->floor)
                <div class="flex flex-col gap-0.5">
                    <span class="text-xs font-semibold uppercase tracking-wide opacity-50">Floor</span>
                    <span class="font-medium">{{ $machine->floor->name }}</span>
                </div>
                @endif
                @endmodule

                @if($machine->installation_date)
                <div class="flex flex-col gap-0.5">
                    <span class="text-xs font-semibold uppercase tracking-wide opacity-50">Installed</span>
                    <span class="font-medium">{{ $machine->installation_date->format('M d, Y') }}</span>
                </div>
                @endif

                @if($machine->last_maintenance_date)
                <div class="flex flex-col gap-0.5">
                    <span class="text-xs font-semibold uppercase tracking-wide opacity-50">Last Maintenance</span>
                    <span class="font-medium">{{ $machine->last_maintenance_date->format('M d, Y') }}</span>
                </div>
                @endif

            </div>

            <!-- Log count warning -->
            @php $logCount = $machine->logs()->count(); @endphp
            @if($logCount > 0)
            <div class="flex items-center gap-3 p-3 rounded-xl bg-warning/10 border border-warning/30">
                <svg class="w-5 h-5 text-warning shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                          d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                </svg>
                <div>
                    <p class="text-sm font-semibold text-warning">
                        {{ number_format($logCount) }} log {{ Str::plural('entry', $logCount) }} will be deleted
                    </p>
                    <p class="text-xs opacity-70 mt-0.5">All machine logs and associated data will be permanently removed</p>
                </div>
            </div>
            @endif

        </div>
    </div>

    <!-- Confirmation Form -->
    <div class="card bg-base-100 shadow-xl border border-base-300/50" x-data="{ confirmed: false, deleting: false }">
        <div class="card-body space-y-5">

            <div>
                <p class="font-semibold text-base">Confirm deletion</p>
                <p class="text-sm opacity-60 mt-1">
                    Please acknowledge that you understand this action is permanent before proceeding.
                </p>
            </div>

            <!-- Checkbox confirmation -->
            <label class="flex items-start gap-3 p-4 rounded-xl border border-base-300 bg-base-200/50 cursor-pointer hover:bg-base-200 transition-colors"
                   :class="confirmed ? 'border-error/40 bg-error/5' : ''">
                <input type="checkbox" class="checkbox checkbox-error mt-0.5" x-model="confirmed"/>
                <div>
                    <p class="font-semibold text-sm">
                        I understand that deleting
                        <span class="text-error">{{ $machine->name }}</span>
                        is permanent and irreversible
                    </p>
                    <p class="text-xs opacity-60 mt-0.5">
                        This will remove the machine, all its logs, and all associated records from the system
                    </p>
                </div>
            </label>

            <!-- Action Buttons -->
            <div class="flex items-center justify-between pt-2 border-t border-base-300">
                <a href="{{ route('machines.show', $machine) }}" class="btn btn-ghost gap-2">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
                    </svg>
                    Cancel
                </a>

                <form action="{{ route('machines.destroy', $machine) }}" method="POST"
                      @submit.prevent="deleting = true; $el.submit()">
                    @csrf
                    @method('DELETE')
                    <button type="submit"
                            class="btn btn-error gap-2"
                            :disabled="!confirmed || deleting">
                        <span x-show="!deleting">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                      d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                            </svg>
                        </span>
                        <span x-show="deleting" class="loading loading-spinner loading-sm"></span>
                        <span x-text="deleting ? 'Deleting...' : 'Delete Machine'"></span>
                    </button>
                </form>
            </div>

        </div>
    </div>

</div>
@endsection
