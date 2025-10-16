@props([
    'startTimeFormatted' => '-',
    'endTimeFormatted' => '-',
    'reportName' => 'ไม่พบชื่อรายงาน',
    'report' => null,
    'user' => null,
    'assignment' => null,
    'assessmentType' => null
])

<div class="p-6 rounded-2xl shadow-md"
    style="background: linear-gradient(180deg, #ffffffff 0%, #f9f5ffff 100%); border-color: #f3e8ff;">
    <h3 class="text-xl font-bold text-purple-900 mb-6 border-b border-purple-300 pb-2">ข้อมูลผู้รับการประเมิน</h3>

    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
        <!-- Left Column -->
        <div class="space-y-3">
            <div class="flex">
                <span class="font-bold text-gray-800 w-32 flex-shrink-0">ชื่อ-สกุล:</span>
                <span class="text-gray-700">{{ $user->name ?? '-' }}</span>
            </div>
            <div class="flex">
                <span class="font-bold text-gray-800 w-32 flex-shrink-0">ชื่อผู้ประเมิน:</span>
                 <span class="text-gray-700">
                    {{ $assignment->assignmentData->evaluatorUser?->name ?? '-' }}
                 </span>
            </div>
            <div class="flex">
                <span class="font-bold text-gray-800 w-32 flex-shrink-0">ตำแหน่ง:</span>
                <span class="text-gray-700">{{ $user->position->name ?? '-' }}</span>
            </div>
        </div>

        <!-- Right Column -->
        <div class="space-y-3">
            <div class="flex">
                <span class="font-bold text-gray-800 w-36 flex-shrink-0">หน่วยงาน/คณะ:</span>
                <span class="text-gray-700">{{ $user->department->department_name ?? '-' }}</span>
            </div>
            <div class="flex">
                <span class="font-bold text-gray-800 w-36 flex-shrink-0">กลุ่มงาน:</span>
                <span class="text-gray-700">{{ $assessmentType }}</span>
            </div>
            <div class="flex">
                <span class="font-bold text-gray-800 w-36 flex-shrink-0">รอบการประเมิน:</span>
                <span class="text-gray-700">{{ $startTimeFormatted }} - {{ $endTimeFormatted }}</span>
            </div>
        </div>
    </div>
</div>