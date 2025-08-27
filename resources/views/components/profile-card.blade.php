@props(['user','title'])

<div class="bg-purple-50 p-6 rounded-2xl shadow-md border-purple-300 border">
    <h3 class="text-xl font-bold text-purple-900 mb-6 border-b border-purple-300 pb-2">{{ $title}}</h3>

    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-y-6 gap-x-6 sm:gap-x-10 lg:gap-x-16">

        <!-- Left Column -->
        <div class="space-y-3">
            <div class="flex">
                <span class="font-bold text-gray-800 w-32 flex-shrink-0">ชื่อ-สกุล:</span>
                <span class="text-gray-700 break-all">{{ $user->name ?? '-' }}</span>
            </div>
            <div class="flex">
                <span class="font-bold text-gray-800 w-32 flex-shrink-0">ตำแหน่ง:</span>
                <span class="text-gray-700">{{ $user->position->name ?? '-' }}</span>
            </div>
        </div>

        <!-- Middle Column -->
        <div class="space-y-3">
            <div class="flex">
                <span class="font-bold text-gray-800 w-36 flex-shrink-0">รหัสประจำตัว:</span>
                <span class="text-gray-700">{{ $user->employee_id ?? '-' }}</span>
            </div>
            <div class="flex">
                <span class="font-bold text-gray-800 w-36 flex-shrink-0">หน่วยงาน/คณะ:</span>
                <span class="text-gray-700 break-all">{{ $user->department->department_name ?? '-' }}</span>
            </div>
        </div>
        
        <!-- Right Column -->
        <div class="space-y-3">
            <div class="flex">
                <span class="font-bold text-gray-800 w-36 flex-shrink-0">Email:</span>
                <span class="text-gray-700 break-all">{{ $user->email ?? '-' }}</span>
            </div>
            <div class="flex">
                <span class="font-bold text-gray-800 w-36 flex-shrink-0">ประเภทบุคลากร:</span>
                <span class="text-gray-700">{{ $user->personnel_type ?? '-' }}</span>
            </div>
        </div>

    </div>
</div>
