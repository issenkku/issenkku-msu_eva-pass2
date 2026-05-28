@extends('layouts.app')
@section('content')
    @include('partials.dashboard-overview-styles')

    {{-- หน้าแดชบอร์ดของผู้ประเมิน: โปรไฟล์, ตัวกรอง, ภาพรวม, งานที่ควรติดตาม และตารางรายการประเมิน --}}
    <div id="evaluatorDashboardRoot" class="max-w-8xl mx-auto space-y-6">
        @include('partials.dashboard-flash-message')

        <x-profile-card :user="$user" title="ข้อมูลผู้ประเมิน" />

        <div class="rounded-xl py-4 lg:mx-10 lg:px-13 my-4">
            @include('partials.dashboard-filter-panel', [
                'scope' => 'evaluator',
                'hasFilters' => $hasEvaluatorFilters,
                'years' => $years,
                'statusOptions' => $evaluatorFilterStatusOptions,
            ])

            <div class="grid grid-cols-1 gap-6 xl:grid-cols-3">
                @include('partials.dashboard-overview-panel', [
                    'overviewTitle' => 'ภาพรวมความคืบหน้างานประเมิน',
                    'overviewSubtitle' => 'ใช้ติดตามงานที่ต้องดำเนินการ และงานที่ส่งต่อไปยังขั้นตอนถัดไปแล้ว',
                    'overviewChart' => $overviewChart,
                    'overviewPercents' => $overviewPercents,
                    'activeFilters' => $activeEvaluatorFilters,
                ])

                @include('partials.dashboard-follow-up-panel', [
                    'followUpTitle' => 'รายการที่ควรติดตาม',
                    'followUpSubtitle' => 'แสดงงานที่กำลังรอคุณหรือใกล้ครบกำหนดก่อน',
                ])
            </div>
        </div>

        <div id="evaluation-table">
            <x-evaluator-table :evaluations="$evaluations" :statusCounts="$statusCounts" :years="$years" />
        </div>
    </div>

    @include('partials.dashboard-overview-script', [
        'scope' => 'evaluator',
        'rootId' => 'evaluatorDashboardRoot',
        'overviewChart' => $overviewChart,
    ])
@endsection
