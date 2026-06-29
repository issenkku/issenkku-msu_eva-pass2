{{-- ปุ่มกรองสถานะของรายการประเมินในตาราง --}}
@php
    $statusStyles = [
        'มอบหมาย' => 'bg-red-100 text-red-800 hover:bg-red-200',
        'เริ่มกรอกข้อมูล' => 'bg-blue-100 text-blue-800 hover:bg-blue-200',
        'กำลังดำเนินการ' => 'bg-yellow-100 text-yellow-800 hover:bg-yellow-200',
        'ประเมินเสร็จสิ้น' => 'bg-green-100 text-green-800 hover:bg-green-200',
    ];

    $firstStatus = array_key_first($statusCounts);
@endphp

<div class="mx-4 flex flex-wrap justify-between gap-2 pt-3">
    <div class="mb-3">
        <div class="text-xs font-semibold uppercase tracking-wide text-slate-500">กรองแบบเร็ว</div>
        <div class="mt-1 text-sm text-slate-600">แตะป้ายสถานะเพื่อกรองตารางรายชื่อด้านล่าง</div>
    </div>
    <div class="mb-6 flex flex-wrap gap-3">
        @foreach ($statusCounts as $status => $count)
            @php
                $isShowAll = $status === $firstStatus;
                $isActive = $isShowAll;
                $style = $statusStyles[$status] ?? 'bg-gray-100 text-gray-800 hover:bg-gray-200';
                $activeClass = $isActive ? 'ring-2 ring-offset-2 ring-blue-300' : '';
                $ariaLabel = ($isShowAll ? 'แสดงทั้งหมด' : 'กรองสถานะ ' . $status) . ' จำนวน ' . $count . ' รายการ';
            @endphp

            <button
                type="button"
                data-status-filter="{{ $isShowAll ? 'all' : $status }}"
                aria-pressed="{{ $isActive ? 'true' : 'false' }}"
                aria-label="{{ $ariaLabel }}"
                class="dashboard-status-filter inline-block rounded-full px-3 py-1 text-sm font-medium transition {{ $style }} {{ $activeClass }}">
                {{ $status }} ({{ $count }})
            </button>
        @endforeach
    </div>
</div>
