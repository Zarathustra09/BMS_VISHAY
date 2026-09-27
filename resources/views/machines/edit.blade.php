@extends('layouts.app')

@section('title', 'Edit Machine — ' . $machine->name)

@section('breadcrumbs')
    <li><a href="{{ route('machines.index') }}">Machines</a></li>
    <li><a href="{{ route('machines.show', $machine) }}">{{ $machine->name }}</a></li>
    <li>Edit</li>
@endsection

@section('back', route('machines.show', $machine))

@section('content')
    @include('machines._form', [
        'machine' => $machine,
        'buildings' => $buildings,
        'floors' => $floors,
        'thresholds' => $thresholds,
    ])
@endsection
