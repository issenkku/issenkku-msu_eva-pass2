{{-- ไฟล์มุมมอง: resources/views\components\evaluate-report-card.blade.php --}}
@props([
    'reportName' => 'ไม่พบชื่อรายงาน',
    'reportDescription' => '-',
    'assessmentType' => null,
    'reportComment' => '-',
])

{{-- บล็อกเนื้อหา --}}
<div class="p-6 rounded-2xl shadow-md"
    style="background: linear-gradient(180deg, #faf1ffff 0%, #f9f5ffff 100%); border-color: #f3e8ff;">
    <h3 class="text-xl font-bold text-purple-900 mb-6 border-b border-purple-300 pb-2">ข้อมูลเกณฑ์ประเมิน</h3>

    {{--  --}}
    <div class="grid grid-cols-1 gap-6">
        {{-- บล็อกเนื้อหา --}}
        <div class="space-y-3">
            <div class="flex">
                <span class="font-bold text-gray-800 w-40 flex-shrink-0">ชื่อเกณฑ์:</span>
                <span class="text-gray-700">{{ $reportName }}</span>
            </div>
            <div class="flex">
                <span class="font-bold text-gray-800 w-40 flex-shrink-0">คำอธิบายเกณฑ์:</span>
                <span class="text-gray-700">{{ $reportDescription }}</span>
            </div>
            <div class="flex">
                <span class="font-bold text-gray-800 w-40 flex-shrink-0">ประเภท:</span>
                <span class="text-gray-700">{{ $assessmentType }}</span>
            </div>
            <div class="flex">
                <span class="font-bold text-gray-800 w-40 flex-shrink-0">หมายเหตุ:</span>
                <span class="text-gray-700">{{ $reportComment }}</span>
            </div>
        </div>
    </div>
</div>
