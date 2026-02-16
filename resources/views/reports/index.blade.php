@extends('layouts.app')

@section('title', 'Reports')

@section('breadcrumbs')
    <li>Reports & Analytics</li>
@endsection

@section('content')
<div class="space-y-6">
    <!-- Page Header -->
    <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
        <div>
            <h1 class="text-3xl font-bold flex items-center gap-3">
                <div class="w-12 h-12 rounded-xl bg-gradient-to-br from-primary to-secondary flex items-center justify-center shadow-lg">
                    <svg class="w-7 h-7 text-base-100" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path>
                    </svg>
                </div>
                Reports & Analytics
            </h1>
            <p class="text-sm opacity-70 mt-2">Comprehensive insights into facility operations and performance</p>
        </div>
        <div class="flex gap-2">
            <button class="btn btn-ghost gap-2">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6V4m0 2a2 2 0 100 4m0-4a2 2 0 110 4m-6 8a2 2 0 100-4m0 4a2 2 0 110-4m0 4v2m0-6V4m6 6v10m6-2a2 2 0 100-4m0 4a2 2 0 110-4m0 4v2m0-6V4"></path>
                </svg>
                Customize
            </button>
            <button class="btn btn-primary gap-2 shadow-lg hover:shadow-primary/50">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                </svg>
                Export Reports
            </button>
        </div>
    </div>

    <!-- Date Range Filter -->
    <div class="card bg-base-100 shadow-lg border border-base-300/50">
        <div class="card-body">
            <div class="flex flex-col lg:flex-row gap-4">
                <div class="form-control flex-1">
                    <label class="label">
                        <span class="label-text font-medium">Date Range</span>
                    </label>
                    <select class="select select-bordered">
                        <option selected>Last 30 Days</option>
                        <option>Last 7 Days</option>
                        <option>This Month</option>
                        <option>Last Month</option>
                        <option>This Quarter</option>
                        <option>This Year</option>
                        <option>Custom Range</option>
                    </select>
                </div>

                <div class="form-control flex-1">
                    <label class="label">
                        <span class="label-text font-medium">Building</span>
                    </label>
                    <select class="select select-bordered">
                        <option selected>All Buildings</option>
                        <option>Building A</option>
                        <option>Building B</option>
                        <option>Building C</option>
                        <option>Warehouse</option>
                    </select>
                </div>

                <div class="form-control flex-1">
                    <label class="label">
                        <span class="label-text font-medium">Department</span>
                    </label>
                    <select class="select select-bordered">
                        <option selected>All Departments</option>
                        <option>Production</option>
                        <option>Maintenance</option>
                        <option>Quality Control</option>
                        <option>Warehouse</option>
                    </select>
                </div>

                <div class="form-control flex-1">
                    <label class="label">
                        <span class="label-text font-medium">Report Type</span>
                    </label>
                    <select class="select select-bordered">
                        <option selected>All Reports</option>
                        <option>Operations</option>
                        <option>Financial</option>
                        <option>Maintenance</option>
                        <option>Safety</option>
                    </select>
                </div>
            </div>
        </div>
    </div>

    <!-- Key Performance Indicators -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
        <div class="stat bg-gradient-to-br from-primary/10 to-secondary/10 rounded-box shadow-lg border border-primary/20 hover:shadow-xl transition-all">
            <div class="stat-figure text-primary">
                <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"></path>
                </svg>
            </div>
            <div class="stat-title">Production Efficiency</div>
            <div class="stat-value text-primary">87.5%</div>
            <div class="stat-desc text-success">↗︎ 4.2% from last month</div>
        </div>

        <div class="stat bg-gradient-to-br from-success/10 to-accent/10 rounded-box shadow-lg border border-success/20 hover:shadow-xl transition-all">
            <div class="stat-figure text-success">
                <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                </svg>
            </div>
            <div class="stat-title">Revenue (Monthly)</div>
            <div class="stat-value text-success">$458K</div>
            <div class="stat-desc text-success">↗︎ $42K increase</div>
        </div>

        <div class="stat bg-gradient-to-br from-warning/10 to-error/10 rounded-box shadow-lg border border-warning/20 hover:shadow-xl transition-all">
            <div class="stat-figure text-warning">
                <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                </svg>
            </div>
            <div class="stat-title">Downtime Hours</div>
            <div class="stat-value text-warning">23.5h</div>
            <div class="stat-desc text-error">↗︎ 5.2h from target</div>
        </div>

        <div class="stat bg-gradient-to-br from-info/10 to-primary/10 rounded-box shadow-lg border border-info/20 hover:shadow-xl transition-all">
            <div class="stat-figure text-info">
                <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"></path>
                </svg>
            </div>
            <div class="stat-title">Safety Score</div>
            <div class="stat-value text-info">95.2%</div>
            <div class="stat-desc text-success">↗︎ 2.1% improvement</div>
        </div>
    </div>

    <!-- Charts Section -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <!-- Production Output Chart -->
        <div class="card bg-base-100 shadow-lg border border-base-300/50">
            <div class="card-body">
                <div class="flex items-center justify-between mb-4">
                    <h2 class="card-title">Production Output Trends</h2>
                    <div class="dropdown dropdown-end">
                        <div tabindex="0" role="button" class="btn btn-ghost btn-sm btn-circle">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 5v.01M12 12v.01M12 19v.01M12 6a1 1 0 110-2 1 1 0 010 2zm0 7a1 1 0 110-2 1 1 0 010 2zm0 7a1 1 0 110-2 1 1 0 010 2z"></path>
                            </svg>
                        </div>
                        <ul tabindex="0" class="dropdown-content z-[1] menu p-2 shadow-lg bg-base-100 rounded-box w-52 border border-base-300/50">
                            <li><a>Download Chart</a></li>
                            <li><a>View Full Report</a></li>
                            <li><a>Export Data</a></li>
                        </ul>
                    </div>
                </div>

                <!-- Placeholder Chart -->
                <div class="h-64 bg-base-200 rounded-lg flex items-end justify-around p-4 gap-2">
                    <div class="tooltip" data-tip="Week 1: 850 units">
                        <div class="w-full h-32 bg-gradient-to-t from-primary to-primary/50 rounded-t"></div>
                    </div>
                    <div class="tooltip" data-tip="Week 2: 920 units">
                        <div class="w-full h-40 bg-gradient-to-t from-primary to-primary/50 rounded-t"></div>
                    </div>
                    <div class="tooltip" data-tip="Week 3: 780 units">
                        <div class="w-full h-28 bg-gradient-to-t from-primary to-primary/50 rounded-t"></div>
                    </div>
                    <div class="tooltip" data-tip="Week 4: 1050 units">
                        <div class="w-full h-52 bg-gradient-to-t from-primary to-primary/50 rounded-t"></div>
                    </div>
                </div>

                <div class="flex justify-center gap-4 mt-4">
                    <div class="flex items-center gap-2">
                        <div class="w-3 h-3 rounded-full bg-primary"></div>
                        <span class="text-sm">Production Units</span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Equipment Utilization -->
        <div class="card bg-base-100 shadow-lg border border-base-300/50">
            <div class="card-body">
                <div class="flex items-center justify-between mb-4">
                    <h2 class="card-title">Equipment Utilization</h2>
                    <div class="dropdown dropdown-end">
                        <div tabindex="0" role="button" class="btn btn-ghost btn-sm btn-circle">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 5v.01M12 12v.01M12 19v.01M12 6a1 1 0 110-2 1 1 0 010 2zm0 7a1 1 0 110-2 1 1 0 010 2zm0 7a1 1 0 110-2 1 1 0 010 2z"></path>
                        </div>
                        <ul tabindex="0" class="dropdown-content z-[1] menu p-2 shadow-lg bg-base-100 rounded-box w-52 border border-base-300/50">
                            <li><a>Download Chart</a></li>
                            <li><a>View Details</a></li>
                            <li><a>Export Data</a></li>
                        </ul>
                    </div>
                </div>

                <div class="space-y-4">
                    <div>
                        <div class="flex justify-between text-sm mb-2">
                            <span class="font-medium">CNC Machines</span>
                            <span class="text-primary font-bold">89%</span>
                        </div>
                        <progress class="progress progress-primary" value="89" max="100"></progress>
                    </div>

                    <div>
                        <div class="flex justify-between text-sm mb-2">
                            <span class="font-medium">Press Machines</span>
                            <span class="text-success font-bold">94%</span>
                        </div>
                        <progress class="progress progress-success" value="94" max="100"></progress>
                    </div>

                    <div>
                        <div class="flex justify-between text-sm mb-2">
                            <span class="font-medium">Welding Robots</span>
                            <span class="text-warning font-bold">76%</span>
                        </div>
                        <progress class="progress progress-warning" value="76" max="100"></progress>
                    </div>

                    <div>
                        <div class="flex justify-between text-sm mb-2">
                            <span class="font-medium">Assembly Lines</span>
                            <span class="text-info font-bold">85%</span>
                        </div>
                        <progress class="progress progress-info" value="85" max="100"></progress>
                    </div>

                    <div>
                        <div class="flex justify-between text-sm mb-2">
                            <span class="font-medium">Packaging Units</span>
                            <span class="text-secondary font-bold">91%</span>
                        </div>
                        <progress class="progress progress-secondary" value="91" max="100"></progress>
                    </div>
                </div>

                <div class="divider"></div>

                <div class="flex justify-between items-center">
                    <span class="text-sm opacity-70">Average Utilization</span>
                    <span class="text-2xl font-bold text-primary">87%</span>
                </div>
            </div>
        </div>
    </div>

    <!-- Available Reports -->
    <div class="card bg-base-100 shadow-lg border border-base-300/50">
        <div class="card-body">
            <h2 class="card-title mb-4">Available Reports</h2>

            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                <!-- Operations Report -->
                <div class="card bg-primary/5 border border-primary/20 hover:shadow-lg transition-all group cursor-pointer">
                    <div class="card-body p-4">
                        <div class="flex items-start justify-between mb-2">
                            <div class="w-12 h-12 rounded-lg bg-primary/20 flex items-center justify-center group-hover:scale-110 transition-transform">
                                <svg class="w-6 h-6 text-primary" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path>
                                </svg>
                            </div>
                            <div class="badge badge-primary badge-sm">Monthly</div>
                        </div>
                        <h3 class="font-bold text-lg">Operations Report</h3>
                        <p class="text-sm opacity-70 mt-1">Comprehensive overview of production operations and KPIs</p>
                        <div class="divider my-2"></div>
                        <div class="flex items-center justify-between text-xs">
                            <span class="opacity-70">Last Generated:</span>
                            <span class="font-medium">Today</span>
                        </div>
                        <button class="btn btn-primary btn-sm w-full mt-3 gap-2">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path>
                            </svg>
                            View Report
                        </button>
                    </div>
                </div>

                <!-- Financial Report -->
                <div class="card bg-success/5 border border-success/20 hover:shadow-lg transition-all group cursor-pointer">
                    <div class="card-body p-4">
                        <div class="flex items-start justify-between mb-2">
                            <div class="w-12 h-12 rounded-lg bg-success/20 flex items-center justify-center group-hover:scale-110 transition-transform">
                                <svg class="w-6 h-6 text-success" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                </svg>
                            </div>
                            <div class="badge badge-success badge-sm">Quarterly</div>
                        </div>
                        <h3 class="font-bold text-lg">Financial Summary</h3>
                        <p class="text-sm opacity-70 mt-1">Revenue, costs, and profitability analysis</p>
                        <div class="divider my-2"></div>
                        <div class="flex items-center justify-between text-xs">
                            <span class="opacity-70">Last Generated:</span>
                            <span class="font-medium">3 days ago</span>
                        </div>
                        <button class="btn btn-success btn-sm w-full mt-3 gap-2">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path>
                            </svg>
                            View Report
                        </button>
                    </div>
                </div>

                <!-- Maintenance Report -->
                <div class="card bg-warning/5 border border-warning/20 hover:shadow-lg transition-all group cursor-pointer">
                    <div class="card-body p-4">
                        <div class="flex items-start justify-between mb-2">
                            <div class="w-12 h-12 rounded-lg bg-warning/20 flex items-center justify-center group-hover:scale-110 transition-transform">
                                <svg class="w-6 h-6 text-warning" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"></path>
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                </svg>
                            </div>
                            <div class="badge badge-warning badge-sm">Weekly</div>
                        </div>
                        <h3 class="font-bold text-lg">Maintenance Log</h3>
                        <p class="text-sm opacity-70 mt-1">Equipment maintenance history and schedules</p>
                        <div class="divider my-2"></div>
                        <div class="flex items-center justify-between text-xs">
                            <span class="opacity-70">Last Generated:</span>
                            <span class="font-medium">Today</span>
                        </div>
                        <button class="btn btn-warning btn-sm w-full mt-3 gap-2">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path>
                            </svg>
                            View Report
                        </button>
                    </div>
                </div>

                <!-- Safety Report -->
                <div class="card bg-info/5 border border-info/20 hover:shadow-lg transition-all group cursor-pointer">
                    <div class="card-body p-4">
                        <div class="flex items-start justify-between mb-2">
                            <div class="w-12 h-12 rounded-lg bg-info/20 flex items-center justify-center group-hover:scale-110 transition-transform">
                                <svg class="w-6 h-6 text-info" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"></path>
                                </svg>
                            </div>
                            <div class="badge badge-info badge-sm">Monthly</div>
                        </div>
                        <h3 class="font-bold text-lg">Safety Compliance</h3>
                        <p class="text-sm opacity-70 mt-1">Safety incidents, audits, and compliance metrics</p>
                        <div class="divider my-2"></div>
                        <div class="flex items-center justify-between text-xs">
                            <span class="opacity-70">Last Generated:</span>
                            <span class="font-medium">2 days ago</span>
                        </div>
                        <button class="btn btn-info btn-sm w-full mt-3 gap-2">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path>
                            </svg>
                            View Report
                        </button>
                    </div>
                </div>

                <!-- Inventory Report -->
                <div class="card bg-secondary/5 border border-secondary/20 hover:shadow-lg transition-all group cursor-pointer">
                    <div class="card-body p-4">
                        <div class="flex items-start justify-between mb-2">
                            <div class="w-12 h-12 rounded-lg bg-secondary/20 flex items-center justify-center group-hover:scale-110 transition-transform">
                                <svg class="w-6 h-6 text-secondary" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"></path>
                                </svg>
                            </div>
                            <div class="badge badge-secondary badge-sm">Daily</div>
                        </div>
                        <h3 class="font-bold text-lg">Inventory Status</h3>
                        <p class="text-sm opacity-70 mt-1">Stock levels, turnover rates, and reorder alerts</p>
                        <div class="divider my-2"></div>
                        <div class="flex items-center justify-between text-xs">
                            <span class="opacity-70">Last Generated:</span>
                            <span class="font-medium">Today</span>
                        </div>
                        <button class="btn btn-secondary btn-sm w-full mt-3 gap-2">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path>
                            </svg>
                            View Report
                        </button>
                    </div>
                </div>

                <!-- Quality Control Report -->
                <div class="card bg-accent/5 border border-accent/20 hover:shadow-lg transition-all group cursor-pointer">
                    <div class="card-body p-4">
                        <div class="flex items-start justify-between mb-2">
                            <div class="w-12 h-12 rounded-lg bg-accent/20 flex items-center justify-center group-hover:scale-110 transition-transform">
                                <svg class="w-6 h-6 text-accent" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4"></path>
                                </svg>
                            </div>
                            <div class="badge badge-accent badge-sm">Weekly</div>
                        </div>
                        <h3 class="font-bold text-lg">Quality Control</h3>
                        <p class="text-sm opacity-70 mt-1">Quality metrics, defect rates, and inspections</p>
                        <div class="divider my-2"></div>
                        <div class="flex items-center justify-between text-xs">
                            <span class="opacity-70">Last Generated:</span>
                            <span class="font-medium">Yesterday</span>
                        </div>
                        <button class="btn btn-accent btn-sm w-full mt-3 gap-2">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path>
                            </svg>
                            View Report
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Recent Reports History -->
    <div class="card bg-base-100 shadow-lg border border-base-300/50">
        <div class="card-body">
            <h2 class="card-title mb-4">Recent Report History</h2>

            <div class="overflow-x-auto">
                <table class="table table-zebra">
                    <thead>
                        <tr>
                            <th>Report Name</th>
                            <th>Type</th>
                            <th>Generated By</th>
                            <th>Date</th>
                            <th>Size</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr class="hover">
                            <td class="font-medium">January Operations Summary</td>
                            <td><span class="badge badge-primary badge-sm">Operations</span></td>
                            <td>System Auto</td>
                            <td>Today, 8:00 AM</td>
                            <td>2.4 MB</td>
                            <td>
                                <div class="join">
                                    <button class="btn join-item btn-xs btn-ghost tooltip" data-tip="Download">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"></path>
                                        </svg>
                                    </button>
                                    <button class="btn join-item btn-xs btn-ghost tooltip" data-tip="View">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path>
                                        </svg>
                                    </button>
                                    <button class="btn join-item btn-xs btn-ghost tooltip" data-tip="Share">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8.684 13.342C8.886 12.938 9 12.482 9 12c0-.482-.114-.938-.316-1.342m0 2.684a3 3 0 110-2.684m0 2.684l6.632 3.316m-6.632-6l6.632-3.316m0 0a3 3 0 105.367-2.684 3 3 0 00-5.367 2.684zm0 9.316a3 3 0 105.368 2.684 3 3 0 00-5.368-2.684z"></path>
                                        </svg>
                                    </button>
                                </div>
                            </td>
                        </tr>
                        <tr class="hover">
                            <td class="font-medium">Q4 2024 Financial Report</td>
                            <td><span class="badge badge-success badge-sm">Financial</span></td>
                            <td>Admin User</td>
                            <td>3 days ago</td>
                            <td>5.8 MB</td>
                            <td>
                                <div class="join">
                                    <button class="btn join-item btn-xs btn-ghost">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"></path>
                                        </svg>
                                    </button>
                                    <button class="btn join-item btn-xs btn-ghost">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path>
                                        </svg>
                                    </button>
                                    <button class="btn join-item btn-xs btn-ghost">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8.684 13.342C8.886 12.938 9 12.482 9 12c0-.482-.114-.938-.316-1.342m0 2.684a3 3 0 110-2.684m0 2.684l6.632 3.316m-6.632-6l6.632-3.316m0 0a3 3 0 105.367-2.684 3 3 0 00-5.367 2.684zm0 9.316a3 3 0 105.368 2.684 3 3 0 00-5.368-2.684z"></path>
                                        </svg>
                                    </button>
                                </div>
                            </td>
                        </tr>
                        <tr class="hover">
                            <td class="font-medium">Weekly Maintenance Log</td>
                            <td><span class="badge badge-warning badge-sm">Maintenance</span></td>
                            <td>System Auto</td>
                            <td>Yesterday</td>
                            <td>1.2 MB</td>
                            <td>
                                <div class="join">
                                    <button class="btn join-item btn-xs btn-ghost">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"></path>
                                        </svg>
                                    </button>
                                    <button class="btn join-item btn-xs btn-ghost">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path>
                                        </svg>
                                    </button>
                                    <button class="btn join-item btn-xs btn-ghost">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8.684 13.342C8.886 12.938 9 12.482 9 12c0-.482-.114-.938-.316-1.342m0 2.684a3 3 0 110-2.684m0 2.684l6.632 3.316m-6.632-6l6.632-3.316m0 0a3 3 0 105.367-2.684 3 3 0 00-5.367 2.684zm0 9.316a3 3 0 105.368 2.684 3 3 0 00-5.368-2.684z"></path>
                                        </svg>
                                    </button>
                                </div>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection
