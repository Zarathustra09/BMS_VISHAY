@extends('layouts.guest.app')

@section('content')
<div class="w-full max-w-6xl">
    <div class="card bg-base-100 shadow-2xl border border-base-300/50 overflow-hidden backdrop-blur-sm">
        <div class="grid lg:grid-cols-2">
            <!-- Left Side - Branding -->
            <div class="hidden lg:flex flex-col justify-between p-12 bg-gradient-to-br from-primary/10 via-secondary/5 to-accent/10 border-r border-base-300/50 relative overflow-hidden">
                <div class="absolute inset-0 bg-[url('data:image/svg+xml;base64,PHN2ZyB3aWR0aD0iNjAiIGhlaWdodD0iNjAiIHZpZXdCb3g9IjAgMCA2MCA2MCIgeG1sbnM9Imh0dHA6Ly93d3cudzMub3JnLzIwMDAvc3ZnIj48ZyBmaWxsPSJub25lIiBmaWxsLXJ1bGU9ImV2ZW5vZGQiPjxnIGZpbGw9IiMwMDAiIGZpbGwtb3BhY2l0eT0iMC4wMyI+PHBhdGggZD0iTTM2IDE0YzMuMzEgMCA2IDIuNjkgNiA2cy0yLjY5IDYtNiA2LTYtMi42OS02LTYgMi42OS02IDYtNnpNNiAzNGMzLjMxIDAgNiAyLjY5IDYgNnMtMi42OSA2LTYgNi02LTIuNjktNi02IDIuNjktNiA2LTZ6Ii8+PC9nPjwvZz48L3N2Zz4=')] opacity-50"></div>

                <div class="relative z-10">
                    <div class="flex items-center gap-3 group cursor-pointer">
                        <div class="w-14 h-14 rounded-2xl bg-gradient-to-br from-primary to-secondary flex items-center justify-center shadow-lg group-hover:shadow-primary/50 transition-all duration-300 group-hover:scale-110">
                            <svg class="w-8 h-8 text-base-100" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"></path>
                            </svg>
                        </div>
                        <div>
                            <div class="text-2xl font-bold bg-gradient-to-r from-primary to-secondary bg-clip-text text-transparent">
                                {{ config('app.name', 'Laravel') }}
                            </div>
                            <div class="text-sm opacity-70">Welcome back!</div>
                        </div>
                    </div>

                    <div class="mt-12 space-y-6">
                        <div class="flex items-start gap-4 group">
                            <div class="w-10 h-10 rounded-xl bg-primary/10 flex items-center justify-center group-hover:bg-primary/20 transition-all">
                                <svg class="w-5 h-5 text-primary" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                                </svg>
                            </div>
                            <div>
                                <div class="font-semibold">Secure Authentication</div>
                                <div class="text-sm opacity-70">Industry-standard security protocols</div>
                            </div>
                        </div>
                        <div class="flex items-start gap-4 group">
                            <div class="w-10 h-10 rounded-xl bg-secondary/10 flex items-center justify-center group-hover:bg-secondary/20 transition-all">
                                <svg class="w-5 h-5 text-secondary" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"></path>
                                </svg>
                            </div>
                            <div>
                                <div class="font-semibold">Lightning Fast</div>
                                <div class="text-sm opacity-70">Optimized for performance</div>
                            </div>
                        </div>
                        <div class="flex items-start gap-4 group">
                            <div class="w-10 h-10 rounded-xl bg-accent/10 flex items-center justify-center group-hover:bg-accent/20 transition-all">
                                <svg class="w-5 h-5 text-accent" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21a4 4 0 01-4-4V5a2 2 0 012-2h4a2 2 0 012 2v12a4 4 0 01-4 4zm0 0h12a2 2 0 002-2v-4a2 2 0 00-2-2h-2.343M11 7.343l1.657-1.657a2 2 0 012.828 0l2.829 2.829a2 2 0 010 2.828l-8.486 8.485M7 17h.01"></path>
                                </svg>
                            </div>
                            <div>
                                <div class="font-semibold">Modern Design</div>
                                <div class="text-sm opacity-70">Beautiful & intuitive interface</div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="relative z-10">
                    <div class="stats bg-base-100/50 backdrop-blur-sm shadow-lg">
                        <div class="stat">
                            <div class="stat-value text-primary">99.9%</div>
                            <div class="stat-title">Uptime</div>
                        </div>
                        <div class="stat">
                            <div class="stat-value text-secondary">24/7</div>
                            <div class="stat-title">Support</div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Right Side - Form -->
            <div class="p-8 lg:p-12">
                <div class="mb-8">
                    <h1 class="text-3xl font-bold tracking-tight">{{ __('Sign In') }}</h1>
                    <p class="mt-2 text-sm opacity-70">Enter your credentials to access your account</p>
                </div>

                <form method="POST" action="{{ route('login') }}" class="space-y-6">
                    @csrf

                    <!-- Email Input -->
                    <div class="form-control">
                        <label class="label" for="email">
                            <span class="label-text font-medium">{{ __('Email Address') }}</span>
                        </label>
                        <label class="input input-bordered flex items-center gap-2 @error('email') input-error @enderror focus-within:input-primary transition-all">
                            <svg class="w-4 h-4 opacity-70" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 12a4 4 0 10-8 0 4 4 0 008 0zm0 0v1.5a2.5 2.5 0 005 0V12a9 9 0 10-9 9m4.5-1.206a8.959 8.959 0 01-4.5 1.207"></path>
                            </svg>
                            <input
                                id="email"
                                type="email"
                                name="email"
                                value="{{ old('email') }}"
                                required
                                autocomplete="email"
                                autofocus
                                class="grow"
                                placeholder="you@example.com"
                            />
                        </label>
                        @error('email')
                            <label class="label">
                                <span class="label-text-alt text-error">{{ $message }}</span>
                            </label>
                        @enderror
                    </div>

                    <!-- Password Input -->
                    <div class="form-control">
                        <label class="label" for="password">
                            <span class="label-text font-medium">{{ __('Password') }}</span>
                        </label>
                        <label class="input input-bordered flex items-center gap-2 @error('password') input-error @enderror focus-within:input-primary transition-all">
                            <svg class="w-4 h-4 opacity-70" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"></path>
                            </svg>
                            <input
                                id="password"
                                type="password"
                                name="password"
                                required
                                autocomplete="current-password"
                                class="grow"
                                placeholder="••••••••"
                            />
                        </label>
                        @error('password')
                            <label class="label">
                                <span class="label-text-alt text-error">{{ $message }}</span>
                            </label>
                        @enderror
                    </div>

                    <!-- Remember & Forgot Password -->
                    <div class="flex items-center justify-between">
                        <label class="label cursor-pointer justify-start gap-3 p-0">
                            <input
                                type="checkbox"
                                name="remember"
                                id="remember"
                                class="checkbox checkbox-primary checkbox-sm"
                                {{ old('remember') ? 'checked' : '' }}
                            />
                            <span class="label-text">{{ __('Remember me') }}</span>
                        </label>

                        @if (Route::has('password.request'))
                            <a class="link link-primary link-hover text-sm" href="{{ route('password.request') }}">
                                {{ __('Forgot password?') }}
                            </a>
                        @endif
                    </div>

                    <!-- Submit Button -->
                    <button type="submit" class="btn btn-primary w-full group shadow-lg hover:shadow-primary/50 transition-all">
                        <span class="flex items-center gap-2">
                            {{ __('Sign In') }}
                            <svg class="w-4 h-4 group-hover:translate-x-1 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6"></path>
                            </svg>
                        </span>
                    </button>

                    <!-- Divider -->
                    @if (Route::has('register'))
                        <div class="divider text-xs opacity-50">OR</div>

                        <!-- Register Link -->
                        <div class="text-center">
                            <span class="text-sm opacity-70">Don't have an account?</span>
                            <a class="link link-primary link-hover ml-1 font-medium" href="{{ route('register') }}">
                                {{ __('Create account') }}
                            </a>
                        </div>
                    @endif
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
