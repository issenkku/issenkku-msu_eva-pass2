@extends('layouts.app')

@section('content')
<div class="max-w-4xl mx-auto p-6 bg-white rounded shadow">
    <div class="flex items-center justify-between mb-6">
        <h2 class="text-2xl font-semibold">โปรไฟล์บุคลากร</h2>
    </div>

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

    @php($educationHistory = $user->education_history_entries)

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
                <p class="text-gray-700 mt-2">ไม่ระบุข้อมูล</p>
            @endif
        </div>

        @if($user->portfolio)
        <div class="col-span-full bg-gray-50 p-4 rounded-lg">
            <strong class="text-gray-800">ผลงาน:</strong>
            <p class="text-gray-700 whitespace-pre-line mt-2">{{ $user->portfolio }}</p>
        </div>
        @endif
    </div>
</div>

<style>
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
function printProfile() {
    window.print();
}

document.addEventListener('keydown', function(e) {
    if (e.ctrlKey && e.key === 'p') {
        e.preventDefault();
        printProfile();
    }
});
</script>
@endsection
