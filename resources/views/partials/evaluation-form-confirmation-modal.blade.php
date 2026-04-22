{{-- โมดัลยืนยันก่อนส่งแบบประเมิน --}}
<div id="confirmationModal" class="fixed inset-0 z-50 hidden bg-gray-900 bg-opacity-75 p-4 transition-opacity duration-300 flex items-center justify-center">
    <div id="modal-content" class="w-full max-w-sm scale-95 rounded-2xl bg-white p-8 text-center opacity-0 shadow-xl transform transition-all duration-300">
        <div class="mx-auto mb-5 flex h-16 w-16 items-center justify-center rounded-full bg-blue-100">
            <svg class="h-8 w-8 text-blue-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8.228 9.049c.534-2.203 2.51-3.79 4.772-3.79s4.238 1.587 4.772 3.79M8.228 9.049L6.5 10.5m1.728-1.451L9.5 6.5m6.228 2.549L17.5 10.5m-1.728-1.451L14.5 6.5M12 21a9 9 0 110-18 9 9 0 010 18z"></path>
            </svg>
        </div>

        <h3 class="text-xl font-bold text-gray-800">ยืนยันการส่งแบบประเมิน</h3>

        <div class="mb-6 mt-2">
            <p class="px-4 text-sm text-gray-500">
                เมื่อส่งแล้วจะไม่สามารถกลับมาแก้ไขได้อีก<br>คุณต้องการดำเนินการต่อหรือไม่?
            </p>
        </div>

        <div class="flex flex-col space-y-3">
            <button id="confirmSubmitBtn" class="w-full rounded-lg bg-purple-600 px-4 py-3 font-semibold text-white transition-colors duration-200 hover:bg-purple-700">
                ยืนยัน
            </button>
            <button id="cancelModalBtn" class="w-full rounded-lg bg-gray-100 px-4 py-3 font-semibold text-gray-700 transition-colors duration-200 hover:bg-gray-200">
                ยกเลิก
            </button>
        </div>
    </div>
</div>
