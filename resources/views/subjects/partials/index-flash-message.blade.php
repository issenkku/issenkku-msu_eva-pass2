{{-- flash message หลังบันทึกข้อมูลสำเร็จ --}}
@if(session('success'))
    <div id="successMessage" class="fixed top-4 right-4 bg-green-500 text-white px-6 py-4 rounded-lg shadow-lg z-[10000] transform transition-transform duration-300">
        {{-- กล่องข้อความสำเร็จแบบลอยมุมขวาบน --}}
        <div class="flex items-center space-x-3">
            <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
            </svg>
            <span>{{ session('success') }}</span>
    <button type="button" aria-label="ปิดข้อความแจ้งเตือน" title="ปิดข้อความแจ้งเตือน" data-flash-close class="ml-2 text-white hover:text-gray-200">
        <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
        </svg>
    </button>
        </div>
    </div>
@endif
