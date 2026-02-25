@extends('layouts.app')

@section('content')
{{-- หน้าดูโปรไฟล์สาธารณะ --}}
<div class="max-w-4xl mx-auto p-6 bg-white rounded shadow">
    {{-- ชื่อหัวข้อหน้า --}}
    <div class="flex items-center justify-between mb-6">
        <h2 class="text-2xl font-semibold">โปรไฟล์บุคลากร</h2>
    </div>

    {{-- รูปโปรไฟล์ + ข้อมูลพื้นฐาน --}}
    <div class="flex items-center mb-6 gap-6">
        <div class="flex-shrink-0">
            <img src="{{ $user->profile_photo_url }}" 
                 alt="Profile Photo"
                 class="w-32 h-32 rounded-full object-cover border-4 border-gray-200 shadow-lg">
        </div>
        <div class="flex-grow">
            <h3 class="text-2xl font-bold text-gray-800">{{ $user->prefix }} {{ $user->name }}</h3>
            <p class="text-lg text-gray-600">{{ $user->email }}</p>
            <p class="text-sm text-gray-500 mt-1">{{ optional($user->position)->name ?? '-' }} | {{ optional($user->department)->department_name ?? '-' }}</p>
        </div>
    </div>

    {{-- ตารางข้อมูลผู้ใช้ --}}
    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
        <div class="bg-gray-50 p-4 rounded-lg">
            <strong class="text-gray-800">ตำแหน่ง:</strong>
            <p class="text-gray-700 mt-1">{{ optional($user->position)->name ?? '-' }}</p>
        </div>

        <div class="bg-gray-50 p-4 rounded-lg">
            <strong class="text-gray-800">สาขาวิชา:</strong>
            <p class="text-gray-700 mt-1">{{ optional($user->department)->department_name ?? '-' }}</p>
        </div>

        <div class="col-span-full bg-gray-50 p-4 rounded-lg">
            <strong class="text-gray-800">ประวัติการศึกษา:</strong>
            <p class="text-gray-700 whitespace-pre-line mt-2">{{ $user->bio ?? 'ไม่ระบุข้อมูล' }}</p>
        </div>

        {{-- ส่วนผลงาน (แสดงเมื่อมีข้อมูล) --}}
        @if($user->portfolio)
        <div class="col-span-full bg-gray-50 p-4 rounded-lg">
            <strong class="text-gray-800">ผลงาน:</strong>
            <p class="text-gray-700 whitespace-pre-line mt-2">{{ $user->portfolio }}</p>
        </div>
        @endif
    </div>
</div>

<style>
{{-- สไตล์สำหรับการพิมพ์ --}}
/* เพิ่มสไตล์สำหรับการพิมพ์ */
@media print {
    .no-print {
        display: none;
    }
    
    body {
        font-size: 12pt;
        line-height: 1.4;
    }
    
    .bg-gray-50 {
        background: #f9f9f9 !important;
        border: 1px solid #e5e5e5 !important;
    }
}
</style>

<script>
{{-- ฟังก์ชันช่วยสำหรับการพิมพ์ --}}
// เพิ่มความสามารถในการพิมพ์
function printProfile() {
    window.print();
}

// เพิ่ม keyboard shortcut สำหรับ print (Ctrl+P)
document.addEventListener('keydown', function(e) {
    if (e.ctrlKey && e.key === 'p') {
        e.preventDefault();
        printProfile();
    }
});
</script>
@endsection
