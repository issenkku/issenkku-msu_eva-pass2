@extends('layouts.app')
{{-- ไฟล์มุมมอง: resources/views/dashboard/show.blade.php --}}

@section('content')
    @include('dashboard.partials.show-styles')

    {{-- หน้าแสดงผลการประเมินแบบอ่านอย่างเดียว --}}
    <div class="container">
        @include('dashboard.partials.show-page-header')
        @include('dashboard.partials.show-report-info')
        @include('dashboard.partials.show-evaluatee-info')
        @include('dashboard.partials.show-section-divider')

        @foreach ($categories as $category)
            @include('dashboard.partials.show-category-card', ['category' => $category])
        @endforeach

        @include('dashboard.partials.show-comment-card')
        @include('dashboard.partials.show-back-button')
    </div>
@endsection
