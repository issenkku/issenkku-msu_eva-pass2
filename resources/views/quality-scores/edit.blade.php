@extends('layouts.app')
@section('title', 'แก้ไขคะแนนเชิงคุณภาพ')

@section('content')
    @include('quality-scores.partials.form-styles')

    <div class="quality-score-page">
        @include('quality-scores.partials.page-header', [
            'icon' => 'fa-edit',
            'title' => 'แก้ไขคะแนนเชิงคุณภาพ',
            'description' => 'แก้ไขคะแนนเชิงคุณภาพสำหรับผู้ใช้งานที่บันทึกไว้แล้ว',
        ])

        @include('quality-scores.partials.edit-form-section')
    </div>

    @include('quality-scores.partials.edit-script')
@endsection
