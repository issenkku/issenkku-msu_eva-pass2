{{-- ส่วน pagination ของรายการผลการประเมิน --}}
<div data-evaluation-pagination class="border-t border-gray-200 px-6 py-3">
    <p data-evaluation-result-summary aria-live="polite" class="sr-only">
        แสดง {{ $evaluations->firstItem() ?? 0 }} ถึง
        {{ $evaluations->lastItem() ?? 0 }} จาก {{ $evaluations->total() }} รายการ
    </p>
    {{ $evaluations->onEachSide(1)->links() }}
</div>
