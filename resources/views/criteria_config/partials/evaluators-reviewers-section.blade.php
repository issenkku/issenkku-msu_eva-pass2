<!-- ส่วนที่เกี่ยวกับ Evaluators -->
<div>
    <div class="flex items-center justify-between mb-4">
        <label for="evaluators" class="block text-sm font-medium text-gray-700">
            รายชื่อผู้ประเมิน :
        </label>
        <div class="text-sm text-gray-500">
            <span id="evaluators-available-count">0</span> คนที่แสดง จาก
            <span id="evaluators-total-count">5</span> คนทั้งหมด
        </div>
    </div>
    <select id="evaluators" name="evaluators[]" multiple
        class="form-multi-select w-full focus:outline-none focus:ring-2 focus:ring-blue-500">
        <option value="boss1" data-department="dev">หัวหน้าแผนกพัฒนา - ฝ่ายพัฒนาซอฟต์แวร์
        </option>
        <option value="boss2" data-department="marketing">หัวหน้าฝ่ายการตลาด - ฝ่ายการตลาด
        </option>
        <option value="boss3" data-department="hr">หัวหน้า HR - ฝ่ายบุคคล</option>
        <option value="boss4" data-department="support">หัวหน้าฝ่ายสนับสนุน -
            ฝ่ายสนับสนุนลูกค้า</option>
        <option value="boss5" data-department="dev">ผู้นำทีม DEV - ฝ่ายพัฒนาซอฟต์แวร์</option>
    </select>

    <!-- Selected Display for Evaluators -->
    <div class="mt-4 p-4 bg-gray-50 rounded-lg min-h-[60px]">
        <p class="text-sm font-medium text-gray-700 mb-2">
            รายชื่อผู้ประเมินที่เลือก:
            <span id="evaluators-selected-count" class="text-blue-600 font-semibold">0</span> คน
        </p>
        <div id="selected-evaluators" class="flex flex-col gap-2">
            <span class="text-sm text-gray-500">ยังไม่ได้เลือกรายชื่อ</span>
        </div>
    </div>
</div>
