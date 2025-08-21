@extends('layouts.app')

@section('content')
<div class="max-w-4xl mx-auto p-6 bg-white rounded shadow">
    <h2 class="text-2xl font-semibold mb-6">แก้ไขข้อมูลโปรไฟล์</h2>

    <form action="{{ route('profile.update') }}" method="POST" enctype="multipart/form-data">
        @csrf
        @method('PATCH')

        <!-- Profile Photo Section -->
        <div class="mb-8 flex items-center gap-6">
            <div class="flex-shrink-0">
                <img id="preview-photo" 
                     src="{{ $user->profile_photo_url }}" 
                     alt="Profile Photo"
                     class="w-32 h-32 rounded-full object-cover border-4 border-gray-200 shadow-lg">
            </div>
            <div class="flex-grow">
                <label for="profile_photo" class="block text-sm font-medium text-gray-700 mb-2">รูปโปรไฟล์</label>
                <input type="file" 
                       id="profile_photo" 
                       name="profile_photo" 
                       accept="image/*"
                       class="block w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-full file:border-0 file:text-sm file:font-semibold file:bg-blue-50 file:text-blue-700 hover:file:bg-blue-100 @error('profile_photo') border-red-500 @enderror">
                @error('profile_photo')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
                <p class="mt-1 text-sm text-gray-500">รองรับไฟล์ JPG, JPEG, PNG, GIF ขนาดไม่เกิน 2MB</p>
            </div>
        </div>

        <div class="flex items-center mb-6 gap-4">
            <div class="w-40">
                <label for="prefix" class="block text-sm font-medium text-gray-700 mb-1">คำนำหน้า</label>
                <select id="prefix" name="prefix"
                        class="block w-full rounded-md border border-black shadow-sm focus:border-black focus:ring-black px-3 py-2 @error('personnel_type') border-red-500 @enderror">
                    <option value="">คำนำหน้า</option>
                    <option value="นาย" {{ old('prefix', $user->prefix) == 'นาย' ? 'selected' : '' }}>นาย</option>
                    <option value="นาง" {{ old('prefix', $user->prefix) == 'นาง' ? 'selected' : '' }}>นาง</option>
                    <option value="นางสาว" {{ old('prefix', $user->prefix) == 'นางสาว' ? 'selected' : '' }}>นางสาว</option>
                </select>
                @error('prefix')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>
            <div class="flex-1">
                <label for="name" class="block text-sm font-medium text-gray-700">ชื่อ-นามสกุล</label>
                <input type="text" id="name" name="name" value="{{ old('name', $user->name) }}"
                       class="mt-1 block w-full rounded-md border border-black shadow-sm focus:border-black focus:ring-black px-3 py-2 @error('name') border-red-500 @enderror">
                @error('name')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>
        </div>
        <div class="mb-2">
            <label for="email" class="block text-sm font-medium text-gray-700">อีเมล</label>
            <input type="email" id="email" name="email" value="{{ old('email', $user->email) }}"
                   class="mt-1 block w-full rounded-md border border-black shadow-sm focus:border-black focus:ring-black px-3 py-2 @error('email') border-red-500 @enderror">
            @error('email')
                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
            @enderror
        </div>

        <!-- User Info -->
        <div class="grid grid-cols-2 gap-6">
            <div>
                <label for="employee_id" class="block text-sm font-medium text-gray-700 mb-1">รหัสพนักงาน</label>
                <input type="text" id="employee_id" name="employee_id" value="{{ old('employee_id', $user->employee_id) }}"
                       class="block w-full rounded-md border border-black shadow-sm focus:border-black focus:ring-black bg-gray-100 cursor-not-allowed px-3 py-2 @error('employee_id') border-red-500 @enderror" readonly>
                @error('employee_id')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <label for="phone" class="block text-sm font-medium text-gray-700 mb-1">เบอร์โทร</label>
                <input type="tel" id="phone" name="phone" value="{{ old('phone', $user->phone) }}"
                       placeholder="0812345678"
                       class="block w-full rounded-md border border-black shadow-sm focus:border-black focus:ring-black px-3 py-2 @error('phone') border-red-500 @enderror">
                @error('phone')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <label for="personnel_type" class="block text-sm font-medium text-gray-700 mb-1">ประเภทบุคลากร</label>
                <select id="personnel_type" name="personnel_type"
                        class="block w-full rounded-md border border-black shadow-sm focus:border-black focus:ring-black bg-gray-100 cursor-not-allowed px-3 py-2 @error('personnel_type') border-red-500 @enderror" disabled>
                    <option value="">เลือกประเภทบุคลากร</option>
                    <option value="สนับสนุน" {{ old('personnel_type', $user->personnel_type) == 'สนับสนุน' ? 'selected' : '' }}>สนับสนุน</option>
                    <option value="วิชาการ" {{ old('personnel_type', $user->personnel_type) == 'วิชาการ' ? 'selected' : '' }}>วิชาการ</option>
                </select>
                <input type="hidden" name="personnel_type" value="{{ old('personnel_type', $user->personnel_type) }}">
                @error('personnel_type')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <label for="position_id" class="block text-sm font-medium text-gray-700 mb-1">ตำแหน่ง</label>
                <select id="position_id" name="position_id"
                        class="block w-full rounded-md border border-black shadow-sm focus:border-black focus:ring-black bg-gray-100 cursor-not-allowed px-3 py-2 @error('position_id') border-red-500 @enderror" disabled>
                    <option value="">เลือกตำแหน่ง</option>
                    @foreach ($positions as $position) 
                        <option value="{{ $position->id }}"
                            {{ old('position_id', $user->position_id ?? '') == $position->id ? 'selected' : '' }}>
                            {{ $position->name }}
                        </option>
                    @endforeach
                </select>
                <input type="hidden" name="position_id" value="{{ old('position_id', $user->position_id) }}">
                @error('position_id')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <label for="department_id" class="block text-sm font-medium text-gray-700 mb-1">สาขาวิชา</label>
                <select id="department_id" name="department_id"
                        class="block w-full rounded-md border border-black shadow-sm focus:border-black focus:ring-black bg-gray-100 cursor-not-allowed px-3 py-2 @error('department_id') border-red-500 @enderror" disabled>
                    <option value="">เลือกสาขาวิชา</option>
                    @foreach ($departments as $department)
                        <option value="{{ $department->id }}"
                            {{ old('department_id', $user->department_id ?? '') == $department->id ? 'selected' : '' }}>
                            {{ $department->department_name }}
                        </option>
                    @endforeach
                </select>
                <input type="hidden" name="department_id" value="{{ old('department_id', $user->department_id) }}">
                @error('department_id')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <div class="col-span-2">
                <label for="bio" class="block text-sm font-medium text-gray-700 mb-1">ประวัติการศึกษา</label>
                <textarea id="bio" name="bio" rows="5"
                          class="block w-full rounded-md border border-black shadow-sm focus:border-black focus:ring-black px-3 py-2 @error('bio') border-red-500 @enderror"
                          placeholder="กรอกประวัติการศึกษา เช่น ปริญญาตรี, ปริญญาโท, ปริญญาเอก และสถาบันที่สำเร็จการศึกษา">{{ old('bio', $user->bio) }}</textarea>
                @error('bio')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <div class="col-span-2">
                <label for="portfolio" class="block text-sm font-medium text-gray-700 mb-1">ผลงาน</label>
                <textarea id="portfolio" name="portfolio" rows="6"
                          class="block w-full rounded-md border border-black shadow-sm focus:border-black focus:ring-black px-3 py-2 @error('portfolio') border-red-500 @enderror"
                          placeholder="กรอกข้อมูลผลงาน เช่น งานวิจัย, บทความ, หนังสือ, รางวัลที่ได้รับ และผลงานอื่นๆ ที่สำคัญ">{{ old('portfolio', $user->portfolio ?? '') }}</textarea>
                @error('portfolio')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>
        </div>

        <!-- Password Change Section -->
        <div class="mt-8 pt-6 border-t border-gray-200">
            <h3 class="text-lg font-medium text-gray-900 mb-4">เปลี่ยนรหัสผ่าน (กรอกเฉพาะกรณีต้องการเปลี่ยนรหัส)</h3>
            <div class="grid grid-cols-2 gap-6">
                <div>
                    <label for="current_password" class="block text-sm font-medium text-gray-700 mb-1">รหัสผ่านปัจจุบัน</label>
                    <input type="password" id="current_password" name="current_password"
                           class="block w-full rounded-md border border-black shadow-sm focus:border-black focus:ring-black px-3 py-2 @error('current_password') border-red-500 @enderror">
                    @error('current_password')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>
                <div></div>
                <div>
                    <label for="password" class="block text-sm font-medium text-gray-700 mb-1">รหัสผ่านใหม่</label>
                    <input type="password" id="password" name="password"
                           class="block w-full rounded-md border border-black shadow-sm focus:border-black focus:ring-black px-3 py-2 @error('password') border-red-500 @enderror">
                    @error('password')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>
                <div>
                    <label for="password_confirmation" class="block text-sm font-medium text-gray-700 mb-1">ยืนยันรหัสผ่านใหม่</label>
                    <input type="password" id="password_confirmation" name="password_confirmation"
                           class="block w-full rounded-md border border-black shadow-sm focus:border-black focus:ring-black px-3 py-2">
                </div>
            </div>
        </div>

        <!-- Action Buttons -->
        <div class="mt-6 flex items-center justify-between">
            <x-button 
                type= defualt 
                text="ย้อนกลับ" 
                icon="fas fa-arrow-left"
                href="{{ route('profile.show') }}" />
            <x-button 
                type="warning"
                buttonType="submit" 
                text="บันทึกการเปลี่ยนแปลง" 
                icon="fas fa-save" />
        </div>
    </form>
</div>

<script>
// Preview photo when selected
document.getElementById('profile_photo').addEventListener('change', function(e) {
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