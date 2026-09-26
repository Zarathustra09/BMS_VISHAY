@extends('layouts.guest.app')

@section('title', 'Sign In')

@section('content')
<div class="w-full max-w-5xl">
    <div class="card bg-base-100 shadow-2xl border border-base-300/30 overflow-hidden rounded-2xl">
        <div class="grid lg:grid-cols-5">

            {{-- ── Left Panel (Branding) ──────────────────────────────────── --}}
            <div class="hidden lg:flex lg:col-span-2 flex-col justify-between p-10 bg-base-content text-base-100 relative overflow-hidden">
                {{-- Subtle grid pattern --}}
                <div class="absolute inset-0 opacity-[0.04]"
                     style="background-image: linear-gradient(currentColor 1px, transparent 1px), linear-gradient(90deg, currentColor 1px, transparent 1px); background-size: 32px 32px;">
                </div>
                {{-- Glowing orb --}}
                <div class="absolute -bottom-24 -left-24 w-72 h-72 bg-primary/20 rounded-full blur-3xl pointer-events-none"></div>

                <div class="relative z-10 space-y-8">
                    {{-- Logo + Wordmark --}}
                    <div class="space-y-1">
                        <img src="{{ asset('assets/images/logo-white.png') }}"
                             alt="NextBuild BMS"
                             class="h-9 w-auto object-contain"
                             onerror="this.onerror=null;this.src='{{ asset('assets/images/logo-black.png') }}'">
                        <p class="text-[10px] tracking-widest uppercase opacity-40 pl-0.5">Building Management System</p>
                    </div>

                    {{-- Headline --}}
                    <div class="space-y-2">
                        <h2 class="text-3xl font-bold leading-snug tracking-tight">
                            Welcome<br>back.
                        </h2>
                        <p class="text-sm opacity-60 leading-relaxed">
                            Sign in to manage your facilities, assets, and operations across your manufacturing floor.
                        </p>
                    </div>

                    {{-- Feature list --}}
                    <ul class="space-y-4 text-sm">
                        @foreach ([
                            ['icon' => 'M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4',
                              'label' => 'Facility & asset management'],
                            ['icon' => 'M9 3H5a2 2 0 00-2 2v4m6-6h10a2 2 0 012 2v4M9 3v18m0 0h10a2 2 0 002-2V9M9 21H5a2 2 0 01-2-2V9m0 0h18',
                              'label' => 'Semicon & manufacturing ready'],
                            ['icon' => 'M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z',
                              'label' => 'Enterprise-grade security & compliance'],
                        ] as $feature)
                            <li class="flex items-center gap-3 opacity-80">
                                <span class="flex-shrink-0 w-7 h-7 rounded-lg bg-primary/20 flex items-center justify-center">
                                    <svg class="w-3.5 h-3.5 text-primary" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="{{ $feature['icon'] }}"></path>
                                    </svg>
                                </span>
                                {{ $feature['label'] }}
                            </li>
                        @endforeach
                    </ul>
                </div>

                {{-- Bottom badge --}}
                <div class="relative z-10">
                    <div class="inline-flex items-center gap-2 bg-base-100/10 backdrop-blur-sm border border-base-100/20 rounded-full px-4 py-2 text-xs opacity-70">
                        <span class="w-2 h-2 rounded-full bg-success animate-pulse"></span>
                        All systems operational
                    </div>
                </div>
            </div>

            {{-- ── Right Panel (Form) ─────────────────────────────────────── --}}
            <div class="lg:col-span-3 p-6 sm:p-8 lg:p-12 flex flex-col justify-center">
                {{-- Mobile logo --}}
                <div class="lg:hidden mb-6 flex flex-col items-center gap-1">
                    @include('partials._brand-logo', ['class' => 'h-8 w-auto object-contain'])
                    <span class="text-[10px] tracking-widest uppercase opacity-40">Building Management System</span>
                </div>

                <div class="mb-6 sm:mb-8">
                    <h1 class="text-2xl font-bold tracking-tight">Sign in to your account</h1>
                    <p class="mt-1.5 text-sm opacity-60">Enter your credentials below to continue</p>
                </div>

                <form method="POST" action="{{ route('login') }}" class="space-y-4 sm:space-y-5">
                    @csrf

                    {{-- Email --}}
                    <div class="flex flex-col gap-1.5">
                        <label class="label py-0" for="email">
                            <span class="text-sm font-medium">Email address</span>
                        </label>
                        <label class="input flex items-center gap-2.5 @error('email') input-error @enderror">
                            <svg class="w-4 h-4 opacity-40 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"></path>
                            </svg>
                            <input id="email" type="email" name="email" value="{{ old('email') }}"
                                   required autocomplete="email" autofocus class="grow text-sm"
                                   placeholder="you@company.com" />
                        </label>
                        @error('email')
                            <p class="text-error text-xs mt-0.5">{{ $message }}</p>
                        @enderror
                    </div>

                    {{-- Password --}}
                    <div class="flex flex-col gap-1.5">
                        <div class="flex items-center justify-between">
                            <label class="label py-0" for="password">
                                <span class="text-sm font-medium">Password</span>
                            </label>
                            @if (Route::has('password.request'))
                                <a class="text-xs text-primary hover:underline" href="{{ route('password.request') }}">
                                    Forgot password?
                                </a>
                            @endif
                        </div>
                        <label class="input flex items-center gap-2.5 @error('password') input-error @enderror">
                            <svg class="w-4 h-4 opacity-40 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"></path>
                            </svg>
                            <input id="password" type="password" name="password"
                                   required autocomplete="current-password" class="grow text-sm"
                                   placeholder="••••••••" />
                        </label>
                        @error('password')
                            <p class="text-error text-xs mt-0.5">{{ $message }}</p>
                        @enderror
                    </div>

                    {{-- Remember me --}}
                    <div class="flex flex-col">
                        <label class="label cursor-pointer justify-start gap-3 py-0">
                            <input type="checkbox" name="remember" id="remember"
                                   class="checkbox checkbox-primary checkbox-sm"
                                   {{ old('remember') ? 'checked' : '' }} />
                            <span class="text-sm">Keep me signed in</span>
                        </label>
                    </div>

                    {{-- Submit --}}
                    <button type="submit" class="btn btn-primary w-full font-semibold shadow-lg shadow-primary/20">
                        Sign In
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 8l4 4m0 0l-4 4m4-4H3"></path>
                        </svg>
                    </button>

                    {{-- Register link --}}
                    @if (Route::has('register'))
                        <p class="text-center text-sm opacity-60 pt-1">
                            Don't have an account?
                            <a href="{{ route('register') }}" class="text-primary font-medium hover:underline ml-1">
                                Create one
                            </a>
                        </p>
                    @endif
                </form>
            </div>

        </div>
    </div>
</div>
@endsection
