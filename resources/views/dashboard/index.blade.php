@extends('layouts.app')
@section('content')
    @include('dashboard.partials.index-styles')

    @php
        use Carbon\Carbon;

        // เรียงรายการประเมินจากใหม่ไปเก่าเพื่อให้ติดตามงานล่าสุดได้ง่าย
        $sortedEvaluations = $evaluations->sortByDesc(function ($evaluatorAssignment) {
            $endTime = optional($evaluatorAssignment->assignmentData)->end_time;
            if ($endTime) {
                return Carbon::parse($endTime)->timestamp;
            }

            $startTime = optional($evaluatorAssignment->assignmentData)->start_time;
            if ($startTime) {
                return Carbon::parse($startTime)->timestamp;
            }

            return optional($evaluatorAssignment->report)->updated_at
                ? Carbon::parse($evaluatorAssignment->report->updated_at)->timestamp
                : (optional($evaluatorAssignment)->created_at
                    ? Carbon::parse($evaluatorAssignment->created_at)->timestamp
                    : 0);
        })->values();

        $progressMap = [
            'Assigned' => 0,
            'Manager_assign' => 0,
            'Draft' => 25,
            'Pending' => 50,
            'Evaluator_draft' => 50,
            'Director_assigned' => 75,
            'Manager_draft' => 75,
            'Director_draft' => 90,
            'Completed' => 100,
        ];

        $statusLabelMap = [
            'Assigned' => 'ยังไม่ประเมิน',
            'Draft' => 'เริ่มกรอกข้อมูล',
            'Pending' => 'รอผู้ประเมินประเมิน',
            'Evaluator_draft' => 'ผู้ประเมินเริ่มประเมิน',
            'Director_assigned' => 'รอกรรมการรับรองผล',
            'Director_draft' => 'กรรมการเริ่มรับรองผล',
            'Manager_assign' => 'ยังไม่ประเมิน',
            'Manager_draft' => 'กำลังดำเนินการ',
            'Completed' => 'ประเมินเสร็จสิ้น',
        ];

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

        $overviewMiniCards = [
            [
                'title' => 'บุคลากรทั้งหมด',
                'value' => $totalUsers,
                'unit' => 'คน',
                'accent' => 'text-blue-600',
                'badge' => 'bg-blue-100 text-blue-700',
            ],
            [
                'title' => 'ผู้เข้ารับการประเมิน',
                'value' => $totalEvaluatees,
                'unit' => 'คน',
                'accent' => 'text-emerald-600',
                'badge' => 'bg-emerald-100 text-emerald-700',
            ],
        ];

        $overviewStatusCards = [
            [
                'label' => 'มอบหมาย',
                'count' => $overviewEvaluateeStatusCounts['มอบหมาย'] ?? 0,
                'percent' => $totalEvaluatees > 0 ? round((($overviewEvaluateeStatusCounts['มอบหมาย'] ?? 0) / $totalEvaluatees) * 100, 1) : 0,
                'color' => '#ef4444',
                'filter' => 'มอบหมาย',
                'text' => 'ยังไม่ประเมิน',
            ],
            [
                'label' => 'เริ่มกรอกข้อมูล',
                'count' => $overviewEvaluateeStatusCounts['เริ่มกรอกข้อมูล'] ?? 0,
                'percent' => $totalEvaluatees > 0 ? round((($overviewEvaluateeStatusCounts['เริ่มกรอกข้อมูล'] ?? 0) / $totalEvaluatees) * 100, 1) : 0,
                'color' => '#f97316',
                'filter' => 'เริ่มกรอกข้อมูล',
                'text' => 'เริ่มกรอกข้อมูล',
            ],
            [
                'label' => 'กำลังดำเนินการ',
                'count' => $overviewEvaluateeStatusCounts['กำลังดำเนินการ'] ?? 0,
                'percent' => $totalEvaluatees > 0 ? round((($overviewEvaluateeStatusCounts['กำลังดำเนินการ'] ?? 0) / $totalEvaluatees) * 100, 1) : 0,
                'color' => '#3b82f6',
                'filter' => 'กำลังดำเนินการ',
                'text' => 'กำลังดำเนินการ',
            ],
            [
                'label' => 'ประเมินเสร็จสิ้น',
                'count' => $overviewEvaluateeStatusCounts['ประเมินเสร็จสิ้น'] ?? 0,
                'percent' => $totalEvaluatees > 0 ? round((($overviewEvaluateeStatusCounts['ประเมินเสร็จสิ้น'] ?? 0) / $totalEvaluatees) * 100, 1) : 0,
                'color' => '#22c55e',
                'filter' => 'ประเมินเสร็จสิ้น',
                'text' => 'ประเมินเสร็จสิ้น',
            ],
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

            {{-- การ์ดสรุปด้านบน --}}
            <div class="gap-6 mb-8">
                <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                    @include('dashboard.partials.index-overview-section')

                    @include('dashboard.partials.index-follow-up-section')
                </div>
            </div>

            {{-- ตารางผลการประเมินรายบุคคล --}}
            <div id="evaluation-list" class="bg-white rounded-xl shadow-lg overflow-hidden animate-fadeIn" style="animation-delay: 0.6s;">
                @include('dashboard.partials.index-list-header')

                @include('dashboard.partials.index-status-filters')

                <div class="overflow-x-auto">
                    {{-- ตารางข้อมูล --}}
                    <table id="userParticipant" class="min-w-full divide-y divide-gray-200">
                        @include('dashboard.partials.index-table-head')
                        <tbody id="userTableBody" class="bg-white divide-y divide-gray-200">
                            @forelse($sortedEvaluations as $evaluation)
                                @include('dashboard.partials.index-table-row', ['evaluation' => $evaluation, 'progressMap' => $progressMap])
                            @empty
                                @include('dashboard.partials.index-table-empty-row')
                            @endforelse
                        </tbody>
                    </table>
                </div>

                @include('dashboard.partials.index-loading-state')

                @include('dashboard.partials.index-empty-state')

                @include('dashboard.partials.index-pagination')
            </div>
        </div>
    </div>

    @include('dashboard.partials.index-reviewer-modal')
@endsection

@push('scripts')
    @include('dashboard.partials.index-script')
@endpush
