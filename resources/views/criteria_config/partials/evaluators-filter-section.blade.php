<!-- ฟิลเตอร์หน่วยงาน -->
<div class="mb-6 pb-6 border-b border-gray-200">
    <label for="department_filter" class="block text-sm font-medium text-gray-700 mb-2">
        ฟิลเตอร์ตามหน่วยงาน :
    </label>
    <select id="department_filter" name="department_filter"
        class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
        <option value="total">แสดงทั้งหมด</option>
        <option value="dev">ฝ่ายพัฒนาซอฟต์แวร์</option>
        <option value="marketing">ฝ่ายการตลาด</option>
        <option value="hr">ฝ่ายบุคคล</option>
        <option value="accounting">ฝ่ายบัญชี</option>
        <option value="support">ฝ่ายสนับสนุนลูกค้า</option>
    </select>
    <p class="mt-2 text-xs text-gray-500">
        เลือกหน่วยงานเพื่อกรองรายชื่อผู้ประเมินและผู้รับการประเมินด้านล่าง
    </p>
    <div id="filter-summary" class="mt-2 p-2 bg-blue-50 rounded text-sm text-blue-700 hidden">
        <!-- แสดงสรุปการฟิลเตอร์ -->
    </div>
</div>
