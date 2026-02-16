@extends('layouts.app')

@section('title', 'Maintenance')

@section('breadcrumbs')
    <li>Maintenance</li>
@endsection

@section('content')
<div class="space-y-6">
    <!-- Page Header -->
    <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
        <div>
            <h1 class="text-3xl font-bold flex items-center gap-3">
                <div class="w-12 h-12 rounded-xl bg-gradient-to-br from-primary to-secondary flex items-center justify-center shadow-lg">
                    <svg class="w-7 h-7 text-base-100" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"></path>
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                    </svg>
                </div>
                Maintenance Dashboard
            </h1>
            <p class="text-sm opacity-70 mt-2">Monitor equipment health and schedule preventive maintenance</p>
        </div>
        <div class="flex gap-2">
            <button class="btn btn-ghost gap-2">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                </svg>
                Export Report
            </button>
            <button class="btn btn-primary gap-2 shadow-lg hover:shadow-primary/50">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
                </svg>
                Schedule Maintenance
            </button>
        </div>
    </div>

    <!-- Stats Overview -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
        <div class="stat bg-base-100 rounded-box shadow-lg border border-base-300/50 hover:shadow-xl transition-all">
            <div class="stat-figure text-success">
                <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                </svg>
            </div>
            <div class="stat-title">Completed Today</div>
            <div class="stat-value text-success">12</div>
            <div class="stat-desc">↗︎ 8 more than yesterday</div>
        </div>

        <div class="stat bg-base-100 rounded-box shadow-lg border border-base-300/50 hover:shadow-xl transition-all">
            <div class="stat-figure text-warning">
                <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                </svg>
            </div>
            <div class="stat-title">Scheduled</div>
            <div class="stat-value text-warning">18</div>
            <div class="stat-desc">Next 7 days</div>
        </div>

        <div class="stat bg-base-100 rounded-box shadow-lg border border-base-300/50 hover:shadow-xl transition-all">
            <div class="stat-figure text-error">
                <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path>
                </svg>
            </div>
            <div class="stat-title">Overdue</div>
            <div class="stat-value text-error">3</div>
            <div class="stat-desc">Requires attention</div>
        </div>

        <div class="stat bg-base-100 rounded-box shadow-lg border border-base-300/50 hover:shadow-xl transition-all">
            <div class="stat-figure text-info">
                <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"></path>
                </svg>
            </div>
            <div class="stat-title">Avg Response Time</div>
            <div class="stat-value text-info text-2xl">2.3h</div>
            <div class="stat-desc">↘︎ 15% improvement</div>
        </div>
    </div>

    <!-- Quick Actions & Calendar -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- Calendar Card -->
        <div class="lg:col-span-2 card bg-base-100 shadow-lg border border-base-300/50">
            <div class="card-body">
                <div class="flex items-center justify-between mb-4">
                    <h2 class="card-title">Maintenance Calendar</h2>
                    <div class="flex gap-2">
                        <button class="btn btn-ghost btn-sm btn-circle">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path>
                            </svg>
                        </button>
                        <span class="text-lg font-semibold px-4 py-2">January 2025</span>
                        <button class="btn btn-ghost btn-sm btn-circle">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
                            </svg>
                        </button>
                    </div>
                </div>

                <!-- Calendar Grid -->
                <div class="grid grid-cols-7 gap-2">
                    <!-- Day Headers -->
                    <div class="text-center text-xs font-semibold opacity-70 py-2">Sun</div>
                    <div class="text-center text-xs font-semibold opacity-70 py-2">Mon</div>
                    <div class="text-center text-xs font-semibold opacity-70 py-2">Tue</div>
                    <div class="text-center text-xs font-semibold opacity-70 py-2">Wed</div>
                    <div class="text-center text-xs font-semibold opacity-70 py-2">Thu</div>
                    <div class="text-center text-xs font-semibold opacity-70 py-2">Fri</div>
                    <div class="text-center text-xs font-semibold opacity-70 py-2">Sat</div>

                    <!-- Calendar Days -->
                    <div class="aspect-square flex flex-col items-center justify-center rounded-lg opacity-50">
                        <span class="text-sm">29</span>
                    </div>
                    <div class="aspect-square flex flex-col items-center justify-center rounded-lg opacity-50">
                        <span class="text-sm">30</span>
                    </div>
                    <div class="aspect-square flex flex-col items-center justify-center rounded-lg opacity-50">
                        <span class="text-sm">31</span>
                    </div>
                    <div class="aspect-square flex flex-col items-center justify-center rounded-lg hover:bg-base-200 cursor-pointer transition-all">
                        <span class="text-sm">1</span>
                    </div>
                    <div class="aspect-square flex flex-col items-center justify-center rounded-lg hover:bg-base-200 cursor-pointer transition-all">
                        <span class="text-sm">2</span>
                    </div>
                    <div class="aspect-square flex flex-col items-center justify-center rounded-lg bg-primary/10 border border-primary/30 cursor-pointer relative">
                        <span class="text-sm font-bold">3</span>
                        <div class="absolute bottom-1 flex gap-0.5">
                            <span class="w-1 h-1 rounded-full bg-warning"></span>
                            <span class="w-1 h-1 rounded-full bg-success"></span>
                        </div>
                    </div>
                    <div class="aspect-square flex flex-col items-center justify-center rounded-lg hover:bg-base-200 cursor-pointer transition-all">
                        <span class="text-sm">4</span>
                    </div>

                    <!-- Week 2 -->
                    <div class="aspect-square flex flex-col items-center justify-center rounded-lg hover:bg-base-200 cursor-pointer transition-all">
                        <span class="text-sm">5</span>
                    </div>
                    <div class="aspect-square flex flex-col items-center justify-center rounded-lg hover:bg-base-200 cursor-pointer transition-all">
                        <span class="text-sm">6</span>
                    </div>
                    <div class="aspect-square flex flex-col items-center justify-center rounded-lg bg-error/10 border border-error/30 cursor-pointer relative">
                        <span class="text-sm font-bold">7</span>
                        <div class="absolute bottom-1 flex gap-0.5">
                            <span class="w-1 h-1 rounded-full bg-error"></span>
                        </div>
                    </div>
                    <div class="aspect-square flex flex-col items-center justify-center rounded-lg hover:bg-base-200 cursor-pointer transition-all">
                        <span class="text-sm">8</span>
                    </div>
                    <div class="aspect-square flex flex-col items-center justify-center rounded-lg hover:bg-base-200 cursor-pointer transition-all">
                        <span class="text-sm">9</span>
                    </div>
                    <div class="aspect-square flex flex-col items-center justify-center rounded-lg bg-success/10 border border-success/30 cursor-pointer relative">
                        <span class="text-sm font-bold">10</span>
                        <div class="absolute bottom-1 flex gap-0.5">
                            <span class="w-1 h-1 rounded-full bg-success"></span>
                            <span class="w-1 h-1 rounded-full bg-success"></span>
                        </div>
                    </div>
                    <div class="aspect-square flex flex-col items-center justify-center rounded-lg hover:bg-base-200 cursor-pointer transition-all">
                        <span class="text-sm">11</span>
                    </div>

                    <!-- Additional weeks... -->
                    @for($i = 12; $i <= 31; $i++)
                        <div class="aspect-square flex flex-col items-center justify-center rounded-lg hover:bg-base-200 cursor-pointer transition-all">
                            <span class="text-sm">{{ $i }}</span>
                        </div>
                    @endfor
                </div>

                <!-- Legend -->
                <div class="flex flex-wrap gap-4 mt-4 pt-4 border-t border-base-300/50">
                    <div class="flex items-center gap-2 text-sm">
                        <span class="w-3 h-3 rounded-full bg-success"></span>
                        <span class="opacity-70">Completed</span>
                    </div>
                    <div class="flex items-center gap-2 text-sm">
                        <span class="w-3 h-3 rounded-full bg-warning"></span>
                        <span class="opacity-70">Scheduled</span>
                    </div>
                    <div class="flex items-center gap-2 text-sm">
                        <span class="w-3 h-3 rounded-full bg-error"></span>
                        <span class="opacity-70">Overdue</span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Quick Stats -->
        <div class="space-y-4">
            <!-- Equipment Health Card -->
            <div class="card bg-base-100 shadow-lg border border-base-300/50">
                <div class="card-body">
                    <h3 class="card-title text-lg">Equipment Health</h3>
                    <div class="space-y-3 mt-4">
                        <div>
                            <div class="flex justify-between text-sm mb-1">
                                <span class="opacity-70">Excellent</span>
                                <span class="font-medium text-success">42%</span>
                            </div>
                            <progress class="progress progress-success" value="42" max="100"></progress>
                        </div>
                        <div>
                            <div class="flex justify-between text-sm mb-1">
                                <span class="opacity-70">Good</span>
                                <span class="font-medium text-primary">35%</span>
                            </div>
                            <progress class="progress progress-primary" value="35" max="100"></progress>
                        </div>
                        <div>
                            <div class="flex justify-between text-sm mb-1">
                                <span class="opacity-70">Fair</span>
                                <span class="font-medium text-warning">18%</span>
                            </div>
                            <progress class="progress progress-warning" value="18" max="100"></progress>
                        </div>
                        <div>
                            <div class="flex justify-between text-sm mb-1">
                                <span class="opacity-70">Poor</span>
                                <span class="font-medium text-error">5%</span>
                            </div>
                            <progress class="progress progress-error" value="5" max="100"></progress>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Technician Status -->
            <div class="card bg-base-100 shadow-lg border border-base-300/50">
                <div class="card-body">
                    <h3 class="card-title text-lg">Technician Status</h3>
                    <div class="space-y-3 mt-4">
                        <div class="flex items-center justify-between">
                            <div class="flex items-center gap-2">
                                <div class="avatar placeholder">
                                    <div class="bg-success text-success-content rounded-lg w-10">
                                        <span class="text-xs">MJ</span>
                                    </div>
                                </div>
                                <div>
                                    <div class="font-medium text-sm">Mike Johnson</div>
                                    <div class="text-xs opacity-70">Available</div>
                                </div>
                            </div>
                            <div class="badge badge-success badge-sm">Active</div>
                        </div>
                        <div class="flex items-center justify-between">
                            <div class="flex items-center gap-2">
                                <div class="avatar placeholder">
                                    <div class="bg-warning text-warning-content rounded-lg w-10">
                                        <span class="text-xs">SC</span>
                                    </div>
                                </div>
                                <div>
                                    <div class="font-medium text-sm">Sarah Chen</div>
                                    <div class="text-xs opacity-70">On Task</div>
                                </div>
                            </div>
                            <div class="badge badge-warning badge-sm">Busy</div>
                        </div>
                        <div class="flex items-center justify-between">
                            <div class="flex items-center gap-2">
                                <div class="avatar placeholder">
                                    <div class="bg-success text-success-content rounded-lg w-10">
                                        <span class="text-xs">DL</span>
                                    </div>
                                </div>
                                <div>
                                    <div class="font-medium text-sm">David Lee</div>
                                    <div class="text-xs opacity-70">Available</div>
                                </div>
                            </div>
                            <div class="badge badge-success badge-sm">Active</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Recent Maintenance Activities -->
    <div class="card bg-base-100 shadow-lg border border-base-300/50">
        <div class="card-body">
            <div class="flex items-center justify-between mb-4">
                <h2 class="card-title">Recent Maintenance Activities</h2>
                <div class="tabs tabs-boxed">
                    <a class="tab tab-active">All</a>
                    <a class="tab">Preventive</a>
                    <a class="tab">Corrective</a>
                    <a class="tab">Emergency</a>
                </div>
            </div>

            <div class="overflow-x-auto">
                <table class="table table-zebra">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Equipment</th>
                            <th>Type</th>
                            <th>Technician</th>
                            <th>Status</th>
                            <th>Date</th>
                            <th>Duration</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr class="hover">
                            <td class="font-mono text-sm">#M-2024-156</td>
                            <td>
                                <div class="flex items-center gap-2">
                                    <div class="w-8 h-8 rounded-lg bg-primary/10 flex items-center justify-center">
                                        <svg class="w-4 h-4 text-primary" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.663 17h4.673M12 3v1m6.364 1.636l-.707.707M21 12h-1M4 12H3m3.343-5.657l-.707-.707m2.828 9.9a5 5 0 117.072 0l-.548.547A3.374 3.374 0 0014 18.469V19a2 2 0 11-4 0v-.531c0-.895-.356-1.754-.988-2.386l-.548-.547z"></path>
                                        </svg>
                                    </div>
                                    <div>
                                        <div class="font-medium">CNC Machine 01</div>
                                        <div class="text-xs opacity-70">Building A</div>
                                    </div>
                                </div>
                            </td>
                            <td><span class="badge badge-primary badge-sm">Preventive</span></td>
                            <td>Mike Johnson</td>
                            <td><span class="badge badge-success gap-1"><span class="w-2 h-2 bg-success-content rounded-full"></span>Completed</span></td>
                            <td>Today, 9:30 AM</td>
                            <td>2.5 hours</td>
                            <td>
                                <div class="dropdown dropdown-end">
                                    <div tabindex="0" role="button" class="btn btn-ghost btn-xs">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 5v.01M12 12v.01M12 19v.01M12 6a1 1 0 110-2 1 1 0 010 2zm0 7a1 1 0 110-2 1 1 0 010 2zm0 7a1 1 0 110-2 1 1 0 010 2z"></path>
                                        </svg>
                                    </div>
                                    <ul tabindex="0" class="dropdown-content z-[1] menu p-2 shadow-lg bg-base-100 rounded-box w-52 border border-base-300/50">
                                        <li><a>View Details</a></li>
                                        <li><a>Download Report</a></li>
                                        <li><a>View History</a></li>
                                    </ul>
                                </div>
                            </td>
                        </tr>
                        <tr class="hover">
                            <td class="font-mono text-sm">#M-2024-155</td>
                            <td>
                                <div class="flex items-center gap-2">
                                    <div class="w-8 h-8 rounded-lg bg-warning/10 flex items-center justify-center">
                                        <svg class="w-4 h-4 text-warning" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"></path>
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                        </svg>
                                    </div>
                                    <div>
                                        <div class="font-medium">Press Machine 05</div>
                                        <div class="text-xs opacity-70">Building B</div>
                                    </div>
                                </div>
                            </td>
                            <td><span class="badge badge-warning badge-sm">Corrective</span></td>
                            <td>Sarah Chen</td>
                            <td><span class="badge badge-warning gap-1"><svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"></path></svg>In Progress</span></td>
                            <td>Today, 11:00 AM</td>
                            <td>1.5 hours</td>
                            <td>
                                <div class="dropdown dropdown-end">
                                    <div tabindex="0" role="button" class="btn btn-ghost btn-xs">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 5v.01M12 12v.01M12 19v.01M12 6a1 1 0 110-2 1 1 0 010 2zm0 7a1 1 0 110-2 1 1 0 010 2zm0 7a1 1 0 110-2 1 1 0 010 2z"></path>
                                        </svg>
                                    </div>
                                    <ul tabindex="0" class="dropdown-content z-[1] menu p-2 shadow-lg bg-base-100 rounded-box w-52 border border-base-300/50">
                                        <li><a>View Details</a></li>
                                        <li><a>Update Status</a></li>
                                        <li><a>Add Notes</a></li>
                                    </ul>
                                </div>
                            </td>
                        </tr>
                        <tr class="hover">
                            <td class="font-mono text-sm">#M-2024-154</td>
                            <td>
                                <div class="flex items-center gap-2">
                                    <div class="w-8 h-8 rounded-lg bg-error/10 flex items-center justify-center">
                                        <svg class="w-4 h-4 text-error" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path>
                                        </svg>
                                    </div>
                                    <div>
                                        <div class="font-medium">Welding Robot 03</div>
                                        <div class="text-xs opacity-70">Building C</div>
                                    </div>
                                </div>
                            </td>
                            <td><span class="badge badge-error badge-sm">Emergency</span></td>
                            <td>David Lee</td>
                            <td><span class="badge badge-error gap-1"><svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>Overdue</span></td>
                            <td>Yesterday</td>
                            <td>4.2 hours</td>
                            <td>
                                <div class="dropdown dropdown-end">
                                    <div tabindex="0" role="button" class="btn btn-ghost btn-xs">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 5v.01M12 12v.01M12 19v.01M12 6a1 1 0 110-2 1 1 0 010 2zm0 7a1 1 0 110-2 1 1 0 010 2zm0 7a1 1 0 110-2 1 1 0 010 2z"></path>
                                        </svg>
                                    </div>
                                    <ul tabindex="0" class="dropdown-content z-[1] menu p-2 shadow-lg bg-base-100 rounded-box w-52 border border-base-300/50">
                                        <li><a>View Details</a></li>
                                        <li><a>Escalate</a></li>
                                        <li><a>Request Parts</a></li>
                                    </ul>
                                </div>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Upcoming Scheduled Maintenance -->
    <div class="card bg-base-100 shadow-lg border border-base-300/50">
        <div class="card-body">
            <h2 class="card-title">Upcoming Scheduled Maintenance</h2>
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4 mt-4">
                <div class="card bg-warning/5 border border-warning/20 hover:shadow-lg transition-all">
                    <div class="card-body p-4">
                        <div class="flex items-start justify-between">
                            <div class="flex items-center gap-3">
                                <div class="w-12 h-12 rounded-lg bg-warning/20 flex items-center justify-center">
                                    <svg class="w-6 h-6 text-warning" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                    </svg>
                                </div>
                                <div>
                                    <h3 class="font-bold">Tomorrow</h3>
                                    <p class="text-sm opacity-70">9:00 AM</p>
                                </div>
                            </div>
                            <div class="badge badge-warning badge-sm">Due Soon</div>
                        </div>
                        <div class="divider my-2"></div>
                        <div class="space-y-1">
                            <p class="font-medium">Conveyor System Check</p>
                            <p class="text-sm opacity-70">Building A - Production Line 2</p>
                            <div class="flex items-center gap-2 mt-2">
                                <div class="avatar placeholder">
                                    <div class="bg-warning text-warning-content rounded w-6 text-xs">
                                        <span>MJ</span>
                                    </div>
                                </div>
                                <span class="text-xs">Assigned to Mike Johnson</span>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="card bg-primary/5 border border-primary/20 hover:shadow-lg transition-all">
                    <div class="card-body p-4">
                        <div class="flex items-start justify-between">
                            <div class="flex items-center gap-3">
                                <div class="w-12 h-12 rounded-lg bg-primary/20 flex items-center justify-center">
                                    <svg class="w-6 h-6 text-primary" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                                    </svg>
                                </div>
                                <div>
                                    <h3 class="font-bold">Jan 10</h3>
                                    <p class="text-sm opacity-70">2:00 PM</p>
                                </div>
                            </div>
                            <div class="badge badge-primary badge-sm">Scheduled</div>
                        </div>
                        <div class="divider my-2"></div>
                        <div class="space-y-1">
                            <p class="font-medium">HVAC System Service</p>
                            <p class="text-sm opacity-70">Building B - All Floors</p>
                            <div class="flex items-center gap-2 mt-2">
                                <div class="avatar placeholder">
                                    <div class="bg-primary text-primary-content rounded w-6 text-xs">
                                        <span>SC</span>
                                    </div>
                                </div>
                                <span class="text-xs">Assigned to Sarah Chen</span>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="card bg-info/5 border border-info/20 hover:shadow-lg transition-all">
                    <div class="card-body p-4">
                        <div class="flex items-start justify-between">
                            <div class="flex items-center gap-3">
                                <div class="w-12 h-12 rounded-lg bg-info/20 flex items-center justify-center">
                                    <svg class="w-6 h-6 text-info" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                                    </svg>
                                </div>
                                <div>
                                    <h3 class="font-bold">Jan 15</h3>
                                    <p class="text-sm opacity-70">10:30 AM</p>
                                </div>
                            </div>
                            <div class="badge badge-info badge-sm">Scheduled</div>
                        </div>
                        <div class="divider my-2"></div>
                        <div class="space-y-1">
                            <p class="font-medium">Safety Equipment Audit</p>
                            <p class="text-sm opacity-70">Building C - All Areas</p>
                            <div class="flex items-center gap-2 mt-2">
                                <div class="avatar placeholder">
                                    <div class="bg-info text-info-content rounded w-6 text-xs">
                                        <span>DL</span>
                                    </div>
                                </div>
                                <span class="text-xs">Assigned to David Lee</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
