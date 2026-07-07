<div class="modal fade" id="bulkStatusUsersModal" tabindex="-1" aria-labelledby="bulkStatusUsersModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content mt-6 max-w-md w-full rounded-xl border border-purple-200 bg-white p-8 text-center shadow-2xl">
            <div class="modal-body text-center">
                <div class="mx-auto mb-4 flex h-16 w-16 items-center justify-center rounded-full bg-purple-100">
                    <i class="fas fa-user-check text-2xl text-purple-600" aria-hidden="true"></i>
                </div>
                <h3 id="bulkStatusUsersModalLabel" class="mb-2 text-xl font-bold text-gray-900">
                    ยืนยันการเปลี่ยนสถานะ
                </h3>
                <p class="mb-6 text-gray-600">
                    ต้องการเปลี่ยนสถานะเจ้าหน้าที่ที่เลือก
                    <span class="font-semibold text-purple-700" data-user-bulk-status-selected-count>0</span>
                    รายการเป็น
                    <span class="font-semibold text-purple-700" data-user-bulk-status-label>-</span>
                    หรือไม่?
                </p>
            </div>
            <div class="modal-footer justify-content-center">
                <button type="button" class="rounded-md bg-gray-200 px-6 py-2 font-semibold text-gray-700 hover:bg-gray-300" data-bs-dismiss="modal">
                    <i class="fas fa-times me-2" aria-hidden="true"></i>ยกเลิก
                </button>
                <button type="button" class="rounded-md bg-purple-600 px-6 py-2 font-semibold text-white hover:bg-purple-500" data-user-bulk-status-confirm>
                    <i class="fas fa-check me-2" aria-hidden="true"></i>ยืนยัน
                </button>
            </div>
        </div>
    </div>
</div>
