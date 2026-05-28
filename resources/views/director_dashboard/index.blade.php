@extends('layouts.app')
@section('content')
    @include('partials.dashboard-overview-styles')

    {{-- หน้าแดชบอร์ดของกรรมการ: โปรไฟล์, ตัวกรอง, ภาพรวม, งานที่ควรติดตาม และตารางรายการประเมิน --}}
    <div id="directorDashboardRoot" class="max-w-8xl mx-auto space-y-6">
        @include('partials.dashboard-flash-message')

        <x-profile-card :user="$user" title="ข้อมูลกรรมการ" />

        <div class="rounded-xl py-4 lg:mx-10 lg:px-13 my-4">
            @include('partials.dashboard-filter-panel', [
                'scope' => 'director',
                'hasFilters' => $hasDirectorFilters,
                'showDepartment' => true,
                'departments' => $departments ?? [],
                'years' => $years,
                'statusOptions' => $directorFilterStatusOptions,
            ])

            <div class="grid grid-cols-1 gap-6 xl:grid-cols-3">
                @include('partials.dashboard-overview-panel', [
                    'overviewTitle' => 'ภาพรวมการพิจารณา',
                    'overviewSubtitle' => 'ใช้ติดตามคิวงานที่ต้องเข้าพิจารณา และงานที่รอส่งต่อให้ผู้บริหาร',
                    'overviewChart' => $directorOverviewChart,
                    'overviewPercents' => $directorOverviewPercents,
                    'activeFilters' => $activeDirectorFilters,
                ])

                @include('partials.dashboard-follow-up-panel', [
                    'followUpTitle' => 'รายการที่ควรติดตาม',
                    'followUpSubtitle' => 'แสดงงานที่รอกรรมการพิจารณาก่อน และเรียงตามกำหนดเวลา',
                ])
            </div>
        </div>

        <div id="evaluation-table">
            <x-director-table :evaluations="$evaluations" :statusCounts="$statusCounts" :years="$years" />
        </div>
    </div>

    @include('partials.dashboard-overview-script', [
        'scope' => 'director',
        'rootId' => 'directorDashboardRoot',
        'overviewChart' => $directorOverviewChart,
    ])
@endsection
