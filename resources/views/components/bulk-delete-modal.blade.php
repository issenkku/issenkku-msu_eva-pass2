@props([
    'modalId',
    'title',
    'entityText',
    'formAction',
    'inputName' => 'ids',
    'async' => false,
    'buttonText' => 'ลบรายการที่เลือก',
])

<div class="modal fade" id="{{ $modalId }}" tabindex="-1" aria-labelledby="{{ $modalId }}Label" aria-hidden="true" data-bulk-delete-modal>
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content mt-6 max-w-md w-full rounded-xl border border-red-200 bg-white p-8 text-center shadow-2xl">
            <div class="modal-body text-center">
                <div class="mx-auto mb-4 flex h-16 w-16 items-center justify-center rounded-full bg-red-100">
                    <i class="fas fa-trash-alt text-2xl text-red-500" aria-hidden="true"></i>
                </div>
                <h3 id="{{ $modalId }}Label" class="mb-2 text-xl font-bold text-gray-900">
                    {{ $title }}
                </h3>
                <p class="mb-6 text-gray-600">
                    ต้องการลบ{{ $entityText }}ที่เลือก
                    <span class="font-semibold text-red-600" data-bulk-delete-selected-count>0</span>
                    รายการหรือไม่?
                    <br>
                    <span class="font-semibold text-red-500">ข้อมูลนี้จะไม่สามารถกู้คืนได้</span>
                </p>
            </div>
            <div class="modal-footer justify-content-center">
                <button type="button" class="rounded-md bg-gray-200 px-6 py-2 font-semibold text-gray-700 hover:bg-gray-300" data-bs-dismiss="modal">
                    <i class="fas fa-times me-2"></i>ยกเลิก
                </button>
                <form method="POST" action="{{ $formAction }}" data-bulk-delete-form @if($async) data-async-bulk-delete-form @endif>
                    @csrf
                    @method('DELETE')
                    <div data-bulk-delete-selected-inputs data-bulk-delete-input-name="{{ $inputName }}"></div>
                    <button type="submit" class="rounded-md bg-red-600 px-6 py-2 font-semibold text-white hover:bg-red-500">
                        <i class="fas fa-trash me-2"></i>{{ $buttonText }}
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>

@once
    @include('components.bulk-delete-script')
@endonce
