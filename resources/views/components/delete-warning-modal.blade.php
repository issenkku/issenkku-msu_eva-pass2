{{-- ไฟล์มุมมอง: resources/views/components/delete-warning-modal.blade.php --}}
@props([
    'text',
    'formAction' => '#',
    'entityUrl' => null,
])

{{-- Delete Confirmation Modal --}}
<div class="modal fade" id="deleteModal" tabindex="-1" aria-labelledby="deleteModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content mt-6 max-w-md w-full rounded-xl border border-red-200 bg-white p-8 text-center shadow-2xl">
            <div class="modal-body delete-modal-body text-center">
                <div class="mx-auto mb-4 flex h-16 w-16 items-center justify-center rounded-full bg-red-100">
                    <svg class="h-8 w-8 text-red-500" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </div>
                <h3 class="mb-2 text-xl font-bold text-gray-900">ยืนยันการลบ{{ $text }}</h3>
                <p class="mb-6 text-gray-600">
                    คุณต้องการลบ{{ $text }}นี้หรือไม่?
                    <br>
                    <span class="font-semibold text-red-500">ข้อมูลนี้จะไม่สามารถกู้คืนได้</span>
                </p>
            </div>
            <div class="modal-footer justify-content-center">
                <button type="button" class="rounded-md bg-gray-200 px-6 py-2 font-semibold text-gray-700 hover:bg-gray-300" data-bs-dismiss="modal">
                    <i class="fas fa-times me-2"></i>ยกเลิก
                </button>
                <form id="deleteForm" method="POST" style="display: inline;">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="rounded-md bg-red-600 px-6 py-2 font-semibold text-white hover:bg-red-500">
                        <i class="fas fa-trash me-2"></i>ลบข้อมูล
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>

@include('components.delete-warning-modal-script')
