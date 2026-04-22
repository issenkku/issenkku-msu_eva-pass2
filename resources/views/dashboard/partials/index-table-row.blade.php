{{-- แถวข้อมูลของตารางผลการประเมินรายบุคคล --}}
@php
    $evaluateeName = $evaluation->evaluateeName ?? '-';
    $evaluatorName = $evaluation->evaluatorName ?? '-';
    $reviewerEntries = collect($evaluation->reviewerEntries ?? []);
    $primaryReviewer = $reviewerEntries->first();
    $additionalReviewerCount = max($reviewerEntries->count() - 1, 0);
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

<tr data-dashboard-row data-status-group="{{ $statusGroup }}" class="text-gray-900 transition-colors duration-150 hover:bg-gray-50">
    <td class="whitespace-nowrap px-6 py-4 text-center">
        {{ $loop->iteration }}
    </td>
    <td class="whitespace-nowrap px-6 py-4">
        <div class="flex items-center">
            <div class="text-sm font-medium text-gray-900">
                {{ $evaluateeName }}
            </div>
        </div>
    </td>

    @include('dashboard.partials.index-table-reviewers')

    <td class="whitespace-nowrap px-6 py-4 text-center align-middle">
        <span class="inline-flex items-center rounded px-2 py-0.5 text-xs font-medium {{ $statusClass }}">
            {{ $prettyStatus }}
        </span>
    </td>
    <td class="whitespace-nowrap px-6 py-4">
        <div class="mx-auto max-w-[160px]">
            <div class="flex items-center justify-between text-xs text-gray-500">
                <span>Progress</span>
                <span>{{ $progressPercentPerRow }}%</span>
            </div>
            <div class="mt-2 h-2 w-full overflow-hidden rounded-full bg-gray-100">
                <div
                    class="h-full rounded-full {{ $progressPercentPerRow === 100 ? 'bg-green-500' : ($progressPercentPerRow === 0 ? 'bg-red-500' : 'bg-blue-500') }}"
                    style="width: {{ $progressPercentPerRow }}%"></div>
            </div>
        </div>
    </td>
    <td class="whitespace-nowrap px-6 py-4">
        <div class="text-center text-sm font-medium text-gray-900">{{ $score }}</div>
    </td>

    @include('dashboard.partials.index-table-action-link')

    @include('dashboard.partials.index-table-export-action')
</tr>
