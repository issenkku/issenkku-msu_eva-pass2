{{-- ฟอร์มหลักสำหรับแก้ชื่อบทบาท สิทธิ์ และผู้ใช้ที่ผูกกับบทบาทนี้ --}}
<div class="mb-4">
    <label class="block mb-1 font-medium">ชื่อบทบาท</label>
    <input type="text" name="name" value="{{ old('name', $role->name) }}" class="w-full border px-3 py-2 rounded">
</div>

<div class="mb-4">
    <label class="block mb-1 font-medium">สิทธิ์</label>
    <div class="grid grid-cols-2 gap-2">
        @foreach($permissions as $permission)
            <label>
                <input type="checkbox" name="permissions[]" value="{{ $permission->name }}"
                    {{ in_array($permission->name, $rolePermissions) ? 'checked' : '' }}>
                {{ $permission->name }}
            </label>
        @endforeach
    </div>
</div>

<div class="mb-4">
    <label class="block mb-1 font-medium">ผู้ใช้ที่ได้รับบทบาทนี้</label>
    <div class="grid grid-cols-2 gap-2">
        @foreach($users as $user)
            <label class="flex items-center space-x-2">
                <input type="checkbox" name="users[]" value="{{ $user->id }}"
                    {{ in_array($user->id, $assignedUsers) ? 'checked' : '' }}>
                <span>{{ $user->name }}</span>
            </label>
        @endforeach
    </div>
</div>
