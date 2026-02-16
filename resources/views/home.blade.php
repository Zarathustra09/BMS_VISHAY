@extends('layouts.app')

@section('title', 'Dashboard')

@section('content')
<div class="space-y-6">
    <!-- Welcome Section -->
    <div class="card bg-gradient-to-br from-primary/10 via-secondary/5 to-accent/10 border border-primary/20 shadow-lg">
        <div class="card-body">
            <div class="flex flex-col md:flex-row items-start md:items-center justify-between gap-4">
                <div>
                    <h1 class="text-3xl font-bold mb-2">Welcome back, {{ Auth::user()->name }}! 👋</h1>
                    <p class="text-sm opacity-70">Here's what's happening in your facilities today</p>
                </div>
                <div class="flex gap-2">
                    <button class="btn btn-primary gap-2 shadow-lg hover:shadow-primary/50">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
                        </svg>
                        New Work Order
                    </button>
                    <button class="btn btn-ghost gap-2">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                        </svg>
                        Export Report
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Key Metrics -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
        <!-- Production Efficiency -->
        <div class="stat bg-base-100 rounded-box shadow-lg border border-base-300/50 hover:shadow-xl transition-all group cursor-pointer">
            <div class="stat-figure text-primary">
                <div class="w-12 h-12 rounded-xl bg-primary/20 flex items-center justify-center group-hover:scale-110 transition-transform">
                    <svg class="w-7 h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"></path>
                    </svg>
                </div>
            </div>
            <div class="stat-title">Production Efficiency</div>
            <div class="stat-value text-primary">87.5%</div>
            <div class="stat-desc text-success">↗︎ 4.2% from last week</div>
        </div>

        <!-- Active Equipment -->
        <div class="stat bg-base-100 rounded-box shadow-lg border border-base-300/50 hover:shadow-xl transition-all group cursor-pointer">
            <div class="stat-figure text-success">
                <div class="w-12 h-12 rounded-xl bg-success/20 flex items-center justify-center group-hover:scale-110 transition-transform">
                    <svg class="w-7 h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                </div>
            </div>
            <div class="stat-title">Active Equipment</div>
            <div class="stat-value text-success">24/29</div>
            <div class="stat-desc">5 under maintenance</div>
        </div>

        <!-- Pending Work Orders -->
        <div class="stat bg-base-100 rounded-box shadow-lg border border-base-300/50 hover:shadow-xl transition-all group cursor-pointer">
            <div class="stat-figure text-warning">
                <div class="w-12 h-12 rounded-xl bg-warning/20 flex items-center justify-center group-hover:scale-110 transition-transform">
                    <svg class="w-7 h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"></path>
                    </svg>
                </div>
            </div>
            <div class="stat-title">Pending Work Orders</div>
            <div class="stat-value text-warning">23</div>
            <div class="stat-desc">6 urgent priority</div>
        </div>

        <!-- Monthly Revenue -->
        <div class="stat bg-base-100 rounded-box shadow-lg border border-base-300/50 hover:shadow-xl transition-all group cursor-pointer">
            <div class="stat-figure text-info">
                <div class="w-12 h-12 rounded-xl bg-info/20 flex items-center justify-center group-hover:scale-110 transition-transform">
                    <svg class="w-7 h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                </div>
            </div>
            <div class="stat-title">Monthly Revenue</div>
            <div class="stat-value text-info">$458K</div>
            <div class="stat-desc text-success">↗︎ $42K increase</div>
        </div>
    </div>

    <!-- Charts and Analytics -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- Production Overview Chart -->
        <div class="lg:col-span-2 card bg-base-100 shadow-lg border border-base-300/50">
            <div class="card-body">
                <div class="flex items-center justify-between mb-4">
                    <h2 class="card-title">Production Overview</h2>
                    <select class="select select-bordered select-sm">
                        <option>Last 7 Days</option>
                        <option selected>Last 30 Days</option>
                        <option>Last 90 Days</option>
                        <option>This Year</option>
                    </select>
                </div>

                <!-- Bar Chart Visualization -->
                <div class="h-64 flex items-end justify-around gap-2">
                    <div class="tooltip flex-1" data-tip="Mon: 850 units">
                        <div class="h-32 bg-gradient-to-t from-primary to-primary/50 rounded-t hover:from-primary hover:to-primary/70 transition-all cursor-pointer"></div>
                        <div class="text-center text-xs mt-2 opacity-70">Mon</div>
                    </div>
                    <div class="tooltip flex-1" data-tip="Tue: 920 units">
                        <div class="h-40 bg-gradient-to-t from-primary to-primary/50 rounded-t hover:from-primary hover:to-primary/70 transition-all cursor-pointer"></div>
                        <div class="text-center text-xs mt-2 opacity-70">Tue</div>
                    </div>
                    <div class="tooltip flex-1" data-tip="Wed: 780 units">
                        <div class="h-28 bg-gradient-to-t from-primary to-primary/50 rounded-t hover:from-primary hover:to-primary/70 transition-all cursor-pointer"></div>
                        <div class="text-center text-xs mt-2 opacity-70">Wed</div>
                    </div>
                    <div class="tooltip flex-1" data-tip="Thu: 1050 units">
                        <div class="h-52 bg-gradient-to-t from-primary to-primary/50 rounded-t hover:from-primary hover:to-primary/70 transition-all cursor-pointer"></div>
                        <div class="text-center text-xs mt-2 opacity-70">Thu</div>
                    </div>
                    <div class="tooltip flex-1" data-tip="Fri: 980 units">
                        <div class="h-44 bg-gradient-to-t from-primary to-primary/50 rounded-t hover:from-primary hover:to-primary/70 transition-all cursor-pointer"></div>
                        <div class="text-center text-xs mt-2 opacity-70">Fri</div>
                    </div>
                    <div class="tooltip flex-1" data-tip="Sat: 620 units">
                        <div class="h-24 bg-gradient-to-t from-base-300 to-base-300/50 rounded-t hover:from-base-300 hover:to-base-300/70 transition-all cursor-pointer"></div>
                        <div class="text-center text-xs mt-2 opacity-70">Sat</div>
                    </div>
                    <div class="tooltip flex-1" data-tip="Sun: 450 units">
                        <div class="h-20 bg-gradient-to-t from-base-300 to-base-300/50 rounded-t hover:from-base-300 hover:to-base-300/70 transition-all cursor-pointer"></div>
                        <div class="text-center text-xs mt-2 opacity-70">Sun</div>
                    </div>
                </div>

                <div class="flex justify-between items-center mt-4 pt-4 border-t border-base-300">
                    <div class="flex items-center gap-2">
                        <div class="w-3 h-3 rounded-full bg-primary"></div>
                        <span class="text-sm">Production Days</span>
                    </div>
                    <div class="flex items-center gap-2">
                        <div class="w-3 h-3 rounded-full bg-base-300"></div>
                        <span class="text-sm">Weekends</span>
                    </div>
                    <div class="stats stats-horizontal shadow">
                        <div class="stat py-2 px-4">
                            <div class="stat-title text-xs">Total Units</div>
                            <div class="stat-value text-lg">5,650</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Equipment Status -->
        <div class="card bg-base-100 shadow-lg border border-base-300/50">
            <div class="card-body">
                <h2 class="card-title mb-4">Equipment Status</h2>

                <div class="space-y-4">
                    <div>
                        <div class="flex justify-between text-sm mb-2">
                            <span class="font-medium">Operational</span>
                            <span class="text-success font-bold">82.8%</span>
                        </div>
                        <progress class="progress progress-success" value="82.8" max="100"></progress>
                    </div>

                    <div>
                        <div class="flex justify-between text-sm mb-2">
                            <span class="font-medium">Maintenance</span>
                            <span class="text-warning font-bold">10.3%</span>
                        </div>
                        <progress class="progress progress-warning" value="10.3" max="100"></progress>
                    </div>

                    <div>
                        <div class="flex justify-between text-sm mb-2">
                            <span class="font-medium">Offline</span>
                            <span class="text-error font-bold">6.9%</span>
                        </div>
                        <progress class="progress progress-error" value="6.9" max="100"></progress>
                    </div>
                </div>

                <div class="divider"></div>

                <div class="grid grid-cols-2 gap-3">
                    <div class="stat bg-success/10 rounded-lg p-3 border border-success/20">
                        <div class="stat-value text-2xl text-success">24</div>
                        <div class="stat-desc text-xs">Active Now</div>
                    </div>
                    <div class="stat bg-warning/10 rounded-lg p-3 border border-warning/20">
                        <div class="stat-value text-2xl text-warning">3</div>
                        <div class="stat-desc text-xs">In Maintenance</div>
                    </div>
                    <div class="stat bg-error/10 rounded-lg p-3 border border-error/20">
                        <div class="stat-value text-2xl text-error">2</div>
                        <div class="stat-desc text-xs">Offline</div>
                    </div>
                    <div class="stat bg-info/10 rounded-lg p-3 border border-info/20">
                        <div class="stat-value text-2xl text-info">29</div>
                        <div class="stat-desc text-xs">Total Units</div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Building Performance & Recent Alerts -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <!-- Building Performance -->
        <div class="card bg-base-100 shadow-lg border border-base-300/50">
            <div class="card-body">
                <h2 class="card-title">Building Performance</h2>

                <div class="space-y-3 mt-4">
                    <div class="flex items-center gap-3 p-3 rounded-lg bg-base-200 hover:bg-base-300 transition-all cursor-pointer">
                        <div class="radial-progress text-primary" style="--value:92; --size:3.5rem; --thickness:4px">
                            <span class="text-sm font-bold">92%</span>
                        </div>
                        <div class="flex-1">
                            <div class="font-medium">Building A - Main Facility</div>
                            <div class="text-xs opacity-70">12 machines • 156 employees</div>
                        </div>
                        <svg class="w-5 h-5 opacity-50" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
                        </svg>
                    </div>

                    <div class="flex items-center gap-3 p-3 rounded-lg bg-base-200 hover:bg-base-300 transition-all cursor-pointer">
                        <div class="radial-progress text-success" style="--value:88; --size:3.5rem; --thickness:4px">
                            <span class="text-sm font-bold">88%</span>
                        </div>
                        <div class="flex-1">
                            <div class="font-medium">Building B - Production Wing</div>
                            <div class="text-xs opacity-70">8 machines • 98 employees</div>
                        </div>
                        <svg class="w-5 h-5 opacity-50" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
                        </svg>
                    </div>

                    <div class="flex items-center gap-3 p-3 rounded-lg bg-base-200 hover:bg-base-300 transition-all cursor-pointer">
                        <div class="radial-progress text-warning" style="--value:76; --size:3.5rem; --thickness:4px">
                            <span class="text-sm font-bold">76%</span>
                        </div>
                        <div class="flex-1">
                            <div class="font-medium">Building C - R&D Center</div>
                            <div class="text-xs opacity-70">9 machines • 67 employees</div>
                        </div>
                        <svg class="w-5 h-5 opacity-50" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
                        </svg>
                    </div>
                </div>
            </div>
        </div>

        <!-- Recent Alerts -->
        <div class="card bg-base-100 shadow-lg border border-base-300/50">
            <div class="card-body">
                <div class="flex items-center justify-between mb-4">
                    <h2 class="card-title">Recent Alerts</h2>
                    <button class="btn btn-ghost btn-sm">View All</button>
                </div>

                <div class="space-y-3">
                    <div class="alert alert-error shadow-lg">
                        <div class="flex-1">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path>
                            </svg>
                            <div>
                                <div class="font-bold">CNC Machine 05 Failure</div>
                                <div class="text-xs">Building A • 2 hours ago</div>
                            </div>
                        </div>
                        <button class="btn btn-sm btn-ghost">Details</button>
                    </div>

                    <div class="alert alert-warning">
                        <div class="flex-1">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                            </svg>
                            <div>
                                <div class="font-bold">Maintenance Due</div>
                                <div class="text-xs">Press Machine 03 • Tomorrow</div>
                            </div>
                        </div>
                        <button class="btn btn-sm btn-ghost">Schedule</button>
                    </div>

                    <div class="alert alert-info">
                        <div class="flex-1">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                            </svg>
                            <div>
                                <div class="font-bold">New Work Order</div>
                                <div class="text-xs">Welding Robot 03 • 5 hours ago</div>
                            </div>
                        </div>
                        <button class="btn btn-sm btn-ghost">View</button>
                    </div>

                    <div class="alert alert-success">
                        <div class="flex-1">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                            </svg>
                            <div>
                                <div class="font-bold">Maintenance Completed</div>
                                <div class="text-xs">Conveyor Line 2 • Today</div>
                            </div>
                        </div>
                        <button class="btn btn-sm btn-ghost">Report</button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Recent Work Orders & Quick Actions -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- Recent Work Orders -->
        <div class="lg:col-span-2 card bg-base-100 shadow-lg border border-base-300/50">
            <div class="card-body">
                <div class="flex items-center justify-between mb-4">
                    <h2 class="card-title">Recent Work Orders</h2>
                    <button class="btn btn-ghost btn-sm gap-2">
                        View All
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
                        </svg>
                    </button>
                </div>

                <div class="overflow-x-auto">
                    <table class="table table-zebra">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Equipment</th>
                                <th>Type</th>
                                <th>Status</th>
                                <th>Priority</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr class="hover">
                                <td class="font-mono text-xs">#WO-156</td>
                                <td>
                                    <div class="font-medium">CNC Machine 01</div>
                                    <div class="text-xs opacity-70">Building A</div>
                                </td>
                                <td><span class="badge badge-primary badge-sm">Preventive</span></td>
                                <td><span class="badge badge-success badge-sm gap-1"><span class="w-2 h-2 bg-success-content rounded-full"></span>Completed</span></td>
                                <td><span class="badge badge-ghost badge-sm">Low</span></td>
                                <td>
                                    <button class="btn btn-ghost btn-xs">View</button>
                                </td>
                            </tr>
                            <tr class="hover">
                                <td class="font-mono text-xs">#WO-155</td>
                                <td>
                                    <div class="font-medium">Press Machine 05</div>
                                    <div class="text-xs opacity-70">Building B</div>
                                </td>
                                <td><span class="badge badge-warning badge-sm">Corrective</span></td>
                                <td><span class="badge badge-warning badge-sm gap-1"><svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"></path></svg>In Progress</span></td>
                                <td><span class="badge badge-warning badge-sm">Medium</span></td>
                                <td>
                                    <button class="btn btn-ghost btn-xs">View</button>
                                </td>
                            </tr>
                            <tr class="hover">
                                <td class="font-mono text-xs">#WO-154</td>
                                <td>
                                    <div class="font-medium">Welding Robot 03</div>
                                    <div class="text-xs opacity-70">Building C</div>
                                </td>
                                <td><span class="badge badge-error badge-sm">Emergency</span></td>
                                <td><span class="badge badge-error badge-sm gap-1"><svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>Overdue</span></td>
                                <td><span class="badge badge-error badge-sm">Urgent</span></td>
                                <td>
                                    <button class="btn btn-ghost btn-xs">View</button>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- Quick Actions -->
        <div class="card bg-base-100 shadow-lg border border-base-300/50">
            <div class="card-body">
                <h2 class="card-title mb-4">Quick Actions</h2>

                <div class="space-y-2">
                    <button class="btn btn-primary w-full justify-start gap-3">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
                        </svg>
                        Create Work Order
                    </button>

                    <button class="btn btn-outline btn-primary w-full justify-start gap-3">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                        </svg>
                        Schedule Maintenance
                    </button>

                    <button class="btn btn-outline btn-secondary w-full justify-start gap-3">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path>
                        </svg>
                        Generate Report
                    </button>

                    <button class="btn btn-outline btn-accent w-full justify-start gap-3">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"></path>
                        </svg>
                        Manage Tenants
                    </button>

                    <div class="divider"></div>

                    <div class="card bg-info/5 border border-info/20">
                        <div class="card-body p-4">
                            <h3 class="font-bold text-sm mb-2">System Status</h3>
                            <div class="space-y-2 text-xs">
                                <div class="flex justify-between">
                                    <span class="opacity-70">Server Status</span>
                                    <span class="badge badge-success badge-xs gap-1">
                                        <span class="w-1.5 h-1.5 bg-success-content rounded-full animate-pulse"></span>
                                        Online
                                    </span>
                                </div>
                                <div class="flex justify-between">
                                    <span class="opacity-70">Database</span>
                                    <span class="badge badge-success badge-xs">Healthy</span>
                                </div>
                                <div class="flex justify-between">
                                    <span class="opacity-70">Last Backup</span>
                                    <span class="font-medium">2 hours ago</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Occupancy Analytics -->
    <div class="card bg-base-100 shadow-lg border border-base-300/50">
        <div class="card-body">
            <div class="flex items-center justify-between mb-4">
                <h2 class="card-title">Facility Occupancy Analytics</h2>
                <div class="tabs tabs-boxed">
                    <a class="tab tab-active">Today</a>
                    <a class="tab">This Week</a>
                    <a class="tab">This Month</a>
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <div class="stat bg-gradient-to-br from-primary/10 to-secondary/10 rounded-lg border border-primary/20">
                    <div class="stat-figure text-primary">
                        <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"></path>
                        </svg>
                    </div>
                    <div class="stat-title">Total Occupancy</div>
                    <div class="stat-value text-primary">1,247</div>
                    <div class="stat-desc">Employees across all facilities</div>
                </div>

                <div class="stat bg-gradient-to-br from-success/10 to-accent/10 rounded-lg border border-success/20">
                    <div class="stat-figure text-success">
                        <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"></path>
                        </svg>
                    </div>
                    <div class="stat-title">Active Workstations</div>
                    <div class="stat-value text-success">892</div>
                    <div class="stat-desc text-success">↗︎ 72% utilization rate</div>
                </div>

                <div class="stat bg-gradient-to-br from-warning/10 to-error/10 rounded-lg border border-warning/20">
                    <div class="stat-figure text-warning">
                        <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                    </div>
                    <div class="stat-title">Peak Hour Load</div>
                    <div class="stat-value text-warning">94%</div>
                    <div class="stat-desc">10:00 AM - 11:00 AM</div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
