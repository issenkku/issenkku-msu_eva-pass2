<!-- ผู้รับการประเมิน Section -->
<div>
    <div class="flex items-center justify-between mb-4">
        <div class="block text-sm font-medium text-gray-700">
            รายชื่อผู้รับการประเมิน :
        </div>
        <div class="text-sm text-gray-500">
            <span id="evaluatees-available-count">0</span> คนที่แสดง จาก
            <span id="evaluatees-total-count">5</span> คนทั้งหมด
        </div>
    </div>
    <select id="evaluatees" name="evaluatees[]" multiple data-coreui-search="true"
        class="form-multi-select w-full focus:outline-none focus:ring-2 focus:ring-blue-500">
        <option value="1" data-department="dev">นายสมชาย ใจดี - ฝ่ายพัฒนาซอฟต์แวร์</option>
        <option value="2" data-department="accounting">นางสาวสมศรี มีสุข - ฝ่ายบัญชี
        </option>
        <option value="3" data-department="hr">นายพัฒนา รักงาน - ฝ่ายทรัพยากรมนุษย์
        </option>
        <option value="4" data-department="hr">นางนิภาพร ใฝ่รู้ - ฝ่ายบุคคล</option>
        <option value="5" data-department="marketing">นายอนันต์ ใจเย็น - ฝ่ายการตลาด
        </option>
    </select>

    <!-- Selected Display for Evaluatees -->
    <div class="mt-4 p-4 bg-gray-50 rounded-lg min-h-[60px]">
        <p class="text-sm font-medium text-gray-700 mb-2">
            รายชื่อผู้รับการประเมินที่เลือก:
            <span id="evaluatees-selected-count" class="text-blue-600 font-semibold">0</span> คน
        </p>
        <div id="selected-evaluatees" class="flex flex-col gap-2">
            <span class="text-sm text-gray-500">ยังไม่ได้เลือกรายชื่อ</span>
        </div>
    </div>
</div>
