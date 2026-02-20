<!doctype html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" data-theme="forest">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>NextBuild BMS — @yield('title', 'Welcome')</title>
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=inter:400,500,600,700&display=swap" rel="stylesheet" />
    <link href="https://cdn.jsdelivr.net/npm/daisyui@5/themes.css" rel="stylesheet" type="text/css" />
    <link href="https://cdn.jsdelivr.net/npm/daisyui@5" rel="stylesheet" type="text/css" />
    <script src="https://cdn.jsdelivr.net/npm/@tailwindcss/browser@4"></script>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="min-h-screen bg-base-200 font-inter antialiased">

    <!-- Navbar -->
    <nav class="sticky top-0 z-50 backdrop-blur-xl bg-base-100/80 border-b border-base-300/40 shadow-sm">
        <div class="container mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex items-center justify-between h-16">
                <!-- Logo -->
                <a href="{{ url('/') }}" class="flex items-center gap-3 group">
                    <img src="{{ asset('assets/images/logo-white.png') }}"
                         alt="NextBuild BMS"
                         class="h-8 w-auto object-contain group-hover:opacity-80 transition-opacity duration-200"
                         onerror="this.onerror=null;this.src='{{ asset('assets/images/logo-black.png') }}'">
                    <div class="hidden sm:block">
                        <span class="text-sm font-bold leading-none block">NextBuild</span>
                        <span class="text-[10px] opacity-50 leading-none tracking-widest uppercase">Building Management</span>
                    </div>
                </a>

                <!-- Nav Actions -->
                <div class="flex items-center gap-2">
                    @guest
                        <a href="{{ route('login') }}" class="btn btn-ghost btn-sm font-medium">Sign In</a>
                        @if (Route::has('register'))
                            <a href="{{ route('register') }}" class="btn btn-primary btn-sm font-medium shadow-md">Get Started</a>
                        @endif
                    @else
                        <a href="{{ route('home') }}" class="btn btn-primary btn-sm font-medium shadow-md">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"></path>
                            </svg>
                            Dashboard
                        </a>
                    @endguest
                </div>
            </div>
        </div>
    </nav>

    <!-- Page Content -->
    <main class="min-h-[calc(100vh-4rem-10rem)] flex items-center justify-center pt-10 pb-24">
        <div class="container mx-auto px-4 sm:px-6 lg:px-8">
            <div class="animate-fadeIn flex justify-center items-center w-full">
                @yield('content')
            </div>
        </div>
    </main>

    <!-- Footer -->
    <footer class="footer footer-center bg-base-100 border-t border-base-300/40 px-4 sm:px-6 lg:px-8 py-10 gap-6">
        {{-- Logo + Wordmark --}}
        <aside class="flex flex-col items-center gap-2">
            <a href="{{ url('/') }}" class="flex items-center gap-3">
                <img src="{{ asset('assets/images/logo-white.png') }}"
                     alt="NextBuild BMS"
                     class="h-7 w-auto object-contain opacity-80"
                     onerror="this.onerror=null;this.src='{{ asset('assets/images/logo-black.png') }}'">
                <div class="text-left">
                    <span class="text-sm font-bold leading-none block">NextBuild</span>
                    <span class="text-[10px] opacity-40 leading-none tracking-widest uppercase">Building Management System</span>
                </div>
            </a>
            <p class="text-xs opacity-40 max-w-xs text-center">
                Purpose-built for manufacturing companies and the semiconductor industry.
            </p>
        </aside>

        {{-- Nav links --}}
        <nav class="flex flex-wrap justify-center gap-x-6 gap-y-2">
            <a href="#" class="link link-hover text-sm opacity-50 hover:opacity-100 hover:text-primary transition-all">About</a>
            <a href="#" class="link link-hover text-sm opacity-50 hover:opacity-100 hover:text-primary transition-all">Features</a>
            <a href="#" class="link link-hover text-sm opacity-50 hover:opacity-100 hover:text-primary transition-all">Contact</a>
            <a href="#" class="link link-hover text-sm opacity-50 hover:opacity-100 hover:text-primary transition-all">Privacy</a>
            <a href="#" class="link link-hover text-sm opacity-50 hover:opacity-100 hover:text-primary transition-all">Terms</a>
        </nav>

        {{-- Copyright --}}
        <aside>
            <p class="text-xs opacity-40">
                © {{ date('Y') }} NextBuild BMS. All rights reserved.
            </p>
        </aside>
    </footer>

    <style>
        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(12px); }
            to   { opacity: 1; transform: translateY(0); }
        }
        .animate-fadeIn { animation: fadeIn 0.4s ease-out; }
        body { font-family: 'Inter', sans-serif; }
    </style>
</body>
</html>
