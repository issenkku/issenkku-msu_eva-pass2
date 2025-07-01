@extends('layouts.user-management-page')

@section('content')
<div class="max-w-4xl mx-auto p-6 bg-white rounded shadow">
    <h2 class="text-2xl font-semibold mb-6">แก้ไขข้อมูลโปรไฟล์</h2>

    <form action="{{ route('profile.update') }}" method="POST" enctype="multipart/form-data">
        @csrf
        @method('PATCH')

        <!-- Profile Photo and Name -->
        <div class="flex items-center mb-6">
            <div class="relative mr-4">
                <img src="{{ $user->photo_url ?? asset('images/default-avatar.png') }}"
                     alt="User Photo"
                     id="preview-photo"
                     class="w-24 h-24 rounded-full object-cover border">
                <label for="photo" class="absolute bottom-0 right-0 bg-blue-600 text-white rounded-full p-1 cursor-pointer hover:bg-blue-700">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 9a2 2 0 012-2h.93a2 2 0 001.664-.89l.812-1.22A2 2 0 0110.07 4h3.86a2 2 0 011.664.89l.812 1.22A2 2 0 0018.07 7H19a2 2 0 012 2v9a2 2 0 01-2 2H5a2 2 0 01-2-2V9z"></path>
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 13a3 3 0 11-6 0 3 3 0 016 0z"></path>
                    </svg>
                </label>
                <input type="file" id="photo" name="photo" accept="image/*" class="hidden">
            </div>
            <div class="flex-1">
                <div class="mb-2">
                    <label for="name" class="block text-sm font-medium text-gray-700">ชื่อ-นามสกุล</label>
                    <input type="text" id="name" name="name" value="{{ old('name', $user->name) }}"
                           class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 @error('name') border-red-500 @enderror">
                    @error('name')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>
                <div>
                    <label for="email" class="block text-sm font-medium text-gray-700">อีเมล</label>
                    <input type="email" id="email" name="email" value="{{ old('email', $user->email) }}"
                           class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 @error('email') border-red-500 @enderror">
                    @error('email')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>
                @if($user->email_verified_at)
                    <p class="text-sm text-green-600 mt-1">Verified at: {{ $user->email_verified_at->format('d M Y, H:i') }}</p>
                @else
                    <p class="text-sm text-red-600 mt-1">Email not verified</p>
                @endif
            </div>
        </div>

        <!-- User Info -->
        <div class="grid grid-cols-2 gap-6">
            <div>
                <label for="prefix" class="block text-sm font-medium text-gray-700 mb-1">คำนำหน้า</label>
                <select id="prefix" name="prefix"
                        class="block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 @error('personnel_type') border-red-500 @enderror">
                    <option value="">คำนำหน้า</option>
                    <option value="นาย" {{ old('prefix', $user->prefix) == 'นาย' ? 'selected' : '' }}>นาย</option>
                    <option value="นาง" {{ old('prefix', $user->prefix) == 'นาง' ? 'selected' : '' }}>นาง</option>
                    <option value="นางสาว" {{ old('prefix', $user->prefix) == 'นางสาว' ? 'selected' : '' }}>นางสาว</option>
                </select>
                @error('prefix')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <label for="employee_id" class="block text-sm font-medium text-gray-700 mb-1">รหัสพนักงาน</label>
                <input type="text" id="employee_id" name="employee_id" value="{{ old('employee_id', $user->employee_id) }}"
                       class="block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 @error('employee_id') border-red-500 @enderror">
                @error('employee_id')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <label for="phone" class="block text-sm font-medium text-gray-700 mb-1">เบอร์โทร</label>
                <input type="tel" id="phone" name="phone" value="{{ old('phone', $user->phone) }}"
                       placeholder="0812345678"
                       class="block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 @error('phone') border-red-500 @enderror">
                @error('phone')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <label for="personnel_type" class="block text-sm font-medium text-gray-700 mb-1">ประเภทบุคลากร</label>
                <select id="personnel_type" name="personnel_type"
                        class="block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 @error('personnel_type') border-red-500 @enderror">
                    <option value="">เลือกประเภทบุคลากร</option>
                    <option value="สนับสนุน" {{ old('personnel_type', $user->personnel_type) == 'สนับสนุน' ? 'selected' : '' }}>สนับสนุน</option>
                    <option value="วิชาการ" {{ old('personnel_type', $user->personnel_type) == 'วิชาการ' ? 'selected' : '' }}>วิชาการ</option>
                </select>
                @error('personnel_type')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <label for="position_id" class="block text-sm font-medium text-gray-700 mb-1">ตำแหน่ง</label>
                <select id="position_id" name="position_id"
                        class="block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 @error('position_id') border-red-500 @enderror">
                    <option value="">เลือกตำแหน่ง</option>
                    @foreach ($positions as $position) 
                        <option value="{{ $position->id }}"
                            {{ old('position_id', $user->position_id ?? '') == $position->id ? 'selected' : '' }}>
                            {{ $position->name }}
                        </option>
                    @endforeach
                </select>
                @error('position_id')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <label for="department_id" class="block text-sm font-medium text-gray-700 mb-1">สาขาวิชา</label>
                <select id="department_id" name="department_id"
                        class="block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 @error('department_id') border-red-500 @enderror">
                    <option value="">เลือกสาขาวิชา</option>
                    @foreach ($departments as $department)
                        <option value="{{ $department->id }}"
                            {{ old('department_id', $user->department_id ?? '') == $department->id ? 'selected' : '' }}>
                            {{ $department->department_name }}
                        </option>
                    @endforeach
                </select>
                @error('department_id')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <div class="col-span-2">
                <label for="bio" class="block text-sm font-medium text-gray-700 mb-1">ประวัติการศึกษา</label>
                <textarea id="bio" name="bio" rows="5"
                          class="block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 @error('bio') border-red-500 @enderror"
                          placeholder="กรอกประวัติการศึกษา เช่น ปริญญาตรี, ปริญญาโท, ปริญญาเอก และสถาบันที่สำเร็จการศึกษา">{{ old('bio', $user->bio) }}</textarea>
                @error('bio')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>
        </div>

        <!-- Password Change Section -->
        <div class="mt-8 pt-6 border-t border-gray-200">
            <h3 class="text-lg font-medium text-gray-900 mb-4">เปลี่ยนรหัสผ่าน (ไม่จำเป็น)</h3>
            <div class="grid grid-cols-2 gap-6">
                <div>
                    <label for="current_password" class="block text-sm font-medium text-gray-700 mb-1">รหัสผ่านปัจจุบัน</label>
                    <input type="password" id="current_password" name="current_password"
                           class="block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 @error('current_password') border-red-500 @enderror">
                    @error('current_password')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>
                <div></div>
                <div>
                    <label for="password" class="block text-sm font-medium text-gray-700 mb-1">รหัสผ่านใหม่</label>
                    <input type="password" id="password" name="password"
                           class="block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 @error('password') border-red-500 @enderror">
                    @error('password')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>
                <div>
                    <label for="password_confirmation" class="block text-sm font-medium text-gray-700 mb-1">ยืนยันรหัสผ่านใหม่</label>
                    <input type="password" id="password_confirmation" name="password_confirmation"
                           class="block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                </div>
            </div>
        </div>

        <!-- Action Buttons -->
        <div class="mt-6 flex items-center justify-between">
            <a href="{{ route('profile.show') }}"
               class="inline-block bg-gray-600 text-white px-4 py-2 rounded hover:bg-gray-700">
                ยกเลิก
            </a>
            <button type="submit"
                    class="inline-block bg-blue-600 text-white px-6 py-2 rounded hover:bg-blue-700">
                บันทึกการเปลี่ยนแปลง
            </button>
        </div>
    </form>
</div>

<script>
// Preview photo when selected
document.getElementById('photo').addEventListener('change', function(e) {
    const file = e.target.files[0];
    if (file) {
        const reader = new FileReader();
        reader.onload = function(e) {
            document.getElementById('preview-photo').src = e.target.result;
        };
        reader.readAsDataURL(file);
    }
});

// Format phone number as user types
document.getElementById('phone').addEventListener('input', function(e) {
    let value = e.target.value.replace(/\D/g, '');
    if (value.length <= 10) {
        e.target.value = value;
    }
});
</script>
@endsection