{{-- ส่วนหัวหน้าแก้ไขรอบการประเมิน --}}
<div class="bg-white shadow-sm rounded-lg p-6 mb-6">
    <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
        <div>
            <h1 class="text-2xl font-bold text-gray-800 flex items-center">
                <i class="fas fa-edit mr-3 text-blue-600"></i>
                แก้ไขรอบการประเมิน #{{ $assignmentData->id }}
            </h1>
            <p class="text-gray-600 mt-1">แก้ไขข้อมูลรอบการประเมิน</p>
        </div>
        <div class="flex gap-3">
            <a
                href="{{ route('assignment-data.index') }}"
                class="px-4 py-2 bg-gray-100 text-gray-700 rounded-md hover:bg-gray-200 transition-colors flex items-center"
            >
                <i class="fas fa-arrow-left mr-2"></i>กลับ
            </a>
        </div>
    </div>
</div>
