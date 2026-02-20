<!doctype html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" data-theme="forest">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <!-- CSRF Token -->
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ config('app.name', 'Laravel') }} - @yield('title', 'Dashboard')</title>

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=inter:400,500,600,700&display=swap" rel="stylesheet" />

    <link href="https://cdn.jsdelivr.net/npm/daisyui@5/themes.css" rel="stylesheet" type="text/css" />
    <link href="https://cdn.jsdelivr.net/npm/daisyui@5" rel="stylesheet" type="text/css" />
    <script src="https://cdn.jsdelivr.net/npm/@tailwindcss/browser@4"></script>

    <!-- Alpine.js for reactive components -->
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
    @stack('styles')

    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="min-h-screen bg-gradient-to-br from-base-300 via-base-200 to-base-300">
    <div id="app" class="min-h-screen">
        <div class="drawer lg:drawer-open">
            <input id="sidebar-drawer" type="checkbox" class="drawer-toggle" />

            <!-- Sidebar - Rendered First -->
            <div class="drawer-side z-50">
                <label for="sidebar-drawer" aria-label="close sidebar" class="drawer-overlay"></label>
                <aside class="min-h-screen w-72 bg-base-100 border-r border-base-300/50 flex flex-col">
                    <!-- Logo -->
                    <div class="p-4 border-b border-base-300/50">
                        <a href="{{ route('home') }}" class="flex items-center gap-3 group">
                            <div class="h-8 rounded-xl bg-gradient-to-br from-primary to-secondary flex items-center justify-center shadow-lg group-hover:shadow-primary/50 transition-all duration-300 group-hover:scale-105 overflow-hidden px-3">
                                <img src="{{ asset('assets/images/logo-white.png') }}" alt="BMS VISHAY Logo" class="h-5 w-auto object-contain scale-150">
                            </div>
                            <div>
                                <div class="text-lg font-bold bg-gradient-to-r from-primary to-secondary bg-clip-text text-transparent">
                                    BMS VISHAY
                                </div>
                                <div class="text-xs opacity-70">NextBuild - BMS</div>
                            </div>
                        </a>
                    </div>

                    <!-- Navigation Menu -->
                    <ul class="menu menu-lg p-4 flex-1 gap-1 overflow-y-auto">
                        <li>
                            <a href="{{ route('home') }}" class="gap-3 {{ request()->is('/') || request()->is('home') ? 'bg-primary text-primary-content font-semibold shadow-lg' : 'hover:bg-base-200' }}">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"></path>
                                </svg>
                                Dashboard
                            </a>
                        </li>

                        <div class="divider text-xs opacity-50 my-2">MANAGEMENT</div>

                        <li>
                            <a href="{{ route('buildings.index') }}" class="gap-3 {{ request()->is('buildings*') ? 'bg-primary text-primary-content font-semibold shadow-lg' : 'hover:bg-base-200' }}">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"></path>
                                </svg>
                                Buildings
                                <span class="badge badge-primary badge-sm">12</span>
                            </a>
                        </li>

                        <li>
                            <a href="{{ route('users.index') }}" class="gap-3 {{ request()->is('users*') ? 'bg-primary text-primary-content font-semibold shadow-lg' : 'hover:bg-base-200' }}" title="User Management — Roles & Permissions">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"></path>
                                </svg>
                                Users
                            </a>
                        </li>

                      <li>
                            <a href="{{ route('machines.index') }}" class="gap-3 {{ request()->is('machines*') ? 'bg-primary text-primary-content font-semibold shadow-lg' : 'hover:bg-base-200' }}">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"></path>
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                </svg>
                                Machines
                            </a>
                        </li>

                        <li>
                            <a href="{{ route('work-orders.index') }}" class="gap-3 {{ request()->is('work-orders*') ? 'bg-primary text-primary-content font-semibold shadow-lg' : 'hover:bg-base-200' }}">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01"></path>
                                </svg>
                                Work Orders
                            </a>
                        </li>

                        <div class="divider text-xs opacity-50 my-2">SERVICES</div>

                        <li>
                            <a href="{{ route('maintenance.index') }}" class="gap-3 {{ request()->is('maintenance*') ? 'bg-primary text-primary-content font-semibold shadow-lg' : 'hover:bg-base-200' }}">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"></path>
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                </svg>
                                Maintenance
                                <span class="badge badge-warning badge-sm">5</span>
                            </a>
                        </li>

                        <li>
                            <a href="{{ route('reports.index') }}" class="gap-3 {{ request()->is('reports*') ? 'bg-primary text-primary-content font-semibold shadow-lg' : 'hover:bg-base-200' }}">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path>
                                </svg>
                                Reports
                            </a>
                        </li>

                        <div class="divider text-xs opacity-50 my-2">SYSTEM</div>

                        <li>
                            <a href="{{ route('settings.index') }}" class="gap-3 {{ request()->is('settings*') ? 'bg-primary text-primary-content font-semibold shadow-lg' : 'hover:bg-base-200' }}">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"></path>
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                </svg>
                                Settings
                            </a>
                        </li>
                    </ul>

                    <!-- User Card -->
                    <div class="p-4 border-t border-base-300/50">
                        <div class="card bg-gradient-to-br from-primary/10 to-secondary/10 border border-primary/20">
                            <div class="card-body p-4">
                                <div class="flex items-center gap-3">
                                    <div class="avatar placeholder">
                                        <div class="bg-gradient-to-br from-primary to-secondary text-primary-content rounded-lg w-12">
                                            <span class="text-sm font-bold">{{ substr(Auth::user()->name, 0, 2) }}</span>
                                        </div>
                                    </div>
                                    <div class="flex-1 min-w-0">
                                        <div class="font-semibold truncate">{{ Auth::user()->name }}</div>
                                        <div class="text-xs opacity-70 truncate">Administrator</div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </aside>
            </div>

            <!-- Main Content Area - Rendered After Sidebar -->
            <div class="drawer-content flex flex-col">
                @include('layouts.header')

                <!-- Page Content with Loading State -->
                <main class="flex-1 p-4 lg:p-6 animate-slide-in">
                    <div id="content-loader" class="content-loading">
                        @include('components.skeleton-loader')
                    </div>
                    <div id="actual-content" class="content-hidden">
                        @yield('content')
                    </div>
                </main>

                @include('layouts.footer')
            </div>
        </div>
    </div>

    @stack('scripts')
    <script>
        // Hide skeleton loader and show actual content when page is fully loaded
        window.addEventListener('load', function() {
            setTimeout(function() {
                document.getElementById('content-loader').style.display = 'none';
                const actualContent = document.getElementById('actual-content');
                actualContent.classList.remove('content-hidden');
                actualContent.classList.add('content-visible');
            }, 300);
        });
    </script>
</body>
</html>
