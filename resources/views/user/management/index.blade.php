@extends('layouts.app')
@php
    $personnelTypes = [
        'สนับสนุน' => 'สนับสนุน',
        'วิชาการ' => 'วิชาการ',
        'บริหาร' => 'บริหาร',
    ];
@endphp

@section('content')
    @include('user.management.partials.index-page-header')
    @include('user.management.partials.index-toolbar')
    @include('user.management.partials.index-modals')
    @include('user.management.partials.index-filters', ['personnelTypes' => $personnelTypes])
    @include('user.management.partials.index-table')
    @include('user.management.partials.index-footer-summary')
@endsection
