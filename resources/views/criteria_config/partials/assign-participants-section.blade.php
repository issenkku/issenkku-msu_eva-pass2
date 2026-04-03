{{-- ส่วนเลือกผู้รับการประเมินและผู้ประเมิน พร้อม filter ตามหน่วยงาน --}}
<div class="bg-white shadow-sm rounded-lg p-6 mb-6">
    <h2 class="text-xl font-semibold text-gray-800 mb-4">กำหนดผู้ประเมิน / ผู้รับการประเมิน</h2>

    <div class="mb-6 pb-6 border-b border-gray-200">
        <label for="department_filter" class="block text-sm font-medium text-gray-700">ฟิลเตอร์ตามหน่วยงานย่อย :</label>
        <select id="department_filter" name="department_filter" class="mt-1 form-select-custom">
            <option value="">เลือกหน่วยงาน</option>
            <option value="dev">ฝ่ายพัฒนาซอฟต์แวร์</option>
            <option value="marketing">ฝ่ายการตลาด</option>
            <option value="hr">ฝ่ายบุคคล</option>
            <option value="support">ฝ่ายสนับสนุนลูกค้า</option>
        </select>
        <p class="mt-2 text-xs text-gray-500">เลือกหน่วยงานเพื่อกรองรายชื่อผู้ประเมินและผู้รับการประเมินด้านล่าง</p>
    </div>

    <div class="space-y-6">
        <div>
            <label for="evaluatees" class="block text-sm font-medium text-gray-700">รายชื่อผู้รับการประเมิน :</label>
            <select id="evaluatees" name="evaluatees[]" multiple class="mt-1 form-select-custom" size="4">
                <option>นายสมชาย ใจดี</option>
                <option>นางสาวสมศรี มีสุข</option>
                <option>นายพัฒนา รักงาน</option>
            </select>
            <div class="mt-2 p-4 bg-gray-200 rounded-md min-h-[80px] text-sm text-gray-700">
                แสดงรายชื่อที่เลือกทั้งหมด
            </div>
        </div>
        <div>
            <label for="evaluators" class="block text-sm font-medium text-gray-700">รายชื่อผู้ประเมิน :</label>
            <select id="evaluators" name="evaluators[]" multiple class="mt-1 form-select-custom" size="4">
                <option>หัวหน้าแผนก</option>
                <option>ผู้จัดการฝ่าย</option>
            </select>
            <div class="mt-2 p-4 bg-gray-200 rounded-md min-h-[80px] text-sm text-gray-700">
                แสดงรายชื่อที่เลือกทั้งหมด
            </div>
        </div>
    </div>
</div>
