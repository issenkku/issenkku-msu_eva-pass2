@extends('layouts.app')
@section('content')
    @include('dashboard.partials.index-styles')

    @php
        $overviewChart = [
            'id' => 'overallCompletionChart',
            'labels' => ['มอบหมาย', 'เริ่มกรอกข้อมูล', 'กำลังดำเนินการ', 'ประเมินเสร็จสิ้น'],
            'data' => [
                $overviewEvaluateeStatusCounts['มอบหมาย'] ?? 0,
                $overviewEvaluateeStatusCounts['เริ่มกรอกข้อมูล'] ?? 0,
                $overviewEvaluateeStatusCounts['กำลังดำเนินการ'] ?? 0,
                $overviewEvaluateeStatusCounts['ประเมินเสร็จสิ้น'] ?? 0,
            ],
            'colors' => ['#ef4444', '#f97316', '#3b82f6', '#22c55e'],
            'filters' => ['มอบหมาย', 'เริ่มกรอกข้อมูล', 'กำลังดำเนินการ', 'ประเมินเสร็จสิ้น'],
            'centerValue' => $overviewCompletedEvaluateesPercent . '%',
            'centerLabel' => 'ความคืบหน้ารวม',
            'centerSubLabel' => 'เสร็จสิ้นแล้ว ' . $overviewCompletedEvaluatees . ' จาก ' . $totalEvaluatees . ' คน',
        ];
    @endphp

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
