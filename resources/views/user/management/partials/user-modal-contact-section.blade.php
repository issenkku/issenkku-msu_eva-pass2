{{-- ข้อมูลติดต่อ ใช้สำหรับ login, แจ้งเตือน, และการติดต่อกลับจากระบบ --}}
<div>
    <h3 class="mb-2 font-semibold text-purple-600">ข้อมูลติดต่อ</h3>
    <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
        <div>
            <label class="block">อีเมล <span class="text-red-600">*</span></label>
            <input type="email" name="email" id="email" class="w-full rounded border px-3 py-2" required>
            <div class="mt-1 hidden text-sm text-red-500" id="emailError">จำเป็นต้องกรอกข้อมูล</div>
        </div>
        <div>
            <label class="block">เบอร์โทร <span class="text-red-600">*</span></label>
            <input type="text" name="phone" id="phone" class="w-full rounded border px-3 py-2" required>
            <div class="mt-1 hidden text-sm text-red-500" id="phoneError">จำเป็นต้องกรอกข้อมูล</div>
        </div>
    </div>
</div>
