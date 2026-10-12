@extends('layouts.app')

@section('title', 'Alert — '.($alert->machine->name ?? 'Unknown machine'))

@section('breadcrumbs')
    <li><a href="{{ route('alerts.index') }}">Alerts</a></li>
    <li>Alert #{{ $alert->id }}</li>
@endsection

@section('back', route('alerts.index'))

@section('content')
@php
    $severityColor = $alert->severity === 'critical' ? 'error' : 'warning';
    $statusColor = match ($alert->status) {
        'open' => 'error',
        'acknowledged' => 'warning',
        'resolved' => 'success',
        default => 'ghost',
    };
@endphp
<div class="space-y-6 max-w-4xl mx-auto" data-alert-id="{{ $alert->id }}" data-live-alert-page>
    <div class="flex flex-col sm:flex-row justify-between items-start gap-4">
        <div>
            <h1 class="text-3xl font-bold flex items-center gap-3">
                Alert #{{ $alert->id }}
                <span class="badge badge-{{ $severityColor }}">{{ ucfirst($alert->severity) }}</span>
                <span class="badge badge-{{ $statusColor }} badge-outline">{{ ucfirst($alert->status) }}</span>
            </h1>
            <p class="text-sm opacity-70 mt-2">{{ $alert->message }}</p>
        </div>
        <div class="flex gap-2">
            @if($alert->status === 'open')
                @can('alerts.acknowledge')
                <form action="{{ route('alerts.acknowledge', $alert) }}" method="POST">
                    @csrf
                    <button type="submit" class="btn btn-sm btn-outline">Acknowledge</button>
                </form>
                @endcan
            @endif
            @if($alert->status !== 'resolved')
                @can('alerts.resolve')
                <form action="{{ route('alerts.resolve', $alert) }}" method="POST">
                    @csrf
                    <button type="submit" class="btn btn-sm btn-outline btn-success">Resolve</button>
                </form>
                @endcan
            @endif
        </div>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
        <div class="card bg-base-100 shadow-lg border border-base-300/50">
            <div class="card-body space-y-2 text-sm">
                <h2 class="card-title">Details</h2>
                <div class="flex justify-between">
                    <span class="opacity-70">Machine</span>
                    <span class="font-medium">
                        @if($alert->machine && auth()->user()->can('machines.view'))
                            <a href="{{ route('machines.show', $alert->machine) }}" class="link link-hover">{{ $alert->machine->name }}</a>
                        @else
                            {{ $alert->machine->name ?? 'Unknown' }}
                        @endif
                    </span>
                </div>
                <div class="flex justify-between"><span class="opacity-70">Type</span><span class="font-medium">{{ $alert->type === 'offline' ? 'Device offline' : 'Threshold breach' }}</span></div>
                @if($alert->metric)
                    <div class="flex justify-between"><span class="opacity-70">Reading</span><span class="font-medium">{{ ucfirst(str_replace('_', ' ', $alert->metric)) }}: {{ rtrim(rtrim((string) $alert->value, '0'), '.') }}</span></div>
                @endif
                <div class="flex justify-between"><span class="opacity-70">Raised</span><span class="font-medium">{{ $alert->created_at->format('M d, Y H:i') }}</span></div>
                @if($alert->acknowledged_at)
                    <div class="flex justify-between"><span class="opacity-70">Acknowledged</span><span class="font-medium">{{ $alert->acknowledgedBy?->name ?? 'Unknown' }} · {{ $alert->acknowledged_at->format('M d, H:i') }}</span></div>
                @endif
                @if($alert->resolved_at)
                    <div class="flex justify-between"><span class="opacity-70">Resolved</span><span class="font-medium">{{ $alert->resolvedBy?->name ?? 'System' }} · {{ $alert->resolved_at->format('M d, H:i') }}</span></div>
                @endif
            </div>
        </div>

        <div class="card bg-base-100 shadow-lg border border-base-300/50">
            <div class="card-body">
                <h2 class="card-title">Trail</h2>
                <p class="text-xs opacity-60">Everything that happened to this alert, and who did it.</p>
                <div id="alertTrail">
                    @include('alerts._timeline', ['events' => $alert->events])
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
