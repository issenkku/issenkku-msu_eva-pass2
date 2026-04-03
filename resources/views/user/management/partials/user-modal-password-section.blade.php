{{-- รหัสผ่านใช้เฉพาะตอนสร้างผู้ใช้ใหม่ ส่วนโหมดแก้ไขจะซ่อนก้อนนี้ผ่าน JavaScript --}}
<div id="passwordPanel">
    <h3 class="mb-2 font-semibold text-purple-600">รหัสผ่าน <span class="text-red-600">*</span></h3>
    <input type="password" name="password" id="password" class="w-full rounded border px-3 py-2">
    <div class="mt-1 hidden text-sm text-red-500" id="passwordError">รหัสผ่านต้องมีอย่างน้อย 6 ตัวอักษร</div>
</div>
