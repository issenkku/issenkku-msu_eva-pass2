<div class="bg-gradient-to-br from-purple-100 to-pink-100 p-6 rounded-2xl shadow-md">
    <h3 class="text-xl font-bold text-purple-900 mb-6 border-b border-purple-300 pb-2">ข้อมูลผู้รับการประเมิน</h3>

    <div class="flex items-center justify-center gap-20">
        <!-- Avatar -->
        <div class="w-24 h-24 bg-gray-300 rounded-full overflow-hidden shadow-inner border-4 border-white">
            <img
                src="{{ $user->photo_url ?? asset('images/default-avatar.png') }}"
                alt="User Photo"
                class="w-full h-full object-cover"
            >
        </div>

        <!-- Info -->
        <div class="grid grid-cols-1 md:grid-cols-2 gap-20">
            <!-- Left Column -->
            <div class="space-y-3">
                <div class="flex">
                    <span class="font-bold text-gray-800 w-32 flex-shrink-0">ชื่อ-สกุล:</span>
                    <span class="text-gray-700">{{ $user->name ?? '-' }}</span>
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
                    <span class="font-bold text-gray-800 w-36 flex-shrink-0">ประเภทบุคลากร:</span>
                    <span class="text-gray-700">{{ $user->personnel_type ?? '-' }}</span>
                </div>
            </div>
        </div>
    </div>
</div>