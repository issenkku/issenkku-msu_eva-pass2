{{-- modal ยืนยันก่อนส่งแบบประเมิน --}}
<div id="confirmationModal" class="fixed inset-0 bg-gray-900 bg-opacity-75 flex items-center justify-center p-4 hidden z-50 transition-opacity duration-300">
    <div class="bg-white rounded-2xl shadow-xl w-full max-w-sm p-8 text-center transform transition-all duration-300 scale-95 opacity-0" id="modal-content">
        <div class="mx-auto flex items-center justify-center h-16 w-16 rounded-full bg-blue-100 mb-5">
            <svg class="h-8 w-8 text-blue-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8.228 9.049c.534-2.203 2.51-3.79 4.772-3.79s4.238 1.587 4.772 3.79M8.228 9.049L6.5 10.5m1.728-1.451L9.5 6.5m6.228 2.549L17.5 10.5m-1.728-1.451L14.5 6.5M12 21a9 9 0 110-18 9 9 0 010 18z"></path>
            </svg>
        </div>

        <h3 class="text-xl font-bold text-gray-800">ยืนยันการส่งแบบประเมิน</h3>

        <div class="mt-2 mb-6">
            <p class="text-sm text-gray-500 px-4">
                เมื่อส่งแล้วจะไม่สามารถกลับมาแก้ไขได้อีก<br>คุณต้องการดำเนินการต่อหรือไม่?
            </p>
        </div>

        <div class="flex flex-col space-y-3">
            <button id="confirmSubmitBtn" class="w-full px-4 py-3 bg-purple-600 text-white rounded-lg font-semibold hover:bg-purple-700 transition-colors duration-200">
                ยืนยัน
            </button>
            <button id="cancelModalBtn" class="w-full px-4 py-3 bg-gray-100 text-gray-700 rounded-lg font-semibold hover:bg-gray-200 transition-colors duration-200">
                ยกเลิก
            </button>
        </div>
    </div>
</div>
