@extends('layouts.app')

@section('title', $machine->name)

@section('breadcrumbs')
    <li><a href="{{ route('machines.index') }}">Machines</a></li>
    <li>{{ $machine->name }}</li>
@endsection

@section('content')
<div class="space-y-6">
    <!-- Header -->
    <div class="flex flex-col lg:flex-row justify-between items-start gap-4">
        <div class="flex items-start gap-4">
            <a href="{{ route('machines.index') }}" class="btn btn-circle btn-ghost">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
                </svg>
            </a>
            <div>
                <div class="flex items-center gap-3 mb-2">
                    <h1 class="text-3xl font-bold">{{ $machine->name }}</h1>
                    @php
                        $statusConfig = [
                            'active' => ['color' => 'success', 'text' => 'Active'],
                            'maintenance' => ['color' => 'warning', 'text' => 'Maintenance'],
                            'fault' => ['color' => 'error', 'text' => 'Fault'],
                            'inactive' => ['color' => 'base-300', 'text' => 'Inactive'],
                        ];
                        $config = $statusConfig[$machine->status] ?? $statusConfig['inactive'];
                    @endphp
                    <div class="badge badge-{{ $config['color'] }} badge-lg gap-2">
                        @if($machine->status === 'active')
                            <span class="w-2 h-2 bg-{{ $config['color'] }}-content rounded-full animate-pulse"></span>
                        @endif
                        {{ $config['text'] }}
                    </div>
                    @if($machine->is_critical)
                        <div class="badge badge-error badge-lg gap-2">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path>
                            </svg>
                            Critical System
                        </div>
                    @endif
                </div>
                <p class="text-sm opacity-70">{{ $machine->machine_code }} • {{ $machine->type }}</p>
            </div>
        </div>
        <div class="flex gap-2">
            <a href="{{ route('machines.edit', $machine) }}" class="btn btn-outline gap-2">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
                </svg>
                Edit
            </a>
            <a href="{{ route('machines.confirm-delete', $machine) }}" class="btn btn-error btn-outline gap-2">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                </svg>
                Delete
            </a>
        </div>
    </div>

    <!-- Machine Info Cards -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- Basic Info -->
        <div class="card bg-base-100 shadow-lg border border-base-300/50">
            <div class="card-body">
                <h2 class="card-title text-lg mb-4">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                    Basic Information
                </h2>
                <div class="space-y-3 text-sm">
                    <div class="flex justify-between">
                        <span class="opacity-70">Manufacturer:</span>
                        <span class="font-medium">{{ $machine->manufacturer ?? 'N/A' }}</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="opacity-70">Model:</span>
                        <span class="font-medium">{{ $machine->model_number ?? 'N/A' }}</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="opacity-70">Serial Number:</span>
                        <span class="font-medium">{{ $machine->serial_number ?? 'N/A' }}</span>
                    </div>
                    <div class="divider my-2"></div>
                    <div class="flex justify-between">
                        <span class="opacity-70">Building:</span>
                        <span class="font-medium">{{ $machine->building?->name ?? 'N/A' }}</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="opacity-70">Floor:</span>
                        <span class="font-medium">{{ $machine->floor?->name ?? 'N/A' }}</span>
                    </div>
                    @if($machine->ip_address)
                        <div class="flex justify-between">
                            <span class="opacity-70">IP Address:</span>
                            <span class="font-medium font-mono text-xs">{{ $machine->ip_address }}</span>
                        </div>
                    @endif
                    @if($machine->description)
                        <div class="pt-2">
                            <span class="opacity-70 block mb-1">Description:</span>
                            <p class="text-sm">{{ $machine->description }}</p>
                        </div>
                    @endif
                </div>
            </div>
        </div>

        <!-- Specifications -->
        <div class="card bg-base-100 shadow-lg border border-base-300/50">
            <div class="card-body">
                <h2 class="card-title text-lg mb-4">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 3v2m6-2v2M9 19v2m6-2v2M5 9H3m2 6H3m18-6h-2m2 6h-2M7 19h10a2 2 0 002-2V7a2 2 0 00-2-2H7a2 2 0 00-2 2v10a2 2 0 002 2zM9 9h6v6H9V9z"></path>
                    </svg>
                    Specifications
                </h2>
                <div class="space-y-3 text-sm">
                    @if($machine->power_rating)
                        <div class="flex justify-between">
                            <span class="opacity-70">Power Rating:</span>
                            <span class="font-medium">{{ $machine->power_rating }} kW</span>
                        </div>
                    @endif
                    @if($machine->spec_voltage)
                        <div class="flex justify-between">
                            <span class="opacity-70">Voltage:</span>
                            <span class="font-medium">{{ $machine->spec_voltage }} V</span>
                        </div>
                    @endif
                    @if($machine->capacity_tons)
                        <div class="flex justify-between">
                            <span class="opacity-70">Capacity:</span>
                            <span class="font-medium">{{ $machine->capacity_tons }} tons</span>
                        </div>
                    @endif
                    @if($machine->flow_lpm)
                        <div class="flex justify-between">
                            <span class="opacity-70">Flow Rate:</span>
                            <span class="font-medium">{{ $machine->flow_lpm }} L/min</span>
                        </div>
                    @endif
                    @if($machine->fuel)
                        <div class="flex justify-between">
                            <span class="opacity-70">Fuel Type:</span>
                            <span class="font-medium">{{ ucfirst(str_replace('_', ' ', $machine->fuel)) }}</span>
                        </div>
                    @endif
                    @if($machine->capacity_persons)
                        <div class="flex justify-between">
                            <span class="opacity-70">Capacity:</span>
                            <span class="font-medium">{{ $machine->capacity_persons }} persons</span>
                        </div>
                    @endif
                    @if($machine->speed_m_s)
                        <div class="flex justify-between">
                            <span class="opacity-70">Speed:</span>
                            <span class="font-medium">{{ $machine->speed_m_s }} m/s</span>
                        </div>
                    @endif
                    @if($machine->head_m)
                        <div class="flex justify-between">
                            <span class="opacity-70">Head:</span>
                            <span class="font-medium">{{ $machine->head_m }} m</span>
                        </div>
                    @endif
                    @if($machine->cells)
                        <div class="flex justify-between">
                            <span class="opacity-70">Cells:</span>
                            <span class="font-medium">{{ $machine->cells }}</span>
                        </div>
                    @endif
                    <div class="divider my-2"></div>
                    <div class="flex justify-between">
                        <span class="opacity-70">Autostart:</span>
                        <span class="font-medium">{{ $machine->autostart ? 'Yes' : 'No' }}</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="opacity-70">Makeup Valve:</span>
                        <span class="font-medium">{{ $machine->makeup_valve ? 'Yes' : 'No' }}</span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Maintenance Info -->
        <div class="card bg-base-100 shadow-lg border border-base-300/50">
            <div class="card-body">
                <h2 class="card-title text-lg mb-4">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                    Maintenance Schedule
                </h2>
                <div class="space-y-3 text-sm">
                    @if($machine->installation_date)
                        <div class="flex justify-between">
                            <span class="opacity-70">Installed:</span>
                            <span class="font-medium">{{ $machine->installation_date->format('M d, Y') }}</span>
                        </div>
                        <div class="flex justify-between">
                            <span class="opacity-70">Age:</span>
                            <span class="font-medium">{{ $machine->installation_date->diffForHumans(null, true) }}</span>
                        </div>
                    @endif
                    <div class="divider my-2"></div>
                    @if($machine->last_maintenance_date)
                        <div class="flex justify-between">
                            <span class="opacity-70">Last Maintenance:</span>
                            <span class="font-medium">{{ $machine->last_maintenance_date->format('M d, Y') }}</span>
                        </div>
                    @endif
                    @if($machine->next_maintenance_date)
                        <div class="flex justify-between">
                            <span class="opacity-70">Next Maintenance:</span>
                            <span class="font-medium">{{ $machine->next_maintenance_date->format('M d, Y') }}</span>
                        </div>
                        @php
                            $daysUntilMaintenance = now()->diffInDays($machine->next_maintenance_date, false);
                            $isOverdue = $daysUntilMaintenance < 0;
                        @endphp
                        <div class="alert {{ $isOverdue ? 'alert-error' : 'alert-info' }} py-2 mt-2">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                            </svg>
                            <span class="text-xs">
                                @if($isOverdue)
                                    Overdue by {{ abs($daysUntilMaintenance) }} days
                                @else
                                    Due in {{ $daysUntilMaintenance }} days
                                @endif
                            </span>
                        </div>
                    @endif
                    @if($machine->maintenance_interval_days)
                        <div class="flex justify-between">
                            <span class="opacity-70">Interval:</span>
                            <span class="font-medium">Every {{ $machine->maintenance_interval_days }} days</span>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <!-- Log Statistics -->
    <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-6 gap-4">
        <div class="stat bg-base-100 rounded-box shadow-lg border border-base-300/50">
            <div class="stat-title text-xs">Total Logs</div>
            <div class="stat-value text-2xl">{{ number_format($logStats['total']) }}</div>
        </div>
        <div class="stat bg-base-100 rounded-box shadow-lg border border-base-300/50">
            <div class="stat-title text-xs">Today</div>
            <div class="stat-value text-2xl text-info">{{ $logStats['today'] }}</div>
        </div>
        <div class="stat bg-base-100 rounded-box shadow-lg border border-base-300/50">
            <div class="stat-title text-xs">This Week</div>
            <div class="stat-value text-2xl text-primary">{{ $logStats['week'] }}</div>
        </div>
        <div class="stat bg-base-100 rounded-box shadow-lg border border-base-300/50">
            <div class="stat-title text-xs">Critical</div>
            <div class="stat-value text-2xl text-error">{{ $logStats['critical'] }}</div>
        </div>
        <div class="stat bg-base-100 rounded-box shadow-lg border border-base-300/50">
            <div class="stat-title text-xs">Errors</div>
            <div class="stat-value text-2xl text-warning">{{ $logStats['errors'] }}</div>
        </div>
        <div class="stat bg-base-100 rounded-box shadow-lg border border-base-300/50">
            <div class="stat-title text-xs">Warnings</div>
            <div class="stat-value text-2xl text-warning">{{ $logStats['warnings'] }}</div>
        </div>
    </div>

    <!-- Recent Alerts -->
    @if($recentAlerts->isNotEmpty())
        <div class="card bg-base-100 shadow-lg border border-base-300/50">
            <div class="card-body">
                <h2 class="card-title">
                    <svg class="w-5 h-5 text-error" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path>
                    </svg>
                    Recent Alerts
                </h2>
                <div class="space-y-2">
                    @foreach($recentAlerts as $alert)
                        <div class="alert alert-{{ $alert->severity === 'critical' ? 'error' : 'warning' }}">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path>
                            </svg>
                            <div class="flex-1">
                                <div class="font-medium">{{ $alert->event }}</div>
                                <div class="text-xs opacity-70">{{ $alert->logged_at->format('M d, Y H:i:s') }} ({{ $alert->logged_at->diffForHumans() }})</div>
                            </div>
                            <div class="badge badge-{{ $alert->severity === 'critical' ? 'error' : 'warning' }}">{{ ucfirst($alert->severity) }}</div>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>
    @endif

    <!-- Machine Logs -->
    <div class="card bg-base-100 shadow-lg border border-base-300/50">
        <div class="card-body">
            <h2 class="card-title mb-4">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                </svg>
                Machine Logs
            </h2>

            @if($logs->isEmpty())
                <div class="alert alert-info">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                    <span>No logs available for this machine.</span>
                </div>
            @else
                <div class="overflow-x-auto">
                    <table class="table table-zebra">
                        <thead>
                            <tr>
                                <th>Timestamp</th>
                                <th>Type</th>
                                <th>Event</th>
                                <th>Severity</th>
                                <th>Sensors</th>
                                <th>Performed By</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($logs as $log)
                                @php
                                    $severityConfig = [
                                        'info' => ['color' => 'info', 'icon' => 'M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z'],
                                        'warning' => ['color' => 'warning', 'icon' => 'M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z'],
                                        'error' => ['color' => 'error', 'icon' => 'M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z'],
                                        'critical' => ['color' => 'error', 'icon' => 'M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z'],
                                    ];
                                    $sevConfig = $severityConfig[$log->severity] ?? $severityConfig['info'];
                                @endphp
                                <tr class="hover">
                                    <td>
                                        <div class="text-sm">{{ $log->logged_at->format('M d, Y') }}</div>
                                        <div class="text-xs opacity-70">{{ $log->logged_at->format('H:i:s') }}</div>
                                    </td>
                                    <td>
                                        <div class="badge badge-outline badge-sm">{{ str_replace('_', ' ', ucfirst($log->log_type)) }}</div>
                                    </td>
                                    <td>
                                        <div class="text-sm">{{ $log->event }}</div>
                                        @if($log->notes)
                                            <div class="text-xs opacity-70 truncate max-w-xs">{{ $log->notes }}</div>
                                        @endif
                                    </td>
                                    <td>
                                        <div class="badge badge-{{ $sevConfig['color'] }} gap-2">
                                            <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="{{ $sevConfig['icon'] }}"></path>
                                            </svg>
                                            {{ ucfirst($log->severity) }}
                                        </div>
                                    </td>
                                    <td>
                                        <div class="text-xs space-y-1">
                                            @if($log->temperature)
                                                <div>🌡️ {{ $log->temperature }}°C</div>
                                            @endif
                                            @if($log->power_consumption)
                                                <div>⚡ {{ $log->power_consumption }} kW</div>
                                            @endif
                                            @if($log->vibration_rms)
                                                <div>📊 {{ $log->vibration_rms }} RMS</div>
                                            @endif
                                            @if($log->pressure)
                                                <div>💨 {{ $log->pressure }} bar</div>
                                            @endif
                                        </div>
                                    </td>
                                    <td>
                                        <div class="text-sm">{{ $log->performed_by ?? 'System' }}</div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                <!-- Pagination -->
                <div class="flex justify-center mt-6">
                    {{ $logs->links() }}
                </div>
            @endif
        </div>
    </div>
</div>

<!-- Floating Chat Widget -->
<div class="fixed bottom-6 right-6 z-50" x-data="chatWidget()" x-cloak>

    <!-- Chat Window -->
    <div x-show="isOpen"
         x-transition:enter="transition ease-out duration-300 transform"
         x-transition:enter-start="opacity-0 translate-y-4 scale-95"
         x-transition:enter-end="opacity-100 translate-y-0 scale-100"
         x-transition:leave="transition ease-in duration-200 transform"
         x-transition:leave-start="opacity-100 translate-y-0 scale-100"
         x-transition:leave-end="opacity-0 translate-y-4 scale-95"
         class="mb-4 w-96 max-w-[calc(100vw-3rem)] h-[600px] max-h-[calc(100vh-8rem)] flex flex-col bg-base-100 rounded-2xl shadow-2xl border border-base-300 overflow-hidden">

        <!-- Chat Header -->
        <div class="bg-gradient-to-r from-primary to-secondary p-4 flex items-center justify-between">
            <div class="flex items-center gap-3">
                <div class="avatar online placeholder">
                    <div class="bg-base-100 text-primary rounded-full w-10 h-10 flex items-center justify-center">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.663 17h4.673M12 3v1m6.364 1.636l-.707.707M21 12h-1M4 12H3m3.343-5.657l-.707-.707m2.828 9.9a5 5 0 117.072 0l-.548.547A3.374 3.374 0 0014 18.469V19a2 2 0 11-4 0v-.531c0-.895-.356-1.754-.988-2.386l-.548-.547z"></path>
                        </svg>
                    </div>
                </div>
                <div>
                    <div class="font-bold text-base-100">BMS AI Assistant</div>
                    <div class="text-xs text-base-100/80 flex items-center gap-1">
                        <span class="w-2 h-2 bg-success rounded-full animate-pulse"></span>
                        Powered by OpenAI GPT
                    </div>
                </div>
            </div>
            <button @click="closeChat()" class="btn btn-ghost btn-sm btn-circle text-base-100 hover:bg-base-100/20">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                </svg>
            </button>
        </div>

        <!-- Chat Messages -->
        <div id="chat-messages"
             class="flex-1 overflow-y-auto p-4 space-y-4 bg-base-200/30"
             x-ref="messagesContainer">
            <template x-for="(message, index) in messages" :key="index">
                <div :class="message.type === 'user' ? 'chat chat-end' : 'chat chat-start'">
                    <div class="chat-image avatar">
                        <div class="w-10 rounded-full" :class="message.type === 'user' ? 'bg-primary' : 'bg-secondary'">
                            <div class="w-full h-full flex items-center justify-center text-base-100">
                                <svg x-show="message.type === 'user'" class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                                </svg>
                                <svg x-show="message.type === 'bot'" class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.663 17h4.673M12 3v1m6.364 1.636l-.707.707M21 12h-1M4 12H3m3.343-5.657l-.707-.707m2.828 9.9a5 5 0 117.072 0l-.548.547A3.374 3.374 0 0014 18.469V19a2 2 0 11-4 0v-.531c0-.895-.356-1.754-.988-2.386l-.548-.547z"></path>
                                </svg>
                            </div>
                        </div>
                    </div>
                    <div class="chat-header text-xs opacity-70 mb-1" x-text="message.type === 'user' ? 'You' : 'AI Assistant'"></div>
                    <div class="chat-bubble text-sm whitespace-pre-wrap"
                         :class="message.type === 'user' ? 'chat-bubble-primary' : 'chat-bubble-secondary'"
                         x-html="formatMessage(message.text)"></div>
                    <div class="chat-footer opacity-50 text-xs mt-1" x-text="message.time"></div>
                </div>
            </template>

            <!-- Typing Indicator -->
            <div x-show="isTyping" class="chat chat-start">
                <div class="chat-image avatar">
                    <div class="w-10 rounded-full bg-secondary">
                        <div class="w-full h-full flex items-center justify-center text-base-100">
                            <svg class="w-5 h-5 animate-spin" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                      d="M9.663 17h4.673M12 3v1m6.364 1.636l-.707.707M21 12h-1M4 12H3
                                         m3.343-5.657l-.707-.707m2.828 9.9a5 5 0 117.072 0l-.548.547
                                         A3.374 3.374 0 0014 18.469V19a2 2 0 11-4 0v-.531
                                         c0-.895-.356-1.754-.988-2.386l-.548-.547z"></path>
                            </svg>
                        </div>
                    </div>
                </div>
                <div class="chat-bubble chat-bubble-secondary">
                    <span class="loading loading-dots loading-sm"></span>
                    <span class="text-xs ml-2">Analyzing machine data...</span>
                </div>
            </div>
        </div>

        <!-- Suggested Questions -->
        <div x-show="suggestedQuestions.length > 0" class="px-4 py-3 border-t border-base-300 bg-base-100">
            <div class="text-xs font-medium mb-2 opacity-70">💡 Quick Insights:</div>
            <div class="flex flex-wrap gap-2 max-h-32 overflow-y-auto">
                <template x-for="(q, index) in suggestedQuestions" :key="index">
                    <button @click="sendQuickAction(q.question)"
                            class="btn btn-xs btn-outline gap-1 normal-case"
                            :disabled="isTyping">
                        <span x-text="q.icon"></span>
                        <span x-text="q.question" class="truncate max-w-[200px]"></span>
                    </button>
                </template>
            </div>
        </div>

        <!-- Chat Input -->
        <div class="p-4 border-t border-base-300 bg-base-100">
            <form @submit.prevent="sendMessage()" class="flex gap-2">
                <input type="text"
                       x-model="messageInput"
                       placeholder="Ask about machine insights..."
                       class="input input-bordered flex-1 focus:outline-none focus:ring-2 focus:ring-primary text-sm"
                       :disabled="isTyping">
                <button type="submit"
                        class="btn btn-primary btn-circle"
                        :disabled="messageInput.trim() === '' || isTyping">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8"></path>
                    </svg>
                </button>
            </form>
            <div class="text-xs opacity-50 mt-2 text-center">
                Press Enter to send • Powered by OpenAI GPT
            </div>
        </div>
    </div>

    <!-- Floating Button -->
    <button @click="toggleChat()"
            class="btn btn-circle btn-lg shadow-2xl relative group transition-all duration-300"
            :class="isOpen ? 'bg-error hover:bg-error/90' : 'bg-gradient-to-br from-primary to-secondary hover:shadow-primary/50 hover:scale-110'">

        <svg x-show="!isOpen"
             class="w-7 h-7 text-base-100 transition-transform group-hover:scale-110"
             fill="none"
             stroke="currentColor"
             viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8.228 9c.549-1.165 2.03-2 3.772-2 2.21 0 4 1.343 4 3 0 1.4-1.278 2.575-3.006 2.907-.542.104-.994.54-.994 1.093m0 3h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
        </svg>

        <svg x-show="isOpen"
             class="w-7 h-7 text-base-100"
             fill="none"
             stroke="currentColor"
             viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
        </svg>

        <div x-show="!isOpen && hasUnread"
             class="absolute -top-1 -right-1 w-5 h-5 bg-error rounded-full flex items-center justify-center animate-pulse">
            <span class="text-xs font-bold text-base-100">AI</span>
        </div>

        <span class="absolute inset-0 rounded-full bg-primary opacity-75 animate-ping"
              x-show="!isOpen"></span>
    </button>
</div>

<!-- Alpine.js Component Script -->
<script>
document.addEventListener('alpine:init', () => {
    Alpine.data('chatWidget', () => ({
        isOpen: false,
        isTyping: false,
        messageInput: '',
        hasUnread: true,
        currentCacheKey: null,
        checkInterval: null,
        suggestedQuestions: [],
        messages: [
            {
                type: 'bot',
                text: '👋 Hello! I\'m your AI assistant powered by OpenAI GPT.\n\nI can analyze {{ $machine->name }} logs and provide insights about:\n\n• Health & performance trends\n• Anomaly detection\n• Maintenance recommendations\n• Power consumption analysis\n• Risk assessment\n\nClick on a suggested question or ask me anything!',
                time: 'just now'
            }
        ],

        async init() {
            this.$watch('messages', () => {
                this.$nextTick(() => {
                    if (this.$refs.messagesContainer) {
                        this.$refs.messagesContainer.scrollTop = this.$refs.messagesContainer.scrollHeight;
                    }
                });
            });

            // Load suggested questions immediately
            await this.loadSuggestedQuestions();
        },

        async loadSuggestedQuestions() {
            try {
                // Fixed: /api -> /ai routes
                const response = await fetch('{{ route('ai.questions', $machine) }}');
                const data = await response.json();
                if (data.success && data.questions) {
                    this.suggestedQuestions = data.questions;
                }
            } catch (error) {
                console.error('Failed to load questions:', error);
            }
        },

        toggleChat() {
            this.isOpen = !this.isOpen;
            if (this.isOpen) {
                this.hasUnread = false;
                // Reload questions when opening chat
                if (this.suggestedQuestions.length === 0) {
                    this.loadSuggestedQuestions();
                }
            }
        },

        closeChat() {
            this.isOpen = false;
            if (this.checkInterval) {
                clearInterval(this.checkInterval);
            }
        },

        async sendMessage() {
            if (this.messageInput.trim() === '' || this.isTyping) return;

            const userMessage = this.messageInput.trim();

            this.messages.push({
                type: 'user',
                text: userMessage,
                time: 'just now'
            });

            this.messageInput = '';
            this.isTyping = true;

            try {
                const response = await fetch('{{ route('ai.chat') }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                    },
                    body: JSON.stringify({
                        machine_id: {{ $machine->id }},
                        message: userMessage
                    })
                });

                const data = await response.json();

                if (data.processing) {
                    this.currentCacheKey = data.cache_key;
                    this.startStatusCheck();
                } else if (data.cached) {
                    this.addBotMessage(data.message);
                    this.isTyping = false;
                }

            } catch (error) {
                console.error('Chat error:', error);
                this.addBotMessage('❌ Sorry, I encountered an error. Please try again.');
                this.isTyping = false;
            }
        },

        startStatusCheck() {
            this.checkInterval = setInterval(async () => {
                try {
                    const response = await fetch('{{ route('ai.check-status') }}', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                        },
                        body: JSON.stringify({
                            cache_key: this.currentCacheKey
                        })
                    });

                    const data = await response.json();

                    if (data.ready) {
                        this.addBotMessage(data.message);
                        this.isTyping = false;
                        clearInterval(this.checkInterval);
                        this.currentCacheKey = null;
                        // Reload questions after getting response
                        this.loadSuggestedQuestions();
                    }
                } catch (error) {
                    console.error('Status check error:', error);
                    clearInterval(this.checkInterval);
                    this.isTyping = false;
                }
            }, 2000);
        },

        sendQuickAction(question) {
            this.messageInput = question;
            this.sendMessage();
        },

        addBotMessage(text) {
            this.messages.push({
                type: 'bot',
                text: text,
                time: 'just now'
            });
        },

        formatMessage(text) {
            return text
                .replace(/\*\*(.*?)\*\*/g, '<strong>$1</strong>')
                .replace(/\*(.*?)\*/g, '<em>$1</em>')
                .replace(/\n/g, '<br>');
        }
    }));
});
</script>

<!-- Styles -->
<style>
    [x-cloak] {
        display: none !important;
    }

    #chat-messages::-webkit-scrollbar {
        width: 6px;
    }

    #chat-messages::-webkit-scrollbar-track {
        background: transparent;
    }

    #chat-messages::-webkit-scrollbar-thumb {
        background: hsl(var(--bc) / 0.2);
        border-radius: 3px;
    }

    #chat-messages::-webkit-scrollbar-thumb:hover {
        background: hsl(var(--bc) / 0.3);
    }

    .chat {
        animation: fadeInUp 0.3s ease-out;
    }

    @keyframes fadeInUp {
        from {
            opacity: 0;
            transform: translateY(10px);
        }
        to {
            opacity: 1;
            transform: translateY(0);
        }
    }
</style>
@endsection
