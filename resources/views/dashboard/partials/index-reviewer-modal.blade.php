{{-- โมดัลแสดงรายชื่อผู้ประเมิน --}}
<div id="reviewerModal" class="hidden fixed inset-0 z-50 bg-slate-900/50 p-4">
    <div class="flex h-full min-h-0 items-center justify-center">
        <div data-reviewer-list-modal-panel class="flex max-h-[calc(100dvh-2rem)] w-full max-w-lg flex-col overflow-hidden rounded-2xl bg-white shadow-xl">
            <div data-reviewer-list-modal-header class="flex flex-none items-center justify-between border-b border-slate-200 px-5 py-4">
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
            <div id="reviewerModalBody" data-reviewer-list-modal-body class="min-h-0 flex-1 space-y-3 overflow-y-auto overscroll-contain px-5 py-5"></div>
        </div>
    </div>
</div>
