{{-- ข้อความสถานะหลังนำเข้าผู้ใช้งาน --}}
@if(session('success'))
    <div id="successMessage" class="fixed top-4 right-4 bg-green-500 text-white px-6 py-4 rounded-lg shadow-lg z-[10000] transform transition-transform duration-300">
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

@if(session('warning'))
    <div id="warningMessage" class="fixed top-4 right-4 bg-yellow-500 text-white px-6 py-4 rounded-lg shadow-lg z-[10000] transform transition-transform duration-300">
        <div class="flex items-center space-x-3">
            <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
            </svg>
            <span>{{ session('warning') }}</span>
            <button type="button" aria-label="ปิดข้อความแจ้งเตือน" title="ปิดข้อความแจ้งเตือน" data-flash-close class="ml-2 text-white hover:text-gray-200">
                <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                </svg>
            </button>
        </div>
    </div>
@endif

@if(session('error'))
    <div id="errorMessage" class="fixed top-4 right-4 bg-red-500 text-white px-6 py-4 rounded-lg shadow-lg z-[10000] transform transition-transform duration-300 max-w-md">
        <div class="flex items-start space-x-3">
            <svg class="h-6 w-6 mt-0.5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
            </svg>
            <div>
                <div class="font-medium">เกิดข้อผิดพลาด</div>
                <div class="text-sm mt-1">{{ session('error') }}</div>
            </div>
            <button type="button" aria-label="ปิดข้อความแจ้งเตือน" title="ปิดข้อความแจ้งเตือน" data-flash-close class="ml-4 text-white hover:text-gray-200">
                <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                </svg>
            </button>
        </div>
    </div>
@endif
