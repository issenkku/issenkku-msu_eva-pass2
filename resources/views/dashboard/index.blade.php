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

        $filteredStatus = request('status');
        if ($filteredStatus) {
            // Map display names back to DB status (including multiple statuses)
            $reverseMap = [
                'มอบหมาย' => ['Assigned'],
                'เริ่มกรอกข้อมูล' => ['Draft'],
                'กำลังดำเนินการ' => ['Pending','Evaluator_draft','Director_assigned', 'Director_draft', 'Manager_draft', 'Manager_assign'],
                'ประเมินเสร็จสิ้น' => ['Completed'],
            ];

            $statusCodes = $reverseMap[$filteredStatus] ?? [$filteredStatus];

            $sortedEvaluations = $sortedEvaluations->filter(function($evaluatorAssignment) use ($statusCodes) {
                return in_array(optional($evaluatorAssignment->report)->status, $statusCodes);
            })->values(); // Reset keys
        }

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
                    @if (request('start_time') || request('end_time') || request('department_name'))
                        <span class="text-black ml-4 inline-flex text-sm border px-2 py-1 rounded bg-gray-100">
                            มีการกรองข้อมูล
                        </span>
                    @endif
                </div>
            </div>

            <!-- Filter Inputs -->
            <div class="bg-white rounded-xl shadow-lg p-6 mb-8 animate-fadeIn">
                <h2 class="text-xl font-bold mb-6 text-gray-800">กรองข้อมูลการประเมิน</h2>
                {{-- ฟอร์ม --}}
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
                                
                                <!-- Custom arrow icon -->
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

            <!-- Statistics Cards -->
            <div class="gap-6 mb-8">
                <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                    <div class="lg:col-span-2 bg-white rounded-xl shadow-md p-6 animate-fadeIn">
                        <div class="flex flex-col gap-4 md:flex-row md:items-start md:justify-between">
                            <div>
                                <h3 class="text-lg font-semibold text-gray-900">ภาพรวมความคืบหน้าการกรอกข้อมูล</h3>
                                <p class="text-sm text-gray-500 mt-1">ใช้ติดตามว่าผู้เข้ารับการประเมินอยู่ขั้นตอนไหน และเหลืองานค้างเท่าไร</p>
                            </div>
                            <div class="text-left md:text-right">
                                <div class="text-3xl font-bold text-gray-900">{{ $progressPercent }}%</div>
                                <div class="text-sm text-gray-500">เสร็จสิ้นแล้ว {{ $completedCount }} จาก {{ $totalEvaluations }} รายการ</div>
                            </div>
                        </div>

                        <div class="mt-5">
                            <div class="h-4 w-full overflow-hidden rounded-full bg-gray-100">
                                <div class="h-full rounded-full bg-green-500 transition-all duration-500" style="width: {{ min($progressPercent, 100) }}%"></div>
                            </div>
                        </div>

                        <div class="mt-6 grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 gap-5">
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
                                $isActive = $isShowAll ? is_null(request('status')) : request('status') === $status;
                                $style = $statusStyles[$status] ?? 'bg-gray-100 text-gray-800 hover:bg-gray-200';
                                $activeClass = $isActive ? 'ring-2 ring-offset-2 ring-blue-300' : '';

                                $url = $isShowAll
                                    ? request()->url() 
                                    : request()->fullUrlWithQuery(['status' => $status]);
                            @endphp

                            <a href="{{ $url }}"
                            class="inline-block px-3 py-1 rounded-full text-sm font-medium transition {{ $style }} {{ $activeClass }}">
                                {{ $status }} ({{ $count }})
                            </a>
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

                                    $statusClass = match ($status) {
                                        'Completed' => 'bg-green-100 text-green-800',
                                        'Draft' => 'bg-blue-100 text-blue-800',
                                        'Assigned' => 'bg-red-100 text-red-800',
                                        default => 'bg-yellow-100 text-yellow-800',
                                    };
                                @endphp

                                <tr class="hover:bg-gray-50 text-gray-900 transition-colors duration-150">
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
            document.getElementById('filterForm').submit();
        }
    </script>
@endsection

@push('scripts')
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script src="https://cdn.sheetjs.com/xlsx-latest/package/dist/xlsx.full.min.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Search functionality
            document.getElementById('searchInput').addEventListener('input', function(e) {
                const searchTerm = e.target.value.toLowerCase();
                const rows = document.querySelectorAll('#userTableBody tr');
                let hasMatch = false;

                rows.forEach(row => {
                    const userName = row.querySelector('td:nth-child(2) .text-sm.font-medium')
                        .textContent.toLowerCase();

                    if (userName.includes(searchTerm)) {
                        row.style.display = '';
                        hasMatch = true;
                    } else {
                        row.style.display = 'none';
                    }
                });

                // Show empty state if no matches
                document.getElementById('empty-state').style.display = hasMatch ? 'none' : 'block';
            });
        });

        function openReportDetails(reportId) {
            window.open(`/dashboard-data/${reportId}`, '_blank') ;
        }
    </script>
@endpush
