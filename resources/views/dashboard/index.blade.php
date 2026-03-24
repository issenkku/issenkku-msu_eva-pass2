@extends('layouts.app')
{{-- ไฟล์มุมมอง: resources/views\dashboard\index.blade.php --}}

@section('content')
    <style>
        /* Custom animations */
        @keyframes fadeIn {
            from {
                opacity: 0;
                transform: translateY(10px);
            }

            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .animate-fadeIn {
            animation: fadeIn 0.5s ease-out forwards;
        }

        .stat-card {
            transition: all 0.3s ease;
            opacity: 0;
            animation: fadeIn 0.5s ease-out forwards;
        }

        .stat-card:nth-child(1) {
            animation-delay: 0.1s;
        }

        .stat-card:nth-child(2) {
            animation-delay: 0.2s;
        }

        .stat-card:nth-child(3) {
            animation-delay: 0.3s;
        }

        .hover-scale:hover {
            transform: translateY(-5px);
        }

        .loading-skeleton {
            background: linear-gradient(90deg, #f0f0f0 25%, #f8f8f8 50%, #f0f0f0 75%);
            background-size: 200% 100%;
            animation: loading 1.5s infinite;
        }

        .overview-main-chart {
            position: relative;
            border: 1px solid #e5e7eb;
            border-radius: 1rem;
            background: linear-gradient(180deg, #ffffff 0%, #f8fafc 100%);
            padding: 1.5rem;
        }

        .overview-chart-wrap {
            position: relative;
            width: min(100%, 240px);
            height: 240px;
            margin: 0 auto;
        }

        .overview-chart-center {
            position: absolute;
            inset: 0;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            text-align: center;
            pointer-events: none;
        }

        .overview-chart-value {
            font-size: 1.45rem;
            font-weight: 700;
            line-height: 1;
            color: #0f172a;
        }

        .overview-chart-label {
            margin-top: 0.4rem;
            max-width: 7.5rem;
            font-size: 0.68rem;
            line-height: 0.95rem;
            color: #6b7280;
        }

        .overview-chart-sub-label {
            margin-top: 0.45rem;
            max-width: 10rem;
            font-size: 0.64rem;
            line-height: 0.9rem;
            color: #94a3b8;
        }

        .overview-progress-card {
            border: 1px solid #e5e7eb;
            border-radius: 0.95rem;
            background: #ffffff;
            padding: 1rem;
        }

        .overview-panel-card {
            height: 100%;
            border: 1px solid #dbe4ee;
            border-radius: 1.25rem;
            background: linear-gradient(180deg, #ffffff 0%, #f8fbff 100%);
            padding: 1.25rem;
            box-shadow: 0 10px 30px rgba(15, 23, 42, 0.05);
        }

        .overview-chart-panel {
            display: flex;
            flex-direction: column;
            gap: 1.25rem;
        }

        .overview-mini-card {
            border: 1px solid #dbe4ee;
            border-radius: 1.1rem;
            background: #ffffff;
            padding: 0.8rem 0.95rem;
            box-shadow: 0 10px 25px rgba(15, 23, 42, 0.05);
        }

        .overview-status-card {
            width: 100%;
            border: 1px solid #dbe4ee;
            border-radius: 1.15rem;
            background: linear-gradient(180deg, #ffffff 0%, #f8fbff 100%);
            padding: 0.85rem 1rem;
            text-align: left;
            box-shadow: 0 10px 28px rgba(15, 23, 42, 0.05);
            transition: transform 0.2s ease, box-shadow 0.2s ease, border-color 0.2s ease;
        }

        .overview-status-card:hover {
            transform: translateY(-2px);
            box-shadow: 0 14px 30px rgba(15, 23, 42, 0.08);
        }

        .overview-status-card.is-active {
            border-color: #93c5fd;
            box-shadow: 0 0 0 2px rgba(59, 130, 246, 0.18), 0 14px 30px rgba(15, 23, 42, 0.08);
        }

        .overview-progress-row + .overview-progress-row {
            margin-top: 1rem;
        }

        @keyframes loading {
            0% {
                background-position: 200% 0;
            }

            100% {
                background-position: -200% 0;
            }
        }
    </style>

    @php
        use Carbon\Carbon;

        function formatThaiDate($date)
        {
            if (!$date) return '-';

            Carbon::setLocale('th'); 
            setlocale(LC_TIME, 'th_TH.UTF-8');

            $thaiMonth = $date->translatedFormat('j F'); 
            $buddhistYear = $date->year + 543;
            $time = $date->format('H:i');

            return [
                'date' => "{$thaiMonth} {$buddhistYear}",
                'time' => "{$time} น."
            ];
        }

        // Sort evaluations by most recent first
        $sortedEvaluations = $evaluations->sortByDesc(function($evaluatorAssignment) {
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
        })->values(); // Reset array keys to ensure proper numbering

        $filteredStatus = null;

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

        $urgentFollowUps = collect($followUpEvaluations ?? [])
            ->take(3);

        $overviewChart = [
            'id' => 'overallCompletionChart',
            'labels' => ['มอบหมาย', 'เริ่มกรอกข้อมูล', 'กำลังดำเนินการ', 'ประเมินเสร็จสิ้น'],
            'data' => [
                $statusCounts['มอบหมาย'] ?? 0,
                $statusCounts['เริ่มกรอกข้อมูล'] ?? 0,
                $statusCounts['กำลังดำเนินการ'] ?? 0,
                $statusCounts['ประเมินเสร็จสิ้น'] ?? 0,
            ],
            'colors' => ['#ef4444', '#f97316', '#3b82f6', '#22c55e'],
            'filters' => ['มอบหมาย', 'เริ่มกรอกข้อมูล', 'กำลังดำเนินการ', 'ประเมินเสร็จสิ้น'],
            'centerValue' => $progressPercent.'%',
            'centerLabel' => 'ความคืบหน้ารวม',
            'centerSubLabel' => 'เสร็จสิ้นแล้ว '.$completedCount.' จาก '.$totalEvaluations.' รายการ',
        ];

        $overviewMiniCards = [
            [
                'title' => 'งานทั้งหมด',
                'value' => $totalEvaluations,
                'unit' => 'รายการ',
                'accent' => 'text-blue-600',
                'badge' => 'bg-blue-100 text-blue-700',
            ],
            [
                'title' => 'ผู้เข้าประเมิน',
                'value' => $totalEvaluatees,
                'unit' => 'คน',
                'accent' => 'text-emerald-600',
                'badge' => 'bg-emerald-100 text-emerald-700',
            ],
        ];

        $overviewStatusCards = [
            [
                'label' => 'มอบหมาย',
                'count' => $statusCounts['มอบหมาย'] ?? 0,
                'percent' => $totalEvaluations > 0 ? round((($statusCounts['มอบหมาย'] ?? 0) / $totalEvaluations) * 100, 1) : 0,
                'color' => '#ef4444',
                'filter' => 'มอบหมาย',
                'text' => 'ยังไม่ประเมิน',
            ],
            [
                'label' => 'เริ่มกรอกข้อมูล',
                'count' => $statusCounts['เริ่มกรอกข้อมูล'] ?? 0,
                'percent' => $totalEvaluations > 0 ? round((($statusCounts['เริ่มกรอกข้อมูล'] ?? 0) / $totalEvaluations) * 100, 1) : 0,
                'color' => '#f97316',
                'filter' => 'เริ่มกรอกข้อมูล',
                'text' => 'เริ่มกรอกข้อมูล',
            ],
            [
                'label' => 'กำลังดำเนินการ',
                'count' => $statusCounts['กำลังดำเนินการ'] ?? 0,
                'percent' => $totalEvaluations > 0 ? round((($statusCounts['กำลังดำเนินการ'] ?? 0) / $totalEvaluations) * 100, 1) : 0,
                'color' => '#3b82f6',
                'filter' => 'กำลังดำเนินการ',
                'text' => 'กำลังดำเนินการ',
            ],
            [
                'label' => 'ประเมินเสร็จสิ้น',
                'count' => $statusCounts['ประเมินเสร็จสิ้น'] ?? 0,
                'percent' => $totalEvaluations > 0 ? round((($statusCounts['ประเมินเสร็จสิ้น'] ?? 0) / $totalEvaluations) * 100, 1) : 0,
                'color' => '#22c55e',
                'filter' => 'ประเมินเสร็จสิ้น',
                'text' => 'ประเมินเสร็จสิ้น',
            ],
        ];

        $overviewSections = [
            [
                'title' => 'สัดส่วนผู้ใช้ในระบบ',
                'items' => [
                    [
                        'label' => 'อยู่ในรอบประเมิน',
                        'value' => $totalEvaluatees,
                        'note' => 'จากผู้ใช้ทั้งหมดในระบบ',
                        'percent' => $totalUsers > 0 ? round(($totalEvaluatees / $totalUsers) * 100, 1) : 0,
                        'color' => 'bg-blue-600',
                    ],
                    [
                        'label' => 'นอกขอบเขตรอบนี้',
                        'value' => max($totalUsers - $totalEvaluatees, 0),
                        'note' => 'ผู้ใช้ที่ยังไม่อยู่ในรอบนี้',
                        'percent' => $totalUsers > 0 ? round((max($totalUsers - $totalEvaluatees, 0) / $totalUsers) * 100, 1) : 0,
                        'color' => 'bg-slate-300',
                    ],
                ],
            ],
            [
                'title' => 'สถานะผู้เข้ารับประเมิน',
                'items' => [
                    [
                        'label' => 'ประเมินเสร็จ',
                        'value' => $completedEvaluatees,
                        'note' => 'เสร็จสิ้นครบถ้วน',
                        'percent' => $totalEvaluatees > 0 ? round(($completedEvaluatees / $totalEvaluatees) * 100, 1) : 0,
                        'color' => 'bg-green-600',
                    ],
                    [
                        'label' => 'กำลังดำเนินการ',
                        'value' => $startedEvaluatees,
                        'note' => 'อยู่ระหว่างการกรอก/ประเมิน',
                        'percent' => $totalEvaluatees > 0 ? round(($startedEvaluatees / $totalEvaluatees) * 100, 1) : 0,
                        'color' => 'bg-amber-600',
                    ],
                    [
                        'label' => 'ยังไม่เริ่ม',
                        'value' => $notStartedEvaluatees,
                        'note' => 'ยังไม่มีการเริ่มต้นข้อมูล',
                        'percent' => $totalEvaluatees > 0 ? round(($notStartedEvaluatees / $totalEvaluatees) * 100, 1) : 0,
                        'color' => 'bg-rose-600',
                    ],
                ],
            ],
        ];
    @endphp

    {{-- บล็อกเนื้อหา --}}
    <div class="min-h-screen py-8 bg-gray-50">
        {{-- บล็อกเนื้อหา --}}
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex flex-row justify-between">
                <!-- Header -->
                <div class="mb-8 animate-fadeIn">
                    <h1 class="text-3xl font-bold text-gray-900 mb-2">แดชบอร์ด</h1>
                    <p class="text-gray-600">ภาพรวมความคืบหน้าการประเมินเพื่อใช้ติดตามผู้ที่ยังกรอกไม่เสร็จ</p>
                </div>
                <!-- Filter Summary -->
                <div class="mb-6">
                    @if (request('start_time') || request('end_time') || request('department_name') || request('position_name'))
                        <span class="text-black ml-4 inline-flex text-sm border px-2 py-1 rounded bg-gray-100">
                            มีการกรองข้อมูล
                        </span>
                    @endif
                </div>
            </div>

            <!-- Filter Inputs -->
            @php
                $hasDashboardFilters = request('start_time') || request('end_time') || request('department_name') || request('position_name');
            @endphp
            <div class="bg-white rounded-xl shadow-lg mb-6 animate-fadeIn overflow-hidden">
                <button
                    type="button"
                    id="dashboardFilterToggle"
                    class="flex w-full items-center justify-between gap-3 px-5 py-4 text-left transition hover:bg-slate-50">
                    <div class="flex items-center gap-2.5">
                        <div class="flex h-9 w-9 items-center justify-center rounded-lg bg-blue-50 text-blue-600">
                            <svg class="h-4.5 w-4.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2l-7 7v5l-4 2v-7L3 6V4z" />
                            </svg>
                        </div>
                        <div>
                            <h2 class="text-lg font-bold text-gray-800 leading-tight">กรองข้อมูลการประเมิน</h2>
                            <p class="mt-0.5 text-xs text-gray-500 sm:text-sm">
                                {{ $hasDashboardFilters ? 'มีตัวกรองที่กำลังใช้งานอยู่ กดเพื่อแก้ไขหรือรีเซ็ต' : 'กดเพื่อแสดงตัวเลือกการกรองเพิ่มเติม' }}
                            </p>
                        </div>
                    </div>
                    <div class="flex items-center gap-2">
                        @if ($hasDashboardFilters)
                            <span class="hidden sm:inline-flex rounded-full bg-blue-50 px-2.5 py-1 text-xs font-semibold text-blue-700 ring-1 ring-blue-100">
                                กำลังกรอง
                            </span>
                        @endif
                        <svg id="dashboardFilterChevron" class="h-4.5 w-4.5 text-gray-500 transition-transform duration-200 {{ $hasDashboardFilters ? 'rotate-180' : '' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                        </svg>
                    </div>
                </button>

                <div id="dashboardFilterPanel" class="{{ $hasDashboardFilters ? '' : 'hidden' }} border-t border-gray-100 px-5 pb-5 pt-2">
                    <form id="filterForm" method="get" class="space-y-1">
                        <div class="flex flex-col md:flex-row md:space-x-4 space-y-3 md:space-y-0">
                            <div>
                                <label class="block mb-1 text-gray-700 font-medium text-sm">วันที่เริ่มต้น</label>
                                <input name="start_time" type="date" value="{{ request('start_time', '') }}"
                                    class="text-black bg-gray-100 rounded-lg focus:ring-blue-500 focus:border-blue-500 px-4 py-2 w-48" />
                            </div>
                            <div>
                                <label class="block mb-1 text-gray-700 font-medium text-sm">วันที่สิ้นสุด</label>
                                <input name="end_time" type="date" value="{{ request('end_time', '') }}"
                                    class="text-black bg-gray-100 rounded-lg focus:ring-blue-500 focus:border-blue-500 px-4 py-2 w-48" />
                            </div>
                            <div>
                                <label class="block mb-1 text-gray-700 text-sm">หน่วยงาน/แผนก</label>
                                <div class="relative">
                                    <select name="department_name"
                                        class="appearance-none text-black bg-gray-100 rounded-lg focus:ring-blue-500 focus:border-blue-500 px-4 py-2 w-full pr-10">
                                        <option value="">ทุกหน่วยงาน</option>
                                        @foreach ($departments ?? [] as $dept)
                                            <option value="{{ $dept->department_name }}"
                                                {{ request('department_name') == $dept->department_name ? 'selected' : '' }}>
                                                {{ $dept->department_name }}
                                            </option>
                                        @endforeach
                                    </select>
                                    <div class="pointer-events-none absolute inset-y-0 right-2 flex items-center">
                                        <svg class="w-4 h-4 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                                        </svg>
                                    </div>
                                </div>
                            </div>
                            <div>
                                <label class="block mb-1 text-gray-700 text-sm">ตำแหน่งงาน</label>
                                <div class="relative">
                                    <select name="position_name"
                                        class="appearance-none text-black bg-gray-100 rounded-lg focus:ring-blue-500 focus:border-blue-500 px-4 py-2 w-full pr-10">
                                        <option value="">ทุกตำแหน่งงาน</option>
                                        @foreach ($positions ?? [] as $position)
                                            <option value="{{ $position->name }}"
                                                {{ request('position_name') == $position->name ? 'selected' : '' }}>
                                                {{ $position->name }}
                                            </option>
                                        @endforeach
                                    </select>
                                    <div class="pointer-events-none absolute inset-y-0 right-2 flex items-center">
                                        <svg class="w-4 h-4 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                                        </svg>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="flex md:justify-end lg:justify-end space-x-2 pt-2 flex-col md:flex-row space-y-3 md:space-y-0">
                            <button type="button" onclick="resetFilters()"
                                class="px-5 py-2 rounded-lg bg-gray-200 text-gray-800 hover:bg-gray-300 transition">ล้างค่า</button>
                            <button type="submit"
                                class="px-5 py-2 rounded-lg bg-blue-600 text-white hover:bg-blue-700 transition">กรองข้อมูล</button>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Statistics Cards -->
            <div class="gap-6 mb-8">
                <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                    <div class="lg:col-span-2 bg-white rounded-xl shadow-md p-6 animate-fadeIn">
                        <div class="flex flex-col gap-4 md:flex-row md:items-start md:justify-between">
                            <div>
                                <h3 class="text-lg font-semibold text-gray-900">ภาพรวมความคืบหน้าการกรอกข้อมูล</h3>
                                <p class="text-sm text-gray-500 mt-1">ใช้ติดตามว่าผู้เข้ารับการประเมินอยู่ขั้นตอนไหน และเหลืองานค้างเท่าไร</p>
                            </div>
                            {{-- <div class="text-left md:text-right">
                                <div class="text-3xl font-bold text-gray-900">{{ $progressPercent }}%</div>
                                <div class="text-sm text-gray-500">เสร็จสิ้นแล้ว {{ $completedCount }} จาก {{ $totalEvaluations }} รายการ</div>
                            </div> --}}
                        </div>

                        {{-- <div class="mt-5">
                            <div class="h-4 w-full overflow-hidden rounded-full bg-gray-100">
                                <div class="h-full rounded-full bg-green-500 transition-all duration-500" style="width: {{ min($progressPercent, 100) }}%"></div>
                            </div>
                        </div> --}}

                        <div class="mt-6 grid grid-cols-1 xl:grid-cols-[300px,1fr] gap-6">
                            <div class="overview-chart-panel">
                                <div class="grid grid-cols-1 gap-3 sm:grid-cols-2 xl:grid-cols-1">
                                    @foreach ($overviewMiniCards as $card)
                                        <div class="overview-mini-card">
                                            <div class="flex items-start justify-between gap-3">
                                                <div class="min-w-0">
                                                    <div class="text-sm font-semibold {{ $card['accent'] }}">{{ $card['title'] }}</div>
                                                    <div class="mt-2.5 text-[1.8rem] font-extrabold leading-none text-slate-900">{{ $card['value'] }}</div>
                                                </div>
                                                <span class="inline-flex rounded-full px-3 py-1 text-[11px] font-semibold {{ $card['badge'] }}">{{ $card['unit'] }}</span>
                                            </div>
                                        </div>
                                    @endforeach
                                </div>

                                <div class="overview-panel-card">
                                    <div class="overview-chart-wrap">
                                        <canvas id="{{ $overviewChart['id'] }}"></canvas>
                                        <div class="overview-chart-center">
                                            <div id="overviewChartCenterValue" class="overview-chart-value">{{ $overviewChart['centerValue'] }}</div>
                                            <div id="overviewChartCenterLabel" class="overview-chart-label">{{ $overviewChart['centerLabel'] }}</div>
                                            <div id="overviewChartCenterSubLabel" class="overview-chart-sub-label">{{ $overviewChart['centerSubLabel'] }}</div>
                                        </div>
                                    </div>

                                    <div class="mt-4 flex items-center justify-center gap-2">
                                        <span id="overviewFilterState" class="hidden rounded-full bg-blue-50 px-3 py-1 text-xs font-semibold text-blue-700"></span>
                                        <button
                                            id="overviewClearFilterButton"
                                            type="button"
                                            class="hidden rounded-full border border-slate-200 px-3 py-1 text-xs font-semibold text-slate-600 transition hover:border-slate-300 hover:bg-slate-50 hover:text-slate-800">
                                            ล้างการกรอง
                                        </button>
                                    </div>
                                </div>
                            </div>

                            <div class="grid grid-cols-1 gap-4">
                                @foreach ($overviewStatusCards as $card)
                                    <button
                                        type="button"
                                        data-overview-filter="{{ $card['filter'] }}"
                                        class="overview-status-card">
                                        <div class="flex items-start justify-between gap-4">
                                            <div class="min-w-0">
                                                <div class="flex items-center gap-3">
                                                    <span class="h-3.5 w-3.5 rounded-full" style="background-color: {{ $card['color'] }}"></span>
                                                    <span class="text-lg font-semibold text-slate-900">{{ $card['text'] }}</span>
                                                </div>
                                                <div class="mt-4 text-sm text-slate-500">
                                                    คิดเป็น <span class="font-bold" style="color: {{ $card['color'] }}">{{ $card['percent'] }}%</span> ของงานทั้งหมด {{ $totalEvaluations }} รายการ
                                                </div>
                                            </div>
                                            <div class="min-w-[78px] rounded-2xl px-3.5 py-2.5 text-center" style="background-color: {{ $card['color'] }}14; color: {{ $card['color'] }}">
                                                <div class="text-[2.1rem] font-extrabold leading-none">{{ $card['count'] }}</div>
                                                <div class="mt-1.5 text-sm font-semibold leading-none">รายการ</div>
                                            </div>
                                        </div>
                                        <div class="mt-4 h-2.5 w-full overflow-hidden rounded-full bg-slate-100">
                                            <div class="h-full rounded-full" style="width: {{ min($card['percent'], 100) }}%; background-color: {{ $card['color'] }}"></div>
                                        </div>
                                    </button>
                                @endforeach
                            </div>
                        </div>
                        <div class="hidden mt-6 grid grid-cols-1 md:grid-cols-2  lg:grid-cols-2 gap-6">
                            <x-summary-score
                                title="จำนวนคนที่มีในระบบ"
                                :value="$totalUsers"
                                subtitle="จำนวนผู้ใช้งานทั้งหมดในระบบ"
                                color="blue"
                                icon="fas fa-users"
                                iconSize="text-3xl"
                            />

                            <x-summary-score
                                title="จำนวนผู้เข้าประเมิน"
                                :value="$totalEvaluatees"
                                subtitle="จำนวนผู้ที่อยู่ในรอบประเมินตามเงื่อนไขที่เลือก"
                                color="blue"
                                icon="fas fa-user-check"
                                iconSize="text-3xl"
                            />

                            <x-summary-score
                                title="จำนวนคนที่ประเมินเสร็จ"
                                :value="$completedEvaluatees"
                                :subtitle="'คิดเป็น '.$completedEvaluateesPercent.'% ของผู้เข้ารับการประเมินทั้งหมด'"
                                color="green"
                                icon="fas fa-check-circle"
                                iconSize="text-3xl"
                            />

                            <x-summary-score
                                title="จำนวนคนที่ยังไม่เริ่ม"
                                :value="$notStartedEvaluatees"
                                subtitle="ผู้ที่ยังไม่เริ่มกรอกข้อมูลประเมิน"
                                color="red"
                                icon="fas fa-hourglass-start"
                                iconSize="text-3xl"
                            />

                            <x-summary-score
                                title="จำนวนคนที่เริ่มดำเนินการแล้ว"
                                :value="$startedEvaluatees"
                                :subtitle="'คิดเป็น '.$startedEvaluateesPercent.'% ของผู้เข้ารับการประเมินทั้งหมด'"
                                color="yellow"
                                icon="fas fa-spinner"
                                iconSize="text-3xl"
                            />
                        </div>
                    </div>

                    <div class="bg-white rounded-xl shadow-md p-6 animate-fadeIn">
                        <div class="flex items-start justify-between gap-3">
                            <div>
                                <h3 class="text-lg font-semibold text-gray-900">รายการที่ควรติดตาม</h3>
                                <p class="text-sm text-gray-500 mt-1">แสดงผู้ที่ยังไม่เสร็จ โดยเรียงจากงานที่ค้างมากไปน้อย</p>
                            </div>
                            <a href="#evaluation-list" class="inline-flex items-center whitespace-nowrap text-xs font-semibold leading-none text-blue-600 hover:text-blue-700 transition">
                                ดูทั้งหมด
                            </a>
                        </div>

                        <div class="mt-4 space-y-3">
                            @forelse($followUpEvaluations as $followUp)
                                @php
                                    $followUpStatus = $followUp->report->status ?? 'Assigned';
                                    $followUpProgress = $progressMap[$followUpStatus] ?? 0;
                                    $followUpPrettyStatus = $statusLabelMap[$followUpStatus] ?? $followUpStatus;
                                    $followUpName = $followUp->evaluateeName ?? '-';
                                    $followUpDueDate = optional($followUp->assignmentData)->end_time
                                        ? Carbon::parse($followUp->assignmentData->end_time)->format('d/m/Y')
                                        : '-';
                                @endphp
                                <div class="rounded-xl border border-gray-100 px-4 py-3">
                                    <div class="flex items-start justify-between gap-3">
                                        <div>
                                            <div class="font-medium text-gray-900">{{ $followUpName }}</div>
                                            <div class="text-sm text-gray-500">สถานะ {{ $followUpPrettyStatus }}</div>
                                        </div>
                                        <div class="text-sm font-semibold text-gray-700">{{ $followUpProgress }}%</div>
                                    </div>
                                    <div class="mt-3 h-2 w-full overflow-hidden rounded-full bg-gray-100">
                                        <div class="h-full rounded-full bg-blue-500" style="width: {{ $followUpProgress }}%"></div>
                                    </div>
                                    <div class="mt-2 text-xs text-gray-500">ครบกำหนด {{ $followUpDueDate }}</div>
                                </div>
                            @empty
                                <div class="rounded-xl bg-green-50 px-4 py-6 text-sm text-green-700">
                                    ไม่มีรายการค้างติดตามในเงื่อนไขที่เลือก
                                </div>
                            @endforelse
                        </div>
                    </div>
                </div>
            </div>

            <!-- Users Table -->
            <div id="evaluation-list" class="bg-white rounded-xl shadow-lg overflow-hidden animate-fadeIn" style="animation-delay: 0.6s;">
                <div class="px-6 pt-4">
                    <h3 class="text-lg font-semibold text-gray-900 mb-2 sm:mb-0">ผลการประเมินรายบุคคล</h3>
                    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between pb-3 border-b">
                        <div class="flex flex-col sm:flex-row gap-3">
                            <div class="relative">
                                <x-search-bar  
                                    placeholder="ค้นหาชื่อ, รายงาน..."
                                /> 
                            </div>
                        </div>
                        <div  class="flex flex-wrap justify-between gap-2">
                            <x-export-button 
                                :route="route('admin.export.reports', request()->query())"
                                label="ส่งออกExcelทั้งหมด" />
                             <x-filter-badge-single 
                                name="year"
                                placeholder="ปีการประเมินทั้งหมด"
                                :options="$years->mapWithKeys(fn($y) => [$y => $y + 543])->toArray()"
                            />
                        </div>
                    </div>
                </div>

                @php
                    $statusStyles = [
                        'มอบหมาย' => 'bg-red-100 text-red-800 hover:bg-red-200',
                        'เริ่มกรอกข้อมูล' => 'bg-blue-100 text-blue-800 hover:bg-blue-200',
                        'กำลังดำเนินการ' => 'bg-yellow-100 text-yellow-800 hover:bg-yellow-200',
                        'ประเมินเสร็จสิ้น' => 'bg-green-100 text-green-800 hover:bg-green-200',
                    ];

                    $firstStatus = array_key_first($statusCounts);
                @endphp

                <div  class="flex flex-wrap justify-between gap-2 mx-4 pt-3">
                    <div class=" flex gap-3 mb-6 flex-wrap ">
                        @foreach($statusCounts as $status => $count)
                            @php
                                $isShowAll = $status === $firstStatus;
                                $isActive = $isShowAll;
                                $style = $statusStyles[$status] ?? 'bg-gray-100 text-gray-800 hover:bg-gray-200';
                                $activeClass = $isActive ? 'ring-2 ring-offset-2 ring-blue-300' : '';
                            @endphp

                            <button
                            type="button"
                            data-status-filter="{{ $isShowAll ? 'all' : $status }}"
                            class="dashboard-status-filter inline-block px-3 py-1 rounded-full text-sm font-medium transition {{ $style }} {{ $activeClass }}">
                                {{ $status }} ({{ $count }})
                            </button>
                        @endforeach
                    </div>
                </div>
                

                <div class="overflow-x-auto">
                    {{-- ตารางข้อมูล --}}
                    <table id="userParticipant" class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">ลำดับ</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">ชื่อผู้รับการประเมิน</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">ชื่อผู้ประเมิน</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider text-center">สถานะ</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider text-center">ความคืบหน้า</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider text-center">คะแนน</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider text-center">จัดการ</th>
                                <th class="px-3 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider text-center">ส่งออกไฟล์</th>
                            </tr>
                        </thead>
                        <tbody id="userTableBody" class="bg-white divide-y divide-gray-200">
                            @forelse($sortedEvaluations as $evaluation)
                                @php
                                    // Format score and date
                                    $evaluateeName = $evaluation->evaluateeName ?? '-';
                                    $evaluatorName = $evaluation->evaluatorName ?? '-';
                                    $score = $evaluation->report->score ?? 0;

                                    $status = $evaluation->report->report_status ?? ($evaluation->report->status ?? 'UNKNOWN');
                                    $statusMapping = [
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
                                    $prettyStatus = $statusMapping[$status] ?? $status;
                                    $progressPercentPerRow = $progressMap[$status] ?? 0;
                                    $statusGroupMapping = [
                                        'Assigned' => 'มอบหมาย',
                                        'Draft' => 'เริ่มกรอกข้อมูล',
                                        'Pending' => 'กำลังดำเนินการ',
                                        'Evaluator_draft' => 'กำลังดำเนินการ',
                                        'Director_assigned' => 'กำลังดำเนินการ',
                                        'Director_draft' => 'กำลังดำเนินการ',
                                        'Manager_assign' => 'กำลังดำเนินการ',
                                        'Manager_draft' => 'กำลังดำเนินการ',
                                        'Completed' => 'ประเมินเสร็จสิ้น',
                                    ];
                                    $statusGroup = $statusGroupMapping[$status] ?? $prettyStatus;

                                    $statusClass = match ($status) {
                                        'Completed' => 'bg-green-100 text-green-800',
                                        'Draft' => 'bg-blue-100 text-blue-800',
                                        'Assigned' => 'bg-red-100 text-red-800',
                                        default => 'bg-yellow-100 text-yellow-800',
                                    };
                                @endphp

                                <tr data-dashboard-row data-status-group="{{ $statusGroup }}" class="hover:bg-gray-50 text-gray-900 transition-colors duration-150">
                                    <td class="px-6 py-4 whitespace-nowrap text-center">
                                        {{ $loop->iteration }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="flex items-center">
                                            <div class="text-sm font-medium text-gray-900">
                                                {{ $evaluateeName }}
                                            </div>
                                        </div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="flex items-center">
                                            <div class="text-sm font-medium text-gray-900">
                                                {{ $evaluatorName }}
                                            </div>
                                        </div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-center align-middle">
                                        <span
                                            class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium {{ $statusClass }}">
                                            {{ $prettyStatus }}
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="mx-auto max-w-[160px]">
                                            <div class="flex items-center justify-between text-xs text-gray-500">
                                                <span>Progress</span>
                                                <span>{{ $progressPercentPerRow }}%</span>
                                            </div>
                                            <div class="mt-2 h-2 w-full overflow-hidden rounded-full bg-gray-100">
                                                <div class="h-full rounded-full {{ $progressPercentPerRow === 100 ? 'bg-green-500' : ($progressPercentPerRow === 0 ? 'bg-red-500' : 'bg-blue-500') }}"
                                                    style="width: {{ $progressPercentPerRow }}%"></div>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="text-sm text-center font-medium text-gray-900">{{ $score }}
                                        </div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                        <div class="flex justify-center items-center h-full">
                                            <a href="{{ url('/dashboard-data/' . ($evaluation->report->id ?? $evaluation->report->report_id ?? '')) }}"
                                            class="text-blue-600 hover:text-blue-900 transition-colors text-center">
                                                ดูรายละเอียด
                                            </a>
                                        </div>
                                    </td>
                                    <td class="px-3 py-4 whitespace-nowrap">
                                        <div class="flex justify-center items-center">
                                            @if($status === 'Completed')
                                                <a href="{{ route('single.reports.export', ['id' => $evaluation->report->id ?? 0]) }}"
                                                class="p-2 bg-green-400 hover:bg-green-500 text-white rounded-md transition duration-200"
                                                title="ส่งออกรายงานผลการประเมินของ {{ $evaluateeName  ?? 'บุคคล' }}">
                                                    <i class="fas fa-file-export"></i>
                                                </a>
                                            @else
                                                <div>-</div>
                                            @endif
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="8" class="px-6 py-4 text-center text-gray-500">ไม่พบรายงานการประเมิน</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                <div id="loading-state" class="hidden p-10 text-center">
                    <div class="loading-skeleton h-10 w-full rounded mb-4"></div>
                    <div class="loading-skeleton h-10 w-3/4 rounded mb-4 mx-auto"></div>
                    <div class="loading-skeleton h-10 w-1/2 rounded mx-auto"></div>
                </div>

                <div id="empty-state" class="hidden p-10 text-center">
                    <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor"
                        viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2">
                        </path>
                    </svg>
                    <h3 class="mt-2 text-sm font-medium text-gray-900">ไม่พบผู้เข้าร่วม</h3>
                    <p class="mt-1 text-sm text-gray-500">เริ่มต้นโดยการเพิ่มผู้เข้าร่วมใหม่ในการประเมิน</p>
                </div>

                <!-- Pagination if needed -->
                <div class="px-6 py-3 border-t border-gray-200">
                    {{ $evaluations->links() }}
                </div>

            </div>
        </div>
    </div>

    <script>
        function resetFilters() {
            document.querySelector('input[name="start_time"]').value = '';
            document.querySelector('input[name="end_time"]').value = '';
            document.querySelector('select[name="department_name"]').value = '';
            document.querySelector('select[name="position_name"]').value = '';
            document.getElementById('filterForm').submit();
        }

        document.addEventListener('DOMContentLoaded', function() {
            const toggle = document.getElementById('dashboardFilterToggle');
            const panel = document.getElementById('dashboardFilterPanel');
            const chevron = document.getElementById('dashboardFilterChevron');

            if (!toggle || !panel || !chevron) {
                return;
            }

            toggle.addEventListener('click', function() {
                panel.classList.toggle('hidden');
                chevron.classList.toggle('rotate-180');
            });
        });
    </script>
@endsection

    @push('scripts')
    <script src="https://cdn.sheetjs.com/xlsx-latest/package/dist/xlsx.full.min.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const statusFilterButtons = Array.from(document.querySelectorAll('.dashboard-status-filter'));
            const overviewFilterButtons = Array.from(document.querySelectorAll('[data-overview-filter]'));
            const tableRows = Array.from(document.querySelectorAll('[data-dashboard-row]'));
            const searchInput = document.getElementById('searchInput');
            const emptyState = document.getElementById('empty-state');
            let activeStatusFilter = 'all';

            const clearStatusQuery = () => {
                const url = new URL(window.location.href);
                if (url.searchParams.has('status')) {
                    url.searchParams.delete('status');
                    window.history.replaceState({}, '', url);
                }
            };

            const setActiveStatusButton = (status) => {
                statusFilterButtons.forEach((button) => {
                    const isActive = button.dataset.statusFilter === status;
                    button.classList.toggle('ring-2', isActive);
                    button.classList.toggle('ring-offset-2', isActive);
                    button.classList.toggle('ring-blue-300', isActive);
                });

                overviewFilterButtons.forEach((button) => {
                    button.classList.toggle('is-active', button.dataset.overviewFilter === status);
                });
            };

            const applyDashboardFilters = () => {
                const searchTerm = (searchInput?.value || '').trim().toLowerCase();
                let hasMatch = false;

                tableRows.forEach((row) => {
                    const statusMatches = activeStatusFilter === 'all' || row.dataset.statusGroup === activeStatusFilter;
                    const nameCell = row.querySelector('td:nth-child(2) .text-sm.font-medium');
                    const userName = nameCell ? nameCell.textContent.toLowerCase() : '';
                    const searchMatches = !searchTerm || userName.includes(searchTerm);
                    const matches = statusMatches && searchMatches;

                    row.style.display = matches ? '' : 'none';
                    if (matches) {
                        hasMatch = true;
                    }
                });

                if (emptyState) {
                    emptyState.style.display = hasMatch ? 'none' : 'block';
                }
            };

            const overviewChart = @json($overviewChart);
            const canvas = document.getElementById(overviewChart.id);
            const centerValueEl = document.getElementById('overviewChartCenterValue');
            const centerLabelEl = document.getElementById('overviewChartCenterLabel');
            const centerSubLabelEl = document.getElementById('overviewChartCenterSubLabel');
            const overviewFilterStateEl = document.getElementById('overviewFilterState');
            const overviewClearFilterButton = document.getElementById('overviewClearFilterButton');
            const overviewFilterStateClasses = {
                'มอบหมาย': ['bg-red-50', 'text-red-700'],
                'เริ่มกรอกข้อมูล': ['bg-orange-50', 'text-orange-700'],
                'กำลังดำเนินการ': ['bg-blue-50', 'text-blue-700'],
                'ประเมินเสร็จสิ้น': ['bg-green-50', 'text-green-700'],
            };

            const applyStatusFilter = (status) => {
                clearStatusQuery();
                activeStatusFilter = status || 'all';
                setActiveStatusButton(activeStatusFilter);
                applyDashboardFilters();

                if (overviewFilterStateEl) {
                    Object.values(overviewFilterStateClasses).flat().forEach((className) => {
                        overviewFilterStateEl.classList.remove(className);
                    });

                    if (activeStatusFilter !== 'all') {
                        overviewFilterStateEl.textContent = `กรองอยู่: ${activeStatusFilter}`;
                        (overviewFilterStateClasses[activeStatusFilter] || ['bg-blue-50', 'text-blue-700']).forEach((className) => {
                            overviewFilterStateEl.classList.add(className);
                        });
                        overviewFilterStateEl.classList.remove('hidden');
                    } else {
                        overviewFilterStateEl.textContent = '';
                        overviewFilterStateEl.classList.add('hidden');
                    }
                }

                if (overviewClearFilterButton) {
                    overviewClearFilterButton.classList.toggle('hidden', activeStatusFilter === 'all');
                }
            };

            const resetOverviewCenter = () => {
                if (centerValueEl) {
                    centerValueEl.textContent = overviewChart.centerValue;
                }

                if (centerLabelEl) {
                    centerLabelEl.textContent = overviewChart.centerLabel;
                }

                if (centerSubLabelEl) {
                    centerSubLabelEl.textContent = overviewChart.centerSubLabel || '';
                }
            };

            const updateOverviewCenter = (chart, element) => {
                if (!centerValueEl || !centerLabelEl || !element) {
                    return;
                }

                const index = element.index;
                const value = chart.data.datasets[0].data[index];
                const total = chart.data.datasets[0].data.reduce((sum, item) => sum + item, 0);
                const percent = total > 0 ? ((value / total) * 100).toFixed(1) : '0.0';

                centerValueEl.textContent = `${percent}%`;
                centerLabelEl.textContent = chart.data.labels[index];

                if (centerSubLabelEl) {
                    centerSubLabelEl.textContent = `${value} รายการ`;
                }
            };
            if (canvas) {
                new Chart(canvas, {
                    type: 'doughnut',
                    data: {
                        labels: overviewChart.labels,
                        datasets: [{
                            data: overviewChart.data,
                            backgroundColor: overviewChart.colors,
                            hoverBackgroundColor: overviewChart.colors,
                            hoverOffset: 8,
                            borderColor: '#ffffff',
                            borderWidth: 3,
                            spacing: 1
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        cutout: '74%',
                        plugins: {
                            legend: {
                                display: false
                            },
                            tooltip: {
                                enabled: false
                            }
                        },
                        animation: {
                            animateRotate: true,
                            duration: 900
                        },
                        onHover(event, elements, chart) {
                            chart.canvas.style.cursor = elements.length ? 'pointer' : 'default';
                            if (elements.length) {
                                updateOverviewCenter(chart, elements[0]);
                            } else {
                                resetOverviewCenter();
                            }
                        },
                        onClick(event, elements, chart) {
                            if (!elements.length) {
                                applyStatusFilter('all');
                                resetOverviewCenter();
                                return;
                            }

                            const index = elements[0].index;
                            const nextFilter = overviewChart.filters?.[index] || 'all';
                            applyStatusFilter(activeStatusFilter === nextFilter ? 'all' : nextFilter);
                            updateOverviewCenter(chart, elements[0]);
                        }
                    }
                });
            }

            statusFilterButtons.forEach((button) => {
                button.addEventListener('click', () => {
                    applyStatusFilter(button.dataset.statusFilter || 'all');
                });
            });

            overviewFilterButtons.forEach((button) => {
                button.addEventListener('click', () => {
                    const nextFilter = button.dataset.overviewFilter || 'all';
                    applyStatusFilter(activeStatusFilter === nextFilter ? 'all' : nextFilter);
                });
            });

            if (overviewClearFilterButton) {
                overviewClearFilterButton.addEventListener('click', () => {
                    applyStatusFilter('all');
                    resetOverviewCenter();
                });
            }

            if (searchInput) {
                searchInput.addEventListener('input', function() {
                    applyDashboardFilters();
                });
            }

            applyStatusFilter(activeStatusFilter);
            resetOverviewCenter();
        });

        function openReportDetails(reportId) {
            window.open(`/dashboard-data/${reportId}`, '_blank') ;
        }
    </script>
@endpush
