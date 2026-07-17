@if($importPreview !== null && $importPreviewToken !== null)
    <div class="modal fade" id="subjectImportPreviewModal" tabindex="-1"
         aria-labelledby="subjectImportPreviewModalLabel" aria-hidden="true"
         data-bs-backdrop="static" data-bs-keyboard="false">
        <div class="modal-dialog modal-xl modal-dialog-scrollable modal-fullscreen-sm-down">
            <form id="subjectImportConfirmForm" class="modal-content" method="POST"
                  action="{{ route('subjects.import.confirm', $importPreviewToken) }}"
                  data-subject-import-confirm-form>
                @csrf
                <div class="modal-header">
                    <div>
                        <h2 class="modal-title fs-5" id="subjectImportPreviewModalLabel" tabindex="-1">
                            <i class="fas fa-file-import me-2" aria-hidden="true"></i>ตรวจสอบข้อมูลก่อนนำเข้า
                        </h2>
                        <p class="mb-0 text-muted small">ตรวจรายการใหม่และเลือกรายการซ้ำที่ต้องการอัปเดต</p>
                    </div>
                    <button type="submit" form="subjectImportCancelForm" class="btn-close"
                            aria-label="ยกเลิก Preview และปิด"></button>
                </div>
                <div class="modal-body">
                    @include('subjects.imports.partials.summary', ['preview' => $importPreview])
                    @include('subjects.imports.partials.tables', ['preview' => $importPreview])
                    @if(count($importPreview['errors']) > 0)
                        <p id="subjectImportConfirmDisabledReason" class="text-danger mb-0" role="alert">
                            กรุณาแก้ไขข้อผิดพลาดในไฟล์แล้วสร้าง Preview ใหม่ก่อนยืนยันการนำเข้า
                        </p>
                    @endif
                </div>
                <div class="modal-footer">
                    <x-button type="secondary" buttonType="submit" text="ยกเลิก"
                        form="subjectImportCancelForm" icon="fas fa-times" />
                    <x-button type="primary" buttonType="submit" text="ยืนยันการนำเข้า"
                        icon="fas fa-check" :disabled="count($importPreview['errors']) > 0"
                        aria-describedby="{{ count($importPreview['errors']) > 0 ? 'subjectImportConfirmDisabledReason' : '' }}" />
                </div>
            </form>
        </div>
    </div>

    <form id="subjectImportCancelForm" method="POST"
          action="{{ route('subjects.import.cancel', $importPreviewToken) }}" class="d-none">
        @csrf
        @method('DELETE')
    </form>

    @include('subjects.imports.partials.script')
@endif
