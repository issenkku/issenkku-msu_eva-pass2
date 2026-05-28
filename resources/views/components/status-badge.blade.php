@props(['status', 'count', 'active' => false])

@php
    $statusClasses = [
        'ทั้งหมด' => 'bg-purple-200 text-purple-800',
        'ยังไม่ประเมิน' => 'bg-red-200 text-red-800',
        'บันทึกร่างประเมิน' => 'bg-blue-200 text-blue-800',
        'รอผลการประเมิน' => 'bg-yellow-200 text-yellow-800',
        'ประเมินเสร็จสิ้น' => 'bg-green-200 text-green-800',
    ];
    
    $baseClasses = 'px-4 py-2 rounded-full text-sm font-medium transition-colors cursor-pointer';
    $statusClass = $statusClasses[$status] ?? 'bg-gray-200 text-gray-800';
    $activeClass = $active ? 'ring-2 ring-purple-400' : '';
@endphp

<button class="{{ $baseClasses }} {{ $statusClass }} {{ $activeClass }}">
    {{ $status }} ({{ $count }})
</button>
