@extends('layouts.app')
{{-- ไฟล์มุมมอง: resources/views/quality-scores/show.blade.php --}}
@section('title', 'รายละเอียดคะแนนเชิงคุณภาพ')

@section('content')
    @include('quality-scores.partials.form-styles')

    <div class="quality-score-page">
        @include('quality-scores.partials.page-header', [
            'icon' => 'fa-star',
            'title' => 'รายละเอียดคะแนนเชิงคุณภาพ',
            'description' => 'ดูข้อมูลคะแนนเชิงคุณภาพที่บันทึกไว้รายรายการ',
        ])

        @include('quality-scores.partials.show-details-section')
    </div>
@endsection
