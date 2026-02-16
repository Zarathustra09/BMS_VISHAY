@extends('layouts.guest.app')

@section('content')
<div class="w-full max-w-md">
    <div class="card bg-base-100 shadow-2xl border border-base-300/50">
        <div class="card-body">
            <div class="flex justify-center mb-6">
                <div class="w-20 h-20 rounded-full bg-gradient-to-br from-success to-primary flex items-center justify-center shadow-lg">
                    <svg class="w-10 h-10 text-base-100" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"></path>
                    </svg>
                </div>
            </div>

            <h2 class="card-title justify-center text-2xl">{{ __('Reset Password') }}</h2>
            <p class="text-center opacity-70 mb-6">
                {{ __('Enter your new password below') }}
            </p>

            <form method="POST" action="{{ route('password.update') }}" class="space-y-5">
                @csrf

                <input type="hidden" name="token" value="{{ $token }}">

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
                            value="{{ $email ?? old('email') }}"
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

                <div class="form-control">
                    <label class="label" for="password">
                        <span class="label-text font-medium">{{ __('New Password') }}</span>
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

                <button type="submit" class="btn btn-primary w-full group shadow-lg hover:shadow-primary/50">
                    <span class="flex items-center gap-2">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                        {{ __('Reset Password') }}
                    </span>
                </button>
            </form>
        </div>
    </div>
</div>
@endsection
