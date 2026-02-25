{{-- ไฟล์มุมมอง: resources/views\components\delete-warning-modal.blade.php --}}
@props([
    'text',
    'formAction' => '#',
    'entityUrl' => null,
])

<!-- Delete Confirmation Modal -->
{{--  --}}
<div class="modal fade" id="deleteModal" tabindex="-1" aria-labelledby="deleteModalLabel" aria-hidden="true">
    {{--  --}}
    <div class="modal-dialog modal-dialog-centered">
        {{--  --}}
        <div class="modal-content mt-6 bg-white border border-red-200 rounded-xl shadow-2xl max-w-md w-full p-8 text-center">
            <div class="modal-body delete-modal-body text-center">
                <div class="mx-auto mb-4 flex items-center justify-center w-16 h-16 rounded-full bg-red-100">
                    <svg class="w-8 h-8 text-red-500" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/></svg>
                </div>
                <h3 class="text-xl font-bold text-gray-900 mb-2">ยืนยันการลบ{{ $text }}</h3>
                <p class="text-gray-600 mb-6">คุณต้องการลบ{{ $text }}นี้หรือไม่? <br><span class="text-red-500 font-semibold">ข้อมูลนี้จะไม่สามารถกู้คืนได้</span></p>
            </div>
            <div class="modal-footer justify-content-center">
                <button type="button" class="px-6 py-2 bg-gray-200 text-gray-700 rounded-md font-semibold hover:bg-gray-300" data-bs-dismiss="modal">
                    <i class="fas fa-times me-2"></i>ยกเลิก
                </button>
                {{-- ฟอร์ม --}}
                <form id="deleteForm" method="POST" style="display: inline;">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="px-6 py-2 bg-red-600 text-white rounded-md font-semibold hover:bg-red-500">
                        <i class="fas fa-trash me-2"></i>ลบข้อมูล
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
    window.confirmDelete = function(id) {
        const form = document.getElementById('deleteForm');
        const modalEl = document.getElementById('deleteModal');

        if (!form || !modalEl) return;

        // Replace :id in formAction with the real id
        const actionTemplate = "{{ $formAction }}";
        form.action = actionTemplate.replace(':id', id);

        const modal = bootstrap.Modal.getOrCreateInstance(modalEl);
        modal.show();
    }
</script>
