@extends('layouts.app')
@section('title', 'จัดการคะแนนคุณภาพ')

@section('content')
    @include('quality-scores.partials.index-styles')

    {{-- หน้าจัดการคะแนนคุณภาพ: header, flash, ตารางรายงาน และ script ถูกแยกตามหน้าที่ --}}
    <div class="container-fluid">
        @include('quality-scores.partials.index-header')
        @include('quality-scores.partials.index-flash-message')
        @include('quality-scores.partials.index-table-section')
    </div>

    @include('quality-scores.partials.index-script')
@endsection
