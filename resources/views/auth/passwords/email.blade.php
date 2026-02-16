@extends('layouts.guest.app')

@section('content')
<div class="w-full max-w-md">
    <div class="card bg-base-100 shadow-2xl border border-base-300/50">
        <div class="card-body">
            <div class="flex justify-center mb-6">
                <div class="w-20 h-20 rounded-full bg-gradient-to-br from-info to-primary flex items-center justify-center shadow-lg">
                    <svg class="w-10 h-10 text-base-100" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 7a2 2 0 012 2m4 0a6 6 0 01-7.743 5.743L11 17H9v2H7v2H4a1 1 0 01-1-1v-2.586a1 1 0 01.293-.707l5.964-5.964A6 6 0 1121 9z"></path>
                    </svg>
                </div>
            </div>

            <h2 class="card-title justify-center text-2xl">{{ __('Reset Password') }}</h2>
            <p class="text-center opacity-70 mb-6">
                {{ __('Enter your email and we\'ll send you a reset link') }}
            </p>

            @if (session('status'))
                <div class="alert alert-success mb-4">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                    <span>{{ session('status') }}</span>
                </div>
            @endif

            <form method="POST" action="{{ route('password.email') }}" class="space-y-5">
                @csrf

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

                <button type="submit" class="btn btn-primary w-full group shadow-lg hover:shadow-primary/50">
                    <span class="flex items-center gap-2">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"></path>
                        </svg>
                        {{ __('Send Reset Link') }}
                    </span>
                </button>
            </form>

            <div class="divider text-xs opacity-50">REMEMBER YOUR PASSWORD?</div>

            <a href="{{ route('login') }}" class="btn btn-ghost btn-sm w-full">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 17l-5-5m0 0l5-5m-5 5h12"></path>
                </svg>
                {{ __('Back to Login') }}
            </a>
        </div>
    </div>
</div>
@endsection
