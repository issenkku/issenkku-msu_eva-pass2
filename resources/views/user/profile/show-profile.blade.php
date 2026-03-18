@extends('layouts.app')

@section('content')
{{-- หน้าดูโปรไฟล์ (ส่วนตัว) --}}
<div class="max-w-5xl mx-auto">
    {{-- ส่วนหัวของหน้า --}}
    <x-header 
                title="ตั้งค่าโปรไฟล์"  
                text="ระบบจัดการข้อมูลส่วนบุคคล"  
                icon="fas fa-user" />
    {{-- การ์ดข้อมูลโปรไฟล์ --}}
    <div class="bg-white rounded-2xl shadow-lg border border-gray-200 overflow-hidden">
        {{-- <div class="bg-gradient-to-r from-indigo-50 via-purple-50 to-blue-50 px-6 py-5 border-b border-gray-200">
            <h2 class="text-2xl font-semibold text-gray-800">User Profile</h2>
        </div> --}}
        {{-- บล็อกเนื้อหา --}}
        <div class="p-6">
            {{-- รูปโปรไฟล์ + ข้อมูลพื้นฐาน --}}
            <div class="flex flex-col md:flex-row md:items-center mb-6 gap-6">
                <div class="flex-shrink-0">
                    <img src="{{ $user->profile_photo_url }}" 
                         alt="Profile Photo"
                         class="w-32 h-32 rounded-full object-cover border-4 border-white shadow-lg ring-4 ring-purple-100">
                </div>
                <div class="flex-grow">
                    <h3 class="text-2xl font-bold text-gray-900">{{ $user->prefix }} {{ $user->name }}</h3>
                    <p class="text-lg text-gray-600">{{ $user->email }}</p>
                    <p class="text-sm text-gray-500 mt-1">{{ optional($user->position)->name ?? '-' }} | {{ optional($user->department)->department_name ?? '-' }}</p>
                </div>
            </div>

            {{-- ตารางข้อมูลผู้ใช้ --}}
            <div class="grid grid-cols-1 md:grid-cols-2 gap-2">
                <div class="bg-gray-50 border border-gray-200 rounded-xl p-4">
                    <strong class="text-gray-800">รหัสพนักงาน:</strong>
                    <p class="text-gray-700 mt-1">{{ $user->employee_id ?? '-' }}</p>
                </div>

                <div class="bg-gray-50 border border-gray-200 rounded-xl p-4">
                    <strong class="text-gray-800">เบอร์โทร:</strong>
                    <p class="text-gray-700 mt-1">
                        {{-- จัดรูปแบบเบอร์โทรเมื่อมีข้อมูล --}}
                        {{ $user->phone ? preg_replace('/(\d{3})(\d{3})(\d{4})/', '$1-$2-$3', $user->phone) : '-' }}
                    </p>
                </div>

                <div class="bg-gray-50 border border-gray-200 rounded-xl p-4">
                    <strong class="text-gray-800">ประเภทบุคลากร:</strong>
                    <p class="text-gray-700 mt-1">{{ $user->personnel_type ?? '-' }}</p>
                </div>
                
                <div class="bg-gray-50 border border-gray-200 rounded-xl p-4">
                    <strong class="text-gray-800">บทบาท:</strong>
                    <p class="text-gray-700 mt-1">
                        {{-- รายการบทบาทของผู้ใช้ --}}
                        {{ $user->roles->pluck('name')->join(', ') ?: '-' }}
                    </p>
                </div>

                <div class="md:col-span-2 bg-gray-50 border border-gray-200 rounded-xl p-4">
                    <strong class="text-gray-800">ประวัติการศึกษา:</strong>
                    @php($educationHistory = $user->education_history_entries)
                    @if (!empty($educationHistory))
                        <div class="mt-3 overflow-x-auto">
                            <table class="min-w-full text-left text-sm text-gray-700">
                                <thead class="text-gray-600">
                                    <tr>
                                        <th class="py-2 pr-4">ปีที่จบ</th>
                                        <th class="py-2 pr-4">วุฒิการศึกษา</th>
                                        <th class="py-2">มหาวิทยาลัยที่จบ</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($educationHistory as $entry)
                                        <tr class="border-t border-gray-200">
                                            <td class="py-2 pr-4">{{ $entry['graduation_year'] ?? '-' }}</td>
                                            <td class="py-2 pr-4">{{ $entry['degree'] ?? '-' }}</td>
                                            <td class="py-2">{{ $entry['university'] ?? '-' }}</td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @else
                        <p class="text-gray-700 mt-2">-</p>
                    @endif
                </div>

                {{-- ส่วนผลงาน (แสดงเมื่อมีข้อมูล) --}}
                @if($user->portfolio)
                <div class="md:col-span-2 bg-gray-50 border border-gray-200 rounded-xl p-4">
                    <strong class="text-gray-800">ผลงาน:</strong>
                    <p class="text-gray-700 whitespace-pre-line mt-2">{{ $user->portfolio }}</p>
                </div>
                @endif
            </div>

            {{-- ปุ่มแก้ไขข้อมูล --}}
            <div class="mt-8 flex flex-wrap justify-end gap-3">
                @if ($user->is_public_profile_enabled)
                <x-button
                    type="primary"
                    text="ดู Public View"
                    icon="fas fa-up-right-from-square"
                    href="{{ $user->public_profile_url }}" />
                @endif
                <x-button 
                    type="warning"
                    text="แก้ไขข้อมูล"
                    icon="fas fa-edit"
                    href="{{ route('profile.edit') }}" />
            </div>
        </div>
    </div>
</div>
@endsection
