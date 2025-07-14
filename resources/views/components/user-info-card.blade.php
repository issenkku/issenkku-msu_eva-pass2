@props(['user'])

<div class="bg-white rounded-xl shadow-md p-6">
    <!-- Header -->
    <div class="mb-4">
        <h2 class="text-lg font-semibold" style="color: #6f42c1;">ข้อมูลผู้รับการประเมิน</h2>
        <div class="h-px mt-2" style="background-color: #d1c4e9;"></div>
    </div>

    <!-- User Details Grid -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-x-8 gap-y-4 text-sm">
        
        <!-- Column 1 -->
        <div class="space-y-4">
            <div class="flex">
                <span class="font-bold w-24 shrink-0">ชื่อ-สกุล:</span>
                <span class="text-gray-700">{{ $user['name'] ?? 'N/A' }}</span>
            </div>
            <div class="flex">
                <span class="font-bold w-24 shrink-0">ตำแหน่ง:</span>
                <span class="text-gray-700">{{ $user['position'] ?? 'N/A' }}</span>
            </div>
        </div>
        
        <!-- Column 2 -->
        <div class="space-y-4">
            <div class="flex">
                <span class="font-bold w-24 shrink-0">รหัสประจำตัว:</span>
                <span class="text-gray-700">{{ $user['employee_id'] ?? 'N/A' }}</span>
            </div>
             <div class="flex">
                <span class="font-bold w-24 shrink-0">หน่วยงาน/คณะ:</span>
                <span class="text-gray-700">{{ $user['department'] ?? 'N/A' }}</span>
            </div>
        </div>

        <!-- Column 3 -->
        <div class="space-y-4">
             <div class="flex">
                <span class="font-bold w-24 shrink-0">Email:</span>
                <span class="text-gray-700">{{ $user['email'] ?? 'N/A' }}</span>
            </div>
            <div class="flex">
                <span class="font-bold w-24 shrink-0">ประเภทบุคลากร:</span>
                <span class="text-gray-700">{{ $user['personnel_type'] ?? 'N/A' }}</span>
            </div>
        </div>

    </div>
</div>