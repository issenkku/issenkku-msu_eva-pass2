@extends('layouts.app')
{{-- ไฟล์มุมมอง: resources/views\user\role-management\edit-role.blade.php --}}

@section('content')
{{-- บล็อกเนื้อหา --}}
<div class="p-6 max-w-3xl mx-auto">
    <h2 class="text-2xl font-bold mb-4">แก้ไขบทบาท: {{ $role->name }}</h2>

    {{-- ฟอร์ม --}}
    <form method="POST" action="{{ route('roles.update', $role) }}">
        @csrf
        @method('PUT')

        {{-- บล็อกเนื้อหา --}}
        <div class="mb-4">
            <label class="block mb-1 font-medium">ชื่อบทบาท</label>
            <input type="text" name="name" value="{{ old('name', $role->name) }}"
                   class="w-full border px-3 py-2 rounded">
        </div>

        {{-- บล็อกเนื้อหา --}}
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

        {{-- บล็อกเนื้อหา --}}
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

        {{-- บล็อกเนื้อหา --}}
        <div class="flex justify-end space-x-2">
            <a href="{{ route('roles.index') }}" class="px-4 py-2 bg-gray-300 rounded">ยกเลิก</a>
            <button type="submit" class="px-4 py-2 bg-blue-600 text-white rounded">บันทึก</button>
        </div>
    </form>
</div>
@endsection
