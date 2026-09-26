@extends('layouts.guest.app')

@section('content')
<div class="w-full max-w-md">
    <div class="card bg-base-100 shadow-2xl border border-base-300/50">
        <div class="card-body p-6 sm:p-8">
            <div class="flex justify-center mb-4 sm:mb-6">
                <div class="w-16 h-16 sm:w-20 sm:h-20 rounded-full bg-gradient-to-br from-warning to-error flex items-center justify-center shadow-lg">
                    <svg class="w-8 h-8 sm:w-10 sm:h-10 text-base-100" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"></path>
                    </svg>
                </div>
            </div>

            <h2 class="card-title justify-center text-2xl">{{ __('Verify Your Email') }}</h2>
            <p class="text-center opacity-70 mb-6">
                {{ __('Before proceeding, please check your email for a verification link.') }}
            </p>

            @if (session('resent'))
                <div class="alert alert-success">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                    <span>{{ __('A fresh verification link has been sent to your email address.') }}</span>
                </div>
            @endif

            <div class="card-actions justify-center mt-4">
                <form class="w-full" method="POST" action="{{ route('verification.resend') }}">
                    @csrf
                    <button type="submit" class="btn btn-primary w-full group shadow-lg hover:shadow-primary/50">
                        <span class="flex items-center gap-2">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path>
                            </svg>
                            {{ __('Resend Verification Email') }}
                        </span>
                    </button>
                </form>
            </div>

            <div class="divider text-xs opacity-50">OR</div>

            <form method="POST" action="{{ route('logout') }}">
                @csrf
                <button type="submit" class="btn btn-ghost btn-sm w-full">
                    {{ __('Logout') }}
                </button>
            </form>
        </div>
    </div>
</div>
@endsection
