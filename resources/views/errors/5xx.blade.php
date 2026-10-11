@extends('errors.layout')

@section('code', (string) ($exception->getStatusCode() ?? 500))
@section('title', 'Something went wrong')
@section('message', 'An unexpected error occurred on our side. Please try again in a moment.')
