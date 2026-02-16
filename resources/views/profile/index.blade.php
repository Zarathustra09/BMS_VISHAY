@extends('layouts.app')

@section('title', 'My Profile')

@section('breadcrumbs')
    <li>Profile</li>
@endsection

@section('content')
<div class="space-y-6">
    <!-- Page Header -->
    <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
        <div>
            <h1 class="text-3xl font-bold flex items-center gap-3">
                <div class="w-12 h-12 rounded-xl bg-gradient-to-br from-primary to-secondary flex items-center justify-center shadow-lg">
                    <svg class="w-7 h-7 text-base-100" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                    </svg>
                </div>
                My Profile
            </h1>
            <p class="text-sm opacity-70 mt-2">Manage your account information and preferences</p>
        </div>
        <div class="flex gap-2">
            <button class="btn btn-ghost gap-2">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path>
                </svg>
                Discard Changes
            </button>
            <button class="btn btn-primary gap-2 shadow-lg hover:shadow-primary/50">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                </svg>
                Save Changes
            </button>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- Profile Card -->
        <div class="lg:col-span-1">
            <div class="card bg-base-100 shadow-lg border border-base-300/50 sticky top-24">
                <div class="card-body">
                    <!-- Avatar Section -->
                    <div class="flex flex-col items-center">
                        <div class="avatar placeholder mb-4">
                            <div class="bg-gradient-to-br from-primary to-secondary text-primary-content rounded-full w-32 ring ring-primary ring-offset-base-100 ring-offset-2">
                                <span class="text-4xl font-bold">{{ substr(Auth::user()->name, 0, 2) }}</span>
                            </div>
                        </div>
                        <h3 class="text-2xl font-bold">{{ Auth::user()->name }}</h3>
                        <p class="text-sm opacity-70">{{ Auth::user()->email }}</p>
                        <div class="badge badge-primary badge-lg mt-3 gap-2">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"></path>
                            </svg>
                            System Administrator
                        </div>

                        <div class="divider"></div>

                        <button class="btn btn-outline btn-primary w-full gap-2">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 9a2 2 0 012-2h.93a2 2 0 001.664-.89l.812-1.22A2 2 0 0110.07 4h3.86a2 2 0 011.664.89l.812 1.22A2 2 0 0018.07 7H19a2 2 0 012 2v9a2 2 0 01-2 2H5a2 2 0 01-2-2V9z"></path>
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 13a3 3 0 11-6 0 3 3 0 016 0z"></path>
                            </svg>
                            Change Avatar
                        </button>
                    </div>

                    <div class="divider"></div>

                    <!-- Quick Stats -->
                    <div class="space-y-3">
                        <h4 class="font-bold text-sm opacity-70 uppercase">Account Overview</h4>

                        <div class="flex items-center justify-between">
                            <span class="text-sm opacity-70">Member Since</span>
                            <span class="font-medium">{{ Auth::user()->created_at->format('M Y') }}</span>
                        </div>

                        <div class="flex items-center justify-between">
                            <span class="text-sm opacity-70">Last Login</span>
                            <span class="font-medium">Today, 8:45 AM</span>
                        </div>

                        <div class="flex items-center justify-between">
                            <span class="text-sm opacity-70">Status</span>
                            <div class="badge badge-success badge-sm gap-1">
                                <span class="w-2 h-2 bg-success-content rounded-full animate-pulse"></span>
                                Active
                            </div>
                        </div>

                        <div class="flex items-center justify-between">
                            <span class="text-sm opacity-70">Two-Factor Auth</span>
                            <div class="badge badge-error badge-sm">Disabled</div>
                        </div>
                    </div>

                    <div class="divider"></div>

                    <!-- Activity Stats -->
                    <div class="stats stats-vertical shadow bg-base-200">
                        <div class="stat">
                            <div class="stat-title text-xs">Work Orders</div>
                            <div class="stat-value text-2xl text-primary">23</div>
                            <div class="stat-desc">Completed this month</div>
                        </div>

                        <div class="stat">
                            <div class="stat-title text-xs">Buildings Managed</div>
                            <div class="stat-value text-2xl text-secondary">12</div>
                            <div class="stat-desc">Across all facilities</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Profile Details -->
        <div class="lg:col-span-2 space-y-6">
            <!-- Personal Information -->
            <div class="card bg-base-100 shadow-lg border border-base-300/50">
                <div class="card-body">
                    <h2 class="card-title flex items-center gap-2">
                        <svg class="w-6 h-6 text-primary" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                        </svg>
                        Personal Information
                    </h2>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mt-4">
                        <div class="form-control">
                            <label class="label">
                                <span class="label-text font-medium">Full Name</span>
                            </label>
                            <input type="text" value="{{ Auth::user()->name }}" class="input input-bordered focus:input-primary" />
                        </div>

                        <div class="form-control">
                            <label class="label">
                                <span class="label-text font-medium">Email Address</span>
                            </label>
                            <input type="email" value="{{ Auth::user()->email }}" class="input input-bordered focus:input-primary" />
                        </div>

                        <div class="form-control">
                            <label class="label">
                                <span class="label-text font-medium">Phone Number</span>
                            </label>
                            <input type="tel" placeholder="+1 (555) 000-0000" class="input input-bordered focus:input-primary" />
                        </div>

                        <div class="form-control">
                            <label class="label">
                                <span class="label-text font-medium">Department</span>
                            </label>
                            <select class="select select-bordered focus:select-primary">
                                <option>Operations</option>
                                <option selected>Facilities Management</option>
                                <option>Maintenance</option>
                                <option>Quality Control</option>
                                <option>Administration</option>
                            </select>
                        </div>

                        <div class="form-control">
                            <label class="label">
                                <span class="label-text font-medium">Job Title</span>
                            </label>
                            <input type="text" value="Facilities Manager" class="input input-bordered focus:input-primary" />
                        </div>

                        <div class="form-control">
                            <label class="label">
                                <span class="label-text font-medium">Employee ID</span>
                            </label>
                            <input type="text" value="EMP-2024-156" class="input input-bordered" disabled />
                        </div>
                    </div>

                    <div class="divider"></div>

                    <div class="form-control">
                        <label class="label">
                            <span class="label-text font-medium">Bio</span>
                        </label>
                        <textarea class="textarea textarea-bordered focus:textarea-primary h-24" placeholder="Tell us about yourself...">Experienced facilities manager with 10+ years in manufacturing operations. Specialized in building maintenance, equipment management, and team coordination.</textarea>
                    </div>
                </div>
            </div>

            <!-- Work Information -->
            <div class="card bg-base-100 shadow-lg border border-base-300/50">
                <div class="card-body">
                    <h2 class="card-title flex items-center gap-2">
                        <svg class="w-6 h-6 text-primary" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 13.255A23.931 23.931 0 0112 15c-3.183 0-6.22-.62-9-1.745M16 6V4a2 2 0 00-2-2h-4a2 2 0 00-2 2v2m4 6h.01M5 20h14a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"></path>
                        </svg>
                        Work Information
                    </h2>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mt-4">
                        <div class="form-control">
                            <label class="label">
                                <span class="label-text font-medium">Primary Location</span>
                            </label>
                            <select class="select select-bordered focus:select-primary">
                                <option selected>Building A - Main Facility</option>
                                <option>Building B - Production Wing</option>
                                <option>Building C - R&D Center</option>
                                <option>Warehouse Complex</option>
                            </select>
                        </div>

                        <div class="form-control">
                            <label class="label">
                                <span class="label-text font-medium">Work Shift</span>
                            </label>
                            <select class="select select-bordered focus:select-primary">
                                <option>Morning Shift (6AM - 2PM)</option>
                                <option selected>Day Shift (8AM - 4PM)</option>
                                <option>Evening Shift (4PM - 12AM)</option>
                                <option>Night Shift (12AM - 8AM)</option>
                            </select>
                        </div>

                        <div class="form-control">
                            <label class="label">
                                <span class="label-text font-medium">Manager</span>
                            </label>
                            <input type="text" value="Sarah Johnson" class="input input-bordered" disabled />
                        </div>

                        <div class="form-control">
                            <label class="label">
                                <span class="label-text font-medium">Team Size</span>
                            </label>
                            <input type="text" value="8 Members" class="input input-bordered" disabled />
                        </div>
                    </div>

                    <div class="divider"></div>

                    <!-- Permissions & Access -->
                    <h3 class="font-bold mb-3">Permissions & Access</h3>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
                        <div class="form-control">
                            <label class="label cursor-pointer justify-start gap-3">
                                <input type="checkbox" class="checkbox checkbox-primary" checked disabled />
                                <span class="label-text">Building Access - All Facilities</span>
                            </label>
                        </div>
                        <div class="form-control">
                            <label class="label cursor-pointer justify-start gap-3">
                                <input type="checkbox" class="checkbox checkbox-primary" checked disabled />
                                <span class="label-text">Work Order Management</span>
                            </label>
                        </div>
                        <div class="form-control">
                            <label class="label cursor-pointer justify-start gap-3">
                                <input type="checkbox" class="checkbox checkbox-primary" checked disabled />
                                <span class="label-text">Equipment Control</span>
                            </label>
                        </div>
                        <div class="form-control">
                            <label class="label cursor-pointer justify-start gap-3">
                                <input type="checkbox" class="checkbox checkbox-primary" checked disabled />
                                <span class="label-text">Reporting & Analytics</span>
                            </label>
                        </div>
                        <div class="form-control">
                            <label class="label cursor-pointer justify-start gap-3">
                                <input type="checkbox" class="checkbox checkbox-primary" disabled />
                                <span class="label-text">Financial Reports</span>
                            </label>
                        </div>
                        <div class="form-control">
                            <label class="label cursor-pointer justify-start gap-3">
                                <input type="checkbox" class="checkbox checkbox-primary" checked disabled />
                                <span class="label-text">Team Management</span>
                            </label>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Security Settings -->
            <div class="card bg-base-100 shadow-lg border border-base-300/50">
                <div class="card-body">
                    <h2 class="card-title flex items-center gap-2">
                        <svg class="w-6 h-6 text-primary" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"></path>
                        </svg>
                        Security Settings
                    </h2>

                    <div class="space-y-4 mt-4">
                        <!-- Change Password -->
                        <div class="collapse collapse-arrow bg-base-200">
                            <input type="checkbox" />
                            <div class="collapse-title font-medium flex items-center gap-2">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 7a2 2 0 012 2m4 0a6 6 0 01-7.743 5.743L11 17H9v2H7v2H4a1 1 0 01-1-1v-2.586a1 1 0 01.293-.707l5.964-5.964A6 6 0 1121 9z"></path>
                                </svg>
                                Change Password
                            </div>
                            <div class="collapse-content">
                                <div class="space-y-3 mt-2">
                                    <div class="form-control">
                                        <label class="label">
                                            <span class="label-text">Current Password</span>
                                        </label>
                                        <input type="password" class="input input-bordered focus:input-primary" />
                                    </div>
                                    <div class="form-control">
                                        <label class="label">
                                            <span class="label-text">New Password</span>
                                        </label>
                                        <input type="password" class="input input-bordered focus:input-primary" />
                                    </div>
                                    <div class="form-control">
                                        <label class="label">
                                            <span class="label-text">Confirm New Password</span>
                                        </label>
                                        <input type="password" class="input input-bordered focus:input-primary" />
                                    </div>
                                    <button class="btn btn-primary btn-sm w-full">Update Password</button>
                                </div>
                            </div>
                        </div>

                        <!-- Two-Factor Authentication -->
                        <div class="collapse collapse-arrow bg-base-200">
                            <input type="checkbox" />
                            <div class="collapse-title font-medium flex items-center gap-2">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 18h.01M8 21h8a2 2 0 002-2V5a2 2 0 00-2-2H8a2 2 0 00-2 2v14a2 2 0 002 2z"></path>
                                </svg>
                                Two-Factor Authentication
                                <div class="badge badge-error badge-sm">Disabled</div>
                            </div>
                            <div class="collapse-content">
                                <div class="space-y-3 mt-2">
                                    <p class="text-sm opacity-70">Add an extra layer of security to your account by enabling two-factor authentication.</p>
                                    <div class="alert alert-info">
                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                        </svg>
                                        <span class="text-sm">You'll need an authenticator app to set this up.</span>
                                    </div>
                                    <button class="btn btn-success btn-sm w-full gap-2">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
                                        </svg>
                                        Enable 2FA
                                    </button>
                                </div>
                            </div>
                        </div>

                        <!-- Active Sessions -->
                        <div class="collapse collapse-arrow bg-base-200">
                            <input type="checkbox" />
                            <div class="collapse-title font-medium flex items-center gap-2">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.75 17L9 20l-1 1h8l-1-1-.75-3M3 13h18M5 17h14a2 2 0 002-2V5a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"></path>
                                </svg>
                                Active Sessions
                            </div>
                            <div class="collapse-content">
                                <div class="space-y-3 mt-2">
                                    <div class="card bg-base-100 border border-base-300">
                                        <div class="card-body p-3">
                                            <div class="flex items-start justify-between">
                                                <div class="flex items-center gap-3">
                                                    <div class="w-10 h-10 rounded-lg bg-success/20 flex items-center justify-center">
                                                        <svg class="w-5 h-5 text-success" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.75 17L9 20l-1 1h8l-1-1-.75-3M3 13h18M5 17h14a2 2 0 002-2V5a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"></path>
                                                        </svg>
                                                    </div>
                                                    <div>
                                                        <div class="font-medium">Windows PC • Chrome</div>
                                                        <div class="text-xs opacity-70">192.168.1.100 • Active Now</div>
                                                    </div>
                                                </div>
                                                <div class="badge badge-success badge-sm">Current</div>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="card bg-base-100 border border-base-300">
                                        <div class="card-body p-3">
                                            <div class="flex items-start justify-between">
                                                <div class="flex items-center gap-3">
                                                    <div class="w-10 h-10 rounded-lg bg-base-300 flex items-center justify-center">
                                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 18h.01M8 21h8a2 2 0 002-2V5a2 2 0 00-2-2H8a2 2 0 00-2 2v14a2 2 0 002 2z"></path>
                                                        </svg>
                                                    </div>
                                                    <div>
                                                        <div class="font-medium">iPhone • Safari</div>
                                                        <div class="text-xs opacity-70">192.168.1.105 • 2 hours ago</div>
                                                    </div>
                                                </div>
                                                <button class="btn btn-ghost btn-xs text-error">Revoke</button>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Notification Preferences -->
            <div class="card bg-base-100 shadow-lg border border-base-300/50">
                <div class="card-body">
                    <h2 class="card-title flex items-center gap-2">
                        <svg class="w-6 h-6 text-primary" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"></path>
                        </svg>
                        Notification Preferences
                    </h2>

                    <div class="space-y-3 mt-4">
                        <div class="form-control">
                            <label class="label cursor-pointer justify-start gap-3">
                                <input type="checkbox" class="toggle toggle-primary" checked />
                                <div>
                                    <span class="label-text font-medium">Email Notifications</span>
                                    <p class="text-xs opacity-70">Receive updates via email</p>
                                </div>
                            </label>
                        </div>

                        <div class="divider my-2"></div>

                        <div class="form-control">
                            <label class="label cursor-pointer justify-start gap-3">
                                <input type="checkbox" class="toggle toggle-primary" checked />
                                <div>
                                    <span class="label-text font-medium">Work Order Alerts</span>
                                    <p class="text-xs opacity-70">Notify when work orders are assigned to you</p>
                                </div>
                            </label>
                        </div>

                        <div class="divider my-2"></div>

                        <div class="form-control">
                            <label class="label cursor-pointer justify-start gap-3">
                                <input type="checkbox" class="toggle toggle-warning" checked />
                                <div>
                                    <span class="label-text font-medium">Maintenance Reminders</span>
                                    <p class="text-xs opacity-70">Get reminders for scheduled maintenance</p>
                                </div>
                            </label>
                        </div>

                        <div class="divider my-2"></div>

                        <div class="form-control">
                            <label class="label cursor-pointer justify-start gap-3">
                                <input type="checkbox" class="toggle toggle-error" checked />
                                <div>
                                    <span class="label-text font-medium">Critical Alerts</span>
                                    <p class="text-xs opacity-70">Immediate notifications for emergencies</p>
                                </div>
                            </label>
                        </div>

                        <div class="divider my-2"></div>

                        <div class="form-control">
                            <label class="label cursor-pointer justify-start gap-3">
                                <input type="checkbox" class="toggle toggle-info" />
                                <div>
                                    <span class="label-text font-medium">Weekly Reports</span>
                                    <p class="text-xs opacity-70">Summary of your activities every week</p>
                                </div>
                            </label>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Activity Log -->
            <div class="card bg-base-100 shadow-lg border border-base-300/50">
                <div class="card-body">
                    <h2 class="card-title flex items-center gap-2">
                        <svg class="w-6 h-6 text-primary" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                        Recent Activity
                    </h2>

                    <div class="space-y-3 mt-4">
                        <div class="flex items-start gap-3 p-3 rounded-lg hover:bg-base-200 transition-all">
                            <div class="w-10 h-10 rounded-lg bg-primary/10 flex items-center justify-center flex-shrink-0">
                                <svg class="w-5 h-5 text-primary" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"></path>
                                </svg>
                            </div>
                            <div class="flex-1">
                                <div class="font-medium">Completed Work Order #WO-2024-156</div>
                                <div class="text-xs opacity-70">CNC Machine maintenance completed</div>
                                <div class="text-xs opacity-50 mt-1">2 hours ago</div>
                            </div>
                        </div>

                        <div class="flex items-start gap-3 p-3 rounded-lg hover:bg-base-200 transition-all">
                            <div class="w-10 h-10 rounded-lg bg-success/10 flex items-center justify-center flex-shrink-0">
                                <svg class="w-5 h-5 text-success" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                </svg>
                            </div>
                            <div class="flex-1">
                                <div class="font-medium">Profile Updated</div>
                                <div class="text-xs opacity-70">Changed contact information</div>
                                <div class="text-xs opacity-50 mt-1">5 hours ago</div>
                            </div>
                        </div>

                        <div class="flex items-start gap-3 p-3 rounded-lg hover:bg-base-200 transition-all">
                            <div class="w-10 h-10 rounded-lg bg-warning/10 flex items-center justify-center flex-shrink-0">
                                <svg class="w-5 h-5 text-warning" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path>
                                </svg>
                            </div>
                            <div class="flex-1">
                                <div class="font-medium">Created Maintenance Alert</div>
                                <div class="text-xs opacity-70">Press Machine 05 requires attention</div>
                                <div class="text-xs opacity-50 mt-1">Yesterday</div>
                            </div>
                        </div>

                        <div class="flex items-start gap-3 p-3 rounded-lg hover:bg-base-200 transition-all">
                            <div class="w-10 h-10 rounded-lg bg-info/10 flex items-center justify-center flex-shrink-0">
                                <svg class="w-5 h-5 text-info" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                                </svg>
                            </div>
                            <div class="flex-1">
                                <div class="font-medium">Generated Monthly Report</div>
                                <div class="text-xs opacity-70">Operations summary for December 2024</div>
                                <div class="text-xs opacity-50 mt-1">2 days ago</div>
                            </div>
                        </div>
                    </div>

                    <button class="btn btn-ghost btn-sm w-full mt-4">View All Activity</button>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

