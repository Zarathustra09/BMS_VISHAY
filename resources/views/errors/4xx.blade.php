@extends('errors.layout')

@section('code', (string) ($exception->getStatusCode() ?? 400))
@section('title', 'Request could not be completed')
@section('message', "Something about that request wasn't right. Go back and try again.")
