<!doctype html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" data-theme="light">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <!-- CSRF Token -->
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ config('app.name', 'Laravel') }} - @yield('title', 'Dashboard')</title>

    <!--
        Light ("light" — a light version of forest's green, see resources/css/app.css) is the
        default theme; dark ("forest") is opt-in via the header toggle or the Settings page,
        both of which write the same localStorage 'theme' key. This runs synchronously before
        any stylesheet so there's no flash of the wrong theme on load. window.setTheme is the
        single place that changes the theme anywhere in the app — the header toggle and
        settings/index.blade.php both call it.
    -->
    <script>
        (function () {
            window.setTheme = function (theme) {
                document.documentElement.setAttribute('data-theme', theme);
                try { localStorage.setItem('theme', theme); } catch (e) {}
                document.querySelectorAll('.js-theme-toggle').forEach(function (cb) {
                    cb.checked = theme === 'forest';
                });
            };

            var stored = 'light';
            try { stored = localStorage.getItem('theme') || 'light'; } catch (e) {}
            document.documentElement.setAttribute('data-theme', stored);
        })();
    </script>

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=inter:400,500,600,700&display=swap" rel="stylesheet" />

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
                                <img src="{{ asset('assets/images/logo-white.png') }}" alt="{{ $companyName }} Logo" class="h-5 w-auto object-contain scale-150">
                            </div>
                            <div>
                                <div class="text-lg font-bold bg-gradient-to-r from-primary to-secondary bg-clip-text text-transparent">
                                    {{ $companyName }}
                                </div>
                                <div class="text-xs opacity-70">LogXense Enterprise</div>
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

                        @module('buildings')
                        @can('buildings.view')
                        <li>
                            <a href="{{ route('buildings.index') }}" class="gap-3 {{ request()->is('buildings*') ? 'bg-primary text-primary-content font-semibold shadow-lg' : 'hover:bg-base-200' }}">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"></path>
                                </svg>
                                Buildings
                                <span class="badge badge-primary badge-sm">12</span>
                            </a>
                        </li>
                        @endcan
                        @endmodule

                        @can('machines.view')
                        <li>
                            <a href="{{ route('machines.index') }}" class="gap-3 {{ request()->is('machines*') ? 'bg-primary text-primary-content font-semibold shadow-lg' : 'hover:bg-base-200' }}">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"></path>
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                </svg>
                                Machines
                            </a>
                        </li>
                        @endcan

                        @can('machine-types.view')
                        <li>
                            <a href="{{ route('machine-types.index') }}" class="gap-3 {{ request()->is('machine-types*') ? 'bg-primary text-primary-content font-semibold shadow-lg' : 'hover:bg-base-200' }}">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 10.83V6a3 3 0 013-3z"></path>
                                </svg>
                                Machine Types
                            </a>
                        </li>
                        @endcan

        @can('work-orders.view')
        <li>
                            <a href="{{ route('work-orders.index') }}" class="gap-3 {{ request()->is('work-orders*') ? 'bg-primary text-primary-content font-semibold shadow-lg' : 'hover:bg-base-200' }}">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01"></path>
                                </svg>
                                Work Orders
                            </a>
                        </li>
                        @endcan

                        @can('alerts.view')
                        <li>
                            <a href="{{ route('alerts.index') }}" class="gap-3 {{ request()->is('alerts*') ? 'bg-primary text-primary-content font-semibold shadow-lg' : 'hover:bg-base-200' }}">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"></path>
                                </svg>
                                Alerts
                                @if($unreadAlertCount > 0)
                                    <span class="badge badge-error badge-sm">{{ $unreadAlertCount }}</span>
                                @endif
                            </a>
                        </li>
                        @endcan

                        @canany(['users.view', 'groups.view', 'roles.view', 'permissions.view'])
                        <div class="divider text-xs opacity-50 my-2">ACCESS CONTROL</div>

                        @can('users.view')
                        <li>
                            <a href="{{ route('users.index') }}" class="gap-3 {{ request()->is('users*') ? 'bg-primary text-primary-content font-semibold shadow-lg' : 'hover:bg-base-200' }}">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"></path>
                                </svg>
                                Users
                            </a>
                        </li>
                        @endcan

                        @can('groups.view')
                        <li>
                            <a href="{{ route('groups.index') }}" class="gap-3 {{ request()->is('groups*') ? 'bg-primary text-primary-content font-semibold shadow-lg' : 'hover:bg-base-200' }}">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                </svg>
                                Groups
                            </a>
                        </li>
                        @endcan

                        @can('roles.view')
                        <li>
                            <a href="{{ route('roles.index') }}" class="gap-3 {{ request()->is('roles*') ? 'bg-primary text-primary-content font-semibold shadow-lg' : 'hover:bg-base-200' }}">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"></path>
                                </svg>
                                Roles
                            </a>
                        </li>
                        @endcan

                        @can('permissions.view')
                        <li>
                            <a href="{{ route('permissions.index') }}" class="gap-3 {{ request()->is('permissions*') ? 'bg-primary text-primary-content font-semibold shadow-lg' : 'hover:bg-base-200' }}">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 7a2 2 0 012 2m4 0a6 6 0 01-7.743 5.743L11 17H9v2H7v2H4a1 1 0 01-1-1v-2.586a1 1 0 01.293-.707l5.964-5.964A6 6 0 1121 9z"></path>
                                </svg>
                                Permissions
                            </a>
                        </li>
                        @endcan
                        @endcanany

                        @canany(['maintenance.view', 'reports.view'])
                        <div class="divider text-xs opacity-50 my-2">SERVICES</div>

                        @can('maintenance.view')
                        <li>
                            <a href="{{ route('maintenance.index') }}" class="gap-3 {{ request()->is('maintenance*') ? 'bg-primary text-primary-content font-semibold shadow-lg' : 'hover:bg-base-200' }}">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"></path>
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                </svg>
                                Maintenance
                            </a>
                        </li>
                        @endcan

                        @can('reports.view')
                        <li>
                            <a href="{{ route('reports.index') }}" class="gap-3 {{ request()->is('reports*') ? 'bg-primary text-primary-content font-semibold shadow-lg' : 'hover:bg-base-200' }}">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path>
                                </svg>
                                Reports
                            </a>
                        </li>
                        @endcan
                        @endcanany

                        @can('settings.view')
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
                        @endcan
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
                                        <div class="text-xs opacity-70 truncate">{{ Auth::user()->getRoleNames()->first() ? ucfirst(Auth::user()->getRoleNames()->first()) : 'No role' }}</div>
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
                        {{--
                            Shared back button — only nested pages (show/create/edit/delete)
                            set @section('back', ...) with their logical parent route; index/
                            top-level pages (reached from the sidebar) set none, so no button
                            renders. See docs/08-frontend.md.
                        --}}
                        @hasSection('back')
                            <a href="@yield('back')" class="btn btn-ghost btn-sm gap-2 mb-4">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
                                </svg>
                                Back
                            </a>
                        @endif
                        @yield('content')
                    </div>
                </main>

                @include('layouts.footer')
            </div>
        </div>
    </div>

    @stack('scripts')
    <script>
        // Hide skeleton loader and show actual content. `.content-hidden`/`.content-visible`
        // only started actually doing anything once app.css was compiled for real (see
        // docs/08-frontend.md) — before that this ran the same way but had no visible effect.
        // Now that it does, a single window.load listener would strand the page on a stalled
        // image or a blocked CDN script (Alpine/jQuery/DataTables), since `load` waits on
        // every one of them. revealContent() is idempotent and called from three triggers —
        // whichever fires first wins, and the timeout guarantees the page can never stay
        // hidden indefinitely.
        function revealContent() {
            const loader = document.getElementById('content-loader');
            const actualContent = document.getElementById('actual-content');
            if (loader) loader.style.display = 'none';
            if (actualContent) {
                actualContent.classList.remove('content-hidden');
                actualContent.classList.add('content-visible');
            }
        }

        document.addEventListener('DOMContentLoaded', function() {
            setTimeout(revealContent, 300);
        });
        window.addEventListener('load', function() {
            setTimeout(revealContent, 300);
        });
        setTimeout(revealContent, 2000);
    </script>
</body>
</html>
