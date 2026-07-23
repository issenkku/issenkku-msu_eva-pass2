{{-- ข้อผิดพลาดของการโหลดรายการแบบไม่รีเฟรชทั้งหน้า --}}
<div data-evaluation-request-error role="alert" class="hidden p-6 text-center">
    <p class="text-sm text-red-700">ไม่สามารถโหลดรายการได้ กรุณาลองอีกครั้ง</p>
    <a
        href="{{ request()->fullUrl() }}"
        class="mt-3 inline-flex rounded-lg border border-red-200 px-4 py-2 text-sm text-red-700">
        โหลดหน้าใหม่
    </a>
</div>
