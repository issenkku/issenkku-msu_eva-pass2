@extends('layouts.app')
@section('content')
    @include('evaluator_dashboard.partials.form-styles')

    {{-- ฟอร์มประเมินผลงานสำหรับผู้ประเมิน --}}
    <form id="approve_eva" action="{{ route('evaluator.evaluatee.update', $assignment->report_id) }}" method="POST">
        @csrf
        @method('PUT')

        <div class="container">
            @include('evaluator_dashboard.partials.form-page-header')
            @include('evaluator_dashboard.partials.form-report-info')
            @include('evaluator_dashboard.partials.form-evaluatee-info')
            @include('evaluator_dashboard.partials.form-section-divider')

            @foreach ($categories as $category)
                @include('evaluator_dashboard.partials.form-category-card', ['category' => $category])
            @endforeach

            @include('evaluator_dashboard.partials.form-comment-card')
            @include('evaluator_dashboard.partials.form-actions')
        </div>
    </form>

    @include('evaluator_dashboard.partials.form-modals')
    @include('evaluator_dashboard.partials.form-loading-overlay')
    @include('evaluator_dashboard.partials.form-script')
@endsection
