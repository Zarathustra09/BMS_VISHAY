@extends('layouts.app')

@section('title', 'Add Group')

@section('breadcrumbs')
    <li><a href="{{ route('groups.index') }}">Groups</a></li>
    <li>Add Group</li>
@endsection

@section('back', route('groups.index'))

@section('content')
<div class="space-y-6 max-w-5xl mx-auto">
    <h1 class="text-3xl font-bold flex items-center gap-3">
        <div class="w-12 h-12 rounded-xl bg-gradient-to-br from-primary to-secondary flex items-center justify-center shadow-lg">
            <svg class="w-7 h-7 text-base-100" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
            </svg>
        </div>
        Add Group
    </h1>

    @include('groups._form', ['group' => null])
</div>
@endsection
