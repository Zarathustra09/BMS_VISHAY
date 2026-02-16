@extends('layouts.app')

@section('title', 'Tenants')

@section('breadcrumbs')
    <li>Tenants</li>
@endsection

@section('content')
<div class="space-y-6">
    <!-- Page Header -->
    <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
        <div>
            <h1 class="text-3xl font-bold flex items-center gap-3">
                <div class="w-12 h-12 rounded-xl bg-gradient-to-br from-primary to-secondary flex items-center justify-center shadow-lg">
                    <svg class="w-7 h-7 text-base-100" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"></path>
                    </svg>
                </div>
                Tenant Management
            </h1>
            <p class="text-sm opacity-70 mt-2">Manage manufacturing partners and facility occupants</p>
        </div>
        <button class="btn btn-primary gap-2 shadow-lg hover:shadow-primary/50">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
            </svg>
            Add Tenant
        </button>
    </div>

    <!-- Stats Overview -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
        <div class="stat bg-base-100 rounded-box shadow-lg border border-base-300/50 hover:shadow-xl transition-all">
            <div class="stat-figure text-primary">
                <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"></path>
                </svg>
            </div>
            <div class="stat-title">Total Tenants</div>
            <div class="stat-value text-primary">34</div>
            <div class="stat-desc">Across all facilities</div>
        </div>

        <div class="stat bg-base-100 rounded-box shadow-lg border border-base-300/50 hover:shadow-xl transition-all">
            <div class="stat-figure text-success">
                <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                </svg>
            </div>
            <div class="stat-title">Active Contracts</div>
            <div class="stat-value text-success">28</div>
            <div class="stat-desc">Currently leasing</div>
        </div>

        <div class="stat bg-base-100 rounded-box shadow-lg border border-base-300/50 hover:shadow-xl transition-all">
            <div class="stat-figure text-info">
                <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                </svg>
            </div>
            <div class="stat-title">Monthly Revenue</div>
            <div class="stat-value text-info">$285K</div>
            <div class="stat-desc">From leases</div>
        </div>

        <div class="stat bg-base-100 rounded-box shadow-lg border border-base-300/50 hover:shadow-xl transition-all">
            <div class="stat-figure text-warning">
                <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                </svg>
            </div>
            <div class="stat-title">Expiring Soon</div>
            <div class="stat-value text-warning">6</div>
            <div class="stat-desc">Within 60 days</div>
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
                        <input type="text" class="grow" placeholder="Search by company name, contact, or location..." />
                    </label>
                </div>

                <!-- Status Filter -->
                <select class="select select-bordered w-full lg:w-48">
                    <option selected>All Status</option>
                    <option>Active</option>
                    <option>Pending</option>
                    <option>Expiring</option>
                    <option>Inactive</option>
                </select>

                <!-- Building Filter -->
                <select class="select select-bordered w-full lg:w-48">
                    <option selected>All Buildings</option>
                    <option>Building A</option>
                    <option>Building B</option>
                    <option>Building C</option>
                    <option>Warehouse</option>
                </select>

                <!-- Sort -->
                <select class="select select-bordered w-full lg:w-48">
                    <option selected>Sort by Name</option>
                    <option>Sort by Date</option>
                    <option>Sort by Space</option>
                    <option>Sort by Revenue</option>
                </select>
            </div>
        </div>
    </div>

    <!-- Tenants Grid -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <!-- Tenant Card 1 - Active -->
        <div class="card bg-base-100 shadow-lg border border-base-300/50 hover:shadow-2xl transition-all group">
            <div class="card-body">
                <div class="flex items-start justify-between">
                    <div class="flex items-center gap-4">
                        <div class="avatar placeholder">
                            <div class="bg-gradient-to-br from-primary to-secondary text-primary-content rounded-xl w-16">
                                <span class="text-2xl font-bold">AC</span>
                            </div>
                        </div>
                        <div>
                            <h3 class="card-title text-xl">Apex Components Ltd.</h3>
                            <p class="text-sm opacity-70">Electronics Manufacturing</p>
                            <div class="badge badge-success badge-sm gap-1 mt-2">
                                <span class="w-2 h-2 bg-success-content rounded-full animate-pulse"></span>
                                Active Contract
                            </div>
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
                            <li><a>Edit Information</a></li>
                            <li><a>View Contract</a></li>
                            <li><a>Payment History</a></li>
                            <li><a>Send Message</a></li>
                            <div class="divider my-0"></div>
                            <li><a class="text-error">Terminate Contract</a></li>
                        </ul>
                    </div>
                </div>

                <div class="divider my-2"></div>

                <div class="grid grid-cols-2 gap-4">
                    <div class="space-y-2 text-sm">
                        <div class="flex items-center gap-2">
                            <svg class="w-4 h-4 opacity-70" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                            </svg>
                            <div>
                                <div class="opacity-70 text-xs">Contact Person</div>
                                <div class="font-medium">John Anderson</div>
                            </div>
                        </div>
                        <div class="flex items-center gap-2">
                            <svg class="w-4 h-4 opacity-70" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"></path>
                            </svg>
                            <div>
                                <div class="opacity-70 text-xs">Phone</div>
                                <div class="font-medium">+1 555-0123</div>
                            </div>
                        </div>
                    </div>

                    <div class="space-y-2 text-sm">
                        <div class="flex items-center gap-2">
                            <svg class="w-4 h-4 opacity-70" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"></path>
                            </svg>
                            <div>
                                <div class="opacity-70 text-xs">Location</div>
                                <div class="font-medium">Building A - F2</div>
                            </div>
                        </div>
                        <div class="flex items-center gap-2">
                            <svg class="w-4 h-4 opacity-70" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 8V4m0 0h4M4 4l5 5m11-1V4m0 0h-4m4 0l-5 5M4 16v4m0 0h4m-4 0l5-5m11 5l-5-5m5 5v-4m0 4h-4"></path>
                            </svg>
                            <div>
                                <div class="opacity-70 text-xs">Space</div>
                                <div class="font-medium">12,500 sq ft</div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="divider my-2"></div>

                <div class="flex items-center justify-between">
                    <div class="space-y-1">
                        <div class="text-sm opacity-70">Monthly Rent</div>
                        <div class="text-2xl font-bold text-primary">$18,500</div>
                    </div>
                    <div class="text-right space-y-1">
                        <div class="text-sm opacity-70">Contract Expires</div>
                        <div class="font-medium">Dec 31, 2025</div>
                    </div>
                </div>

                <div class="card-actions justify-end mt-4">
                    <button class="btn btn-ghost btn-sm gap-2">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"></path>
                        </svg>
                        Message
                    </button>
                    <button class="btn btn-primary btn-sm gap-2">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path>
                        </svg>
                        View Details
                    </button>
                </div>
            </div>
        </div>

        <!-- Tenant Card 2 - Expiring Soon -->
        <div class="card bg-base-100 shadow-lg border border-warning/50 hover:shadow-2xl transition-all group">
            <div class="card-body">
                <div class="flex items-start justify-between">
                    <div class="flex items-center gap-4">
                        <div class="avatar placeholder">
                            <div class="bg-gradient-to-br from-warning to-accent text-warning-content rounded-xl w-16">
                                <span class="text-2xl font-bold">TP</span>
                            </div>
                        </div>
                        <div>
                            <h3 class="card-title text-xl">TechPro Solutions</h3>
                            <p class="text-sm opacity-70">Precision Tools Manufacturing</p>
                            <div class="badge badge-warning badge-sm gap-1 mt-2">
                                <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                </svg>
                                Expiring Soon
                            </div>
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
                            <li><a>Renew Contract</a></li>
                            <li><a>View Contract</a></li>
                            <li><a>Payment History</a></li>
                            <li><a>Send Message</a></li>
                            <div class="divider my-0"></div>
                            <li><a class="text-error">Terminate Contract</a></li>
                        </ul>
                    </div>
                </div>

                <div class="divider my-2"></div>

                <div class="grid grid-cols-2 gap-4">
                    <div class="space-y-2 text-sm">
                        <div class="flex items-center gap-2">
                            <svg class="w-4 h-4 opacity-70" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                            </svg>
                            <div>
                                <div class="opacity-70 text-xs">Contact Person</div>
                                <div class="font-medium">Sarah Martinez</div>
                            </div>
                        </div>
                        <div class="flex items-center gap-2">
                            <svg class="w-4 h-4 opacity-70" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"></path>
                            </svg>
                            <div>
                                <div class="opacity-70 text-xs">Phone</div>
                                <div class="font-medium">+1 555-0456</div>
                            </div>
                        </div>
                    </div>

                    <div class="space-y-2 text-sm">
                        <div class="flex items-center gap-2">
                            <svg class="w-4 h-4 opacity-70" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"></path>
                            </svg>
                            <div>
                                <div class="opacity-70 text-xs">Location</div>
                                <div class="font-medium">Building B - F3</div>
                            </div>
                        </div>
                        <div class="flex items-center gap-2">
                            <svg class="w-4 h-4 opacity-70" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 8V4m0 0h4M4 4l5 5m11-1V4m0 0h-4m4 0l-5 5M4 16v4m0 0h4m-4 0l5-5m11 5l-5-5m5 5v-4m0 4h-4"></path>
                            </svg>
                            <div>
                                <div class="opacity-70 text-xs">Space</div>
                                <div class="font-medium">8,200 sq ft</div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="divider my-2"></div>

                <div class="alert alert-warning">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                    <span class="text-xs">Contract expires in 45 days - Consider renewal</span>
                </div>

                <div class="flex items-center justify-between">
                    <div class="space-y-1">
                        <div class="text-sm opacity-70">Monthly Rent</div>
                        <div class="text-2xl font-bold text-warning">$12,800</div>
                    </div>
                    <div class="text-right space-y-1">
                        <div class="text-sm opacity-70">Contract Expires</div>
                        <div class="font-medium text-warning">Feb 15, 2025</div>
                    </div>
                </div>

                <div class="card-actions justify-end mt-4">
                    <button class="btn btn-ghost btn-sm gap-2">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"></path>
                        </svg>
                        Message
                    </button>
                    <button class="btn btn-warning btn-sm gap-2">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path>
                        </svg>
                        Renew Contract
                    </button>
                </div>
            </div>
        </div>

        <!-- Tenant Card 3 - Pending -->
        <div class="card bg-base-100 shadow-lg border border-info/50 hover:shadow-2xl transition-all group">
            <div class="card-body">
                <div class="flex items-start justify-between">
                    <div class="flex items-center gap-4">
                        <div class="avatar placeholder">
                            <div class="bg-gradient-to-br from-info to-primary text-info-content rounded-xl w-16">
                                <span class="text-2xl font-bold">GM</span>
                            </div>
                        </div>
                        <div>
                            <h3 class="card-title text-xl">Global Metals Inc.</h3>
                            <p class="text-sm opacity-70">Metal Fabrication</p>
                            <div class="badge badge-info badge-sm gap-1 mt-2">
                                <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                </svg>
                                Pending Approval
                            </div>
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
                            <li><a>Approve Application</a></li>
                            <li><a>Request Documents</a></li>
                            <li><a>Schedule Visit</a></li>
                            <div class="divider my-0"></div>
                            <li><a class="text-error">Reject Application</a></li>
                        </ul>
                    </div>
                </div>

                <div class="divider my-2"></div>

                <div class="grid grid-cols-2 gap-4">
                    <div class="space-y-2 text-sm">
                        <div class="flex items-center gap-2">
                            <svg class="w-4 h-4 opacity-70" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                            </svg>
                            <div>
                                <div class="opacity-70 text-xs">Contact Person</div>
                                <div class="font-medium">Robert Chen</div>
                            </div>
                        </div>
                        <div class="flex items-center gap-2">
                            <svg class="w-4 h-4 opacity-70" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"></path>
                            </svg>
                            <div>
                                <div class="opacity-70 text-xs">Phone</div>
                                <div class="font-medium">+1 555-0789</div>
                            </div>
                        </div>
                    </div>

                    <div class="space-y-2 text-sm">
                        <div class="flex items-center gap-2">
                            <svg class="w-4 h-4 opacity-70" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"></path>
                            </svg>
                            <div>
                                <div class="opacity-70 text-xs">Requested Location</div>
                                <div class="font-medium">Warehouse C</div>
                            </div>
                        </div>
                        <div class="flex items-center gap-2">
                            <svg class="w-4 h-4 opacity-70" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 8V4m0 0h4M4 4l5 5m11-1V4m0 0h-4m4 0l-5 5M4 16v4m0 0h4m-4 0l5-5m11 5l-5-5m5 5v-4m0 4h-4"></path>
                            </svg>
                            <div>
                                <div class="opacity-70 text-xs">Space</div>
                                <div class="font-medium">15,000 sq ft</div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="divider my-2"></div>

                <div class="flex items-center justify-between">
                    <div class="space-y-1">
                        <div class="text-sm opacity-70">Proposed Rent</div>
                        <div class="text-2xl font-bold text-info">$22,000</div>
                    </div>
                    <div class="text-right space-y-1">
                        <div class="text-sm opacity-70">Application Date</div>
                        <div class="font-medium">3 days ago</div>
                    </div>
                </div>

                <div class="card-actions justify-end mt-4">
                    <button class="btn btn-ghost btn-sm gap-2">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                        </svg>
                        Reject
                    </button>
                    <button class="btn btn-success btn-sm gap-2">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                        Approve
                    </button>
                </div>
            </div>
        </div>

        <!-- Tenant Card 4 - Inactive -->
        <div class="card bg-base-100 shadow-lg border border-base-300/50 hover:shadow-2xl transition-all group opacity-60">
            <div class="card-body">
                <div class="flex items-start justify-between">
                    <div class="flex items-center gap-4">
                        <div class="avatar placeholder">
                            <div class="bg-base-300 text-base-content rounded-xl w-16">
                                <span class="text-2xl font-bold">PS</span>
                            </div>
                        </div>
                        <div>
                            <h3 class="card-title text-xl">Precision Systems</h3>
                            <p class="text-sm opacity-70">Automation Equipment</p>
                            <div class="badge badge-ghost badge-sm mt-2">
                                Contract Ended
                            </div>
                        </div>
                    </div>
                    <div class="dropdown dropdown-end">
                        <div tabindex="0" role="button" class="btn btn-ghost btn-sm btn-circle">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 5v.01M12 12v.01M12 19v.01M12 6a1 1 0 110-2 1 1 0 010 2zm0 7a1 1 0 110-2 1 1 0 010 2zm0 7a1 1 0 110-2 1 1 0 010 2z"></path>
                            </svg>
                        </div>
                        <ul tabindex="0" class="dropdown-content z-[1] menu p-2 shadow-lg bg-base-100 rounded-box w-52 border border-base-300/50">
                            <li><a>View History</a></li>
                            <li><a>View Documents</a></li>
                            <li><a>Contact</a></li>
                            <div class="divider my-0"></div>
                            <li><a class="text-error">Archive</a></li>
                        </ul>
                    </div>
                </div>

                <div class="divider my-2"></div>

                <div class="grid grid-cols-2 gap-4">
                    <div class="space-y-2 text-sm">
                        <div class="flex items-center gap-2">
                            <svg class="w-4 h-4 opacity-70" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                            </svg>
                            <div>
                                <div class="opacity-70 text-xs">Contact Person</div>
                                <div class="font-medium">Lisa Wong</div>
                            </div>
                        </div>
                        <div class="flex items-center gap-2">
                            <svg class="w-4 h-4 opacity-70" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"></path>
                            </svg>
                            <div>
                                <div class="opacity-70 text-xs">Previous Location</div>
                                <div class="font-medium">Building A - F1</div>
                            </div>
                        </div>
                    </div>

                    <div class="space-y-2 text-sm">
                        <div class="flex items-center gap-2">
                            <svg class="w-4 h-4 opacity-70" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                            </svg>
                            <div>
                                <div class="opacity-70 text-xs">Contract Ended</div>
                                <div class="font-medium">Nov 30, 2024</div>
                            </div>
                        </div>
                        <div class="flex items-center gap-2">
                            <svg class="w-4 h-4 opacity-70" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                            </svg>
                            <div>
                                <div class="opacity-70 text-xs">Total Revenue</div>
                                <div class="font-medium">$195,000</div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="card-actions justify-end mt-4">
                    <button class="btn btn-ghost btn-sm gap-2">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                        View History
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
            <button class="join-item btn btn-sm">»</button>
        </div>
    </div>
</div>
@endsection
