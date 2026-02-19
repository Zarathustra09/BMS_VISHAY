@extends('layouts.app')

@section('title', ($building->name ?? 'Building') . ' - Details')

@section('breadcrumbs')
    <li><a href="{{ route('buildings.index') }}">Buildings</a></li>
    <li>{{ $building->name ?? 'Building Details' }}</li>
@endsection

@section('content')
<div class="space-y-6">
    <!-- Header Section -->
    <div class="card bg-base-100 shadow-xl border border-base-300/50">
        <div class="card-body">
            <div class="flex flex-col lg:flex-row gap-6">
                <!-- Building Image/Icon -->
                <div class="flex-shrink-0">
                    <div class="w-32 h-32 rounded-2xl bg-gradient-to-br from-primary to-secondary flex items-center justify-center shadow-lg">
                        @if($building->image ?? null)
                            <img src="{{ asset('storage/' . $building->image) }}" alt="{{ $building->name ?? 'Building' }}" class="w-full h-full object-cover rounded-2xl" />
                        @else
                            <svg class="w-16 h-16 text-base-100" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"></path>
                            </svg>
                        @endif
                    </div>
                </div>

                <!-- Building Info -->
                <div class="flex-1 space-y-4">
                    <div class="flex flex-col sm:flex-row justify-between items-start gap-4">
                        <div>
                            <div class="flex items-center gap-3 mb-2">
                                <h1 class="text-3xl font-bold">{{ $building->name ?? 'Unnamed Building' }}</h1>
                                <div class="badge badge-lg badge-{{ ($building->status ?? '') === 'Operational' ? 'success' : (($building->status ?? '') === 'Under Construction' ? 'warning' : 'info') }} gap-2">
                                    @if(($building->status ?? '') === 'Operational')
                                        <span class="w-2 h-2 bg-success-content rounded-full animate-pulse"></span>
                                    @endif
                                    {{ $building->status ?? 'Unknown' }}
                                </div>
                            </div>
                            <p class="text-lg opacity-70">{{ $building->type ?? 'N/A' }}</p>
                            <p class="text-sm opacity-60 mt-1">Code: <span class="font-mono font-semibold">{{ $building->code ?? 'N/A' }}</span></p>
                        </div>

                        <!-- Action Buttons -->
                        <div class="flex gap-2">
                            <a href="{{ route('buildings.edit', $building->id ?? 0) }}" class="btn btn-primary gap-2">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
                                </svg>
                                Edit
                            </a>
                            <div class="dropdown dropdown-end">
                                <div tabindex="0" role="button" class="btn btn-ghost btn-square">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 5v.01M12 12v.01M12 19v.01M12 6a1 1 0 110-2 1 1 0 010 2zm0 7a1 1 0 110-2 1 1 0 010 2zm0 7a1 1 0 110-2 1 1 0 010 2z"></path>
                                    </svg>
                                </div>
                                <ul tabindex="0" class="dropdown-content z-[1] menu p-2 shadow-lg bg-base-100 rounded-box w-52 border border-base-300/50">
                                    <li><a>Export Report</a></li>
                                    <li><a>View Analytics</a></li>
                                    <li><a>Maintenance Schedule</a></li>
                                    <div class="divider my-0"></div>
                                    <li>
                                        <form action="{{ route('buildings.destroy', $building->id ?? 0) }}" method="POST" onsubmit="return confirm('Are you sure you want to delete this building?')">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="text-error w-full text-left">Delete Building</button>
                                        </form>
                                    </li>
                                </ul>
                            </div>
                        </div>
                    </div>

                    <!-- Quick Stats -->
                    <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                        <div class="stat bg-primary/10 rounded-lg p-4 border border-primary/20">
                            <div class="stat-title text-xs opacity-70">Total Floors</div>
                            <div class="stat-value text-2xl text-primary">{{ $building->floors?->count() ?? 0 }}</div>
                        </div>
                        <div class="stat bg-secondary/10 rounded-lg p-4 border border-secondary/20">
                            <div class="stat-title text-xs opacity-70">Machines</div>
                            <div class="stat-value text-2xl text-secondary">{{ $building->machines?->count() ?? 0 }}</div>
                        </div>
                        <div class="stat bg-accent/10 rounded-lg p-4 border border-accent/20">
                            <div class="stat-title text-xs opacity-70">Capacity</div>
                            <div class="stat-value text-2xl text-accent">{{ number_format(($building->machines?->count() ?? 0) * 100 / max(($building->floors?->count() ?? 1) * 10, 1), 0) }}%</div>
                        </div>
                        <div class="stat bg-info/10 rounded-lg p-4 border border-info/20">
                            <div class="stat-title text-xs opacity-70">Status</div>
                            <div class="stat-value text-2xl text-info">{{ $building->status === 'Operational' ? '✓' : '○' }}</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Tabs Navigation -->
    <div role="tablist" class="tabs tabs-lifted tabs-lg">
        <input type="radio" name="building_tabs" role="tab" class="tab" aria-label="Overview" checked />
        <div role="tabpanel" class="tab-content bg-base-100 border-base-300 rounded-box p-6">
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                <!-- Location Information -->
                <div class="card bg-base-100 shadow-lg border border-base-300/50">
                    <div class="card-body">
                        <h2 class="card-title text-xl flex items-center gap-2">
                            <svg class="w-5 h-5 text-primary" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"></path>
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"></path>
                            </svg>
                            Location Information
                        </h2>
                        <div class="divider my-2"></div>
                        <div class="space-y-3">
                            <div class="flex justify-between items-center p-3 bg-base-200/50 rounded-lg">
                                <span class="text-sm opacity-70">Address</span>
                                <span class="font-medium">{{ $building->address ?? 'N/A' }}</span>
                            </div>
                            <div class="flex justify-between items-center p-3 bg-base-200/50 rounded-lg">
                                <span class="text-sm opacity-70">City</span>
                                <span class="font-medium">{{ $building->city ?? 'N/A' }}</span>
                            </div>
                            <div class="flex justify-between items-center p-3 bg-base-200/50 rounded-lg">
                                <span class="text-sm opacity-70">State</span>
                                <span class="font-medium">{{ $building->state ?? 'N/A' }}</span>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Emergency Contact -->
                <div class="card bg-base-100 shadow-lg border border-base-300/50">
                    <div class="card-body">
                        <h2 class="card-title text-xl flex items-center gap-2">
                            <svg class="w-5 h-5 text-error" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"></path>
                            </svg>
                            Emergency Contact
                        </h2>
                        <div class="divider my-2"></div>
                        <div class="space-y-3">
                            <div class="flex justify-between items-center p-3 bg-base-200/50 rounded-lg">
                                <span class="text-sm opacity-70">Contact Name</span>
                                <span class="font-medium">{{ $building->emergency_contact_name ?? 'N/A' }}</span>
                            </div>
                            <div class="flex justify-between items-center p-3 bg-base-200/50 rounded-lg">
                                <span class="text-sm opacity-70">Phone Number</span>
                                <span class="font-medium font-mono">{{ $building->emergency_contact_phone ?? 'N/A' }}</span>
                            </div>
                            @if($building->emergency_contact_phone ?? null)
                            <a href="tel:{{ $building->emergency_contact_phone }}" class="btn btn-error btn-block gap-2">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"></path>
                                </svg>
                                Call Emergency Contact
                            </a>
                            @endif
                        </div>
                    </div>
                </div>

                <!-- Notes Section -->
                @if($building->notes ?? null)
                <div class="card bg-base-100 shadow-lg border border-base-300/50 lg:col-span-2">
                    <div class="card-body">
                        <h2 class="card-title text-xl flex items-center gap-2">
                            <svg class="w-5 h-5 text-warning" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
                            </svg>
                            Notes
                        </h2>
                        <div class="divider my-2"></div>
                        <div class="p-4 bg-base-200/50 rounded-lg">
                            <p class="whitespace-pre-wrap">{{ $building->notes }}</p>
                        </div>
                    </div>
                </div>
                @endif
            </div>
        </div>

        <input type="radio" name="building_tabs" role="tab" class="tab" aria-label="Floors" />
        <div role="tabpanel" class="tab-content bg-base-100 border-base-300 rounded-box p-6">
            <div class="space-y-4">
                <div class="flex justify-between items-center">
                    <h2 class="text-2xl font-bold">Floor Management</h2>
                    <button class="btn btn-primary gap-2">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
                        </svg>
                        Add Floor
                    </button>
                </div>

                @forelse($building->floors ?? [] as $floor)
                <div class="card bg-base-100 shadow-lg border border-base-300/50 hover:shadow-xl transition-all">
                    <div class="card-body">
                        <div class="flex justify-between items-start">
                            <div>
                                <h3 class="card-title">{{ $floor->name ?? 'Floor ' . $loop->iteration }}</h3>
                                <p class="text-sm opacity-70">Level {{ $floor->level ?? $loop->iteration }}</p>
                            </div>
                            <div class="badge badge-primary">{{ $floor->machines?->count() ?? 0 }} Machines</div>
                        </div>
                        @if($floor->description ?? null)
                        <p class="text-sm opacity-70 mt-2">{{ $floor->description }}</p>
                        @endif
                        <div class="card-actions justify-end mt-4">
                            <button class="btn btn-sm btn-ghost gap-2">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path>
                                </svg>
                                View Details
                            </button>
                        </div>
                    </div>
                </div>
                @empty
                <div class="text-center py-12">
                    <svg class="w-24 h-24 mx-auto opacity-20" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"></path>
                    </svg>
                    <p class="text-lg opacity-70 mt-4">No floors added yet</p>
                    <button class="btn btn-primary mt-4">Add First Floor</button>
                </div>
                @endforelse
            </div>
        </div>

        <input type="radio" name="building_tabs" role="tab" class="tab" aria-label="Machines" />
        <div role="tabpanel" class="tab-content bg-base-100 border-base-300 rounded-box p-6">
            <div class="space-y-4">
                <div class="flex justify-between items-center">
                    <h2 class="text-2xl font-bold">Machine Inventory</h2>
                    <button class="btn btn-primary gap-2">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
                        </svg>
                        Add Machine
                    </button>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                    @forelse($building->machines ?? [] as $machine)
                    <div class="card bg-base-100 shadow-lg border border-base-300/50 hover:shadow-xl transition-all">
                        <div class="card-body">
                            <div class="flex items-start justify-between">
                                <div class="flex items-center gap-3">
                                    <div class="w-12 h-12 rounded-lg bg-primary/20 flex items-center justify-center">
                                        <svg class="w-6 h-6 text-primary" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.75 17L9 20l-1 1h8l-1-1-.75-3M3 13h18M5 17h14a2 2 0 002-2V5a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"></path>
                                        </svg>
                                    </div>
                                    <div>
                                        <h3 class="font-bold">{{ $machine->name ?? 'Machine ' . $loop->iteration }}</h3>
                                        <p class="text-xs opacity-70">{{ $machine->machine_type ?? 'N/A' }}</p>
                                    </div>
                                </div>
                                <div class="badge badge-sm badge-{{ ($machine->status ?? '') === 'operational' ? 'success' : (($machine->status ?? '') === 'maintenance' ? 'warning' : 'error') }}">
                                    {{ ucfirst($machine->status ?? 'unknown') }}
                                </div>
                            </div>
                            <div class="divider my-2"></div>
                            <div class="text-xs space-y-1">
                                <div class="flex justify-between">
                                    <span class="opacity-70">Serial:</span>
                                    <span class="font-mono">{{ $machine->serial_number ?? 'N/A' }}</span>
                                </div>
                                <div class="flex justify-between">
                                    <span class="opacity-70">Floor:</span>
                                    <span>{{ $machine->floor?->name ?? 'N/A' }}</span>
                                </div>
                            </div>
                            <div class="card-actions justify-end mt-2">
                                <a href="{{ route('machines.show', $machine->id ?? 0) }}" class="btn btn-sm btn-ghost gap-2">
                                    View
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
                                    </svg>
                                </a>
                            </div>
                        </div>
                    </div>
                    @empty
                    <div class="col-span-full text-center py-12">
                        <svg class="w-24 h-24 mx-auto opacity-20" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.75 17L9 20l-1 1h8l-1-1-.75-3M3 13h18M5 17h14a2 2 0 002-2V5a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"></path>
                        </svg>
                        <p class="text-lg opacity-70 mt-4">No machines installed</p>
                        <button class="btn btn-primary mt-4">Add First Machine</button>
                    </div>
                    @endforelse
                </div>
            </div>
        </div>

        <input type="radio" name="building_tabs" role="tab" class="tab" aria-label="Activity" />
        <div role="tabpanel" class="tab-content bg-base-100 border-base-300 rounded-box p-6">
            <div class="space-y-4">
                <h2 class="text-2xl font-bold">Recent Activity</h2>

                <div class="timeline timeline-vertical">
                    <div class="timeline-start timeline-box bg-primary/10 border-primary/20">
                        <div class="flex items-center gap-2">
                            <svg class="w-4 h-4 text-primary" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
                            </svg>
                            <span class="font-semibold">Building Created</span>
                        </div>
                        <p class="text-xs opacity-70 mt-1">{{ $building->created_at?->format('M d, Y h:i A') ?? 'N/A' }}</p>
                    </div>
                    <div class="timeline-middle">
                        <svg class="w-5 h-5 text-primary" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path>
                        </svg>
                    </div>
                    <div class="timeline-end"></div>
                    <hr class="bg-primary" />

                    @if($building->updated_at?->ne($building->created_at))
                    <div class="timeline-start"></div>
                    <div class="timeline-middle">
                        <svg class="w-5 h-5 text-info" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path>
                        </svg>
                    </div>
                    <div class="timeline-end timeline-box bg-info/10 border-info/20">
                        <div class="flex items-center gap-2">
                            <svg class="w-4 h-4 text-info" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
                            </svg>
                            <span class="font-semibold">Last Updated</span>
                        </div>
                        <p class="text-xs opacity-70 mt-1">{{ $building->updated_at?->format('M d, Y h:i A') ?? 'N/A' }}</p>
                    </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
