{{-- ปุ่มลอยแจ้งว่ามีข้อมูลยังไม่ถูกบันทึก --}}
<div id="floating_save_button" class="fixed bottom-6 right-6 z-40 hidden">
    <div class="flex items-center gap-3 rounded-2xl bg-blue-600 px-4 py-3 text-white shadow-2xl ring-1 ring-blue-500/40">
        <div class="hidden sm:block">
            <p class="text-sm font-semibold">มีการแก้ไขที่ยังไม่บันทึก</p>
            <p class="text-xs text-blue-100">กรุณากดบันทึกก่อนออกจากหน้านี้</p>
        </div>
        <button type="button" id="floating_save_submit"
            class="inline-flex items-center gap-2 rounded-xl bg-white px-4 py-2 text-sm font-semibold text-blue-700 transition hover:bg-blue-50">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24"
                stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
            </svg>
            บันทึก
        </button>
    </div>
</div>
