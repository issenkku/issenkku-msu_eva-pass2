{{-- overlay ระหว่างบันทึกข้อมูล เพื่อกันการกดซ้ำระหว่าง submit --}}
<div id="loading-overlay" class="fixed inset-0 bg-gray-900 bg-opacity-75 hidden z-50">
    <div class="flex items-center justify-center h-full">
        <div class="text-center text-white">
            <svg class="animate-spin h-10 w-10 text-white mx-auto mb-4" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
            </svg>
            <p class="text-lg font-semibold">กำลังบันทึกข้อมูล...</p>
            <p class="text-sm">กรุณารอสักครู่</p>
        </div>
    </div>
</div>
