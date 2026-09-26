@extends('layouts.guest.app')

@section('content')
<div class="w-full max-w-md">
    <div class="card bg-base-100 shadow-2xl border border-base-300/50">
        <div class="card-body p-6 sm:p-8">
            <div class="flex justify-center mb-4 sm:mb-6">
                <div class="w-16 h-16 sm:w-20 sm:h-20 rounded-full bg-gradient-to-br from-warning to-primary flex items-center justify-center shadow-lg">
                    <svg class="w-8 h-8 sm:w-10 sm:h-10 text-base-100" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"></path>
                    </svg>
                </div>
            </div>

            <h2 class="card-title justify-center text-2xl">{{ __('Confirm Password') }}</h2>
            <p class="text-center opacity-70 mb-6">
                {{ __('Please confirm your password before continuing.') }}
            </p>

            <form method="POST" action="{{ route('password.confirm') }}" class="space-y-5">
                @csrf

                <div class="flex flex-col">
                    <label class="label" for="password">
                        <span class="font-medium">{{ __('Password') }}</span>
                    </label>
                    <label class="input flex items-center gap-2 @error('password') input-error @enderror focus-within:input-primary transition-all">
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
                        <p class="text-error text-xs mt-0.5">{{ $message }}</p>
                    @enderror
                </div>

                <button type="submit" class="btn btn-primary w-full group shadow-lg hover:shadow-primary/50">
                    <span class="flex items-center gap-2">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                        {{ __('Confirm Password') }}
                    </span>
                </button>
            </form>

            @if (Route::has('password.request'))
                <div class="divider text-xs opacity-50">FORGOT YOUR PASSWORD?</div>

                <a href="{{ route('password.request') }}" class="btn btn-ghost btn-sm w-full">
                    {{ __('Reset Password') }}
                </a>
            @endif
        </div>
    </div>
</div>
@endsection
