{{-- ส่วนสรุปข้อมูลและปุ่ม action ท้ายฟอร์ม --}}
<div class="bg-white shadow-sm rounded-lg p-6 form-section step-card step-4">
    <div class="flex items-center mb-6">
        <div class="flex items-center justify-center w-8 h-8 bg-orange-600 text-white rounded-full mr-3 text-sm font-semibold">
            4
        </div>
        <h2 class="text-xl font-semibold text-gray-800">สรุปและยืนยันการตั้งค่า</h2>
    </div>
    <div class="bg-gradient-to-r from-blue-50 to-green-50 rounded-lg p-6 mb-6">
        <div class="grid grid-cols-1 gap-6 md:grid-cols-2 items-stretch">
            <div class="bg-white rounded-xl border border-gray-200 p-6 shadow-md summary-card flex min-h-[220px] flex-col">
                <div class="mb-4 text-center">
                    <div class="text-lg font-bold text-blue-600">ระยะเวลาประเมิน</div>
                </div>
                <div class="bg-blue-50 rounded-lg p-4 min-h-[120px] flex flex-col items-center justify-center text-center">
                    <div class="text-3xl font-extrabold text-blue-600 leading-none" id="summary-period">-</div>
                    <div class="mt-3 text-sm font-medium text-gray-600">จำนวนวันของรอบประเมิน</div>
                </div>
            </div>
            <div class="bg-white rounded-xl border border-gray-200 p-6 shadow-md summary-card flex min-h-[220px] flex-col">
                <div class="mb-4 text-center">
                    <div class="text-lg font-bold text-green-600">เกณฑ์การประเมินที่เลือก</div>
                </div>
                <div class="bg-green-50 rounded-lg p-4 min-h-[120px] flex flex-col justify-center">
                    <div class="text-sm text-gray-600 font-medium mb-2">ชื่อเกณฑ์:</div>
                    <div class="text-base font-semibold text-green-700" id="summary-criteria-full">-</div>
                    <div class="text-xs text-gray-500 mt-2" id="summary-criteria-description">กรุณาเลือกเกณฑ์การประเมิน</div>
                </div>
            </div>
        </div>
    </div>

    <div class="d-flex flex-column flex-md-row justify-between items-start md:items-center mb-4 gap-3">
        <div class="text-sm text-gray-500">
            <i class="fas fa-info-circle mr-2"></i>
            <span class="font-medium">หมายเหตุ:</span>
            กรุณาตรวจสอบข้อมูลให้ถูกต้องก่อนบันทึก
        </div>
        <div class="flex justify-center items-center gap-2 flex-wrap">
            <a href="/assignment-data"
                class="px-6 py-2 bg-gray-300 text-gray-800 font-semibold rounded-md hover:bg-gray-400 transition-colors">
                <i class="fas fa-arrow-left mr-2"></i>ย้อนกลับ
            </a>
            <button type="button" id="reset-btn"
                class="px-6 py-2 bg-white text-blue-800 border-2 border-blue-500 font-semibold rounded-md hover:bg-blue-50 transition-colors">
                <i class="fas fa-undo mr-2"></i>ล้างค่า
            </button>
            <button type="submit"
                class="px-6 py-2 bg-blue-600 text-white font-semibold rounded-md hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition-colors">
                <i class="fas fa-save mr-2"></i>บันทึกการตั้งค่า
            </button>
        </div>
    </div>
</div>
