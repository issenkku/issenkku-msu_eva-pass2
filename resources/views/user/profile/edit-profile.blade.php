@extends('layouts.app')

@section('content')
<div class="max-w-4xl mx-auto p-6 bg-white rounded shadow">
    <div class="flex items-center justify-between mb-6">
        <h2 class="text-2xl font-semibold">แก้ไขข้อมูลโปรไฟล์</h2>
        <div class="flex items-center gap-3">
            <div class="flex items-center gap-2">
                <input type="checkbox" id="enable-public-profile" 
                       {{ $user->is_public_profile_enabled ? 'checked' : '' }}
                       class="w-4 h-4 text-blue-600 bg-gray-100 border-gray-300 rounded focus:ring-blue-500 focus:ring-2">
                <label for="enable-public-profile" class="text-sm font-medium text-gray-700">
                    เปิดใช้โปรไฟล์สาธารณะ
                </label>
            </div>
            <button type="button" id="copy-profile-link" 
                    class="px-4 py-2 bg-blue-600 text-white rounded-md shadow hover:bg-blue-700 transition-colors flex items-center gap-2 {{ !$user->is_public_profile_enabled ? 'opacity-50 cursor-not-allowed' : '' }}"
                    {{ !$user->is_public_profile_enabled ? 'disabled' : '' }}>
                <i class="fas fa-link"></i>
                คัดลอกลิงก์โปรไฟล์
            </button>
        </div>
    </div>

    <form action="{{ route('profile.update') }}" method="POST" enctype="multipart/form-data">
        @csrf
        @method('PATCH')
        
        <!-- Hidden field for public profile setting -->
        <input type="hidden" name="is_public_profile_enabled" id="is_public_profile_enabled" value="{{ $user->is_public_profile_enabled ? '1' : '0' }}">

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
        <div class="mt-6 flex items-center gap-3">
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

// Copy public profile link
document.getElementById('copy-profile-link').addEventListener('click', function() {
    const isEnabled = document.getElementById('enable-public-profile').checked;
    
    if (!isEnabled) {
        alert('กرุณาเปิดใช้โปรไฟล์สาธารณะก่อนคัดลอกลิงก์');
        return;
    }
    
    const publicUrl = "{{ $user->public_profile_url }}";
    navigator.clipboard.writeText(publicUrl).then(function() {
        // Show a temporary alert
        const btn = document.getElementById('copy-profile-link');
        const original = btn.innerHTML;
        btn.innerHTML = '<i class="fas fa-check"></i> คัดลอกสำเร็จ!';
        btn.classList.remove('bg-blue-600', 'hover:bg-blue-700');
        btn.classList.add('bg-green-600');
        setTimeout(function() {
            btn.innerHTML = original;
            btn.classList.remove('bg-green-600');
            btn.classList.add('bg-blue-600', 'hover:bg-blue-700');
        }, 1500);
    }, function() {
        alert('ไม่สามารถคัดลอกลิงก์ได้');
    });
});

// Handle public profile toggle
document.getElementById('enable-public-profile').addEventListener('change', function() {
    const isEnabled = this.checked;
    const copyBtn = document.getElementById('copy-profile-link');
    const hiddenInput = document.getElementById('is_public_profile_enabled');
    
    hiddenInput.value = isEnabled ? '1' : '0';
    
    if (isEnabled) {
        copyBtn.disabled = false;
        copyBtn.classList.remove('opacity-50', 'cursor-not-allowed');
    } else {
        copyBtn.disabled = true;
        copyBtn.classList.add('opacity-50', 'cursor-not-allowed');
    }
});
</script>
@endsection