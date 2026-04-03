@extends('layouts.app')
{{-- ไฟล์มุมมอง: resources/views/user/management/index.blade.php --}}

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
    @include('user.management.partials.index-filters', ['personnelTypes' => $personnelTypes])
    @include('user.management.partials.index-table')
    @include('user.management.partials.index-footer-summary')
@endsection
