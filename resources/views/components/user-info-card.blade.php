@props(['user'])

{{-- การ์ดข้อมูลผู้รับการประเมิน --}}
<div class="rounded-xl bg-white p-6 shadow-md">
    <div class="mb-4">
        <h2 class="text-lg font-semibold" style="color: #6f42c1;">ข้อมูลผู้รับการประเมิน</h2>
        <div class="mt-2 h-px" style="background-color: #d1c4e9;"></div>
    </div>

    <div class="grid grid-cols-1 gap-x-8 gap-y-4 text-sm md:grid-cols-3">
        <div class="space-y-4">
            <div class="flex">
                <span class="w-24 shrink-0 font-bold">ชื่อ-สกุล:</span>
                <span class="text-gray-700">{{ $user['name'] ?? 'N/A' }}</span>
            </div>
            <div class="flex">
                <span class="w-24 shrink-0 font-bold">ตำแหน่ง:</span>
                <span class="text-gray-700">{{ $user['position'] ?? 'N/A' }}</span>
            </div>
        </div>

        <div class="space-y-4">
            <div class="flex">
                <span class="w-24 shrink-0 font-bold">รหัสประจำตัว:</span>
                <span class="text-gray-700">{{ $user['employee_id'] ?? 'N/A' }}</span>
            </div>
            <div class="flex">
                <span class="w-24 shrink-0 font-bold">หน่วยงาน/คณะ:</span>
                <span class="text-gray-700">{{ $user['department'] ?? 'N/A' }}</span>
            </div>
        </div>

        <div class="space-y-4">
            <div class="flex">
                <span class="w-24 shrink-0 font-bold">Email:</span>
                <span class="break-all text-gray-700">{{ $user['email'] ?? 'N/A' }}</span>
            </div>
            <div class="flex">
                <span class="w-24 shrink-0 font-bold">ประเภทบุคลากร:</span>
                <span class="text-gray-700">{{ $user['personnel_type'] ?? 'N/A' }}</span>
            </div>
        </div>
    </div>
</div>
