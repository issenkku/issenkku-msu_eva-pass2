{{-- ส่วนรายการที่ควรติดตามของแดชบอร์ด --}}
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
                    ? \Carbon\Carbon::parse($followUp->assignmentData->end_time)->format('d/m/Y')
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
