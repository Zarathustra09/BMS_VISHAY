<!-- Top Navigation Bar -->
<nav class="navbar bg-base-100/95 backdrop-blur-xl border-b border-base-300/50 sticky top-0 z-40 shadow-lg">
    <div class="flex-none lg:hidden">
        <label for="sidebar-drawer" class="btn btn-square btn-ghost">
            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
            </svg>
        </label>
    </div>

    <div class="flex-1 px-2 lg:px-4">
        <div class="flex items-center gap-3">

            <div class="breadcrumbs text-sm hidden md:block">
                <ul>
                    <li><a href="{{ route('home') }}">Manufacturing Dashboard</a></li>
                    @yield('breadcrumbs')
                </ul>

            </div>
            <div class="hidden lg:flex items-center gap-2 px-3 py-1 bg-success/10 rounded-lg border border-success/20">
            <span class="relative flex h-2 w-2">
                <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-success opacity-75"></span>
                <span class="relative inline-flex rounded-full h-2 w-2 bg-success"></span>
            </span>
                <span class="text-xs font-medium text-success">Production Active</span>
            </div>

        </div>

    </div>

    <div class="flex-none gap-2">
        <!-- Production Status -->

        <!-- Search -->
        <div class="form-control hidden sm:block">
            <label class="input input-bordered input-sm flex items-center gap-2 bg-base-200/50 focus-within:bg-base-200">
                <svg class="w-4 h-4 opacity-70" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                </svg>
                <input type="text" class="grow w-32 lg:w-48" placeholder="Search orders, parts..." />
            </label>
        </div>

        <!-- Quick Stats -->
        <div class="dropdown dropdown-end">
            <div tabindex="0" role="button" class="btn btn-ghost btn-circle">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path>
                </svg>
            </div>
            <div tabindex="0" class="dropdown-content z-[1] card card-compact w-80 mt-3 shadow-2xl bg-base-100 border border-base-300/50">
                <div class="card-body">
                    <h3 class="font-bold text-lg mb-3">Production Overview</h3>
                    <div class="grid grid-cols-2 gap-3">
                        <div class="stat bg-primary/10 rounded-lg p-3">
                            <div class="stat-value text-2xl text-primary">87%</div>
                            <div class="stat-desc">Efficiency</div>
                        </div>
                        <div class="stat bg-success/10 rounded-lg p-3">
                            <div class="stat-value text-2xl text-success">24</div>
                            <div class="stat-desc">Active Lines</div>
                        </div>
                        <div class="stat bg-warning/10 rounded-lg p-3">
                            <div class="stat-value text-2xl text-warning">3</div>
                            <div class="stat-desc">Pending Orders</div>
                        </div>
                        <div class="stat bg-info/10 rounded-lg p-3">
                            <div class="stat-value text-2xl text-info">156</div>
                            <div class="stat-desc">Units Today</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Alerts & Notifications -->
        <div class="dropdown dropdown-end">
            <div tabindex="0" role="button" class="btn btn-ghost btn-circle">
                <div class="indicator">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"></path>
                    </svg>
                    <span class="badge badge-xs badge-warning indicator-item"></span>
                </div>
            </div>
            <div tabindex="0" class="dropdown-content z-[1] card card-compact w-80 mt-3 shadow-2xl bg-base-100 border border-base-300/50">
                <div class="card-body">
                    <div class="flex items-center justify-between mb-2">
                        <h3 class="font-bold text-lg">Alerts & Notifications</h3>
                        <span class="badge badge-warning badge-sm">2 Alerts</span>
                    </div>
                    <div class="space-y-2 max-h-96 overflow-y-auto">
                        <div class="alert alert-warning">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path>
                            </svg>
                            <div class="text-sm">
                                <div class="font-semibold">Maintenance Required</div>
                                <div class="opacity-70">Production Line 3 - Scheduled in 2 hours</div>
                            </div>
                        </div>
                        <div class="alert alert-info">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                            </svg>
                            <div class="text-sm">
                                <div class="font-semibold">New Order Received</div>
                                <div class="opacity-70">Order #12456 - Priority: High</div>
                            </div>
                        </div>
                        <div class="alert alert-success">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                            </svg>
                            <div class="text-sm">
                                <div class="font-semibold">Quality Check Passed</div>
                                <div class="opacity-70">Batch #789 approved for shipment</div>
                            </div>
                        </div>
                    </div>
                    <div class="card-actions justify-end mt-2">
                        <button class="btn btn-sm btn-ghost">View All Alerts</button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Theme Toggle (light "corporate" ⇄ dark "forest"; see window.setTheme in layouts/app.blade.php) -->
        <label class="swap swap-rotate btn btn-ghost btn-circle" title="Toggle dark mode">
            <input type="checkbox" class="js-theme-toggle" onchange="window.setTheme(this.checked ? 'forest' : 'corporate')">
            <svg class="swap-off w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 3v1m0 16v1m9-9h-1M4 12H3m15.364 6.364l-.707-.707M6.343 6.343l-.707-.707m12.728 0l-.707.707M6.343 17.657l-.707.707M16 12a4 4 0 11-8 0 4 4 0 018 0z"></path>
            </svg>
            <svg class="swap-on w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20.354 15.354A9 9 0 018.646 3.646 9.003 9.003 0 0012 21a9.003 9.003 0 008.354-5.646z"></path>
            </svg>
        </label>

        <!-- User Menu -->
        <div class="dropdown dropdown-end">
            <div tabindex="0" role="button" class="btn btn-ghost gap-2">
                <div class="avatar placeholder">
                    <div class="bg-gradient-to-br from-primary to-secondary text-primary-content rounded-full w-8">
                        <span class="text-xs font-semibold">{{ substr(Auth::user()->name, 0, 2) }}</span>
                    </div>
                </div>
                <span class="hidden md:inline font-medium">{{ Auth::user()->name }}</span>
                <svg class="w-4 h-4 hidden md:inline" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                </svg>
            </div>
            <ul tabindex="0" class="dropdown-content z-[1] menu menu-sm mt-3 w-56 shadow-2xl bg-base-100 rounded-box border border-base-300/50">
                <li class="menu-title">
                    <span class="flex items-center gap-2">
                        <div class="avatar placeholder">
                            <div class="bg-gradient-to-br from-primary to-secondary text-primary-content rounded-full w-8">
                                <span class="text-xs">{{ substr(Auth::user()->name, 0, 2) }}</span>
                            </div>
                        </div>
                        <div>
                            <div class="font-semibold">{{ Auth::user()->name }}</div>
                            <div class="text-xs opacity-70">{{ Auth::user()->email }}</div>
                        </div>
                    </span>
                </li>
                <div class="divider my-1"></div>
                <li>
                    <a href="{{ route('profile.index') }}" class="gap-2">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                        </svg>
                        Profile
                    </a>
                </li>
                <li>
                    <a href="{{ route('settings.index') }}" class="gap-2">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"></path>
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                        </svg>
                        Settings
                    </a>
                </li>
                <div class="divider my-1"></div>
                <li>
                    <a href="{{ route('logout') }}"
                       onclick="event.preventDefault(); document.getElementById('logout-form').submit();"
                       class="gap-2 text-error">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"></path>
                        </svg>
                        Logout
                    </a>
                </li>
            </ul>
            <form id="logout-form" action="{{ route('logout') }}" method="POST" class="hidden">
                @csrf
            </form>
        </div>
    </div>
</nav>

<script>
    // The header markup is re-rendered on every page load, so the toggle's checked state
    // has to be synced to the already-applied theme (set earlier by the no-flash script in
    // layouts/app.blade.php) each time, rather than being knowable server-side.
    document.querySelectorAll('.js-theme-toggle').forEach(function (cb) {
        cb.checked = document.documentElement.getAttribute('data-theme') === 'forest';
    });
</script>

<div>
    <!-- Simplicity is the essence of happiness. - Cedric Bledsoe -->
</div>
