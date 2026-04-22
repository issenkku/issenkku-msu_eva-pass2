{{-- ส่วนหัวหน้ารายการรอบการประเมิน --}}
<div class="bg-white shadow-sm rounded-lg p-6 mb-6">
    <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
        <div>
            <h1 class="text-2xl font-bold text-gray-800 flex items-center">
                <i class="fas fa-clipboard-list mr-3 text-blue-600"></i>
                จัดการรอบการประเมิน
            </h1>
            <p class="text-gray-600 mt-1">ดูข้อมูลและจัดการรอบการประเมินทั้งหมด</p>
        </div>
        <div class="flex flex-col sm:flex-row gap-3">
            <a
                href="{{ route('assignment-data.create') }}"
                class="px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700 transition-colors flex items-center justify-center"
            >
                <i class="fas fa-plus mr-2"></i>สร้างรอบการประเมินใหม่
            </a>
        </div>
    </div>
</div>
