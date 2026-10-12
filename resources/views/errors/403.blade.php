@extends('errors.layout')

@section('code', '403')
@section('title', 'Access denied')
@section('message', 'You don\'t have permission to view this page. Ask an administrator if you need access.')
@section('icon', 'M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z')
@section('hint', 'If you think you should have access, tell your administrator which page you were trying to open.')
