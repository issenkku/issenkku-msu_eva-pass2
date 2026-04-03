{{-- modal ยืนยันการบันทึก ใช้เฉพาะหน้า create --}}
<div id="confirm_modal"
    class="fixed inset-0 {{ $backdropClass ?? 'bg-opacity-50' }} backdrop-blur-md flex items-center justify-center z-50 hidden">
    <div class="bg-white p-8 rounded-xl shadow-2xl max-w-md w-full">
        <div class="text-center">
            <div class="bg-blue-100 rounded-full p-4 mx-auto w-20 h-20 flex items-center justify-center mb-6">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-10 w-10 text-blue-600" fill="none"
                    viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
            </div>
            <h3 class="text-xl font-bold text-gray-900 mb-3">ยืนยันการบันทึกข้อมูล</h3>
            <p class="text-gray-600 mb-3">ชื่อเกณฑ์: <span id="version_name_display" class="font-medium"></span></p>
            <p class="text-gray-600 mb-6">คุณต้องการบันทึกข้อมูลเกณฑ์การประเมินนี้หรือไม่?</p>
            <div class="flex justify-center space-x-4">
                <button id="cancel_modal_btn"
                    class="px-5 py-2.5 bg-gray-200 text-gray-700 rounded-lg hover:bg-gray-300 transition">ยกเลิก</button>
                <button id="confirm_submit_btn"
                    class="px-5 py-2.5 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition">ยืนยัน</button>
            </div>
        </div>
    </div>
</div>
