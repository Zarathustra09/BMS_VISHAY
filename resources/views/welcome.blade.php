@extends('layouts.guest.app')

@section('content')
<div class="space-y-6">
    <div class="breadcrumbs text-sm">
        <ul>
            <li><a href="{{ url('/') }}">Home</a></li>
            <li><a>Documents</a></li>
            <li>Add Document</li>
        </ul>
    </div>

    <div class="card bg-base-100 border border-base-300 shadow-sm">
        <div class="card-body">
            <h2 class="card-title">Welcome</h2>
            <p class="opacity-70 text-sm">Your zinc-themed DaisyUI layout is ready.</p>
        </div>
    </div>
</div>
@endsection
