@extends('layouts.app')

@section('content')
<div class="mx-auto max-w-4xl rounded bg-white p-6 shadow">
    <div class="mb-6 flex items-center justify-between">
        <h2 class="text-2xl font-semibold">แก้ไขข้อมูลโปรไฟล์</h2>
        <div class="flex items-center gap-3">
            <div class="flex items-center gap-2">
                <input
                    type="checkbox"
                    id="enable-public-profile"
                    {{ $user->is_public_profile_enabled ? 'checked' : '' }}
                    class="h-4 w-4 rounded border-gray-300 bg-gray-100 text-blue-600 focus:ring-2 focus:ring-blue-500"
                >
                <label for="enable-public-profile" class="text-sm font-medium text-gray-700">
                    เปิดใช้โปรไฟล์สาธารณะ
                </label>
            </div>
            <button
                type="button"
                id="copy-profile-link"
                class="flex items-center gap-2 rounded-md bg-blue-600 px-4 py-2 text-white shadow transition-colors hover:bg-blue-700 {{ !$user->is_public_profile_enabled ? 'cursor-not-allowed opacity-50' : '' }}"
                {{ !$user->is_public_profile_enabled ? 'disabled' : '' }}
            >
                <i class="fas fa-link"></i>
                คัดลอกลิงก์โปรไฟล์
            </button>
        </div>
    </div>

    <form action="{{ route('profile.update') }}" method="POST" enctype="multipart/form-data">
        @csrf
        @method('PATCH')

        <input type="hidden" name="is_public_profile_enabled" id="is_public_profile_enabled" value="{{ $user->is_public_profile_enabled ? '1' : '0' }}">

        <div class="mb-8 flex items-center gap-6">
            <div class="flex-shrink-0">
                <img
                    id="preview-photo"
                    src="{{ $user->profile_photo_url }}"
                    alt="Profile Photo"
                    class="h-32 w-32 rounded-full border-4 border-gray-200 object-cover shadow-lg"
                >
            </div>
            <div class="flex-grow">
                <label for="profile_photo" class="mb-2 block text-sm font-medium text-gray-700">รูปโปรไฟล์</label>
                <input
                    type="file"
                    id="profile_photo"
                    name="profile_photo"
                    accept="image/*"
                    class="block w-full text-sm text-gray-500 file:mr-4 file:rounded-full file:border-0 file:bg-blue-50 file:px-4 file:py-2 file:text-sm file:font-semibold file:text-blue-700 hover:file:bg-blue-100 @error('profile_photo') border-red-500 @enderror"
                >
                @error('profile_photo')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
                <p class="mt-1 text-sm text-gray-500">รองรับไฟล์ JPG, JPEG, PNG, GIF ขนาดไม่เกิน 2MB</p>
            </div>
        </div>

        <div class="mb-6 flex items-start gap-4">
            <div class="w-56">
                <label for="prefix" class="mb-1 block text-sm font-medium text-gray-700">คำนำหน้า</label>
                <input
                    type="text"
                    id="prefix"
                    name="prefix"
                    list="prefix-options"
                    autocomplete="honorific-prefix"
                    value="{{ old('prefix', $user->prefix) }}"
                    placeholder="เช่น นาย, อ.ดร., ว่าที่ ร.ต."
                    class="block w-full rounded-md border border-black px-3 py-2 shadow-sm focus:border-black focus:ring-black @error('prefix') border-red-500 @enderror"
                >
                <datalist id="prefix-options">
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
                <p class="mt-1 text-xs text-gray-500">รองรับคำนำหน้าแบบกำหนดเอง เช่น อ.ดร.ว่าที่พันตรี</p>
                @error('prefix')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>
            <div class="flex-1">
                <label for="name" class="mb-1 block text-sm font-medium text-gray-700">ชื่อ-นามสกุล</label>
                <input
                    type="text"
                    id="name"
                    name="name"
                    autocomplete="name"
                    value="{{ old('name', $user->name) }}"
                    class="block w-full rounded-md border border-black px-3 py-2 shadow-sm focus:border-black focus:ring-black @error('name') border-red-500 @enderror"
                >
                @error('name')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>
        </div>

        <div class="mb-2">
            <label for="email" class="block text-sm font-medium text-gray-700">อีเมล</label>
            <input
                type="email"
                id="email"
                name="email"
                autocomplete="email"
                value="{{ old('email', $user->email) }}"
                class="mt-1 block w-full rounded-md border border-black px-3 py-2 shadow-sm focus:border-black focus:ring-black @error('email') border-red-500 @enderror"
            >
            @error('email')
                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
            @enderror
        </div>

        <div class="grid grid-cols-2 gap-6">
            <div>
                <label for="employee_id" class="mb-1 block text-sm font-medium text-gray-700">รหัสพนักงาน</label>
                <input
                    type="text"
                    id="employee_id"
                    name="employee_id"
                    autocomplete="username"
                    value="{{ old('employee_id', $user->employee_id) }}"
                    class="block w-full cursor-not-allowed rounded-md border border-black bg-gray-100 px-3 py-2 shadow-sm focus:border-black focus:ring-black @error('employee_id') border-red-500 @enderror"
                    readonly
                >
                @error('employee_id')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <label for="phone" class="mb-1 block text-sm font-medium text-gray-700">เบอร์โทร</label>
                <input
                    type="tel"
                    id="phone"
                    name="phone"
                    autocomplete="tel"
                    value="{{ old('phone', $user->phone) }}"
                    placeholder="0812345678"
                    class="block w-full rounded-md border border-black px-3 py-2 shadow-sm focus:border-black focus:ring-black @error('phone') border-red-500 @enderror"
                >
                @error('phone')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <label for="personnel_type" class="mb-1 block text-sm font-medium text-gray-700">ประเภทบุคลากร</label>
                <select
                    id="personnel_type"
                    name="personnel_type"
                    class="block w-full rounded-md border border-black px-3 py-2 shadow-sm focus:border-black focus:ring-black @error('personnel_type') border-red-500 @enderror"
                >
                    <option value="">เลือกประเภทบุคลากร</option>
                    <option value="สนับสนุน" {{ old('personnel_type', $user->personnel_type) == 'สนับสนุน' ? 'selected' : '' }}>สนับสนุน</option>
                    <option value="วิชาการ" {{ old('personnel_type', $user->personnel_type) == 'วิชาการ' ? 'selected' : '' }}>วิชาการ</option>
                    <option value="บริหาร" {{ old('personnel_type', $user->personnel_type) == 'บริหาร' ? 'selected' : '' }}>บริหาร</option>
                </select>
                @error('personnel_type')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <label for="position_id" class="mb-1 block text-sm font-medium text-gray-700">ตำแหน่ง</label>
                <select
                    id="position_id"
                    name="position_id"
                    class="block w-full rounded-md border border-black px-3 py-2 shadow-sm focus:border-black focus:ring-black @error('position_id') border-red-500 @enderror"
                >
                    <option value="">เลือกตำแหน่ง</option>
                    @foreach ($positions as $position)
                        <option value="{{ $position->id }}" {{ old('position_id', $user->position_id ?? '') == $position->id ? 'selected' : '' }}>
                            {{ $position->name }}
                        </option>
                    @endforeach
                </select>
                @error('position_id')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <label for="job_level_id" class="mb-1 block text-sm font-medium text-gray-700">ระดับตำแหน่งงาน</label>
                <select
                    id="job_level_id"
                    name="job_level_id"
                    class="block w-full rounded-md border border-black px-3 py-2 shadow-sm focus:border-black focus:ring-black @error('job_level_id') border-red-500 @enderror"
                >
                    <option value="">เลือกระดับตำแหน่งงาน</option>
                    @foreach ($jobLevels as $jobLevel)
                        <option value="{{ $jobLevel->id }}" {{ old('job_level_id', $user->job_level_id ?? '') == $jobLevel->id ? 'selected' : '' }}>
                            {{ $jobLevel->name }}
                        </option>
                    @endforeach
                </select>
                @error('job_level_id')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <label for="department_id" class="mb-1 block text-sm font-medium text-gray-700">สาขาวิชา</label>
                <select
                    id="department_id"
                    name="department_id"
                    class="block w-full rounded-md border border-black px-3 py-2 shadow-sm focus:border-black focus:ring-black @error('department_id') border-red-500 @enderror"
                >
                    <option value="">เลือกสาขาวิชา</option>
                    @foreach ($departments as $department)
                        <option value="{{ $department->id }}" {{ old('department_id', $user->department_id ?? '') == $department->id ? 'selected' : '' }}>
                            {{ $department->department_name }}
                        </option>
                    @endforeach
                </select>
                @error('department_id')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <div class="col-span-2">
                <div class="mb-1 block text-sm font-medium text-gray-700">ประวัติการศึกษา</div>
                <div id="educationHistoryRows" class="space-y-3"></div>
                <button type="button" id="addEducationHistoryRow" class="mt-3 rounded-md border border-blue-300 px-3 py-2 text-sm text-blue-700 hover:bg-blue-50">
                    เพิ่มวุฒิการศึกษา
                </button>
                @error('bio')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <div class="col-span-2">
                <label for="portfolio" class="mb-1 block text-sm font-medium text-gray-700">ผลงาน</label>
                <textarea
                    id="portfolio"
                    name="portfolio"
                    autocomplete="off"
                    rows="6"
                    class="block w-full rounded-md border border-black px-3 py-2 shadow-sm focus:border-black focus:ring-black @error('portfolio') border-red-500 @enderror"
                    placeholder="กรอกข้อมูลผลงาน เช่น งานวิจัย, บทความ, หนังสือ, รางวัลที่ได้รับ และผลงานอื่น ๆ ที่สำคัญ"
                >{{ old('portfolio', $user->portfolio ?? '') }}</textarea>
                @error('portfolio')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>
        </div>

        <div class="mt-6 flex items-center gap-3">
            <x-button type="default" text="ย้อนกลับ" icon="fas fa-arrow-left" href="{{ route('profile.show') }}" />
            <x-button type="warning" buttonType="submit" text="บันทึกการเปลี่ยนแปลง" icon="fas fa-save" />
        </div>
    </form>
</div>

@include('user.profile.partials.edit-profile-script')
@endsection
