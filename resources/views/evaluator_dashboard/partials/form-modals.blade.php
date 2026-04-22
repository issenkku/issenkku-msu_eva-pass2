{{-- ฟอร์มปฏิเสธสำหรับการเปลี่ยนสถานะ --}}
<form id="reject-form" action="{{ route('evaluator.reject', $assignment->report_id) }}" method="POST" style="display:inline;">
    @csrf
    @method('PUT')
</form>

{{-- กล่องยืนยันการบันทึกและส่งแบบประเมิน --}}
<div id="submitConfirmationModal" class="fixed inset-0 bg-gray-800 bg-opacity-60 overflow-y-auto h-full w-full hidden z-50 flex items-center justify-center">
    <div class="relative p-5 border w-full max-w-md shadow-lg rounded-xl bg-white">
        <div class="mt-3 text-center">
            <div class="mx-auto flex items-center justify-center h-12 w-12 rounded-full bg-blue-100">
                <svg class="h-6 w-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                </svg>
            </div>
            <h3 class="text-lg leading-6 font-medium text-gray-900 mt-4">ยืนยันการบันทึกข้อมูล</h3>
            <div class="mt-2 px-7 py-3">
                <p class="text-sm text-gray-600">
                    คุณแน่ใจหรือไม่ว่าต้องการบันทึกคะแนนและส่งแบบประเมิน?
                </p>
            </div>
            <div class="items-center px-4 py-3 space-x-4">
                <button id="cancelSubmitModalBtn" class="btn btn-secondary w-28">ยกเลิก</button>
                <button id="confirmSubmitModalBtn" class="btn btn-primary w-28">ยืนยัน</button>
            </div>
        </div>
    </div>
</div>

{{-- กล่องยืนยันการปฏิเสธแบบประเมิน --}}
<div id="rejectConfirmationModal" class="fixed inset-0 bg-gray-800 bg-opacity-60 overflow-y-auto h-full w-full hidden z-50 flex items-center justify-center">
    <div class="relative p-5 border w-full max-w-md shadow-lg rounded-xl bg-white">
        <div class="mt-3 text-center">
            <div class="mx-auto flex items-center justify-center h-12 w-12 rounded-full bg-yellow-100">
                <svg class="h-6 w-6 text-yellow-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M4.93 19h14.14A2 2 0 0020.79 16L13.72 3.86a2 2 0 00-3.44 0L3.21 16A2 2 0 004.93 19z"></path>
                </svg>
            </div>
            <h3 class="text-lg leading-6 font-medium text-gray-900 mt-4">ยืนยันการปฏิเสธ</h3>
            <div class="mt-2 px-7 py-3">
                <p class="text-sm text-gray-600">
                    คุณแน่ใจหรือไม่ว่าต้องการปฏิเสธแบบประเมินนี้?
                </p>
            </div>
            <div class="items-center px-4 py-3 space-x-4">
                <button id="cancelRejectModalBtn" class="btn btn-secondary w-28">ยกเลิก</button>
                <button id="confirmRejectModalBtn" class="btn btn-warning w-28">ยืนยัน</button>
            </div>
        </div>
    </div>
</div>
