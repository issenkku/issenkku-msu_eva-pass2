{{-- การตั้งค่าผู้ใช้ใช้กำหนดสถานะและบทบาทที่มีผลต่อสิทธิ์การเข้าถึงระบบ --}}
<div>
    <h3 class="mb-2 font-semibold text-purple-600">ตั้งค่าผู้ใช้งาน</h3>
    <div class="mb-3">
        <label class="block">สถานะ <span class="text-red-600">*</span></label>
        <select name="status" id="status" class="w-full rounded border px-3 py-2" required>
            <option value="active">active</option>
            <option value="inactive">inactive</option>
        </select>
    </div>
    <div class="mb-3">
        <label class="block">บทบาท</label>
        <div id="roles" class="grid grid-cols-1 gap-2 rounded border px-3 py-3 md:grid-cols-2">
            @foreach ($roles as $role)
                <label class="flex items-center gap-2 text-sm text-gray-700">
                    <input
                        type="checkbox"
                        name="roles[]"
                        value="{{ $role->name }}"
                        class="h-4 w-4 rounded border-gray-300 text-purple-600 focus:ring-purple-500">
                    <span>{{ $role->name }}</span>
                </label>
            @endforeach
        </div>
        <div class="mt-2 text-xs text-gray-500">เลือกได้มากกว่าหนึ่งบทบาท</div>
        <div id="currentRoleDisplay" class="mt-2 text-sm text-green-700">ยังไม่ได้เลือกบทบาท</div>
    </div>
</div>
