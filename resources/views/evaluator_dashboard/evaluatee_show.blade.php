@extends('layouts.app')
{{-- ไฟล์มุมมอง: resources/views/evaluator_dashboard/evaluatee_show.blade.php --}}

@section('content')
    @include('evaluator_dashboard.partials.show-styles')

    {{-- หน้าแสดงผลการประเมินของผู้รับการประเมิน --}}
    <div class="container">
        @include('evaluator_dashboard.partials.show-page-header')
        @include('evaluator_dashboard.partials.show-report-info')
        @include('evaluator_dashboard.partials.show-evaluatee-info')
        @include('evaluator_dashboard.partials.show-section-divider')

        @foreach ($categories as $category)
            @include('evaluator_dashboard.partials.show-category-card', ['category' => $category])
        @endforeach

        @include('evaluator_dashboard.partials.show-comment-card')
        @include('evaluator_dashboard.partials.show-back-button')
    </div>
@endsection
