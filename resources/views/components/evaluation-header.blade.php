{{-- ไฟล์มุมมอง: resources/views\components\evaluation-header.blade.php --}}
@props(['title', 'period', 'deadline', 'daysLeft', 'evaluationId'])

{{-- บล็อกเนื้อหา --}}
<div class="p-6 rounded-xl shadow-md border relative hover:shadow-lg transition duration-200"
     style="background: linear-gradient(180deg, #faf1ffff 0%, #efe6ffff 100%); border-color: #f3e8ff;">
    
    <!-- Ribbon for urgent deadlines -->
    @if($daysLeft === 0)
        <span class="absolute top-3 right-3 bg-red-500 text-white text-xs font-semibold px-2 py-1 rounded-md">
            กำหนดส่งวันนี้
        </span>
    @elseif($daysLeft !== null && $daysLeft <= 3)
        <span class="absolute top-3 right-3 bg-yellow-400 text-white text-xs font-semibold px-2 py-1 rounded-md">
            ใกล้ครบกำหนด
        </span>
    @endif

    <h2 class="text-lg font-bold text-gray-800 mb-2">{{ $title }}</h2>
    <p class="text-sm text-gray-500 mb-1">{{ $period }}</p>
    <p class="text-sm text-gray-400 mb-2">กำหนดส่ง: {{ $deadline }}</p>

    <p class="text-sm font-semibold 
        {{ $daysLeft === 0 ? 'text-red-600' : ($daysLeft <= 3 ? 'text-yellow-600' : 'text-green-600') }}">
        @if($daysLeft === 0)
            วันนี้เป็นวันกำหนดส่ง
        @else
            เหลือเวลาอีก {{ $daysLeft }} วัน
        @endif
    </p>

    {{-- บล็อกเนื้อหา --}}
    <div class="mt-4">
        <a href="{{ route('evaluation.show', $evaluationId) }}"
           class="inline-flex items-center gap-2 px-5 py-2 rounded-full font-medium shadow transition text-white"
           style="background: linear-gradient(90deg, #9333ea 0%, #ec4899 100%);">
            <i class="fas fa-clipboard-check"></i> กรอกแบบประเมิน
        </a>
    </div>
</div>
