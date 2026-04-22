{{-- โมดัลแสดงรายชื่อผู้ประเมิน --}}
<div id="reviewerModal" class="hidden fixed inset-0 z-50 bg-slate-900/50 px-4 py-6">
    <div class="flex min-h-full items-center justify-center">
        <div class="w-full max-w-lg rounded-2xl bg-white shadow-xl">
            <div class="flex items-center justify-between border-b border-slate-200 px-5 py-4">
                <div>
                    <h3 class="text-lg font-semibold text-slate-900">รายชื่อผู้ประเมิน</h3>
                    <p class="text-sm text-slate-500">แสดงผู้ประเมินทั้งหมดตามลำดับที่กำหนด</p>
                </div>
                <button type="button" id="closeReviewerModal" class="text-slate-400 hover:text-slate-600">
                    <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                    </svg>
                </button>
            </div>
            <div id="reviewerModalBody" class="space-y-3 px-5 py-5"></div>
        </div>
    </div>
</div>
