@props(['evaluation'])

@php
    $statusIcons = [
        'ยังไม่ประเมิน' => 'fas fa-exclamation-circle text-red-500',
        'กำลังดำเนินการ' => 'fas fa-clock text-yellow-500',
        'ดำเนินการเสร็จแล้ว' => 'fas fa-check-circle text-green-500'
    ];
    
    $statusStyles = [
        'ยังไม่ประเมิน' => 'bg-red-100 text-red-800',
        'กำลังดำเนินการ' => 'bg-yellow-100 text-yellow-800',
        'ดำเนินการเสร็จแล้ว' => 'bg-green-100 text-green-800'
    ];
    
    $iconClass = $statusIcons[$evaluation['status']] ?? 'fas fa-clock text-yellow-500';
    $statusClass = $statusStyles[$evaluation['status']] ?? 'bg-gray-100 text-gray-800';
@endphp
<div class="flex items-center justify-between p-4 bg-white rounded-lg border hover:shadow-md transition-shadow cursor-pointer">
    <div class="flex items-center gap-3">
        <i class="{{ $iconClass }}"></i>
        <span class="font-medium text-gray-800">{{ $evaluation['title'] }}</span>
    </div>
    <div class="flex items-center gap-4">
        <span class="px-3 py-1 rounded-full text-sm {{ $statusClass }}">
            {{ $evaluation['status'] }}
        </span>
        <div class="flex items-center gap-1 text-sm text-gray-500">
            <i class="fas fa-clock"></i>
            {{ $evaluation['last_update'] }}
        </div>
    </div>
</div>
