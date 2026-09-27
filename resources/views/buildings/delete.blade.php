@extends('layouts.app')

@section('title', 'Delete Building - ' . ($building->name ?? 'Building'))

@section('breadcrumbs')
    <li><a href="{{ route('buildings.index') }}">Buildings</a></li>
    <li><a href="{{ route('buildings.show', $building->id ?? 0) }}">{{ $building->name ?? 'Building' }}</a></li>
    <li class="text-error">Delete</li>
@endsection

@section('back', route('buildings.show', $building->id ?? 0))

@section('content')
<div class="space-y-6 max-w-4xl mx-auto">
    <!-- Warning Header -->
    <div class="alert alert-error shadow-lg">
        <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path>
        </svg>
        <div>
            <h3 class="font-bold text-lg">Danger Zone - Permanent Deletion</h3>
            <p class="text-sm">This action cannot be undone and will permanently delete all associated data.</p>
        </div>
    </div>

    <!-- Building Information Card -->
    <div class="card bg-base-100 shadow-xl border-2 border-error/30">
        <div class="card-body">
            <div class="flex items-start gap-6">
                <!-- Building Image -->
                <div class="flex-shrink-0">
                    <div class="w-24 h-24 rounded-xl bg-gradient-to-br from-error/20 to-error/10 flex items-center justify-center border-2 border-error/30">
                        @if($building->image ?? null)
                            <img src="{{ asset('storage/' . $building->image) }}" alt="{{ $building->name ?? 'Building' }}" class="w-full h-full object-cover rounded-xl opacity-70" />
                        @else
                            <svg class="w-12 h-12 text-error/50" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"></path>
                            </svg>
                        @endif
                    </div>
                </div>

                <!-- Building Details -->
                <div class="flex-1">
                    <h2 class="text-2xl font-bold mb-2">{{ $building->name ?? 'Unnamed Building' }}</h2>
                    <div class="grid grid-cols-2 gap-4 text-sm">
                        <div>
                            <span class="opacity-60">Code:</span>
                            <span class="font-mono font-semibold ml-2">{{ $building->code ?? 'N/A' }}</span>
                        </div>
                        <div>
                            <span class="opacity-60">Type:</span>
                            <span class="ml-2">{{ $building->type ?? 'N/A' }}</span>
                        </div>
                        <div>
                            <span class="opacity-60">Status:</span>
                            <span class="badge badge-sm ml-2">{{ $building->status ?? 'Unknown' }}</span>
                        </div>
                        <div>
                            <span class="opacity-60">Location:</span>
                            <span class="ml-2">{{ $building->city ?? 'N/A' }}, {{ $building->state ?? 'N/A' }}</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Impact Analysis -->
    <div class="card bg-base-100 shadow-xl border border-base-300/50">
        <div class="card-body">
            <h3 class="card-title text-xl flex items-center gap-2">
                <svg class="w-6 h-6 text-warning" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path>
                </svg>
                Deletion Impact Analysis
            </h3>
            <div class="divider my-2"></div>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <!-- Floors Impact -->
                <div class="stat bg-warning/10 rounded-lg border border-warning/30">
                    <div class="stat-figure text-warning">
                        <svg class="w-10 h-10" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"></path>
                        </svg>
                    </div>
                    <div class="stat-title">Floors to Delete</div>
                    <div class="stat-value text-warning">{{ $building->floors?->count() ?? 0 }}</div>
                    <div class="stat-desc">All floor data will be lost</div>
                </div>

                <!-- Machines Impact -->
                <div class="stat bg-error/10 rounded-lg border border-error/30">
                    <div class="stat-figure text-error">
                        <svg class="w-10 h-10" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.75 17L9 20l-1 1h8l-1-1-.75-3M3 13h18M5 17h14a2 2 0 002-2V5a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"></path>
                        </svg>
                    </div>
                    <div class="stat-title">Machines to Delete</div>
                    <div class="stat-value text-error">{{ $building->machines?->count() ?? 0 }}</div>
                    <div class="stat-desc">Including all machine records</div>
                </div>

                <!-- Data Impact -->
                <div class="stat bg-info/10 rounded-lg border border-info/30">
                    <div class="stat-figure text-info">
                        <svg class="w-10 h-10" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 7v10c0 2.21 3.582 4 8 4s8-1.79 8-4V7M4 7c0 2.21 3.582 4 8 4s8-1.79 8-4M4 7c0-2.21 3.582-4 8-4s8 1.79 8 4m0 5c0 2.21-3.582 4-8 4s-8-1.79-8-4"></path>
                        </svg>
                    </div>
                    <div class="stat-title">Total Data Loss</div>
                    <div class="stat-value text-info">{{ ($building->floors?->count() ?? 0) + ($building->machines?->count() ?? 0) + 1 }}</div>
                    <div class="stat-desc">Records permanently removed</div>
                </div>
            </div>
        </div>
    </div>

    <!-- Related Data Preview -->
    @if(($building->floors?->count() ?? 0) > 0 || ($building->machines?->count() ?? 0) > 0)
    <div class="card bg-base-100 shadow-xl border border-base-300/50">
        <div class="card-body">
            <h3 class="card-title text-xl">Related Data to be Deleted</h3>
            <div class="divider my-2"></div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <!-- Floors List -->
                @if(($building->floors?->count() ?? 0) > 0)
                <div>
                    <h4 class="font-semibold mb-3 flex items-center gap-2">
                        <svg class="w-5 h-5 text-warning" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"></path>
                        </svg>
                        Floors ({{ $building->floors->count() }})
                    </h4>
                    <div class="space-y-2 max-h-48 overflow-y-auto">
                        @foreach($building->floors as $floor)
                        <div class="flex items-center gap-2 p-2 bg-base-200/50 rounded">
                            <svg class="w-4 h-4 text-error" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                            </svg>
                            <span class="text-sm">{{ $floor->name ?? 'Floor ' . $loop->iteration }}</span>
                        </div>
                        @endforeach
                    </div>
                </div>
                @endif

                <!-- Machines List -->
                @if(($building->machines?->count() ?? 0) > 0)
                <div>
                    <h4 class="font-semibold mb-3 flex items-center gap-2">
                        <svg class="w-5 h-5 text-error" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.75 17L9 20l-1 1h8l-1-1-.75-3M3 13h18M5 17h14a2 2 0 002-2V5a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"></path>
                        </svg>
                        Machines ({{ $building->machines->count() }})
                    </h4>
                    <div class="space-y-2 max-h-48 overflow-y-auto">
                        @foreach($building->machines->take(10) as $machine)
                        <div class="flex items-center gap-2 p-2 bg-base-200/50 rounded">
                            <svg class="w-4 h-4 text-error" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                            </svg>
                            <span class="text-sm">{{ $machine->name ?? 'Machine ' . $loop->iteration }}</span>
                        </div>
                        @endforeach
                        @if($building->machines->count() > 10)
                        <div class="text-xs opacity-70 pl-6">
                            ... and {{ $building->machines->count() - 10 }} more machines
                        </div>
                        @endif
                    </div>
                </div>
                @endif
            </div>
        </div>
    </div>
    @endif

    <!-- Confirmation Form -->
    <div class="card bg-base-100 shadow-xl border-2 border-error/50">
        <div class="card-body">
            <h3 class="card-title text-xl text-error flex items-center gap-2">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"></path>
                </svg>
                Confirm Deletion
            </h3>
            <div class="divider my-2"></div>

            <form action="{{ route('buildings.destroy', $building->id ?? 0) }}" method="POST" id="deleteForm" class="space-y-6">
                @csrf
                @method('DELETE')

                <!-- Confirmation Instructions -->
                <div class="alert alert-warning">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                    <div>
                        <h4 class="font-bold">Type the building name to confirm</h4>
                        <p class="text-sm mt-1">
                            Please type <span class="font-mono font-bold bg-error text-error-content px-2 py-1 rounded">{{ $building->name ?? 'N/A' }}</span> to proceed with deletion.
                        </p>
                    </div>
                </div>

                <!-- Confirmation Input -->
                <x-form.input name="confirmation_name" id="confirmationInput" label="Building Name Confirmation" required
                              placeholder="Type the building name exactly as shown above" autocomplete="off"
                              help="This field is case-sensitive and must match exactly" />

                <!-- Checkboxes for Acknowledgment -->
                <div class="space-y-3">
                    <x-form.checkbox name="acknowledge1" id="acknowledge1" required class="checkbox-error"
                                      label="I understand that this action cannot be undone" />
                    <x-form.checkbox name="acknowledge2" id="acknowledge2" required class="checkbox-error"
                                      :label="'I acknowledge that all floors (' . ($building->floors?->count() ?? 0) . ') will be permanently deleted'" />
                    <x-form.checkbox name="acknowledge3" id="acknowledge3" required class="checkbox-error"
                                      :label="'I acknowledge that all machines (' . ($building->machines?->count() ?? 0) . ') will be permanently deleted'" />
                </div>

                <!-- Action Buttons -->
                <div class="flex flex-col sm:flex-row gap-3 justify-end pt-4 border-t border-base-300">
                    <a href="{{ route('buildings.show', $building->id ?? 0) }}" class="btn btn-ghost gap-2">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
                        </svg>
                        Cancel & Go Back
                    </a>
                    <button type="submit" class="btn btn-error gap-2">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                        </svg>
                        Permanently Delete Building
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
