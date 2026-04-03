{{-- สถานะเมื่อยังไม่มีรอบการประเมินในระบบ --}}
<div class="px-6 py-12 text-center">
    <div class="max-w-sm mx-auto">
        <div class="p-6 bg-gray-50 rounded-full w-24 h-24 mx-auto flex items-center justify-center mb-4">
            <i class="fas fa-clipboard-list text-3xl text-gray-400"></i>
        </div>
        <h3 class="text-lg font-medium text-gray-900 mb-2">ยังไม่มีรอบการประเมิน</h3>
        <p class="text-gray-500 mb-6">เริ่มต้นด้วยการสร้างรอบการประเมินแรกของคุณ</p>
        <a href="{{ route('assignment-data.create') }}"
            class="inline-flex items-center px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700 transition-colors">
            <i class="fas fa-plus mr-2"></i>สร้างรอบการประเมินใหม่
        </a>
    </div>
</div>
