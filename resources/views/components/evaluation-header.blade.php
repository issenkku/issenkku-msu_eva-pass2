@props(['title', 'period', 'deadline', 'daysLeft', 'evaluationId'])

<div class="relative rounded-xl border p-6 shadow-md transition duration-200 hover:shadow-lg"
     style="background: linear-gradient(180deg, #faf1ff 0%, #efe6ff 100%); border-color: #f3e8ff;">

    @if ($daysLeft === 0)
        <span class="absolute right-3 top-3 rounded-md bg-red-500 px-2 py-1 text-xs font-semibold text-white">
            กำหนดส่งวันนี้
        </span>
    @elseif ($daysLeft !== null && $daysLeft <= 3)
        <span class="absolute right-3 top-3 rounded-md bg-yellow-400 px-2 py-1 text-xs font-semibold text-white">
            ใกล้ครบกำหนด
        </span>
    @endif

    <h2 class="mb-2 text-lg font-bold text-gray-800">{{ $title }}</h2>
    <p class="mb-1 text-sm text-gray-500">{{ $period }}</p>
    <p class="mb-2 text-sm text-gray-400">กำหนดส่ง: {{ $deadline }}</p>

    <div class="mt-4">
        <a href="{{ route('evaluation.show', $evaluationId) }}"
           class="inline-flex items-center gap-2 rounded-full px-5 py-2 font-medium text-white shadow transition"
           style="background: linear-gradient(90deg, #9333ea 0%, #ec4899 100%);">
            <i class="fas fa-clipboard-check"></i> กรอกแบบประเมิน
        </a>
    </div>
</div>
