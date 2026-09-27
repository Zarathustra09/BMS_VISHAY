@extends('layouts.app')

@section('title', 'Create Machine')

@section('breadcrumbs')
    <li><a href="{{ route('machines.index') }}">Machines</a></li>
    <li>Create New Machine</li>
@endsection

@section('back', route('machines.index'))

@section('content')
    @include('machines._form', ['machine' => null, 'buildings' => $buildings, 'floors' => collect()])
@endsection
