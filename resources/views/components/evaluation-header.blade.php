@props(['title', 'period', 'deadline'])

<div class="bg-gradient-to-r from-purple-100 to-pink-100 p-6 rounded-lg mb-6">
    <h2 class="text-xl font-bold text-gray-800 mb-2">{{ $title }}</h2>
    <p class="text-gray-600 mb-4">{{ $period }}</p>
    <p class="text-sm text-gray-500 mb-4">กำหนดส่ง: {{ $deadline }}</p>
    
    <div class="flex gap-3">
        <button class="bg-purple-500 text-white px-4 py-2 rounded-full flex items-center gap-2 hover:bg-purple-600 transition-colors">
            <i class="fas fa-calendar-alt"></i>
            กรอกแบบประเมิน
        </button>
        <button class="bg-white text-purple-500 border border-purple-500 px-4 py-2 rounded-full flex items-center gap-2 hover:bg-purple-50 transition-colors">
            <i class="fas fa-file-text"></i>
            ดูผลประเมินย้อนหลัง
        </button>
    </div>
</div>