{{-- ส่วนสรุปท้ายตาราง แสดงช่วงรายการปัจจุบันและ pagination --}}
<div class="flex justify-between items-center mt-4">
    <span>แสดง {{ $users->firstItem() }} - {{ $users->lastItem() }} จาก {{ $users->total() }} รายการ</span>
    <div class="flex gap-2 items-center">
        {{ $users->links() }}
    </div>
</div>
