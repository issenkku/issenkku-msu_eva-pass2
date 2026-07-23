{{-- สรุปคะแนนรวมของ evaluator รับค่าที่คำนวณจาก controller/support class แล้ว --}}
<div class="mt-6 rounded-2xl border border-blue-200 bg-blue-50 p-6 shadow-sm">
    <h3 class="mb-4 flex items-center gap-2 text-xl font-bold text-blue-900">
        <svg class="h-6 w-6 text-blue-500" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" d="M9 17v-2h6v2m-7 4h8a2 2 0 002-2v-5a2 2 0 00-2-2h-1V7a4 4 0 10-8 0v5H9a2 2 0 00-2 2v5a2 2 0 002 2z" />
        </svg>
        สรุปคะแนนรวม
    </h3>

    <div class="space-y-3 text-blue-800">
        @if ($scoreSummary['has_quantity'] ?? false)
            <div class="flex items-center justify-between">
                <span class="text-base">คะแนนด้านปริมาณ (Quantity)</span>
                <span id="quantity-summary" class="font-semibold text-blue-900">{{ number_format($scoreSummary['quantity'] ?? 0, 2) }}</span>
            </div>
        @endif
        @if ($scoreSummary['has_quality'] ?? false)
            <div class="flex items-center justify-between">
                <span class="text-base">คะแนนด้านคุณภาพ (Quality)</span>
                <span id="quality-summary" class="font-semibold text-blue-900">{{ number_format($scoreSummary['quality'] ?? 0, 2) }}</span>
            </div>
        @endif
        @if ($scoreSummary['has_support'] ?? false)
            <div class="flex items-center justify-between">
                <span class="text-base">คะแนนสายสนับสนุน</span>
                <span id="support-summary" class="font-semibold text-blue-900">{{ number_format($scoreSummary['support'] ?? 0, 2) }}</span>
            </div>
        @endif
    </div>

    <div class="mt-5 flex flex-col gap-3 rounded-xl bg-white p-4 shadow-inner sm:flex-row sm:items-center sm:justify-between">
        <span class="text-lg font-semibold text-blue-700">คะแนนรวมทั้งหมด</span>
        <span id="total-summary" class="text-2xl font-bold text-blue-900">{{ number_format($scoreSummary['total'] ?? 0, 2) }}</span>
    </div>
</div>
