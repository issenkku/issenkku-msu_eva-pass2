{{-- มุมมองตารางสรุปสถานะการประเมินของผู้ประเมิน --}}
@props(['evaluations', 'statusCounts', 'years'])

@php
    use Carbon\Carbon;

    $formatThaiDate = function ($date) {
        if (!$date) {
            return '-';
        }

        Carbon::setLocale('th');
        setlocale(LC_TIME, 'th_TH.UTF-8');

        $thaiMonth = $date->translatedFormat('j F');
        $buddhistYear = $date->year + 543;
        $time = $date->format('H:i');

        return [
            'date' => "{$thaiMonth} {$buddhistYear}",
            'time' => "{$time} น.",
        ];
    };

    // เรียงรายการล่าสุดขึ้นก่อน
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

    $filteredStatus = request('status');
    if ($filteredStatus) {
        $reverseMap = [
            'รอการกรอกข้อมูล' => ['Assigned', 'Draft'],
            'ยังไม่ประเมิน' => ['Pending'],
            'กำลังดำเนินการ' => ['Evaluator_draft'],
            'รอผลการประเมิน' => ['Director_assigned', 'Director_draft', 'Manager_draft', 'Manager_assign'],
            'ประเมินเสร็จสิ้น' => ['Completed'],
        ];

        $statusCodes = $reverseMap[$filteredStatus] ?? [$filteredStatus];

        $sortedEvaluations = $sortedEvaluations->filter(function ($evaluatorAssignment) use ($statusCodes) {
            return in_array(optional($evaluatorAssignment->report)->status, $statusCodes);
        })->values();
    }
@endphp

<div class="evaluation-list-card rounded-lg bg-white p-6">
    <h3 class="mb-4 text-lg font-semibold text-gray-800">ภาพรวมสถานะการประเมิน</h3>

    <div class="evaluation-list-toolbar mb-4 flex flex-wrap justify-between gap-4 border-b pb-4 pl-3 pr-3">
        <x-search-bar placeholder="ค้นหาชื่อ, รายงาน..." />

        <div class="evaluation-toolbar-actions flex flex-wrap justify-between gap-2">
            <x-export-button
                :route="route('export.reports')"
                label="ส่งออก Excel ทั้งหมด" />
            <x-filter-badge-single
                name="year"
                placeholder="ปีการประเมินทั้งหมด"
                :options="$years->mapWithKeys(fn($y) => [$y => $y + 543])->toArray()"
            />
        </div>
    </div>

    @php
        $statusStyles = [
            'รอการกรอกข้อมูล' => 'bg-orange-100 text-orange-800 hover:bg-orange-200',
            'ยังไม่ประเมิน' => 'bg-red-100 text-red-800 hover:bg-red-200',
            'กำลังดำเนินการ' => 'bg-blue-100 text-blue-800 hover:bg-blue-200',
            'รอผลการประเมิน' => 'bg-yellow-100 text-yellow-800 hover:bg-yellow-200',
            'ประเมินเสร็จสิ้น' => 'bg-green-100 text-green-800 hover:bg-green-200',
        ];

        $firstStatus = array_key_first($statusCounts);
    @endphp

    <div class="mb-6 flex flex-wrap gap-3">
        @foreach ($statusCounts as $status => $count)
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
                data-evaluator-ajax-link
                @if ($isActive) aria-current="true" @endif
                class="inline-block rounded-full px-3 py-1 text-sm font-medium transition {{ $style }} {{ $activeClass }}">
                {{ $status }} ({{ $count }})
            </a>
        @endforeach
    </div>

    <div class="relative overflow-x-auto">
        <div class="pointer-events-none absolute left-0 top-0 z-10 h-full w-10 bg-gradient-to-r from-white to-transparent"></div>
        <div class="pointer-events-none absolute right-0 top-0 z-10 h-full w-10 bg-gradient-to-l from-white to-transparent"></div>

        <div class="overflow-x-auto scrollbar-thin scrollbar-track-gray-100 scrollbar-thumb-gray-300">
            <table class="min-w-[900px] w-full border-collapse text-sm">
                <thead>
                    <tr class="bg-gray-50">
                        <th class="whitespace-nowrap border-b p-4 text-left font-medium text-gray-800">อันดับ</th>
                        <th class="whitespace-nowrap border-b p-4 text-left font-medium text-gray-800">รายการประเมิน</th>
                        <th class="whitespace-nowrap border-b p-4 text-left font-medium text-gray-800">วันที่เริ่มประเมิน</th>
                        <th class="whitespace-nowrap border-b p-4 text-left font-medium text-gray-800">วันที่สิ้นสุดประเมิน</th>
                        <th class="whitespace-nowrap border-b p-4 text-left font-medium text-gray-800">ผู้รับการประเมิน</th>
                        <th class="min-w-[180px] whitespace-nowrap border-b p-4 text-center font-medium text-gray-800">สถานะ</th>
                        <th class="whitespace-nowrap border-b p-4 text-center font-medium text-gray-800">การดำเนินการ</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($sortedEvaluations as $index => $evaluatorAssignment)
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
                                'Pending' => 'ยังไม่ประเมิน',
                                'Evaluator_draft' => 'กำลังดำเนินการ',
                                'Director_assigned' => 'รอผลการประเมิน',
                                'Director_draft' => 'รอผลการประเมิน',
                                'Manager_assign' => 'รอผลการประเมิน',
                                'Manager_draft' => 'รอผลการประเมิน',
                                'Completed' => 'ประเมินเสร็จสิ้น',
                            ];
                            $status = $statusMapping[$statusFromDB] ?? $statusFromDB;

                            $start = optional($assignmentData)->start_time ? Carbon::parse($assignmentData->start_time) : null;
                            $end = optional($assignmentData)->end_time ? Carbon::parse($assignmentData->end_time) : null;

                            $evaluateeName = optional($evaluatee)->name ?? '-';

                            $startFormatted = $formatThaiDate($start);
                            $endFormatted = $formatThaiDate($end);

                            $isRecent = false;
                            if ($end && $end->gt(Carbon::now()->subDays(10))) {
                                $isRecent = true;
                            } elseif (!$end && $start && $start->gt(Carbon::now()->subDays(3))) {
                                $isRecent = true;
                            }
                        @endphp

                        <tr class="transition-colors hover:bg-gray-50 {{ $isRecent ? 'bg-blue-50' : '' }}">
                            <td class="border-b p-4 text-gray-500">
                                {{ $index + 1 }}
                                @if ($isRecent)
                                    <span class="ml-2 inline-block h-2 w-2 rounded-full bg-blue-500" title="รายการล่าสุด"></span>
                                @endif
                            </td>

                            <td class="border-b p-4">
                                <div class="font-medium text-gray-800">{{ $reportTitle }}</div>
                                @if ($isRecent)
                                    <div class="mt-1 text-xs text-blue-600">รายการล่าสุด</div>
                                @endif
                            </td>

                            <td class="border-b p-4 text-gray-500">
                                @if ($startFormatted !== '-')
                                    <div class="font-medium">{{ $startFormatted['date'] }}</div>
                                    <div class="text-xs text-gray-400">{{ $startFormatted['time'] }}</div>
                                @else
                                    -
                                @endif
                            </td>

                            <td class="border-b p-4 text-gray-500">
                                @if ($endFormatted !== '-')
                                    <div class="font-medium">{{ $endFormatted['date'] }}</div>
                                    <div class="text-xs text-gray-400">{{ $endFormatted['time'] }}</div>
                                @else
                                    -
                                @endif
                            </td>

                            <td class="border-b p-4 text-gray-500">{{ $evaluateeName }}</td>

                            <td class="min-w-[180px] border-b px-2 py-4 text-center">
                                @php
                                    $statusClasses = [
                                        'รอการกรอกข้อมูล' => 'bg-orange-100 text-orange-800',
                                        'ยังไม่ประเมิน' => 'bg-red-100 text-red-800',
                                        'กำลังดำเนินการ' => 'bg-blue-100 text-blue-800',
                                        'รอผลการประเมิน' => 'bg-yellow-100 text-yellow-800',
                                        'ประเมินเสร็จสิ้น' => 'bg-green-100 text-green-800',
                                    ];
                                    $statusClass = $statusClasses[$status] ?? 'bg-gray-100 text-gray-800';
                                @endphp
                                <span class="rounded-full px-3 py-1 text-sm font-medium {{ $statusClass }}">
                                    {{ $status }}
                                </span>
                            </td>

                            <td class="border-b p-4 text-center">
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
                                        'รอผลการประเมิน' => [
                                            'label' => 'ดูการกรอกข้อมูล',
                                            'classes' => 'bg-yellow-500 hover:bg-yellow-600 text-white',
                                        ],
                                        'ประเมินเสร็จสิ้น' => [
                                            'label' => 'ดูผล',
                                            'classes' => 'bg-green-500 hover:bg-green-600 text-white',
                                        ],
                                        'รอการกรอกข้อมูล' => null,
                                    ];
                                    $action = $actions[$status] ?? null;

                                    $evaluateeId = optional($evaluatee)->id ?? $evaluatorAssignment->evaluatee_id;
                                    $url = route('evaluator.evaluator.show', ['id' => $report->id ?? 0]);

                                    if ($status === 'รอผลการประเมิน' || $status === 'ประเมินเสร็จสิ้น') {
                                        $url .= '?readonly=1';
                                    }
                                @endphp

                                @if ($action && $evaluateeId)
                                    <div class="flex gap-2">
                                        <a href="{{ $url }}"
                                            class="inline-block min-w-[120px] rounded-xl px-4 py-2 text-sm font-medium shadow transition duration-200 {{ $action['classes'] }}">
                                            {{ $action['label'] }}
                                        </a>

                                        @if ($status === 'ประเมินเสร็จสิ้น')
                                            <a href="{{ route('single.reports.export', ['id' => $report->id ?? 0]) }}"
                                                class="rounded-md bg-green-400 p-2 text-white shadow transition duration-200 hover:bg-green-500"
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
                            <td colspan="7" class="py-8 text-center text-gray-500">
                                <i class="fas fa-inbox mb-2 block text-3xl"></i>
                                <p>ไม่มีข้อมูลการประเมิน</p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="border-t border-gray-200 px-6 py-3">
            {{ $evaluations->links() }}
        </div>
    </div>
</div>

