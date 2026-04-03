@extends('layouts.app')

@section('content')
@include('partials.dashboard-overview-styles')
<div id="managerDashboardRoot" class="max-w-8xl mx-auto space-y-6">
    @include('partials.dashboard-flash-message')

    <x-profile-card
        :user="$user"
        title="ข้อมูลผู้บริหาร" />

    <div class="rounded-xl py-4 lg:mx-10 lg:px-13 my-4">
        @include('partials.dashboard-filter-panel', [
            'scope' => 'manager',
            'hasFilters' => $hasManagerFilters,
            'showDepartment' => true,
            'departments' => $departments ?? [],
            'years' => $years,
            'statusOptions' => $managerFilterStatusOptions,
        ])

        <div class="grid grid-cols-1 gap-6 xl:grid-cols-3">
            @include('partials.dashboard-overview-panel', [
                'overviewTitle' => 'ภาพรวมการรับรองผล',
                'overviewSubtitle' => 'ใช้ติดตามคิวงานที่กำลังรอผู้บริหารรับรองและงานที่ยังอยู่ก่อนถึงขั้นตอนของคุณ',
                'overviewChart' => $managerOverviewChart,
                'overviewPercents' => $managerOverviewPercents,
                'activeFilters' => $activeManagerFilters,
            ])

            @include('partials.dashboard-follow-up-panel', [
                'followUpTitle' => 'รายการที่ควรติดตาม',
                'followUpSubtitle' => 'แสดงงานที่ใกล้ถึงคิวผู้บริหารและงานที่รอการรับรองก่อน',
            ])
        </div>
    </div>

    <div id="evaluation-table">
        <x-manager-table
            :evaluations="$evaluations"
            :statusCounts="$statusCounts"
            :years="$years" />
    </div>
</div>

@include('partials.dashboard-overview-script', [
    'scope' => 'manager',
    'rootId' => 'managerDashboardRoot',
    'overviewChart' => $managerOverviewChart,
])
@endsection
