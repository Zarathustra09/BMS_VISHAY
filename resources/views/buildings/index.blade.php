@extends('layouts.app')

@section('title', 'Buildings')

@section('breadcrumbs')
    <li>Buildings</li>
@endsection

@section('content')
<div class="space-y-6">
    <!-- Success/Error Messages -->
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

    <!-- Page Header -->
    <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
        <div>
            <h1 class="text-3xl font-bold flex items-center gap-3">
                <div class="w-12 h-12 rounded-xl bg-gradient-to-br from-primary to-secondary flex items-center justify-center shadow-lg">
                    <svg class="w-7 h-7 text-base-100" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"></path>
                    </svg>
                </div>
                Building Management
            </h1>
            <p class="text-sm opacity-70 mt-2">Manage all manufacturing facilities and infrastructure</p>
        </div>
        <a href="{{ route('buildings.create') }}" class="btn btn-primary gap-2 shadow-lg hover:shadow-primary/50">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
            </svg>
            Add Building
        </a>
    </div>

    <!-- Stats Overview -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
        <div class="stat bg-base-100 rounded-box shadow-lg border border-base-300/50 hover:shadow-xl transition-all">
            <div class="stat-figure text-primary">
                <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"></path>
                </svg>
            </div>
            <div class="stat-title">Total Buildings</div>
            <div class="stat-value text-primary">{{ $totalBuildings ?? 0 }}</div>
            <div class="stat-desc">Managed facilities</div>
        </div>

        <div class="stat bg-base-100 rounded-box shadow-lg border border-base-300/50 hover:shadow-xl transition-all">
            <div class="stat-figure text-success">
                <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                </svg>
            </div>
            <div class="stat-title">Operational</div>
            <div class="stat-value text-success">{{ $operationalBuildings ?? 0 }}</div>
            <div class="stat-desc">Full capacity</div>
        </div>

        <div class="stat bg-base-100 rounded-box shadow-lg border border-base-300/50 hover:shadow-xl transition-all">
            <div class="stat-figure text-info">
                <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.75 17L9 20l-1 1h8l-1-1-.75-3M3 13h18M5 17h14a2 2 0 002-2V5a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"></path>
                </svg>
            </div>
            <div class="stat-title">Total Machines</div>
            <div class="stat-value text-info">{{ $totalOccupancy ?? 0 }}</div>
            <div class="stat-desc">Across all buildings</div>
        </div>

        <div class="stat bg-base-100 rounded-box shadow-lg border border-base-300/50 hover:shadow-xl transition-all">
            <div class="stat-figure text-warning">
                <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21a4 4 0 01-4-4V5a2 2 0 012-2h4a2 2 0 012 2v12a4 4 0 01-4 4zm0 0h12a2 2 0 002-2v-4a2 2 0 00-2-2h-2.343M11 7.343l1.657-1.657a2 2 0 012.828 0l2.829 2.829a2 2 0 010 2.828l-8.486 8.485M7 17h.01"></path>
                </svg>
            </div>
            <div class="stat-title">Total Floors</div>
            <div class="stat-value text-warning">{{ $buildings?->sum(fn($b) => $b->floors?->count() ?? 0) ?? 0 }}</div>
            <div class="stat-desc">Floor spaces</div>
        </div>
    </div>

    <!-- Filters -->
    <div class="card bg-base-100 shadow-lg border border-base-300/50">
        <div class="card-body">
            <div class="flex flex-col lg:flex-row gap-4">
                <!-- Search -->
                <div class="form-control flex-1">
                    <label class="input input-bordered flex items-center gap-2">
                        <svg class="w-4 h-4 opacity-70" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                        </svg>
                        <input type="text" class="grow" placeholder="Search buildings by name, location, or code..." />
                    </label>
                </div>

                <!-- Status Filter -->
                <select class="select select-bordered w-full lg:w-48">
                    <option selected>All Status</option>
                    <option>Operational</option>
                    <option>Under Construction</option>
                    <option>Maintenance</option>
                    <option>Inactive</option>
                </select>

                <!-- Type Filter -->
                <select class="select select-bordered w-full lg:w-48">
                    <option selected>All Types</option>
                    <option>Production Facility</option>
                    <option>Warehouse</option>
                    <option>Office Building</option>
                    <option>R&D Center</option>
                </select>
            </div>
        </div>
    </div>

    <!-- Buildings Grid -->
    <div class="grid grid-cols-1 lg:grid-cols-2 xl:grid-cols-3 gap-6">
        @forelse($buildings ?? [] as $building)
        <div class="card bg-base-100 shadow-lg border border-base-300/50 hover:shadow-2xl transition-all group">
            <figure class="h-48 bg-gradient-to-br from-{{ ($building->status ?? '') === 'Operational' ? 'primary' : (($building->status ?? '') === 'Under Construction' ? 'warning' : 'secondary') }}/20 to-secondary/20 relative overflow-hidden">
                @if($building->image ?? null)
                    <img src="{{ asset('storage/' . $building->image) }}" alt="{{ $building->name ?? 'Building' }}" class="w-full h-full object-cover" />
                    <div class="absolute inset-0 bg-gradient-to-t from-base-100/80 to-transparent"></div>
                @else
                    <div class="absolute inset-0 flex items-center justify-center">
                        <svg class="w-24 h-24 opacity-20" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"></path>
                        </svg>
                    </div>
                @endif
                <div class="badge badge-{{ ($building->status ?? '') === 'Operational' ? 'success' : (($building->status ?? '') === 'Under Construction' ? 'warning' : 'info') }} absolute top-4 right-4 gap-1 shadow-lg">
                    @if(($building->status ?? '') === 'Operational')
                        <span class="w-2 h-2 bg-success-content rounded-full animate-pulse"></span>
                    @endif
                    {{ $building->status ?? 'Unknown' }}
                </div>
            </figure>
            <div class="card-body">
                <div class="flex items-start justify-between">
                    <div>
                        <h2 class="card-title text-xl">{{ $building->name ?? 'Unnamed Building' }}</h2>
                        <p class="text-sm opacity-70">{{ $building->type ?? 'N/A' }}</p>
                    </div>
                    <div class="dropdown dropdown-end">
                        <div tabindex="0" role="button" class="btn btn-ghost btn-sm btn-circle">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 5v.01M12 12v.01M12 19v.01M12 6a1 1 0 110-2 1 1 0 010 2zm0 7a1 1 0 110-2 1 1 0 010 2zm0 7a1 1 0 110-2 1 1 0 010 2z"></path>
                            </svg>
                        </div>
                        <ul tabindex="0" class="dropdown-content z-[1] menu p-2 shadow-lg bg-base-100 rounded-box w-52 border border-base-300/50">
                            <li><a href="{{ route('buildings.show', $building->id ?? 0) }}">View Details</a></li>
                            <li><a href="{{ route('buildings.edit', $building->id ?? 0) }}">Edit Info</a></li>
                            <li><a>View Floor Plans</a></li>
                            <div class="divider my-0"></div>
                            <li>
                                <form action="{{ route('buildings.destroy', $building->id ?? 0) }}" method="POST" onsubmit="return confirm('Are you sure?')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="text-error w-full text-left">Delete</button>
                                </form>
                            </li>
                        </ul>
                    </div>
                </div>

                <div class="divider my-2"></div>

                <div class="space-y-3">
                    <div class="flex items-center gap-2 text-sm">
                        <svg class="w-4 h-4 opacity-70" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"></path>
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"></path>
                        </svg>
                        <span class="opacity-70">Location:</span>
                        <span class="font-medium">{{ $building->city ?? 'N/A' }}</span>
                    </div>
                    <div class="flex items-center gap-2 text-sm">
                        <svg class="w-4 h-4 opacity-70" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z"></path>
                        </svg>
                        <span class="opacity-70">Code:</span>
                        <span class="font-medium">{{ $building->code ?? 'N/A' }}</span>
                    </div>
                    <div class="flex items-center gap-2 text-sm">
                        <svg class="w-4 h-4 opacity-70" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"></path>
                        </svg>
                        <span class="opacity-70">Floors:</span>
                        @php
                            $floorCount = $building->floors?->count() ?? 0;
                        @endphp
                        <span class="font-medium">{{ $floorCount }} Floor{{ $floorCount !== 1 ? 's' : '' }}</span>
                    </div>
                </div>

                <div class="divider my-2"></div>

                <div class="grid grid-cols-2 gap-2">
                    <div class="stat bg-primary/10 rounded-lg p-3 border border-primary/20">
                        <div class="stat-value text-lg text-primary">{{ $building->machines?->count() ?? 0 }}</div>
                        <div class="stat-desc text-xs">Machines</div>
                    </div>
                    <div class="stat bg-info/10 rounded-lg p-3 border border-info/20">
                        <div class="stat-value text-lg text-info">{{ $building->floors?->count() ?? 0 }}</div>
                        <div class="stat-desc text-xs">Floors</div>
                    </div>
                </div>

                <div class="card-actions justify-end mt-4">
                    <a href="{{ route('buildings.show', $building->id ?? 0) }}" class="btn btn-primary btn-sm gap-2">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                        View Details
                    </a>
                </div>
            </div>
        </div>
        @empty
        <div class="col-span-full text-center py-12">
            <svg class="w-24 h-24 mx-auto opacity-20" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"></path>
            </svg>
            <p class="text-lg opacity-70 mt-4">No buildings found</p>
            <a href="{{ route('buildings.create') }}" class="btn btn-primary mt-4">Add Your First Building</a>
        </div>
        @endforelse
    </div>
</div>
@endsection
