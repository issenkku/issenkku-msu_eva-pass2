{{-- ส่วนหัวของหน้า dashboard พร้อมสถานะการกรอง --}}
<div class="flex flex-row justify-between">
    <div class="mb-8 animate-fadeIn">
        <h1 class="mb-2 text-3xl font-bold text-gray-900">แดชบอร์ด</h1>
        <p class="text-gray-600">ภาพรวมความคืบหน้าการประเมินเพื่อใช้ติดตามผู้ที่ยังกรอกไม่เสร็จ</p>
    </div>

    <div class="mb-6">
        @if ($hasDashboardFilters)
            <span class="ml-4 inline-flex rounded border bg-gray-100 px-2 py-1 text-sm text-black">
                มีการกรองข้อมูล
            </span>
        @endif
    </div>
</div>
