@extends('layouts.app')

@section('title', 'Edit Group — ' . $group->name)

@section('breadcrumbs')
    <li><a href="{{ route('groups.index') }}">Groups</a></li>
    <li><a href="{{ route('groups.show', $group) }}">{{ $group->name }}</a></li>
    <li>Edit</li>
@endsection

@section('back', route('groups.show', $group))

@section('content')
<div class="space-y-6 max-w-5xl mx-auto">
    <h1 class="text-3xl font-bold">Edit Group</h1>

    @include('groups._form', ['group' => $group])
</div>
@endsection
