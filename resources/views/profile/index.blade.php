@extends('layouts.app')

@section('title', 'My Profile')

@section('breadcrumbs')
    <li>Profile</li>
@endsection

@section('content')
<div class="space-y-6">
    @if(session('success'))
    <div class="alert alert-success shadow-lg animate-fadeIn">
        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
        </svg>
        <span>{{ session('success') }}</span>
    </div>
    @endif

    <!-- Page Header -->
    <div>
        <h1 class="text-3xl font-bold flex items-center gap-3">
            <div class="w-12 h-12 rounded-xl bg-gradient-to-br from-primary to-secondary flex items-center justify-center shadow-lg">
                <svg class="w-7 h-7 text-base-100" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                </svg>
            </div>
            My Profile
        </h1>
        <p class="text-sm opacity-70 mt-2">Manage your account information and password</p>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- Profile Card -->
        <div class="lg:col-span-1">
            <div class="card bg-base-100 shadow-lg border border-base-300/50 sticky top-24">
                <div class="card-body">
                    <div class="flex flex-col items-center">
                        <div class="avatar placeholder mb-4">
                            <div class="bg-gradient-to-br from-primary to-secondary text-primary-content rounded-full w-32 ring ring-primary ring-offset-base-100 ring-offset-2">
                                <span class="text-4xl font-bold">{{ substr(Auth::user()->name, 0, 2) }}</span>
                            </div>
                        </div>
                        <h3 class="text-2xl font-bold">{{ Auth::user()->name }}</h3>
                        <p class="text-sm opacity-70">{{ Auth::user()->email }}</p>
                        @foreach(Auth::user()->roles as $role)
                            <div class="badge badge-primary badge-lg mt-3 gap-2 capitalize">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"></path>
                                </svg>
                                {{ $role->name }}
                            </div>
                        @endforeach

                        <div class="divider"></div>

                        <div class="w-full space-y-3">
                            <div class="flex items-center justify-between">
                                <span class="text-sm opacity-70">Member Since</span>
                                <span class="font-medium">{{ Auth::user()->created_at->format('M Y') }}</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Profile Details -->
        <div class="lg:col-span-2 space-y-6">
            <!-- Personal Information -->
            <div class="card bg-base-100 shadow-lg border border-base-300/50">
                <div class="card-body">
                    <h2 class="card-title flex items-center gap-2">
                        <svg class="w-6 h-6 text-primary" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                        </svg>
                        Personal Information
                    </h2>

                    <form action="{{ route('profile.update') }}" method="POST" class="mt-4">
                        @csrf
                        @method('PUT')
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div class="form-control">
                                <label class="label">
                                    <span class="label-text font-medium">Full Name</span>
                                </label>
                                <input type="text" name="name" value="{{ old('name', Auth::user()->name) }}"
                                       class="input input-bordered focus:input-primary @error('name') input-error @enderror" required />
                                @error('name')<span class="text-error text-xs mt-1">{{ $message }}</span>@enderror
                            </div>

                            <div class="form-control">
                                <label class="label">
                                    <span class="label-text font-medium">Email Address</span>
                                </label>
                                <input type="email" name="email" value="{{ old('email', Auth::user()->email) }}"
                                       class="input input-bordered focus:input-primary @error('email') input-error @enderror" required />
                                @error('email')<span class="text-error text-xs mt-1">{{ $message }}</span>@enderror
                            </div>
                        </div>

                        <div class="flex justify-end mt-4">
                            <button type="submit" class="btn btn-primary gap-2">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                                </svg>
                                Save Changes
                            </button>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Change Password -->
            <div class="card bg-base-100 shadow-lg border border-base-300/50">
                <div class="card-body">
                    <h2 class="card-title flex items-center gap-2">
                        <svg class="w-6 h-6 text-primary" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 7a2 2 0 012 2m4 0a6 6 0 01-7.743 5.743L11 17H9v2H7v2H4a1 1 0 01-1-1v-2.586a1 1 0 01.293-.707l5.964-5.964A6 6 0 1121 9z"></path>
                        </svg>
                        Change Password
                    </h2>

                    <form action="{{ route('profile.update-password') }}" method="POST" class="mt-4 space-y-3">
                        @csrf
                        @method('PUT')
                        <div class="form-control">
                            <label class="label">
                                <span class="label-text">Current Password</span>
                            </label>
                            <input type="password" name="current_password"
                                   class="input input-bordered focus:input-primary @error('current_password') input-error @enderror" required />
                            @error('current_password')<span class="text-error text-xs mt-1">{{ $message }}</span>@enderror
                        </div>
                        <div class="form-control">
                            <label class="label">
                                <span class="label-text">New Password</span>
                            </label>
                            <input type="password" name="password"
                                   class="input input-bordered focus:input-primary @error('password') input-error @enderror" required />
                            @error('password')<span class="text-error text-xs mt-1">{{ $message }}</span>@enderror
                        </div>
                        <div class="form-control">
                            <label class="label">
                                <span class="label-text">Confirm New Password</span>
                            </label>
                            <input type="password" name="password_confirmation" class="input input-bordered focus:input-primary" required />
                        </div>
                        <button type="submit" class="btn btn-primary btn-sm w-full">Update Password</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
