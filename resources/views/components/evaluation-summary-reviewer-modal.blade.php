{{-- ไฟล์มุมมอง: resources/views/components/evaluation-summary-reviewer-modal.blade.php --}}
<div id="evaluateeReviewerModal" class="fixed inset-0 z-50 hidden bg-slate-900/50 px-4 py-6">
    <div class="flex min-h-full items-center justify-center">
        <div class="w-full max-w-lg rounded-2xl bg-white shadow-xl">
            <div class="flex items-center justify-between border-b border-slate-200 px-5 py-4">
                <div>
                    <h3 class="text-lg font-semibold text-slate-900">รายชื่อผู้ประเมิน</h3>
                    <p class="text-sm text-slate-500">แสดงผู้ประเมินทั้งหมดตามลำดับที่กำหนด</p>
                </div>
                <button type="button" class="text-slate-400 hover:text-slate-600" onclick="window.closeEvaluateeReviewerModal()">
                    <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                    </svg>
                </button>
            </div>
            <div id="evaluateeReviewerModalBody" class="space-y-3 px-5 py-5"></div>
        </div>
    </div>
</div>
