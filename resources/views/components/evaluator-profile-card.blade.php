{{-- ไฟล์มุมมอง: resources/views\components\evaluator-profile-card.blade.php --}}
@props([
    'startTimeFormatted' => '-',
    'endTimeFormatted' => '-',
    'reportName' => 'ไม่พบชื่อรายงาน',
    'report' => null,
    'user' => null,
    'assignment' => null,
    'assessmentType' => null
])

{{-- บล็อกเนื้อหา --}}
<div class="bg-gradient-to-br from-purple-100 to-pink-100 p-6 rounded-2xl shadow-md">
    <h3 class="text-xl font-bold text-purple-900 mb-6 border-b border-purple-300 pb-2">ข้อมูลผู้รับการประเมิน</h3>

    {{--  --}}
    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
        <!-- Left Column -->
        {{-- บล็อกเนื้อหา --}}
        <div class="space-y-3">
            <div class="flex">
                <span class="font-bold text-gray-800 w-32 flex-shrink-0">ชื่อ-สกุล:</span>
                <span class="text-gray-700">{{ $assignment->evaluateeName }}</span>
            </div>
            <div class="flex">
                <span class="font-bold text-gray-800 w-32 flex-shrink-0">ตำแหน่ง:</span>
                <span class="text-gray-700">{{ $assignment->evaluateePosition }}</span>
            </div>
        </div>

        <!-- Right Column -->
        {{-- บล็อกเนื้อหา --}}
        <div class="space-y-3">
            <div class="flex">
                <span class="font-bold text-gray-800 w-36 flex-shrink-0">หน่วยงาน/คณะ:</span>
                <span class="text-gray-700">{{ $assignment->evaluateeDepartment }}</span>
            </div>
            <div class="flex">
                <span class="font-bold text-gray-800 w-36 flex-shrink-0">รอบการประเมิน:</span>
                <span class="text-gray-700">{{ $startTimeFormatted }} - {{ $endTimeFormatted }}</span>
            </div>
        </div>
    </div>
</div>
