@props([
    'startTimeFormatted' => '-',
    'endTimeFormatted' => '-',
    'reportName' => 'ไม่พบชื่อรายงาน',
    'report' => null,
    'user' => null,
    'assignment' => null,
    'assessmentType' => null,
])

<div class="rounded-2xl p-6 shadow-md" style="background: linear-gradient(180deg, #ffffffff 0%, #f9f5ffff 100%); border-color: #f3e8ff;">
    <h3 class="mb-6 border-b border-purple-300 pb-2 text-xl font-bold text-purple-900">ข้อมูลผู้รับการประเมิน</h3>

    <div class="grid grid-cols-1 gap-6 md:grid-cols-2">
        <div class="space-y-3">
            <div class="flex">
                <span class="w-32 flex-shrink-0 font-bold text-gray-800">ชื่อ-สกุล:</span>
                <span class="text-gray-700">{{ $user->name ?? '-' }}</span>
            </div>
            <div class="flex">
                <span class="w-32 flex-shrink-0 font-bold text-gray-800">ตำแหน่ง:</span>
                <span class="text-gray-700">{{ $user->position->name ?? '-' }}</span>
            </div>
            <div class="flex">
                <span class="w-32 flex-shrink-0 font-bold text-gray-800">ระดับตำแหน่ง:</span>
                <span class="text-gray-700">{{ $user->jobLevel->name ?? '-' }}</span>
            </div>
        </div>

        <div class="space-y-3">
            <div class="flex">
                <span class="w-36 flex-shrink-0 font-bold text-gray-800">หน่วยงาน/คณะ:</span>
                <span class="text-gray-700">{{ $user->department->department_name ?? '-' }}</span>
            </div>
            <div class="flex">
                <span class="w-36 flex-shrink-0 font-bold text-gray-800">กลุ่มงาน:</span>
                <span class="text-gray-700">{{ $assessmentType }}</span>
            </div>
            <div class="flex">
                <span class="w-36 flex-shrink-0 font-bold text-gray-800">รอบการประเมิน:</span>
                <span class="text-gray-700">{{ $startTimeFormatted }} - {{ $endTimeFormatted }}</span>
            </div>
        </div>
    </div>
</div>
