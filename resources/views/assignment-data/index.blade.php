@extends('layouts.app')
{{-- หน้า list หลัก ใช้เป็นตัวประกอบหน้าและเรียก partial ตามหน้าที่ --}}

@section('title', 'จัดการรอบการประเมิน')

@section('content')
    <meta name="csrf-token" content="{{ csrf_token() }}">
    @include('assignment-data.partials.index-flash-messages')

    <div class="bg-gray-50 min-h-screen py-4">
        <div class="max-w-7xl mx-auto px-4">
            @include('assignment-data.partials.index-page-header')
            @include('assignment-data.partials.index-table-section')
        </div>

        @include('assignment-data.partials.index-evaluatees-modal')
    </div>

    @include('assignment-data.partials.index-script')
    @include('assignment-data.partials.index-styles')
@endsection
