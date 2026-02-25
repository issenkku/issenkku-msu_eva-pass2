{{-- ไฟล์มุมมอง: resources/views\components\evaluation-summary.blade.php --}}
@props(['evaluations', 'statusCounts', 'years'])

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
    $sortedEvaluations = $evaluations->sortByDesc(function($assignment) {
        // Primary sort: by end_time (most recent first)
        $endTime = optional($assignment->assignmentData)->end_time;
        if ($endTime) {
            return Carbon::parse($endTime)->timestamp;
        }
        
        // Secondary sort: by start_time if no end_time
        $startTime = optional($assignment->assignmentData)->start_time;
        if ($startTime) {
            return Carbon::parse($startTime)->timestamp;
        }
        
        // Tertiary sort: by created_at or updated_at
        return optional($assignment->report)->updated_at 
            ? Carbon::parse($assignment->report->updated_at)->timestamp
            : (optional($assignment)->created_at 
                ? Carbon::parse($assignment->created_at)->timestamp 
                : 0);
    })->values(); // Reset array keys to ensure proper numbering

    $filteredStatus = request('status');
    if ($filteredStatus) {
        // Map display name back to DB status
        $reverseMap = [
            'ยังไม่ประเมิน' => ['Assigned'],
            'กำลังดำเนินการ' => ['Draft'],
            'รอผลการประเมิน' => ['Pending', 'Evaluator_draft', 'Director_assigned', 'Director_draft', 'Manager_assign', 'Manager_draft'],
            'ประเมินเสร็จสิ้น' => ['Completed'],
        ];

        $statusCode = $reverseMap[$filteredStatus] ?? $filteredStatus;

        $sortedEvaluations = $sortedEvaluations->filter(function($assignment) use ($statusCode) {
            return in_array(optional($assignment->report)->status, $statusCode);
        })->values(); // Reset keys
    }
@endphp

{{-- บล็อกเนื้อหา --}}
<div class="bg-white rounded-lg p-6">
    <h3 class="text-lg font-semibold text-gray-800 mb-4">ภาพรวมสถานะการประเมิน</h3>
    {{-- บล็อกเนื้อหา --}}
    <div class="flex flex-wrap gap-4 mb-4 justify-between border-b pb-4 pl-3 pr-3">
        <x-search-bar  
            placeholder="ค้นหาชื่อ, รายงาน..."
        /> 
        <x-filter-badge-single 
            name="year"
            placeholder="ปีการประเมินทั้งหมด"
            :options="$years->mapWithKeys(fn($y) => [$y => $y + 543])->toArray()"
        />
    </div>

    <!-- Status Badges -->
    @php
        $statusStyles = [
            'ยังไม่ประเมิน' => 'bg-red-100 text-red-800 hover:bg-red-200',
            'กำลังดำเนินการ' => 'bg-blue-100 text-blue-800 hover:bg-blue-200',
            'รอผลการประเมิน' => 'bg-yellow-100 text-yellow-800 hover:bg-yellow-200',
            'ประเมินเสร็จสิ้น' => 'bg-green-100 text-green-800 hover:bg-green-200',
        ];

        $firstStatus = array_key_first($statusCounts);
    @endphp

    {{-- บล็อกเนื้อหา --}}
    <div class="flex gap-3 mb-6 flex-wrap">
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

    <!-- Table Format -->
    {{-- บล็อกเนื้อหา --}}
    <div class="relative overflow-x-auto">
        {{-- บล็อกเนื้อหา --}}
        <div class="absolute left-0 top-0 h-full w-10 bg-gradient-to-r from-white to-transparent pointer-events-none z-10"></div>
        {{-- บล็อกเนื้อหา --}}
        <div class="absolute right-0 top-0 h-full w-10 bg-gradient-to-l from-white to-transparent pointer-events-none z-10"></div>

        {{-- บล็อกเนื้อหา --}}
        <div class="overflow-x-auto scrollbar-thin scrollbar-thumb-gray-300 scrollbar-track-gray-100">
            {{-- ตารางข้อมูล --}}
            <table class="min-w-[900px] w-full border-collapse text-sm">
                <thead>
                    <tr class="bg-gray-50">
                        <th class="text-left p-4 border-b font-medium text-gray-800 whitespace-nowrap">อันดับ</th>
                        <th class="text-left p-4 border-b font-medium text-gray-800 whitespace-nowrap">รายการประเมิน</th>
                        <th class="text-left p-4 border-b font-medium text-gray-800 whitespace-nowrap">วันที่เริ่มประเมิน</th>
                        <th class="text-left p-4 border-b font-medium text-gray-800 whitespace-nowrap">วันที่สิ้นสุดประเมิน</th>
                        <th class="text-left p-4 border-b font-medium text-gray-800 whitespace-nowrap">ผู้ประเมิน</th>
                        <th class="text-center p-4 border-b font-medium text-gray-800 whitespace-nowrap min-w-[180px]">สถานะ</th>
                        <th class="text-center p-4 border-b font-medium text-gray-800 whitespace-nowrap">การดำเนินการ</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($sortedEvaluations as $index => $assignment)
                        @php
                            $report = $assignment->report;
                            $assignmentData = $assignment->assignmentData;

                            $reportTitle = optional(optional($assignmentData)->report)->reportData->report_title
                                ?? optional($report)->reportData->report_title
                                ?? '-';

                            $statusFromDB = optional($report)->status ?? 'Assigned';
                            $statusMapping = [
                                'Assigned' => 'ยังไม่ประเมิน',
                                'Draft' => 'กำลังดำเนินการ',
                                'Pending' => 'รอผู้ประเมินประเมิน',
                                'Evaluator_draft' => 'ผู้ประเมินเริ่มประเมิน',
                                'Director_assigned' => 'รอกรรมการรับรองผล',
                                'Director_draft' => 'กรรมการเริ่มรับรองผล',
                                'Manager_assign' => 'รอคณบดีรับรองผล',
                                'Manager_draft' => 'คณบดีเริ่มรับรองผล',
                                'Completed' => 'ประเมินเสร็จสิ้น',
                            ];
                            $status = $statusMapping[$statusFromDB] ?? $statusFromDB;

                            $start = optional($assignmentData)->start_time ? Carbon::parse($assignmentData->start_time) : null;
                            $end = optional($assignmentData)->end_time ? Carbon::parse($assignmentData->end_time) : null;

                            $startFormatted = formatThaiDate($start);
                            $endFormatted = formatThaiDate($end);

                            // Add visual indicator for recent items
                            $isRecent = false;
                            if ($end && $end->gt(Carbon::now()->subDays(3))) {
                                $isRecent = true;
                            } elseif (!$end && $start && $start->gt(Carbon::now()->subDays(3))) {
                                $isRecent = true;
                            }
                        @endphp

                        <tr class="hover:bg-gray-50 transition-colors {{ $isRecent ? 'bg-blue-50' : '' }}">
                            <td class="p-4 border-b text-gray-500">
                                {{ $index + 1 }}
                                @if($isRecent)
                                    <span class="inline-block w-2 h-2 bg-blue-500 rounded-full ml-2" title="รายการล่าสุด"></span>
                                @endif
                            </td>

                            <td class="p-4 border-b">
                                <div class="font-medium text-gray-800">{{ $reportTitle }}</div>
                                @if($isRecent)
                                    <div class="text-xs text-blue-600 mt-1">รายการล่าสุด</div>
                                @endif
                            </td>

                            <td class="p-4 border-b text-gray-500">
                                @if($startFormatted !== '-')
                                    <div class="font-medium">{{ $startFormatted['date'] }}</div>
                                    <div class="text-xs text-gray-400">{{ $startFormatted['time'] }}</div>
                                @else
                                    -
                                @endif
                            </td>

                            <td class="p-4 border-b text-gray-500">
                                @if($endFormatted !== '-')
                                    <div class="font-medium">{{ $endFormatted['date'] }}</div>
                                    <div class="text-xs text-gray-400">{{ $endFormatted['time'] }}</div>
                                @else
                                    -
                                @endif
                            </td>

                            <td class="p-4 border-b text-gray-500 min-w-[150px]">
                                 {{ $assignment->assignmentData->evaluatorUser?->name ?? '-' }}
                            </td>

                            <td class="py-4 px-2 border-b text-center min-w-[180px]">
                                @php
                                    $statusClasses = [
                                        'ยังไม่ประเมิน' => 'bg-red-100 text-red-800',
                                        'กำลังดำเนินการ' => 'bg-blue-100 text-blue-800',
                                        'รอผู้ประเมินประเมิน' => 'bg-yellow-100 text-yellow-800',
                                        'ผู้ประเมินเริ่มประเมิน' => 'bg-yellow-100 text-yellow-800',
                                        'รอกรรมการรับรองผล' => 'bg-yellow-100 text-yellow-800',
                                        'กรรมการเริ่มรับรองผล' => 'bg-yellow-100 text-yellow-800',
                                        'รอคณบดีรับรองผล' => 'bg-yellow-100 text-yellow-800',
                                        'คณบดีเริ่มรับรองผล' => 'bg-yellow-100 text-yellow-800',
                                        'ประเมินเสร็จสิ้น' => 'bg-green-100 text-green-800',
                                    ];
                                    $statusClass = $statusClasses[$status] ?? 'bg-gray-100 text-gray-800';
                                @endphp
                                <span class="px-3 py-1 rounded-full text-sm font-medium {{ $statusClass }}">
                                    {{ $status }}
                                </span>
                            </td>

                            <td class="p-4 border-b text-center \">
                                @php
                                    $actions = [
                                        'ยังไม่ประเมิน' => [
                                            'label' => 'เริ่มประเมิน',
                                            'classes' => 'bg-red-500 hover:bg-red-600 text-white'
                                        ],
                                        'กำลังดำเนินการ' => [
                                            'label' => 'ประเมินต่อ',
                                            'classes' => 'bg-blue-500 hover:bg-blue-600 text-white'
                                        ],
                                        'รอผู้ประเมินประเมิน' => [
                                            'label' => 'ดูการกรอกข้อมูล',
                                            'classes' => 'bg-yellow-500 hover:bg-yellow-600 text-white'
                                        ],
                                        'ผู้ประเมินเริ่มประเมิน' => [
                                            'label' => 'ดูการกรอกข้อมูล',
                                            'classes' => 'bg-yellow-500 hover:bg-yellow-600 text-white',
                                        ],
                                        'รอกรรมการรับรองผล' => [
                                            'label' => 'ดูการกรอกข้อมูล',
                                            'classes' => 'bg-yellow-500 hover:bg-yellow-600 text-white',
                                        ],
                                        'กรรมการเริ่มรับรองผล' => [
                                            'label' => 'ดูการกรอกข้อมูล',
                                            'classes' => 'bg-yellow-500 hover:bg-yellow-600 text-white',
                                        ],
                                        'รอคณบดีรับรองผล' => [
                                            'label' => 'ดูการกรอกข้อมูล',
                                            'classes' => 'bg-yellow-500 hover:bg-yellow-600 text-white',
                                        ],
                                        'คณบดีเริ่มรับรองผล' => [
                                            'label' => 'ดูการกรอกข้อมูล',
                                            'classes' => 'bg-yellow-500 hover:bg-yellow-600 text-white',
                                        ],
                                        'ประเมินเสร็จสิ้น' => [
                                            'label' => 'ดูผล',
                                            'classes' => 'bg-green-500 hover:bg-green-600 text-white'
                                        ],
                                    ];
                                    $action = $actions[$status] ?? null;

                                    $url = route('evaluation.show', ['id' => $report->id ?? 0]);

                                    if ($status === 'รอผลการประเมิน' || $status === 'ประเมินเสร็จสิ้น') {
                                        $url .= '?readonly=1';
                                    }
                                @endphp

                                @if($action)
                                    <a href="{{ $url }}"
                                    class="min-w-[140px] inline-block px-4 py-2 text-sm font-medium rounded-xl shadow transition duration-200 {{ $action['classes'] }}">
                                        {{ $action['label'] }}
                                    </a>
                                @else
                                    <span class="text-sm text-gray-400">-</span>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="text-center py-8 text-gray-500">
                                <i class="fas fa-inbox text-3xl mb-2 block"></i>
                                <p>ไม่มีข้อมูลการประเมิน</p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
        </table>
    </div>
    {{-- บล็อกเนื้อหา --}}
    <div class="mt-6">
        {{ $evaluations->links() }}
    </div>
</div>
