@extends('layouts.app')

@section('title', 'Edit Machine Type — ' . $machineType->name)

@section('breadcrumbs')
    <li><a href="{{ route('machine-types.index') }}">Machine Types</a></li>
    <li>Edit {{ $machineType->name }}</li>
@endsection

@section('back', route('machine-types.index'))

@section('content')
    @include('machine_types._form', ['machineType' => $machineType, 'inUse' => $inUse])
@endsection
