{{-- ข้อมูลส่วนบุคคล ใช้ระบุตัวผู้ใช้หลักที่ระบบจะนำไปผูกกับสิทธิ์และหน่วยงาน --}}
<div>
    <h3 class="mb-2 font-semibold text-purple-600">ข้อมูลส่วนบุคคล</h3>
    <div class="grid grid-cols-1 gap-4 md:grid-cols-3">
        <div>
            <label for="prefix" class="block">คำนำหน้า <span class="text-red-600">*</span></label>
            <input type="text" name="prefix" id="prefix" class="w-full rounded border px-3 py-2" autocomplete="honorific-prefix" required list="user-prefix-options" placeholder="เช่น นาย, อ.ดร., ว่าที่ ร.ต.">
            <datalist id="user-prefix-options">
                <option value="นาย">
                <option value="นาง">
                <option value="นางสาว">
                <option value="อ.ดร.">
                <option value="ผศ.ดร.">
                <option value="รศ.ดร.">
                <option value="ศ.ดร.">
                <option value="ว่าที่ ร.ต.">
                <option value="ว่าที่พันตรี">
            </datalist>
            <div class="mt-1 hidden text-sm text-red-500" id="prefixError">จำเป็นต้องกรอกข้อมูล</div>
            <div class="mt-1 text-xs text-gray-500">กรอกคำนำหน้าแบบกำหนดเองได้</div>
        </div>
        <div class="md:col-span-2">
            <label for="name" class="block">ชื่อ-นามสกุล <span class="text-red-600">*</span></label>
            <input type="text" name="name" id="name" class="w-full rounded border px-3 py-2" autocomplete="name" required>
            <div class="mt-1 hidden text-sm text-red-500" id="nameError">จำเป็นต้องกรอกข้อมูล</div>
        </div>
        <div class="md:col-span-3">
            <label for="employee_id" class="block">รหัสพนักงาน <span class="text-red-600">*</span></label>
            <input type="text" name="employee_id" id="employee_id" class="w-full rounded border px-3 py-2" autocomplete="username" required pattern="[0-9]+" inputmode="numeric">
            <div class="mt-1 hidden text-sm text-red-500" id="employee_idError">จำเป็นต้องกรอกข้อมูล</div>
        </div>
    </div>
</div>
