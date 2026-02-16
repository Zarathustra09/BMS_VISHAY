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
            <div class="stat-value text-success">24</div>
            <div class="stat-desc">↗︎ 12% increase</div>
        </div>

        <div class="stat bg-base-100 rounded-box shadow-lg border border-base-300/50 hover:shadow-xl transition-all">
            <div class="stat-figure text-warning">
                <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                </svg>
            </div>
            <div class="stat-title">In Maintenance</div>
            <div class="stat-value text-warning">3</div>
            <div class="stat-desc">Scheduled repairs</div>
        </div>

        <div class="stat bg-base-100 rounded-box shadow-lg border border-base-300/50 hover:shadow-xl transition-all">
            <div class="stat-figure text-error">
                <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path>
                </svg>
            </div>
            <div class="stat-title">Offline</div>
            <div class="stat-value text-error">2</div>
            <div class="stat-desc">Require attention</div>
        </div>

        <div class="stat bg-base-100 rounded-box shadow-lg border border-base-300/50 hover:shadow-xl transition-all">
            <div class="stat-figure text-info">
                <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"></path>
                </svg>
            </div>
            <div class="stat-title">Efficiency</div>
            <div class="stat-value text-info">87%</div>
            <div class="stat-desc">Overall performance</div>
        </div>
    </div>

    <!-- Filters and Actions -->
    <div class="card bg-base-100 shadow-lg border border-base-300/50">
        <div class="card-body">
            <div class="flex flex-col lg:flex-row gap-4">
                <!-- Search -->
                <div class="form-control flex-1">
                    <label class="input input-bordered flex items-center gap-2">
                        <svg class="w-4 h-4 opacity-70" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                        </svg>
                        <input type="text" class="grow" placeholder="Search machines by name, model, or location..." />
                    </label>
                </div>

                <!-- Status Filter -->
                <select class="select select-bordered w-full lg:w-48">
                    <option selected>All Status</option>
                    <option>Active</option>
                    <option>Maintenance</option>
                    <option>Offline</option>
                    <option>Idle</option>
                </select>

                <!-- Building Filter -->
                <select class="select select-bordered w-full lg:w-48">
                    <option selected>All Buildings</option>
                    <option>Building A</option>
                    <option>Building B</option>
                    <option>Building C</option>
                </select>

                <!-- View Toggle -->
                <div class="btn-group">
                    <button class="btn btn-active">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zM14 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2zM14 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z"></path>
                        </svg>
                    </button>
                    <button class="btn">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 10h16M4 14h16M4 18h16"></path>
                        </svg>
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Machines Grid -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
        <!-- Machine Card 1 -->
        <div class="card bg-base-100 shadow-lg border border-base-300/50 hover:shadow-2xl transition-all group">
            <div class="card-body">
                <div class="flex items-start justify-between">
                    <div class="flex items-center gap-3">
                        <div class="avatar placeholder">
                            <div class="bg-success text-success-content rounded-lg w-12">
                                <span class="text-xl">🏭</span>
                            </div>
                        </div>
                        <div>
                            <h3 class="card-title text-lg">CNC Machine 01</h3>
                            <p class="text-sm opacity-70">Model: XYZ-5000</p>
                        </div>
                    </div>
                    <div class="dropdown dropdown-end">
                        <div tabindex="0" role="button" class="btn btn-ghost btn-sm btn-circle">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 5v.01M12 12v.01M12 19v.01M12 6a1 1 0 110-2 1 1 0 010 2zm0 7a1 1 0 110-2 1 1 0 010 2zm0 7a1 1 0 110-2 1 1 0 010 2z"></path>
                            </svg>
                        </div>
                        <ul tabindex="0" class="dropdown-content z-[1] menu p-2 shadow-lg bg-base-100 rounded-box w-52 border border-base-300/50">
                            <li><a><svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path></svg>View Details</a></li>
                            <li><a><svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path></svg>Edit</a></li>
                            <li><a><svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path></svg>Maintenance</a></li>
                            <div class="divider my-0"></div>
                            <li><a class="text-error"><svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>Delete</a></li>
                        </ul>
                    </div>
                </div>

                <div class="divider my-2"></div>

                <div class="space-y-2">
                    <div class="flex items-center justify-between text-sm">
                        <span class="opacity-70">Status</span>
                        <div class="badge badge-success gap-1">
                            <span class="w-2 h-2 bg-success-content rounded-full animate-pulse"></span>
                            Active
                        </div>
                    </div>
                    <div class="flex items-center justify-between text-sm">
                        <span class="opacity-70">Location</span>
                        <span class="font-medium">Building A - Floor 2</span>
                    </div>
                    <div class="flex items-center justify-between text-sm">
                        <span class="opacity-70">Efficiency</span>
                        <span class="font-medium text-success">92%</span>
                    </div>
                    <div class="flex items-center justify-between text-sm">
                        <span class="opacity-70">Last Maintenance</span>
                        <span class="font-medium">2 days ago</span>
                    </div>
                </div>

                <div class="divider my-2"></div>

                <!-- Progress Bar -->
                <div class="space-y-1">
                    <div class="flex justify-between text-xs">
                        <span class="opacity-70">Production Progress</span>
                        <span class="font-medium">850 / 1000 units</span>
                    </div>
                    <progress class="progress progress-success" value="85" max="100"></progress>
                </div>

                <div class="card-actions justify-end mt-4">
                    <button class="btn btn-ghost btn-sm gap-2">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path>
                        </svg>
                        View Stats
                    </button>
                    <button class="btn btn-primary btn-sm gap-2">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                        Details
                    </button>
                </div>
            </div>
        </div>

        <!-- Machine Card 2 - Maintenance -->
        <div class="card bg-base-100 shadow-lg border border-base-300/50 hover:shadow-2xl transition-all group">
            <div class="card-body">
                <div class="flex items-start justify-between">
                    <div class="flex items-center gap-3">
                        <div class="avatar placeholder">
                            <div class="bg-warning text-warning-content rounded-lg w-12">
                                <span class="text-xl">⚙️</span>
                            </div>
                        </div>
                        <div>
                            <h3 class="card-title text-lg">Press Machine 05</h3>
                            <p class="text-sm opacity-70">Model: PRESS-3000</p>
                        </div>
                    </div>
                    <div class="dropdown dropdown-end">
                        <div tabindex="0" role="button" class="btn btn-ghost btn-sm btn-circle">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 5v.01M12 12v.01M12 19v.01M12 6a1 1 0 110-2 1 1 0 010 2zm0 7a1 1 0 110-2 1 1 0 010 2zm0 7a1 1 0 110-2 1 1 0 010 2z"></path>
                            </svg>
                        </div>
                        <ul tabindex="0" class="dropdown-content z-[1] menu p-2 shadow-lg bg-base-100 rounded-box w-52 border border-base-300/50">
                            <li><a>View Details</a></li>
                            <li><a>Edit</a></li>
                            <li><a>Schedule Maintenance</a></li>
                            <div class="divider my-0"></div>
                            <li><a class="text-error">Delete</a></li>
                        </ul>
                    </div>
                </div>

                <div class="divider my-2"></div>

                <div class="space-y-2">
                    <div class="flex items-center justify-between text-sm">
                        <span class="opacity-70">Status</span>
                        <div class="badge badge-warning gap-1">
                            <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                            </svg>
                            Maintenance
                        </div>
                    </div>
                    <div class="flex items-center justify-between text-sm">
                        <span class="opacity-70">Location</span>
                        <span class="font-medium">Building B - Floor 1</span>
                    </div>
                    <div class="flex items-center justify-between text-sm">
                        <span class="opacity-70">Technician</span>
                        <span class="font-medium">John Doe</span>
                    </div>
                    <div class="flex items-center justify-between text-sm">
                        <span class="opacity-70">Est. Completion</span>
                        <span class="font-medium">2 hours</span>
                    </div>
                </div>

                <div class="divider my-2"></div>

                <div class="alert alert-warning">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path>
                    </svg>
                    <span class="text-xs">Scheduled maintenance in progress</span>
                </div>

                <div class="card-actions justify-end mt-4">
                    <button class="btn btn-ghost btn-sm gap-2">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                        </svg>
                        Maintenance Log
                    </button>
                    <button class="btn btn-warning btn-sm gap-2">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                        Details
                    </button>
                </div>
            </div>
        </div>

        <!-- Machine Card 3 - Offline -->
        <div class="card bg-base-100 shadow-lg border border-base-300/50 hover:shadow-2xl transition-all group">
            <div class="card-body">
                <div class="flex items-start justify-between">
                    <div class="flex items-center gap-3">
                        <div class="avatar placeholder">
                            <div class="bg-error text-error-content rounded-lg w-12">
                                <span class="text-xl">🔧</span>
                            </div>
                        </div>
                        <div>
                            <h3 class="card-title text-lg">Welding Robot 03</h3>
                            <p class="text-sm opacity-70">Model: WR-2500</p>
                        </div>
                    </div>
                    <div class="dropdown dropdown-end">
                        <div tabindex="0" role="button" class="btn btn-ghost btn-sm btn-circle">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 5v.01M12 12v.01M12 19v.01M12 6a1 1 0 110-2 1 1 0 010 2zm0 7a1 1 0 110-2 1 1 0 010 2zm0 7a1 1 0 110-2 1 1 0 010 2z"></path>
                            </svg>
                        </div>
                        <ul tabindex="0" class="dropdown-content z-[1] menu p-2 shadow-lg bg-base-100 rounded-box w-52 border border-base-300/50">
                            <li><a>View Details</a></li>
                            <li><a>Edit</a></li>
                            <li><a>Request Repair</a></li>
                            <div class="divider my-0"></div>
                            <li><a class="text-error">Delete</a></li>
                        </ul>
                    </div>
                </div>

                <div class="divider my-2"></div>

                <div class="space-y-2">
                    <div class="flex items-center justify-between text-sm">
                        <span class="opacity-70">Status</span>
                        <div class="badge badge-error gap-1">
                            <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                            </svg>
                            Offline
                        </div>
                    </div>
                    <div class="flex items-center justify-between text-sm">
                        <span class="opacity-70">Location</span>
                        <span class="font-medium">Building C - Floor 3</span>
                    </div>
                    <div class="flex items-center justify-between text-sm">
                        <span class="opacity-70">Issue</span>
                        <span class="font-medium text-error">Power Failure</span>
                    </div>
                    <div class="flex items-center justify-between text-sm">
                        <span class="opacity-70">Downtime</span>
                        <span class="font-medium">4 hours</span>
                    </div>
                </div>

                <div class="divider my-2"></div>

                <div class="alert alert-error">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path>
                    </svg>
                    <span class="text-xs">Requires immediate attention</span>
                </div>

                <div class="card-actions justify-end mt-4">
                    <button class="btn btn-ghost btn-sm gap-2">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                        View History
                    </button>
                    <button class="btn btn-error btn-sm gap-2">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                        </svg>
                        Request Repair
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Pagination -->
    <div class="flex justify-center">
        <div class="join">
            <button class="join-item btn btn-sm">«</button>
            <button class="join-item btn btn-sm btn-active">1</button>
            <button class="join-item btn btn-sm">2</button>
            <button class="join-item btn btn-sm">3</button>
            <button class="join-item btn btn-sm">4</button>
            <button class="join-item btn btn-sm">»</button>
        </div>
    </div>
</div>
@endsection
