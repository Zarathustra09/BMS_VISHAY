@extends('layouts.app')

@section('title', 'Machines')

@section('breadcrumbs')
    <li>Machines</li>
@endsection

@section('content')
<div class="space-y-6">
    <!-- Page Header -->
    <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
        <div>
            <h1 class="text-3xl font-bold flex items-center gap-3">
                <div class="w-12 h-12 rounded-xl bg-gradient-to-br from-primary to-secondary flex items-center justify-center shadow-lg">
                    <svg class="w-7 h-7 text-base-100" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.663 17h4.673M12 3v1m6.364 1.636l-.707.707M21 12h-1M4 12H3m3.343-5.657l-.707-.707m2.828 9.9a5 5 0 117.072 0l-.548.547A3.374 3.374 0 0014 18.469V19a2 2 0 11-4 0v-.531c0-.895-.356-1.754-.988-2.386l-.548-.547z"></path>
                    </svg>
                </div>
                Machine Management
            </h1>
            <p class="text-sm opacity-70 mt-2">Monitor and manage all manufacturing equipment</p>
        </div>
        <button class="btn btn-primary gap-2 shadow-lg hover:shadow-primary/50">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
            </svg>
            Add Machine
        </button>
    </div>

    <!-- Stats Overview -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
        <div class="stat bg-base-100 rounded-box shadow-lg border border-base-300/50 hover:shadow-xl transition-all">
            <div class="stat-figure text-success">
                <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                </svg>
            </div>
            <div class="stat-title">Active Machines</div>
            <div class="stat-value text-success">{{ $stats['active'] }}</div>
            <div class="stat-desc">{{ number_format(($stats['active'] / max($stats['total'], 1)) * 100, 1) }}% of total</div>
        </div>

        <div class="stat bg-base-100 rounded-box shadow-lg border border-base-300/50 hover:shadow-xl transition-all">
            <div class="stat-figure text-warning">
                <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                </svg>
            </div>
            <div class="stat-title">In Maintenance</div>
            <div class="stat-value text-warning">{{ $stats['maintenance'] }}</div>
            <div class="stat-desc">Scheduled repairs</div>
        </div>

        <div class="stat bg-base-100 rounded-box shadow-lg border border-base-300/50 hover:shadow-xl transition-all">
            <div class="stat-figure text-error">
                <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path>
                </svg>
            </div>
            <div class="stat-title">Faults</div>
            <div class="stat-value text-error">{{ $stats['fault'] }}</div>
            <div class="stat-desc">Require attention</div>
        </div>

        <div class="stat bg-base-100 rounded-box shadow-lg border border-base-300/50 hover:shadow-xl transition-all">
            <div class="stat-figure text-info">
                <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0121 12a11.955 11.955 0 01-1.382 5.984M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0121 12c0 2.485-.59 4.843-1.618 6.984M15.75 12a3.75 3.75 0 11-7.5 0 3.75 3.75 0 017.5 0z"></path>
                </svg>
            </div>
            <div class="stat-title">Critical Systems</div>
            <div class="stat-value text-info">{{ $stats['critical'] }}</div>
            <div class="stat-desc">High priority</div>
        </div>
    </div>

    <!-- Filters and Actions -->
    <div class="card bg-base-100 shadow-lg border border-base-300/50">
        <div class="card-body">
            <form method="GET" action="{{ route('machines.index') }}" class="flex flex-col lg:flex-row gap-4">
                <!-- Search -->
                <div class="form-control flex-1">
                    <label class="input input-bordered flex items-center gap-2">
                        <svg class="w-4 h-4 opacity-70" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                        </svg>
                        <input type="text" name="search" class="grow" placeholder="Search machines..." value="{{ request('search') }}" />
                    </label>
                </div>

                <!-- Status Filter -->
                <select name="status" class="select select-bordered w-full lg:w-48" onchange="this.form.submit()">
                    <option value="all" {{ request('status', 'all') === 'all' ? 'selected' : '' }}>All Status</option>
                    <option value="active" {{ request('status') === 'active' ? 'selected' : '' }}>Active</option>
                    <option value="maintenance" {{ request('status') === 'maintenance' ? 'selected' : '' }}>Maintenance</option>
                    <option value="fault" {{ request('status') === 'fault' ? 'selected' : '' }}>Fault</option>
                    <option value="inactive" {{ request('status') === 'inactive' ? 'selected' : '' }}>Inactive</option>
                </select>

                <!-- Type Filter -->
                <select name="type" class="select select-bordered w-full lg:w-48" onchange="this.form.submit()">
                    <option value="all" {{ request('type', 'all') === 'all' ? 'selected' : '' }}>All Types</option>
                    @foreach($types as $type)
                        <option value="{{ $type }}" {{ request('type') === $type ? 'selected' : '' }}>{{ $type }}</option>
                    @endforeach
                </select>

                <!-- Zone Filter -->
                <select name="zone" class="select select-bordered w-full lg:w-48" onchange="this.form.submit()">
                    <option value="all" {{ request('zone', 'all') === 'all' ? 'selected' : '' }}>All Zones</option>
                    @foreach($zones as $zone)
                        <option value="{{ $zone }}" {{ request('zone') === $zone ? 'selected' : '' }}>{{ $zone }}</option>
                    @endforeach
                </select>

                <button type="submit" class="btn btn-primary">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.293A1 1 0 013 6.586V4z"></path>
                    </svg>
                    Filter
                </button>
            </form>
        </div>
    </div>

    @if($machines->isEmpty())
        <div class="alert alert-info shadow-lg">
            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
            </svg>
            <span>No machines found. Try adjusting your filters or add a new machine.</span>
        </div>
    @else
        <!-- Machines Grid -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
            @foreach($machines as $machine)
                @php
                    $statusConfig = [
                        'active' => ['color' => 'success', 'icon' => 'M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z', 'emoji' => '✅'],
                        'maintenance' => ['color' => 'warning', 'icon' => 'M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z', 'emoji' => '⚙️'],
                        'fault' => ['color' => 'error', 'icon' => 'M6 18L18 6M6 6l12 12', 'emoji' => '⚠️'],
                        'inactive' => ['color' => 'base-300', 'icon' => 'M18.364 18.364A9 9 0 005.636 5.636m12.728 12.728A9 9 0 015.636 5.636m12.728 12.728L5.636 5.636', 'emoji' => '⭕'],
                    ];
                    $config = $statusConfig[$machine->status] ?? $statusConfig['inactive'];
                    $latestLog = $machine->logs->first();
                @endphp

                <div class="card bg-base-100 shadow-lg border border-base-300/50 hover:shadow-2xl transition-all group">
                    <div class="card-body">
                        <div class="flex items-start justify-between">
                            <div class="flex items-center gap-3">
                                <div class="avatar placeholder">
                                    <div class="bg-{{ $config['color'] }} text-{{ $config['color'] }}-content rounded-lg w-12">
                                        <span class="text-xl">{{ $config['emoji'] }}</span>
                                    </div>
                                </div>
                                <div>
                                    <h3 class="card-title text-lg">{{ $machine->name }}</h3>
                                    <p class="text-sm opacity-70">{{ $machine->machine_code }}</p>
                                </div>
                            </div>
                            @if($machine->is_critical)
                                <div class="badge badge-error badge-sm gap-1">
                                    <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path>
                                    </svg>
                                    Critical
                                </div>
                            @endif
                        </div>

                        <div class="divider my-2"></div>

                        <div class="space-y-2">
                            <div class="flex items-center justify-between text-sm">
                                <span class="opacity-70">Status</span>
                                <div class="badge badge-{{ $config['color'] }} gap-1">
                                    @if($machine->status === 'active')
                                        <span class="w-2 h-2 bg-{{ $config['color'] }}-content rounded-full animate-pulse"></span>
                                    @endif
                                    {{ ucfirst($machine->status) }}
                                </div>
                            </div>
                            <div class="flex items-center justify-between text-sm">
                                <span class="opacity-70">Type</span>
                                <span class="font-medium">{{ $machine->type }}</span>
                            </div>
                            <div class="flex items-center justify-between text-sm">
                                <span class="opacity-70">Location</span>
                                <span class="font-medium">{{ $machine->location }}</span>
                            </div>
                            @if($machine->zone)
                                <div class="flex items-center justify-between text-sm">
                                    <span class="opacity-70">Zone</span>
                                    <span class="font-medium">{{ $machine->zone }}</span>
                                </div>
                            @endif
                            <div class="flex items-center justify-between text-sm">
                                <span class="opacity-70">Manufacturer</span>
                                <span class="font-medium">{{ $machine->manufacturer ?? 'N/A' }}</span>
                            </div>
                            @if($machine->power_rating)
                                <div class="flex items-center justify-between text-sm">
                                    <span class="opacity-70">Power Rating</span>
                                    <span class="font-medium">{{ $machine->power_rating }} kW</span>
                                </div>
                            @endif
                        </div>

                        <div class="divider my-2"></div>

                        <!-- Latest Sensor Reading -->
                        @if($latestLog)
                            <div class="bg-base-200 rounded-lg p-3 space-y-1">
                                <div class="text-xs opacity-70 mb-2">Latest Reading ({{ $latestLog->logged_at->diffForHumans() }})</div>
                                <div class="grid grid-cols-2 gap-2 text-xs">
                                    @if($latestLog->temperature)
                                        <div class="flex justify-between">
                                            <span class="opacity-70">Temp:</span>
                                            <span class="font-medium">{{ $latestLog->temperature }}°C</span>
                                        </div>
                                    @endif
                                    @if($latestLog->power_consumption)
                                        <div class="flex justify-between">
                                            <span class="opacity-70">Power:</span>
                                            <span class="font-medium">{{ $latestLog->power_consumption }} kW</span>
                                        </div>
                                    @endif
                                    @if($latestLog->runtime_hours)
                                        <div class="flex justify-between">
                                            <span class="opacity-70">Runtime:</span>
                                            <span class="font-medium">{{ number_format($latestLog->runtime_hours) }}h</span>
                                        </div>
                                    @endif
                                    @if($latestLog->vibration_rms)
                                        <div class="flex justify-between">
                                            <span class="opacity-70">Vibration:</span>
                                            <span class="font-medium">{{ $latestLog->vibration_rms }}</span>
                                        </div>
                                    @endif
                                </div>
                            </div>
                        @endif

                        <!-- Maintenance Info -->
                        @if($machine->next_maintenance_date)
                            <div class="mt-2">
                                @php
                                    $daysUntilMaintenance = now()->diffInDays($machine->next_maintenance_date, false);
                                    $isOverdue = $daysUntilMaintenance < 0;
                                    $isDueSoon = $daysUntilMaintenance >= 0 && $daysUntilMaintenance <= 7;
                                @endphp
                                <div class="alert {{ $isOverdue ? 'alert-error' : ($isDueSoon ? 'alert-warning' : 'alert-info') }} py-2">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                    </svg>
                                    <span class="text-xs">
                                        @if($isOverdue)
                                            Maintenance overdue by {{ abs($daysUntilMaintenance) }} days
                                        @elseif($isDueSoon)
                                            Maintenance due in {{ $daysUntilMaintenance }} days
                                        @else
                                            Next maintenance: {{ $machine->next_maintenance_date->format('M d, Y') }}
                                        @endif
                                    </span>
                                </div>
                            </div>
                        @endif

                        <div class="card-actions justify-end mt-4">
                            <a href="{{ route('machines.show', $machine) }}" class="btn btn-primary btn-sm gap-2">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path>
                                </svg>
                                View Details
                            </a>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>

        <!-- Pagination -->
        <div class="flex justify-center mt-8">
            {{ $machines->links() }}
        </div>
    @endif
</div>
@endsection
