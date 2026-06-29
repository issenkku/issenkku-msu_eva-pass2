@props(['evaluations', 'statusCounts', 'years'])

@php
    use Carbon\Carbon;

        $formatThaiDate = function ($date) {
        if (!$date) return '-';

        Carbon::setLocale('th'); 
        setlocale(LC_TIME, 'th_TH.UTF-8');

        $thaiMonth = $date->translatedFormat('j F'); 
        $buddhistYear = $date->year + 543;
        $time = $date->format('H:i');

        return [
            'date' => "{$thaiMonth} {$buddhistYear}",
            'time' => "{$time} เธ."
        ];
    };
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
            'รอการกรอกข้อมูล' => ['Assigned', 'Draft', 'Pending', 'Evaluator_draft', 'Director_assigned', 'Director_draft'],
            'ยังไม่ประเมิน' => ['Manager_assign'],
            'กำลังดำเนินการ' => ['Manager_draft'],
            'ประเมินเสร็จสิ้น' => ['Completed'],
        ];

        $statusCodes = $reverseMap[$filteredStatus] ?? [$filteredStatus];

        $sortedEvaluations = $sortedEvaluations->filter(function($evaluatorAssignment) use ($statusCodes) {
            return in_array(optional($evaluatorAssignment->report)->status, $statusCodes);
        })->values(); // Reset keys
    }
@endphp
<div class="evaluation-list-card bg-white rounded-lg p-6">
    <h3 class="text-lg font-semibold text-gray-800 mb-4">ภาพรวมสถานะการประเมิน</h3>
    <div class="evaluation-list-toolbar flex flex-wrap gap-4 mb-4 justify-between border-b pb-4 pl-3 pr-3">
        <x-search-bar  
            placeholder="ค้นหาชื่อ, รายงาน..."
        /> 
        <div  class="evaluation-toolbar-actions flex flex-wrap justify-between gap-2">
            <x-export-button 
                :route="route('export.reports', request()->query())"
                label="ส่งออกExcelทั้งหมด" />
            <x-filter-badge-single 
                name="year"
                placeholder="ปีการประเมินทั้งหมด"
                :options="$years->mapWithKeys(fn($y) => [$y => $y + 543])->toArray()"
            />
        </div>
    </div>
    @php
        $statusStyles = [
            'รอการกรอกข้อมูล' => 'bg-yellow-100 text-yellow-800 hover:bg-yellow-200',
            'ยังไม่ประเมิน' => 'bg-red-100 text-red-800 hover:bg-red-200',
            'กำลังดำเนินการ' => 'bg-blue-100 text-blue-800 hover:bg-blue-200',
            'ประเมินเสร็จสิ้น' => 'bg-green-100 text-green-800 hover:bg-green-200',
        ];

        $firstStatus = array_key_first($statusCounts);
    @endphp
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
            data-manager-ajax-link
            @if ($isActive) aria-current="true" @endif
            class="inline-block px-3 py-1 rounded-full text-sm font-medium transition {{ $style }} {{ $activeClass }}">
                {{ $status }} ({{ $count }})
            </a>
        @endforeach
    </div>
    <div class="relative overflow-x-auto">
        <div class="absolute left-0 top-0 h-full w-10 bg-gradient-to-r from-white to-transparent pointer-events-none z-10"></div>
        <div class="absolute right-0 top-0 h-full w-10 bg-gradient-to-l from-white to-transparent pointer-events-none z-10"></div>
        <div class="overflow-x-auto scrollbar-thin scrollbar-thumb-gray-300 scrollbar-track-gray-100">
            {{-- ตารางข้อมูล --}}
            <table class="min-w-[900px] w-full border-collapse text-sm">
                <thead>
                    <tr class="bg-gray-50">
                        <th class="text-left py-4 px-3 border-b font-medium text-gray-800 whitespace-nowrap">อันดับ</th>
                        <th class="text-left p-4 border-b font-medium text-gray-800 whitespace-nowrap">รายการประเมิน</th>
                        <th class="text-left py-4 px-3 border-b font-medium text-gray-800 whitespace-nowrap">วันที่เริ่มประเมิน</th>
                        <th class="text-left py-4 px-3 border-b font-medium text-gray-800 whitespace-nowrap">วันที่สิ้นสุดประเมิน</th>
                        <th class="text-left py-4 px-3 border-b font-medium text-gray-800 whitespace-nowrap">ผู้รับการประเมิน</th>
                        <th class="text-left py-4 px-3 border-b font-medium text-gray-800 whitespace-nowrap">ผู้ประเมิน</th>
                        <th class="text-center py-4 px-3 border-b font-medium text-gray-800 whitespace-nowrap min-w-[180px]">สถานะ</th>
                        <th class="text-center py-4 px-3 border-b font-medium text-gray-800 whitespace-nowrap">การดำเนินการ</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($sortedEvaluations as $index => $evaluatorAssignment)
                        @php
                            $report = $evaluatorAssignment->report;
                            $assignmentData = $evaluatorAssignment->assignmentData;
                            $evaluatee = $evaluatorAssignment->evaluateeUser;

                            $reportTitle = optional(optional($assignmentData)->report)->reportData->report_title
                                ?? optional($report)->reportData->report_title
                                ?? '-';

                            $statusFromDB = optional($report)->status ?? 'Pending';
                            $statusMapping = [
                                'Assigned' => 'รอการกรอกข้อมูล',
                                'Draft' => 'รอการกรอกข้อมูล',
                                'Pending' => 'รอผู้ประเมินประเมิน',
                                'Evaluator_draft' => 'ผู้ประเมินเริ่มประเมิน',
                                'Director_assigned' => 'รอกรรมการรับรองผล',
                                'Director_draft' => 'กรรมการเริ่มรับรองผล',
                                'Manager_assign' => 'ยังไม่ประเมิน',
                                'Manager_draft' => 'กำลังดำเนินการ',
                                'Completed' => 'ประเมินเสร็จสิ้น',
                            ];
                            $status = $statusMapping[$statusFromDB] ?? $statusFromDB;

                            $start = optional($assignmentData)->start_time ? Carbon::parse($assignmentData->start_time) : null;
                            $end = optional($assignmentData)->end_time ? Carbon::parse($assignmentData->end_time) : null;

                            $evaluateeName = optional($evaluatee)->name ?? '-';
                            $reviewerEntries = collect($evaluatorAssignment->reviewerEntries ?? []);
                            $primaryReviewer = $reviewerEntries->first();
                            $additionalReviewerCount = max($reviewerEntries->count() - 1, 0);

                            $startFormatted = $formatThaiDate($start);
                            $endFormatted = $formatThaiDate($end);

                            // Add visual indicator for recent items
                            $isRecent = false;
                            if ($end && $end->gt(Carbon::now()->subDays(10))) {
                                $isRecent = true;
                            } elseif (!$end && $start && $start->gt(Carbon::now()->subDays(3))) {
                                $isRecent = true;
                            }
                        @endphp

                        <tr class="hover:bg-gray-50 transition-colors {{ $isRecent ? 'bg-blue-50' : '' }}">
                            <td class="py-4 px-3 border-b text-gray-500">
                                {{ $index + 1 }}
                                @if($isRecent)
                                    <span class="inline-block w-2 h-2 bg-blue-500 rounded-full ml-2" title="รายการล่าสุด"></span>
                                @endif
                            </td>

                            <td class="p-4 border-b max-w-[250px]">
                                <div class="font-medium text-gray-800">{{ $reportTitle }}</div>
                                @if($isRecent)
                                    <div class="text-xs text-blue-600 mt-1">รายการล่าสุด</div>
                                @endif
                            </td>

                            <td class="py-4 px-3 border-b text-gray-500">
                                @if($startFormatted !== '-')
                                    <div class="font-medium">{{ $startFormatted['date'] }}</div>
                                    <div class="text-xs text-gray-400">{{ $startFormatted['time'] }}</div>
                                @else
                                    -
                                @endif
                            </td>

                            <td class="py-4 px-3 border-b text-gray-500">
                                @if($endFormatted !== '-')
                                    <div class="font-medium">{{ $endFormatted['date'] }}</div>
                                    <div class="text-xs text-gray-400">{{ $endFormatted['time'] }}</div>
                                @else
                                    -
                                @endif
                            </td>

                            <td class="py-4 px-3 border-b text-gray-500">{{ $evaluateeName }}</td>

                            <td class="py-4 px-3 border-b text-gray-500 align-top">
                                <div class="max-w-[280px]">
                                    @if($primaryReviewer)
                                        <div class="break-words font-medium text-gray-700">
                                            {{ $primaryReviewer['label'] }}: {{ $primaryReviewer['name'] }}
                                        </div>
                                        @if($additionalReviewerCount > 0)
                                            <button
                                                type="button"
                                                data-manager-reviewer-open
                                                data-manager-reviewers='@json($reviewerEntries->values())'
                                                class="mt-1 text-xs font-medium text-blue-600 hover:text-blue-800 underline"
                                            >
                                                เพิ่มเติม ({{ $additionalReviewerCount }} คน)
                                            </button>
                                        @endif
                                    @else
                                        -
                                    @endif
                                </div>
                            </td>

                            <td class="py-4 px-2 border-b text-center min-w-[180px]">
                                @php
                                    $statusClasses = [
                                        'รอการกรอกข้อมูล' => 'bg-yellow-100 text-yellow-800',
                                        'ยังไม่ประเมิน' => 'bg-red-100 text-red-800',
                                        'กำลังดำเนินการ' => 'bg-blue-100 text-blue-800',
                                        'รอผู้ประเมินประเมิน' => 'bg-yellow-100 text-yellow-800',
                                        'ผู้ประเมินเริ่มประเมิน' => 'bg-yellow-100 text-yellow-800',
                                        'รอกรรมการรับรองผล' => 'bg-yellow-100 text-yellow-800',
                                        'กรรมการเริ่มรับรองผล' => 'bg-yellow-100 text-yellow-800',
                                        'ประเมินเสร็จสิ้น' => 'bg-green-100 text-green-800',
                                    ];
                                    $statusClass = $statusClasses[$status] ?? 'bg-gray-100 text-gray-800';
                                @endphp
                                <span class="px-3 py-1 rounded-full text-sm font-medium {{ $statusClass }}">
                                    {{ $status }}
                                </span>
                            </td>

                            <td class="py-4 px-3 border-b text-center">
                                @php
                                    $actions = [
                                        'ยังไม่ประเมิน' => [
                                            'label' => 'เริ่มประเมิน',
                                            'classes' => 'bg-red-500 hover:bg-red-600 text-white',
                                        ],
                                        'กำลังดำเนินการ' => [
                                            'label' => 'ดำเนินการต่อ',
                                            'classes' => 'bg-blue-500 hover:bg-blue-600 text-white',
                                        ],
                                        'ประเมินเสร็จสิ้น' => [
                                            'label' => 'ดูผล',
                                            'classes' => 'bg-green-500 hover:bg-green-600 text-white',
                                        ],
                                        'รอการกรอกข้อมูล' => null,
                                        'รอผู้ประเมินประเมิน' => null,
                                        'ผู้ประเมินเริ่มประเมิน' => null,
                                        'รอกรรมการรับรองผล' => null,
                                        'กรรมการเริ่มรับรองผล' => null,
                                    ];
                                    $action = $actions[$status] ?? null;

                                    // Use the evaluatee ID for routing
                                    $evaluateeId = optional($evaluatee)->id ?? $evaluatorAssignment->evaluatee_id;

                                    $url = route('manager.show', ['id' => $report->id ?? 0]);

                                    if ($status === 'ประเมินเสร็จสิ้น') {
                                        $url .= '?readonly=1';
                                    }
                                    
                                @endphp

                                @if($action && $evaluateeId)
                                    <div class="flex items-center justify-center gap-2">
                                        <a href="{{ $url }}"
                                        class="min-w-[120px] inline-block px-4 py-2 text-sm font-medium rounded-xl shadow transition duration-200 {{ $action['classes'] }}">
                                            {{ $action['label'] }}
                                        </a>

                                        @if($status === 'ประเมินเสร็จสิ้น')
                                            <a href="{{ route('single.reports.export', ['id' => $report->id ?? 0]) }}"
                                            class="p-2 bg-green-400 hover:bg-green-500 text-white rounded-md shadow transition duration-200"
                                            title="ส่งออกรายงานผลการประเมินของ {{ $evaluatee->name ?? 'บุคคล' }}">
                                                <i class="fas fa-file-export"></i>
                                            </a>
                                        @endif
                                    </div>
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
        <div class="px-6 py-3 border-t border-gray-200">
            {{ $evaluations->links() }}
        </div>
    </div>
</div>

<div id="managerReviewerModal" class="hidden fixed inset-0 z-50 bg-slate-900/50 px-4 py-6">
    <div class="flex min-h-full items-center justify-center">
        <div class="w-full max-w-lg rounded-2xl bg-white shadow-xl">
            <div class="flex items-center justify-between border-b border-slate-200 px-5 py-4">
                <div>
                    <h3 class="text-lg font-semibold text-slate-900">รายชื่อผู้ประเมิน</h3>
                    <p class="text-sm text-slate-500">แสดงผู้ประเมินทั้งหมดตามลำดับที่กำหนด</p>
                </div>
                <button type="button" class="text-slate-400 hover:text-slate-600" data-manager-reviewer-close>
                    <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                    </svg>
                </button>
            </div>
            <div id="managerReviewerModalBody" class="space-y-3 px-5 py-5"></div>
        </div>
    </div>
</div>

@include('components.manager-table-reviewer-script')

