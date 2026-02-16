@extends('layouts.app')

@section('title', 'Work Orders')

@section('breadcrumbs')
    <li>Work Orders</li>
@endsection

@section('content')
<div class="space-y-6">
    <!-- Page Header -->
    <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
        <div>
            <h1 class="text-3xl font-bold flex items-center gap-3">
                <div class="w-12 h-12 rounded-xl bg-gradient-to-br from-primary to-secondary flex items-center justify-center shadow-lg">
                    <svg class="w-7 h-7 text-base-100" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01"></path>
                    </svg>
                </div>
                Work Orders
            </h1>
            <p class="text-sm opacity-70 mt-2">Track and manage maintenance tasks and service requests</p>
        </div>
        <button class="btn btn-primary gap-2 shadow-lg hover:shadow-primary/50">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
            </svg>
            Create Work Order
        </button>
    </div>

    <!-- Stats Overview -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-5 gap-4">
        <div class="stat bg-base-100 rounded-box shadow-lg border border-base-300/50 hover:shadow-xl transition-all">
            <div class="stat-figure text-info">
                <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"></path>
                </svg>
            </div>
            <div class="stat-title">Total Orders</div>
            <div class="stat-value text-info text-3xl">148</div>
            <div class="stat-desc">Active tasks</div>
        </div>

        <div class="stat bg-base-100 rounded-box shadow-lg border border-base-300/50 hover:shadow-xl transition-all">
            <div class="stat-figure text-warning">
                <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                </svg>
            </div>
            <div class="stat-title">Pending</div>
            <div class="stat-value text-warning text-3xl">23</div>
            <div class="stat-desc">Awaiting assignment</div>
        </div>

        <div class="stat bg-base-100 rounded-box shadow-lg border border-base-300/50 hover:shadow-xl transition-all">
            <div class="stat-figure text-primary">
                <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"></path>
                </svg>
            </div>
            <div class="stat-title">In Progress</div>
            <div class="stat-value text-primary text-3xl">67</div>
            <div class="stat-desc">Being worked on</div>
        </div>

        <div class="stat bg-base-100 rounded-box shadow-lg border border-base-300/50 hover:shadow-xl transition-all">
            <div class="stat-figure text-success">
                <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                </svg>
            </div>
            <div class="stat-title">Completed</div>
            <div class="stat-value text-success text-3xl">52</div>
            <div class="stat-desc">This month</div>
        </div>

        <div class="stat bg-base-100 rounded-box shadow-lg border border-base-300/50 hover:shadow-xl transition-all">
            <div class="stat-figure text-error">
                <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path>
                </svg>
            </div>
            <div class="stat-title">Urgent</div>
            <div class="stat-value text-error text-3xl">6</div>
            <div class="stat-desc">Needs attention</div>
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
                        <input type="text" class="grow" placeholder="Search by order ID, description, or assignee..." />
                    </label>
                </div>

                <!-- Status Filter -->
                <select class="select select-bordered w-full lg:w-48">
                    <option selected>All Status</option>
                    <option>Pending</option>
                    <option>In Progress</option>
                    <option>Completed</option>
                    <option>Cancelled</option>
                </select>

                <!-- Priority Filter -->
                <select class="select select-bordered w-full lg:w-48">
                    <option selected>All Priorities</option>
                    <option>Low</option>
                    <option>Medium</option>
                    <option>High</option>
                    <option>Urgent</option>
                </select>

                <!-- Type Filter -->
                <select class="select select-bordered w-full lg:w-48">
                    <option selected>All Types</option>
                    <option>Preventive</option>
                    <option>Corrective</option>
                    <option>Emergency</option>
                    <option>Inspection</option>
                </select>
            </div>
        </div>
    </div>

    <!-- Work Orders List -->
    <div class="space-y-4">
        <!-- Work Order Card 1 - Urgent -->
        <div class="card bg-base-100 shadow-lg border-l-4 border-error hover:shadow-2xl transition-all group">
            <div class="card-body">
                <div class="flex flex-col lg:flex-row lg:items-center justify-between gap-4">
                    <div class="flex-1 space-y-3">
                        <div class="flex flex-wrap items-center gap-3">
                            <div class="badge badge-error badge-lg gap-2 font-semibold">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path>
                                </svg>
                                URGENT
                            </div>
                            <span class="text-sm opacity-70">#WO-2024-001</span>
                            <div class="badge badge-outline">Emergency</div>
                        </div>

                        <div>
                            <h3 class="text-xl font-bold text-error">Critical Equipment Failure - CNC Machine 05</h3>
                            <p class="text-sm opacity-70 mt-1">Hydraulic system malfunction causing production halt on Line 3</p>
                        </div>

                        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-3 text-sm">
                            <div class="flex items-center gap-2">
                                <svg class="w-4 h-4 opacity-70" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"></path>
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                </svg>
                                <span class="opacity-70">Location:</span>
                                <span class="font-medium">Building A - Floor 2</span>
                            </div>
                            <div class="flex items-center gap-2">
                                <svg class="w-4 h-4 opacity-70" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                                </svg>
                                <span class="opacity-70">Assigned:</span>
                                <span class="font-medium">Mike Johnson</span>
                            </div>
                            <div class="flex items-center gap-2">
                                <svg class="w-4 h-4 opacity-70" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                                </svg>
                                <span class="opacity-70">Created:</span>
                                <span class="font-medium">2 hours ago</span>
                            </div>
                            <div class="flex items-center gap-2">
                                <svg class="w-4 h-4 opacity-70" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                </svg>
                                <span class="opacity-70">Est. Time:</span>
                                <span class="font-medium text-error">4 hours</span>
                            </div>
                        </div>
                    </div>

                    <div class="flex lg:flex-col gap-2">
                        <button class="btn btn-error btn-sm gap-2 flex-1 lg:flex-none">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path>
                            </svg>
                            View Details
                        </button>
                        <div class="dropdown dropdown-end">
                            <div tabindex="0" role="button" class="btn btn-ghost btn-sm btn-square">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 5v.01M12 12v.01M12 19v.01M12 6a1 1 0 110-2 1 1 0 010 2zm0 7a1 1 0 110-2 1 1 0 010 2zm0 7a1 1 0 110-2 1 1 0 010 2z"></path>
                                </svg>
                            </div>
                            <ul tabindex="0" class="dropdown-content z-[1] menu p-2 shadow-lg bg-base-100 rounded-box w-52 border border-base-300/50">
                                <li><a>Edit Order</a></li>
                                <li><a>Reassign</a></li>
                                <li><a>Add Comment</a></li>
                                <li><a>View History</a></li>
                                <div class="divider my-0"></div>
                                <li><a class="text-error">Cancel Order</a></li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Work Order Card 2 - In Progress -->
        <div class="card bg-base-100 shadow-lg border-l-4 border-primary hover:shadow-2xl transition-all group">
            <div class="card-body">
                <div class="flex flex-col lg:flex-row lg:items-center justify-between gap-4">
                    <div class="flex-1 space-y-3">
                        <div class="flex flex-wrap items-center gap-3">
                            <div class="badge badge-primary badge-lg gap-2 font-semibold">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"></path>
                                </svg>
                                IN PROGRESS
                            </div>
                            <span class="text-sm opacity-70">#WO-2024-002</span>
                            <div class="badge badge-outline">Preventive</div>
                        </div>

                        <div>
                            <h3 class="text-xl font-bold">Scheduled Maintenance - Press Machine 03</h3>
                            <p class="text-sm opacity-70 mt-1">Quarterly inspection and lubrication of hydraulic components</p>
                        </div>

                        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-3 text-sm">
                            <div class="flex items-center gap-2">
                                <svg class="w-4 h-4 opacity-70" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"></path>
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                </svg>
                                <span class="opacity-70">Location:</span>
                                <span class="font-medium">Building B - Floor 1</span>
                            </div>
                            <div class="flex items-center gap-2">
                                <svg class="w-4 h-4 opacity-70" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                                </svg>
                                <span class="opacity-70">Assigned:</span>
                                <span class="font-medium">Sarah Chen</span>
                            </div>
                            <div class="flex items-center gap-2">
                                <svg class="w-4 h-4 opacity-70" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                                </svg>
                                <span class="opacity-70">Started:</span>
                                <span class="font-medium">1 day ago</span>
                            </div>
                            <div class="flex items-center gap-2">
                                <svg class="w-4 h-4 opacity-70" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                </svg>
                                <span class="opacity-70">Est. Time:</span>
                                <span class="font-medium">2 hours</span>
                            </div>
                        </div>

                        <div class="space-y-1">
                            <div class="flex justify-between text-xs">
                                <span class="opacity-70">Progress</span>
                                <span class="font-medium">65%</span>
                            </div>
                            <progress class="progress progress-primary" value="65" max="100"></progress>
                        </div>
                    </div>

                    <div class="flex lg:flex-col gap-2">
                        <button class="btn btn-primary btn-sm gap-2 flex-1 lg:flex-none">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path>
                            </svg>
                            View Details
                        </button>
                        <div class="dropdown dropdown-end">
                            <div tabindex="0" role="button" class="btn btn-ghost btn-sm btn-square">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 5v.01M12 12v.01M12 19v.01M12 6a1 1 0 110-2 1 1 0 010 2zm0 7a1 1 0 110-2 1 1 0 010 2zm0 7a1 1 0 110-2 1 1 0 010 2z"></path>
                                </svg>
                            </div>
                            <ul tabindex="0" class="dropdown-content z-[1] menu p-2 shadow-lg bg-base-100 rounded-box w-52 border border-base-300/50">
                                <li><a>Edit Order</a></li>
                                <li><a>Update Progress</a></li>
                                <li><a>Add Comment</a></li>
                                <li><a>Mark Complete</a></li>
                                <div class="divider my-0"></div>
                                <li><a class="text-error">Cancel Order</a></li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Work Order Card 3 - Pending -->
        <div class="card bg-base-100 shadow-lg border-l-4 border-warning hover:shadow-2xl transition-all group">
            <div class="card-body">
                <div class="flex flex-col lg:flex-row lg:items-center justify-between gap-4">
                    <div class="flex-1 space-y-3">
                        <div class="flex flex-wrap items-center gap-3">
                            <div class="badge badge-warning badge-lg gap-2 font-semibold">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                </svg>
                                PENDING
                            </div>
                            <span class="text-sm opacity-70">#WO-2024-003</span>
                            <div class="badge badge-outline">Inspection</div>
                        </div>

                        <div>
                            <h3 class="text-xl font-bold">Safety Equipment Inspection - Building C</h3>
                            <p class="text-sm opacity-70 mt-1">Monthly fire extinguisher and emergency exit verification</p>
                        </div>

                        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-3 text-sm">
                            <div class="flex items-center gap-2">
                                <svg class="w-4 h-4 opacity-70" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"></path>
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                </svg>
                                <span class="opacity-70">Location:</span>
                                <span class="font-medium">Building C - All Floors</span>
                            </div>
                            <div class="flex items-center gap-2">
                                <svg class="w-4 h-4 opacity-70" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                                </svg>
                                <span class="opacity-70">Assigned:</span>
                                <span class="font-medium text-warning">Unassigned</span>
                            </div>
                            <div class="flex items-center gap-2">
                                <svg class="w-4 h-4 opacity-70" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                                </svg>
                                <span class="opacity-70">Due Date:</span>
                                <span class="font-medium">Tomorrow</span>
                            </div>
                            <div class="flex items-center gap-2">
                                <svg class="w-4 h-4 opacity-70" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                </svg>
                                <span class="opacity-70">Est. Time:</span>
                                <span class="font-medium">3 hours</span>
                            </div>
                        </div>

                        <div class="alert alert-warning">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                            </svg>
                            <span class="text-xs">This order needs to be assigned to a technician</span>
                        </div>
                    </div>

                    <div class="flex lg:flex-col gap-2">
                        <button class="btn btn-warning btn-sm gap-2 flex-1 lg:flex-none">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                            </svg>
                            Assign Now
                        </button>
                        <div class="dropdown dropdown-end">
                            <div tabindex="0" role="button" class="btn btn-ghost btn-sm btn-square">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 5v.01M12 12v.01M12 19v.01M12 6a1 1 0 110-2 1 1 0 010 2zm0 7a1 1 0 110-2 1 1 0 010 2zm0 7a1 1 0 110-2 1 1 0 010 2z"></path>
                                </svg>
                            </div>
                            <ul tabindex="0" class="dropdown-content z-[1] menu p-2 shadow-lg bg-base-100 rounded-box w-52 border border-base-300/50">
                                <li><a>View Details</a></li>
                                <li><a>Edit Order</a></li>
                                <li><a>Change Priority</a></li>
                                <div class="divider my-0"></div>
                                <li><a class="text-error">Cancel Order</a></li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Work Order Card 4 - Completed -->
        <div class="card bg-base-100 shadow-lg border-l-4 border-success hover:shadow-2xl transition-all group opacity-75">
            <div class="card-body">
                <div class="flex flex-col lg:flex-row lg:items-center justify-between gap-4">
                    <div class="flex-1 space-y-3">
                        <div class="flex flex-wrap items-center gap-3">
                            <div class="badge badge-success badge-lg gap-2 font-semibold">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                </svg>
                                COMPLETED
                            </div>
                            <span class="text-sm opacity-70">#WO-2024-004</span>
                            <div class="badge badge-outline">Corrective</div>
                        </div>

                        <div>
                            <h3 class="text-xl font-bold">Conveyor Belt Replacement - Line 5</h3>
                            <p class="text-sm opacity-70 mt-1">Replaced worn conveyor belt and realigned tracking system</p>
                        </div>

                        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-3 text-sm">
                            <div class="flex items-center gap-2">
                                <svg class="w-4 h-4 opacity-70" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"></path>
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                </svg>
                                <span class="opacity-70">Location:</span>
                                <span class="font-medium">Building A - Floor 3</span>
                            </div>
                            <div class="flex items-center gap-2">
                                <svg class="w-4 h-4 opacity-70" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                                </svg>
                                <span class="opacity-70">Completed By:</span>
                                <span class="font-medium">David Lee</span>
                            </div>
                            <div class="flex items-center gap-2">
                                <svg class="w-4 h-4 opacity-70" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                                </svg>
                                <span class="opacity-70">Completed:</span>
                                <span class="font-medium">Yesterday</span>
                            </div>
                            <div class="flex items-center gap-2">
                                <svg class="w-4 h-4 opacity-70" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                </svg>
                                <span class="opacity-70">Duration:</span>
                                <span class="font-medium">5.5 hours</span>
                            </div>
                        </div>
                    </div>

                    <div class="flex lg:flex-col gap-2">
                        <button class="btn btn-ghost btn-sm gap-2 flex-1 lg:flex-none">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                            </svg>
                            View Report
                        </button>
                        <div class="dropdown dropdown-end">
                            <div tabindex="0" role="button" class="btn btn-ghost btn-sm btn-square">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 5v.01M12 12v.01M12 19v.01M12 6a1 1 0 110-2 1 1 0 010 2zm0 7a1 1 0 110-2 1 1 0 010 2zm0 7a1 1 0 110-2 1 1 0 010 2z"></path>
                                </svg>
                            </div>
                            <ul tabindex="0" class="dropdown-content z-[1] menu p-2 shadow-lg bg-base-100 rounded-box w-52 border border-base-300/50">
                                <li><a>View Details</a></li>
                                <li><a>Download Report</a></li>
                                <li><a>View Timeline</a></li>
                                <li><a>Create Similar</a></li>
                            </ul>
                        </div>
                    </div>
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
