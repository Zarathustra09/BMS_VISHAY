@extends('layouts.app')

@section('title', 'Add Machine Type')

@section('breadcrumbs')
    <li><a href="{{ route('machine-types.index') }}">Machine Types</a></li>
    <li>Add Type</li>
@endsection

@section('back', route('machine-types.index'))

@section('content')
    @include('machine_types._form', ['machineType' => null, 'inUse' => false])
@endsection
