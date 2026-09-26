@extends('layouts.app')

@section('title', 'Settings')

@section('breadcrumbs')
    <li>Settings</li>
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
                System Settings
            </h1>
            <p class="text-sm opacity-70 mt-2">Configure your Building Management System preferences</p>
        </div>
        <div class="flex gap-2">
            <button class="btn btn-ghost gap-2">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path>
                </svg>
                Reset to Defaults
            </button>
            <button class="btn btn-primary gap-2 shadow-lg hover:shadow-primary/50">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                </svg>
                Save Changes
            </button>
        </div>
    </div>

    <!-- Settings Navigation Tabs -->
    <div class="tabs tabs-boxed bg-base-100 shadow-lg border border-base-300/50 p-2">
        <a class="tab tab-active gap-2">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21a4 4 0 01-4-4V5a2 2 0 012-2h4a2 2 0 012 2v12a4 4 0 01-4 4zm0 0h12a2 2 0 002-2v-4a2 2 0 00-2-2h-2.343M11 7.343l1.657-1.657a2 2 0 012.828 0l2.829 2.829a2 2 0 010 2.828l-8.486 8.485M7 17h.01"></path>
            </svg>
            Appearance
        </a>
        <a class="tab gap-2">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"></path>
            </svg>
            Facilities
        </a>
        <a class="tab gap-2">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"></path>
            </svg>
            Notifications
        </a>
        <a class="tab gap-2">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"></path>
            </svg>
            Security
        </a>
        <a class="tab gap-2">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 7v10c0 2.21 3.582 4 8 4s8-1.79 8-4V7M4 7c0 2.21 3.582 4 8 4s8-1.79 8-4M4 7c0-2.21 3.582-4 8-4s8 1.79 8 4m0 5c0 2.21-3.582 4-8 4s-8-1.79-8-4"></path>
            </svg>
            System
        </a>
    </div>

    <!-- Theme Selection Section -->
    <div class="card bg-base-100 shadow-lg border border-base-300/50">
        <div class="card-body">
            <h2 class="card-title flex items-center gap-2">
                <svg class="w-6 h-6 text-primary" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21a4 4 0 01-4-4V5a2 2 0 012-2h4a2 2 0 012 2v12a4 4 0 01-4 4zm0 0h12a2 2 0 002-2v-4a2 2 0 00-2-2h-2.343M11 7.343l1.657-1.657a2 2 0 012.828 0l2.829 2.829a2 2 0 010 2.828l-8.486 8.485M7 17h.01"></path>
                </svg>
                Theme Selection
            </h2>
            <p class="text-sm opacity-70 mb-4">Choose your preferred color theme for the application</p>

            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-4">
                <!-- Forest Theme (dark) -->
                <div class="theme-option-card card bg-base-200 border border-base-300 hover:border-primary shadow-lg hover:shadow-xl transition-all cursor-pointer" data-theme-option="forest" onclick="changeTheme('forest')">
                    <div class="card-body p-4">
                        <div class="flex items-center justify-between mb-2">
                            <h3 class="font-bold">Forest</h3>
                            <div class="flex gap-1">
                                <div class="badge badge-ghost badge-sm">Dark</div>
                                <div class="badge badge-primary active-badge hidden">Active</div>
                            </div>
                        </div>
                        <div class="grid grid-cols-5 gap-1 h-8">
                            <div class="rounded bg-[#171e29]"></div>
                            <div class="rounded bg-[#1eb854]"></div>
                            <div class="rounded bg-[#1db886]"></div>
                            <div class="rounded bg-[#1e40af]"></div>
                            <div class="rounded bg-[#7b92b2]"></div>
                        </div>
                        <div class="flex items-center gap-2 mt-2">
                            <div class="w-4 h-4 rounded-full bg-success"></div>
                            <span class="text-xs opacity-70">Dark Green Theme</span>
                        </div>
                    </div>
                </div>

                <!-- Light Theme -->
                <div class="theme-option-card card bg-base-200 border border-base-300 hover:border-primary shadow-lg hover:shadow-xl transition-all cursor-pointer" data-theme-option="light" onclick="changeTheme('light')">
                    <div class="card-body p-4">
                        <div class="flex items-center justify-between mb-2">
                            <h3 class="font-bold">Light</h3>
                            <div class="badge badge-primary active-badge hidden">Active</div>
                        </div>
                        <div class="grid grid-cols-5 gap-1 h-8">
                            <div class="rounded bg-white"></div>
                            <div class="rounded bg-[#570df8]"></div>
                            <div class="rounded bg-[#f000b8]"></div>
                            <div class="rounded bg-[#37cdbe]"></div>
                            <div class="rounded bg-[#f3f4f6]"></div>
                        </div>
                        <div class="flex items-center gap-2 mt-2">
                            <div class="w-4 h-4 rounded-full bg-white border"></div>
                            <span class="text-xs opacity-70">Clean & Bright</span>
                        </div>
                    </div>
                </div>

                <!-- Dark Theme -->
                <div class="theme-option-card card bg-base-200 border border-base-300 hover:border-primary shadow-lg hover:shadow-xl transition-all cursor-pointer" data-theme-option="dark" onclick="changeTheme('dark')">
                    <div class="card-body p-4">
                        <div class="flex items-center justify-between mb-2">
                            <h3 class="font-bold">Dark</h3>
                            <div class="badge badge-primary active-badge hidden">Active</div>
                        </div>
                        <div class="grid grid-cols-5 gap-1 h-8">
                            <div class="rounded bg-[#1d232a]"></div>
                            <div class="rounded bg-[#661ae6]"></div>
                            <div class="rounded bg-[#d926aa]"></div>
                            <div class="rounded bg-[#1fb2a5]"></div>
                            <div class="rounded bg-[#191e24]"></div>
                        </div>
                        <div class="flex items-center gap-2 mt-2">
                            <div class="w-4 h-4 rounded-full bg-[#1d232a] border"></div>
                            <span class="text-xs opacity-70">Easy on Eyes</span>
                        </div>
                    </div>
                </div>

                <!-- Cupcake Theme -->
                <div class="theme-option-card card bg-base-200 border border-base-300 hover:border-primary shadow-lg hover:shadow-xl transition-all cursor-pointer" data-theme-option="cupcake" onclick="changeTheme('cupcake')">
                    <div class="card-body p-4">
                        <div class="flex items-center justify-between mb-2">
                            <h3 class="font-bold">Cupcake</h3>
                            <div class="badge badge-primary active-badge hidden">Active</div>
                        </div>
                        <div class="grid grid-cols-5 gap-1 h-8">
                            <div class="rounded bg-[#faf7f5]"></div>
                            <div class="rounded bg-[#65c3c8]"></div>
                            <div class="rounded bg-[#ef9fbc]"></div>
                            <div class="rounded bg-[#eeaf3a]"></div>
                            <div class="rounded bg-[#e0e7f1]"></div>
                        </div>
                        <div class="flex items-center gap-2 mt-2">
                            <div class="w-4 h-4 rounded-full bg-[#65c3c8]"></div>
                            <span class="text-xs opacity-70">Soft & Sweet</span>
                        </div>
                    </div>
                </div>

                <!-- Cyberpunk Theme -->
                <div class="theme-option-card card bg-base-200 border border-base-300 hover:border-primary shadow-lg hover:shadow-xl transition-all cursor-pointer" data-theme-option="cyberpunk" onclick="changeTheme('cyberpunk')">
                    <div class="card-body p-4">
                        <div class="flex items-center justify-between mb-2">
                            <h3 class="font-bold">Cyberpunk</h3>
                            <div class="badge badge-primary active-badge hidden">Active</div>
                        </div>
                        <div class="grid grid-cols-5 gap-1 h-8">
                            <div class="rounded bg-[#ffee00]"></div>
                            <div class="rounded bg-[#ff7598]"></div>
                            <div class="rounded bg-[#75d1f0]"></div>
                            <div class="rounded bg-[#c07eec]"></div>
                            <div class="rounded bg-[#ffb86b]"></div>
                        </div>
                        <div class="flex items-center gap-2 mt-2">
                            <div class="w-4 h-4 rounded-full bg-[#ff7598]"></div>
                            <span class="text-xs opacity-70">Neon Vibes</span>
                        </div>
                    </div>
                </div>

                <!-- Corporate Theme (app default) -->
                <div class="theme-option-card card bg-base-200 border border-base-300 hover:border-primary shadow-lg hover:shadow-xl transition-all cursor-pointer" data-theme-option="corporate" onclick="changeTheme('corporate')">
                    <div class="card-body p-4">
                        <div class="flex items-center justify-between mb-2">
                            <h3 class="font-bold">Corporate</h3>
                            <div class="flex gap-1">
                                <div class="badge badge-ghost badge-sm">Default</div>
                                <div class="badge badge-primary active-badge hidden">Active</div>
                            </div>
                        </div>
                        <div class="grid grid-cols-5 gap-1 h-8">
                            <div class="rounded bg-white"></div>
                            <div class="rounded bg-[#4b6bfb]"></div>
                            <div class="rounded bg-[#7b92b2]"></div>
                            <div class="rounded bg-[#67cba0]"></div>
                            <div class="rounded bg-[#e7eef6]"></div>
                        </div>
                        <div class="flex items-center gap-2 mt-2">
                            <div class="w-4 h-4 rounded-full bg-[#4b6bfb]"></div>
                            <span class="text-xs opacity-70">Professional</span>
                        </div>
                    </div>
                </div>

                <!-- Synthwave Theme -->
                <div class="theme-option-card card bg-base-200 border border-base-300 hover:border-primary shadow-lg hover:shadow-xl transition-all cursor-pointer" data-theme-option="synthwave" onclick="changeTheme('synthwave')">
                    <div class="card-body p-4">
                        <div class="flex items-center justify-between mb-2">
                            <h3 class="font-bold">Synthwave</h3>
                            <div class="badge badge-primary active-badge hidden">Active</div>
                        </div>
                        <div class="grid grid-cols-5 gap-1 h-8">
                            <div class="rounded bg-[#1a103d]"></div>
                            <div class="rounded bg-[#e779c1]"></div>
                            <div class="rounded bg-[#58c7f3]"></div>
                            <div class="rounded bg-[#f9cb28]"></div>
                            <div class="rounded bg-[#221551]"></div>
                        </div>
                        <div class="flex items-center gap-2 mt-2">
                            <div class="w-4 h-4 rounded-full bg-[#e779c1]"></div>
                            <span class="text-xs opacity-70">Retro Wave</span>
                        </div>
                    </div>
                </div>

                <!-- Business Theme -->
                <div class="theme-option-card card bg-base-200 border border-base-300 hover:border-primary shadow-lg hover:shadow-xl transition-all cursor-pointer" data-theme-option="business" onclick="changeTheme('business')">
                    <div class="card-body p-4">
                        <div class="flex items-center justify-between mb-2">
                            <h3 class="font-bold">Business</h3>
                            <div class="badge badge-primary active-badge hidden">Active</div>
                        </div>
                        <div class="grid grid-cols-5 gap-1 h-8">
                            <div class="rounded bg-[#1e293b]"></div>
                            <div class="rounded bg-[#1c4ed8]"></div>
                            <div class="rounded bg-[#7c3aed]"></div>
                            <div class="rounded bg-[#0891b2]"></div>
                            <div class="rounded bg-[#374151]"></div>
                        </div>
                        <div class="flex items-center gap-2 mt-2">
                            <div class="w-4 h-4 rounded-full bg-[#1c4ed8]"></div>
                            <span class="text-xs opacity-70">Formal Dark</span>
                        </div>
                    </div>
                </div>
            </div>

            <div class="alert alert-info mt-4">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                </svg>
                <span class="text-sm">Theme changes will be applied immediately and saved to your preferences.</span>
            </div>
        </div>
    </div>

    <!-- General Preferences -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <!-- Display Settings -->
        <div class="card bg-base-100 shadow-lg border border-base-300/50">
            <div class="card-body">
                <h2 class="card-title flex items-center gap-2">
                    <svg class="w-6 h-6 text-primary" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.75 17L9 20l-1 1h8l-1-1-.75-3M3 13h18M5 17h14a2 2 0 002-2V5a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"></path>
                    </svg>
                    Display Settings
                </h2>

                <div class="space-y-4 mt-4">
                    <div class="form-control">
                        <label class="label cursor-pointer justify-start gap-3">
                            <input type="checkbox" class="toggle toggle-primary" checked />
                            <div>
                                <span class="label-text font-medium">Compact Mode</span>
                                <p class="text-xs opacity-70">Show more information in less space</p>
                            </div>
                        </label>
                    </div>

                    <div class="divider my-2"></div>

                    <div class="form-control">
                        <label class="label cursor-pointer justify-start gap-3">
                            <input type="checkbox" class="toggle toggle-primary" />
                            <div>
                                <span class="label-text font-medium">Animations</span>
                                <p class="text-xs opacity-70">Enable smooth transitions and effects</p>
                            </div>
                        </label>
                    </div>

                    <div class="divider my-2"></div>

                    <div class="form-control">
                        <label class="label">
                            <span class="label-text font-medium">Default View</span>
                        </label>
                        <select class="select select-bordered w-full">
                            <option>Dashboard</option>
                            <option>Buildings</option>
                            <option>Machines</option>
                            <option>Work Orders</option>
                        </select>
                    </div>

                    <div class="form-control">
                        <label class="label">
                            <span class="label-text font-medium">Items Per Page</span>
                        </label>
                        <select class="select select-bordered w-full">
                            <option>10</option>
                            <option selected>25</option>
                            <option>50</option>
                            <option>100</option>
                        </select>
                    </div>
                </div>
            </div>
        </div>

        <!-- Localization -->
        <div class="card bg-base-100 shadow-lg border border-base-300/50">
            <div class="card-body">
                <h2 class="card-title flex items-center gap-2">
                    <svg class="w-6 h-6 text-primary" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5h12M9 3v2m1.048 9.5A18.022 18.022 0 016.412 9m6.088 9h7M11 21l5-10 5 10M12.751 5C11.783 10.77 8.07 15.61 3 18.129"></path>
                    </svg>
                    Localization
                </h2>

                <div class="space-y-4 mt-4">
                    <div class="form-control">
                        <label class="label">
                            <span class="label-text font-medium">Language</span>
                        </label>
                        <select class="select select-bordered w-full">
                            <option selected>English (US)</option>
                            <option>English (UK)</option>
                            <option>Spanish</option>
                            <option>French</option>
                            <option>German</option>
                            <option>Chinese</option>
                            <option>Japanese</option>
                        </select>
                    </div>

                    <div class="form-control">
                        <label class="label">
                            <span class="label-text font-medium">Timezone</span>
                        </label>
                        <select class="select select-bordered w-full">
                            <option selected>UTC-05:00 (Eastern Time)</option>
                            <option>UTC-06:00 (Central Time)</option>
                            <option>UTC-07:00 (Mountain Time)</option>
                            <option>UTC-08:00 (Pacific Time)</option>
                            <option>UTC+00:00 (UTC)</option>
                            <option>UTC+01:00 (CET)</option>
                        </select>
                    </div>

                    <div class="form-control">
                        <label class="label">
                            <span class="label-text font-medium">Date Format</span>
                        </label>
                        <select class="select select-bordered w-full">
                            <option selected>MM/DD/YYYY</option>
                            <option>DD/MM/YYYY</option>
                            <option>YYYY-MM-DD</option>
                            <option>DD.MM.YYYY</option>
                        </select>
                    </div>

                    <div class="form-control">
                        <label class="label">
                            <span class="label-text font-medium">Time Format</span>
                        </label>
                        <select class="select select-bordered w-full">
                            <option selected>12-hour (AM/PM)</option>
                            <option>24-hour</option>
                        </select>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Manufacturing Settings -->
    <div class="card bg-base-100 shadow-lg border border-base-300/50">
        <div class="card-body">
            <h2 class="card-title flex items-center gap-2">
                <svg class="w-6 h-6 text-primary" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"></path>
                </svg>
                Manufacturing Operations
            </h2>

            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mt-4">
                <div class="space-y-4">
                    <div class="form-control">
                        <label class="label cursor-pointer justify-start gap-3">
                            <input type="checkbox" class="toggle toggle-success" checked />
                            <div>
                                <span class="label-text font-medium">Real-time Monitoring</span>
                                <p class="text-xs opacity-70">Track equipment status live</p>
                            </div>
                        </label>
                    </div>

                    <div class="divider my-2"></div>

                    <div class="form-control">
                        <label class="label cursor-pointer justify-start gap-3">
                            <input type="checkbox" class="toggle toggle-warning" checked />
                            <div>
                                <span class="label-text font-medium">Maintenance Alerts</span>
                                <p class="text-xs opacity-70">Notify when maintenance is due</p>
                            </div>
                        </label>
                    </div>

                    <div class="divider my-2"></div>

                    <div class="form-control">
                        <label class="label cursor-pointer justify-start gap-3">
                            <input type="checkbox" class="toggle toggle-error" checked />
                            <div>
                                <span class="label-text font-medium">Downtime Tracking</span>
                                <p class="text-xs opacity-70">Log equipment downtime automatically</p>
                            </div>
                        </label>
                    </div>

                    <div class="divider my-2"></div>

                    <div class="form-control">
                        <label class="label">
                            <span class="label-text font-medium">Default Production Shift</span>
                        </label>
                        <select class="select select-bordered w-full">
                            <option>Morning Shift (6AM - 2PM)</option>
                            <option selected>Day Shift (8AM - 4PM)</option>
                            <option>Evening Shift (4PM - 12AM)</option>
                            <option>Night Shift (12AM - 8AM)</option>
                        </select>
                    </div>
                </div>

                <div class="space-y-4">
                    <div class="form-control">
                        <label class="label cursor-pointer justify-start gap-3">
                            <input type="checkbox" class="toggle toggle-info" checked />
                            <div>
                                <span class="label-text font-medium">Quality Control Checks</span>
                                <p class="text-xs opacity-70">Require QC approval for orders</p>
                            </div>
                        </label>
                    </div>

                    <div class="divider my-2"></div>

                    <div class="form-control">
                        <label class="label cursor-pointer justify-start gap-3">
                            <input type="checkbox" class="toggle toggle-primary" />
                            <div>
                                <span class="label-text font-medium">Auto-assign Work Orders</span>
                                <p class="text-xs opacity-70">Distribute tasks automatically</p>
                            </div>
                        </label>
                    </div>

                    <div class="divider my-2"></div>

                    <div class="form-control">
                        <label class="label">
                            <span class="label-text font-medium">Maintenance Frequency</span>
                        </label>
                        <select class="select select-bordered w-full">
                            <option>Daily</option>
                            <option selected>Weekly</option>
                            <option>Bi-weekly</option>
                            <option>Monthly</option>
                            <option>Quarterly</option>
                        </select>
                    </div>

                    <div class="form-control">
                        <label class="label">
                            <span class="label-text font-medium">Efficiency Alert Threshold</span>
                        </label>
                        <div class="flex items-center gap-2">
                            <input type="range" min="50" max="100" value="80" class="range range-primary flex-1" />
                            <span class="badge badge-primary">80%</span>
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

            <div class="overflow-x-auto mt-4">
                <table class="table">
                    <thead>
                        <tr>
                            <th>Notification Type</th>
                            <th class="text-center">Email</th>
                            <th class="text-center">In-App</th>
                            <th class="text-center">SMS</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td class="font-medium">Equipment Failure</td>
                            <td class="text-center"><input type="checkbox" class="checkbox checkbox-error" checked /></td>
                            <td class="text-center"><input type="checkbox" class="checkbox checkbox-error" checked /></td>
                            <td class="text-center"><input type="checkbox" class="checkbox checkbox-error" checked /></td>
                        </tr>
                        <tr>
                            <td class="font-medium">Maintenance Due</td>
                            <td class="text-center"><input type="checkbox" class="checkbox checkbox-warning" checked /></td>
                            <td class="text-center"><input type="checkbox" class="checkbox checkbox-warning" checked /></td>
                            <td class="text-center"><input type="checkbox" class="checkbox checkbox-warning" /></td>
                        </tr>
                        <tr>
                            <td class="font-medium">Work Order Completed</td>
                            <td class="text-center"><input type="checkbox" class="checkbox checkbox-success" checked /></td>
                            <td class="text-center"><input type="checkbox" class="checkbox checkbox-success" checked /></td>
                            <td class="text-center"><input type="checkbox" class="checkbox checkbox-success" /></td>
                        </tr>
                        <tr>
                            <td class="font-medium">Tenant Updates</td>
                            <td class="text-center"><input type="checkbox" class="checkbox checkbox-info" checked /></td>
                            <td class="text-center"><input type="checkbox" class="checkbox checkbox-info" /></td>
                            <td class="text-center"><input type="checkbox" class="checkbox checkbox-info" /></td>
                        </tr>
                        <tr>
                            <td class="font-medium">System Updates</td>
                            <td class="text-center"><input type="checkbox" class="checkbox checkbox-primary" checked /></td>
                            <td class="text-center"><input type="checkbox" class="checkbox checkbox-primary" checked /></td>
                            <td class="text-center"><input type="checkbox" class="checkbox checkbox-primary" /></td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Danger Zone -->
    <div class="card bg-error/5 border-2 border-error/20 shadow-lg">
        <div class="card-body">
            <h2 class="card-title flex items-center gap-2 text-error">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path>
                </svg>
                Danger Zone
            </h2>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mt-4">
                <div class="card bg-base-100">
                    <div class="card-body p-4">
                        <h3 class="font-bold">Clear All Data</h3>
                        <p class="text-sm opacity-70">Remove all cached data and reset preferences</p>
                        <button class="btn btn-outline btn-error btn-sm mt-2">Clear Data</button>
                    </div>
                </div>

                <div class="card bg-base-100">
                    <div class="card-body p-4">
                        <h3 class="font-bold">Export Data</h3>
                        <p class="text-sm opacity-70">Download all your system data</p>
                        <button class="btn btn-outline btn-warning btn-sm mt-2">Export Data</button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
// This page's picker offers all 8 DaisyUI themes, but shares the same 'theme' localStorage
// key and window.setTheme() function as the header's light/dark toggle (see
// layouts/app.blade.php) — picking anything here also updates what the header switch shows,
// and vice versa.
function changeTheme(theme) {
    window.setTheme(theme);
    markActiveTheme(theme);

    if (window.showToast) {
        window.showToast(`Theme changed to ${theme}`, 'success');
    }
}

function markActiveTheme(theme) {
    document.querySelectorAll('.theme-option-card').forEach(function (card) {
        var isActive = card.dataset.themeOption === theme;
        card.querySelector('.active-badge')?.classList.toggle('hidden', !isActive);
        card.classList.toggle('border-primary', isActive);
        card.classList.toggle('border-2', isActive);
    });
}

document.addEventListener('DOMContentLoaded', function () {
    var savedTheme = localStorage.getItem('theme') || 'corporate';
    markActiveTheme(savedTheme);
});
</script>
@endsection
