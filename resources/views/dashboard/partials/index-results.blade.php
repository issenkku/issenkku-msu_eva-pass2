@php
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

<div data-dashboard-results aria-busy="false" class="relative">
    <script type="application/json" data-overview-chart-config>@json($overviewChart)</script>

    <div
        data-dashboard-results-error
        role="alert"
        class="mb-4 hidden rounded-lg border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-700">
        <span>โหลดข้อมูลไม่สำเร็จ กรุณาลองอีกครั้ง</span>
        <button
            type="button"
            data-dashboard-results-retry
            class="ml-2 font-semibold underline underline-offset-2 hover:text-red-900">
            ลองอีกครั้ง
        </button>
    </div>

    <div
        data-dashboard-results-loading
        aria-hidden="true"
        class="absolute inset-0 z-20 hidden items-start justify-center rounded-xl bg-white/70 pt-12 backdrop-blur-[1px]">
        <span class="rounded-full bg-blue-50 px-4 py-2 text-sm font-semibold text-blue-700">
            กำลังโหลดข้อมูล...
        </span>
    </div>

    <div class="mb-8 gap-6">
        <div class="grid grid-cols-1 gap-6 lg:grid-cols-3">
            @include('dashboard.partials.index-overview-section')
            @include('dashboard.partials.index-follow-up-section')
        </div>
    </div>

    @include('dashboard.partials.index-evaluation-list')
</div>
