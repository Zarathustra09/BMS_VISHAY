@extends('layouts.guest.app')

@section('content')
<div class="w-full max-w-6xl">
    <div class="card bg-base-100 shadow-2xl border border-base-300/50 overflow-hidden backdrop-blur-sm">
        <div class="grid lg:grid-cols-2">
            <!-- Left Side - Branding -->
            <div class="hidden lg:flex flex-col justify-between p-12 bg-gradient-to-br from-secondary/10 via-accent/5 to-primary/10 border-r border-base-300/50 relative overflow-hidden">
                <div class="absolute inset-0 bg-[url('data:image/svg+xml;base64,PHN2ZyB3aWR0aD0iNjAiIGhlaWdodD0iNjAiIHZpZXdCb3g9IjAgMCA2MCA2MCIgeG1sbnM9Imh0dHA6Ly93d3cudzMub3JnLzIwMDAvc3ZnIj48ZyBmaWxsPSJub25lIiBmaWxsLXJ1bGU9ImV2ZW5vZGQiPjxnIGZpbGw9IiMwMDAiIGZpbGwtb3BhY2l0eT0iMC4wMyI+PHBhdGggZD0iTTM2IDE0YzMuMzEgMCA2IDIuNjkgNiA2cy0yLjY5IDYtNiA2LTYtMi42OS02LTYgMi42OS02IDYtNnpNNiAzNGMzLjMxIDAgNiAyLjY5IDYgNnMtMi42OSA2LTYgNi02LTIuNjktNi02IDIuNjktNiA2LTZ6Ii8+PC9nPjwvZz48L3N2Zz4=')] opacity-50"></div>

                <div class="relative z-10">
                    <div class="flex items-center gap-3 group cursor-pointer">
                        <div class="w-14 h-14 rounded-2xl bg-gradient-to-br from-secondary to-accent flex items-center justify-center shadow-lg group-hover:shadow-secondary/50 transition-all duration-300 group-hover:scale-110">
                            <svg class="w-8 h-8 text-base-100" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z"></path>
                            </svg>
                        </div>
                        <div>
                            <div class="text-2xl font-bold bg-gradient-to-r from-secondary to-accent bg-clip-text text-transparent">
                                {{ config('app.name', 'Laravel') }}
                            </div>
                            <div class="text-sm opacity-70">Get started today</div>
                        </div>
                    </div>

                    <div class="mt-12 space-y-6">
                        <div class="flex items-start gap-4 group">
                            <div class="w-10 h-10 rounded-xl bg-primary/10 flex items-center justify-center group-hover:bg-primary/20 transition-all">
                                <svg class="w-5 h-5 text-primary" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"></path>
                                </svg>
                            </div>
                            <div>
                                <div class="font-semibold">Secure by Default</div>
                                <div class="text-sm opacity-70">Your data is encrypted and protected</div>
                            </div>
                        </div>
                        <div class="flex items-start gap-4 group">
                            <div class="w-10 h-10 rounded-xl bg-secondary/10 flex items-center justify-center group-hover:bg-secondary/20 transition-all">
                                <svg class="w-5 h-5 text-secondary" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                </svg>
                            </div>
                            <div>
                                <div class="font-semibold">Quick Setup</div>
                                <div class="text-sm opacity-70">Start in less than 2 minutes</div>
                            </div>
                        </div>
                        <div class="flex items-start gap-4 group">
                            <div class="w-10 h-10 rounded-xl bg-accent/10 flex items-center justify-center group-hover:bg-accent/20 transition-all">
                                <svg class="w-5 h-5 text-accent" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 10h4.764a2 2 0 011.789 2.894l-3.5 7A2 2 0 0115.263 21h-4.017c-.163 0-.326-.02-.485-.06L7 20m7-10V5a2 2 0 00-2-2h-.095c-.5 0-.905.405-.905.905 0 .714-.211 1.412-.608 2.006L7 11v9m7-10h-2M7 20H5a2 2 0 01-2-2v-6a2 2 0 012-2h2.5"></path>
                                </svg>
                            </div>
                            <div>
                                <div class="font-semibold">Free to Start</div>
                                <div class="text-sm opacity-70">No credit card required</div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="relative z-10">
                    <div class="alert alert-info">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                        <span class="text-sm">Join thousands of satisfied users worldwide</span>
                    </div>
                </div>
            </div>

            <!-- Right Side - Form -->
            <div class="p-8 lg:p-12">
                <div class="mb-8">
                    <h1 class="text-3xl font-bold tracking-tight">{{ __('Create Account') }}</h1>
                    <p class="mt-2 text-sm opacity-70">Fill in your details to get started</p>
                </div>

                <form method="POST" action="{{ route('register') }}" class="space-y-5">
                    @csrf

                    <!-- Name Input -->
                    <div class="form-control">
                        <label class="label" for="name">
                            <span class="label-text font-medium">{{ __('Full Name') }}</span>
                        </label>
                        <label class="input input-bordered flex items-center gap-2 @error('name') input-error @enderror focus-within:input-primary transition-all">
                            <svg class="w-4 h-4 opacity-70" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                            </svg>
                            <input
                                id="name"
                                type="text"
                                name="name"
                                value="{{ old('name') }}"
                                required
                                autocomplete="name"
                                autofocus
                                class="grow"
                                placeholder="John Doe"
                            />
                        </label>
                        @error('name')
                            <label class="label">
                                <span class="label-text-alt text-error">{{ $message }}</span>
                            </label>
                        @enderror
                    </div>

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
                                autocomplete="new-password"
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

                    <!-- Confirm Password Input -->
                    <div class="form-control">
                        <label class="label" for="password-confirm">
                            <span class="label-text font-medium">{{ __('Confirm Password') }}</span>
                        </label>
                        <label class="input input-bordered flex items-center gap-2 focus-within:input-primary transition-all">
                            <svg class="w-4 h-4 opacity-70" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"></path>
                            </svg>
                            <input
                                id="password-confirm"
                                type="password"
                                name="password_confirmation"
                                required
                                autocomplete="new-password"
                                class="grow"
                                placeholder="••••••••"
                            />
                        </label>
                    </div>

                    <!-- Submit Button -->
                    <button type="submit" class="btn btn-primary w-full group shadow-lg hover:shadow-primary/50 transition-all">
                        <span class="flex items-center gap-2">
                            {{ __('Create Account') }}
                            <svg class="w-4 h-4 group-hover:translate-x-1 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6"></path>
                            </svg>
                        </span>
                    </button>

                    <!-- Terms -->
                    <p class="text-xs text-center opacity-70">
                        By creating an account, you agree to our
                        <a href="#" class="link link-primary">Terms of Service</a>
                        and
                        <a href="#" class="link link-primary">Privacy Policy</a>
                    </p>

                    <!-- Divider -->
                    @if (Route::has('login'))
                        <div class="divider text-xs opacity-50">ALREADY REGISTERED?</div>

                        <!-- Login Link -->
                        <div class="text-center">
                            <span class="text-sm opacity-70">Already have an account?</span>
                            <a class="link link-primary link-hover ml-1 font-medium" href="{{ route('login') }}">
                                {{ __('Sign in') }}
                            </a>
                        </div>
                    @endif
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
