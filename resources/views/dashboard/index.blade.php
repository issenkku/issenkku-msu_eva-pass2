@extends('layouts.app')
@section('content')
    @include('dashboard.partials.index-styles')

    {{-- เนื้อหาหลักของหน้าแดชบอร์ด --}}
    <div class="min-h-screen py-8 bg-gray-50">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            @php
                $hasDashboardFilters = request('start_time') || request('end_time') || request('department_name') || request('position_name');
            @endphp

            @include('dashboard.partials.index-page-header')

            @include('dashboard.partials.index-filter-panel')

            @include('dashboard.partials.index-results')
        </div>
    </div>

    @include('dashboard.partials.index-reviewer-modal')
@endsection

@push('scripts')
    @include('dashboard.partials.index-script')
@endpush
