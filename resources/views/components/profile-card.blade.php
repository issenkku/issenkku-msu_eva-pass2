{{-- ไฟล์มุมมอง: resources/views/components/profile-card.blade.php --}}
@props(['user', 'title'])

<div class="rounded-2xl border border-purple-300 bg-purple-50 p-6 shadow-md">
    <h3 class="mb-6 border-b border-purple-300 pb-2 text-xl font-bold text-purple-900">{{ $title }}</h3>

    <div class="grid grid-cols-1 gap-x-6 gap-y-6 sm:gap-x-10 md:grid-cols-2 lg:grid-cols-3 lg:gap-x-16">
        <div class="space-y-3">
            <div class="flex">
                <span class="w-32 flex-shrink-0 font-bold text-gray-800">ชื่อ-สกุล:</span>
                <span class="break-all text-gray-700">{{ $user->name ?? '-' }}</span>
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
                <span class="w-36 flex-shrink-0 font-bold text-gray-800">รหัสประจำตัว:</span>
                <span class="text-gray-700">{{ $user->employee_id ?? '-' }}</span>
            </div>
            <div class="flex">
                <span class="w-36 flex-shrink-0 font-bold text-gray-800">หน่วยงาน/คณะ:</span>
                <span class="break-all text-gray-700">{{ $user->department->department_name ?? '-' }}</span>
            </div>
        </div>

        <div class="space-y-3">
            <div class="flex">
                <span class="w-36 flex-shrink-0 font-bold text-gray-800">Email:</span>
                <span class="break-all text-gray-700">{{ $user->email ?? '-' }}</span>
            </div>
            <div class="flex">
                <span class="w-36 flex-shrink-0 font-bold text-gray-800">ประเภทบุคลากร:</span>
                <span class="text-gray-700">{{ $user->personnel_type ?? '-' }}</span>
            </div>
        </div>
    </div>
</div>
