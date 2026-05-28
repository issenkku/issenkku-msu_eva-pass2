@extends('layouts.app')
@section('title', 'เพิ่มคะแนนเชิงคุณภาพ')

@section('content')
    @include('quality-scores.partials.form-styles')

    <div class="quality-score-page">
        @include('quality-scores.partials.page-header', [
            'icon' => 'fa-plus',
            'title' => 'เพิ่มคะแนนเชิงคุณภาพ',
            'description' => 'เพิ่มคะแนนเชิงคุณภาพให้ผู้ใช้งานหลายคนในหลายเกณฑ์พร้อมกัน',
        ])

        @include('quality-scores.partials.create-form-section')
    </div>

    @include('quality-scores.partials.create-script')
@endsection
