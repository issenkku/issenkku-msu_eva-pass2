<div class="modal fade" id="subjectImportModal" tabindex="-1" aria-labelledby="subjectImportModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered modal-dialog-scrollable modal-fullscreen-sm-down subject-import-dialog">
        <div class="modal-content subject-import-content">
            <div class="modal-header">
                <h2 class="modal-title fs-5" id="subjectImportModalLabel">
                    <i class="fas fa-file-import me-2" aria-hidden="true"></i>นำเข้าข้อมูลรายวิชา
                </h2>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="ปิด"></button>
            </div>
            <form class="subject-import-form" method="POST" action="{{ route('subjects.import.preview.store') }}" enctype="multipart/form-data" data-subject-import-form>
                @csrf
                <div class="modal-body">
                    <div class="d-flex flex-wrap gap-2 mb-4">
                        <x-button type="secondary" text="ดาวน์โหลด Template เปล่า" icon="fas fa-download" :href="route('subjects.import.template')" />
                        <x-button type="secondary" text="ส่งออกข้อมูลรายวิชาปัจจุบัน" icon="fas fa-file-export" :href="route('subjects.import.export')" />
                    </div>

                    @php($subjectImportErrors = isset($errors) ? $errors->subjectImport : null)
                    @if($subjectImportErrors?->any())
                        <div class="alert alert-danger" role="alert">
                            @foreach($subjectImportErrors->all() as $message)
                                <div>{{ $message }}</div>
                            @endforeach
                        </div>
                    @endif

                    <label class="form-label fw-semibold" for="subjectImportFile">ไฟล์ Excel (.xlsx)</label>
                    <div class="border rounded p-4 text-center" tabindex="0" role="button"
                         aria-describedby="subjectImportHelp subjectImportClientError" data-subject-import-drop-zone>
                        <i class="fas fa-cloud-upload-alt fs-2 text-secondary" aria-hidden="true"></i>
                        <p class="mb-2">ลากไฟล์มาวาง หรือกดเพื่อเลือกไฟล์</p>
                        <input id="subjectImportFile" class="visually-hidden" type="file" name="import_file"
                               accept=".xlsx,application/vnd.openxmlformats-officedocument.spreadsheetml.sheet"
                               data-subject-import-file required>
                        <div id="subjectImportHelp" class="form-text">ไม่เกิน 10 MB และ 5,000 แถว</div>
                        <div id="subjectImportClientError" class="text-danger mt-2 d-none" role="alert"
                             data-subject-import-client-error></div>
                    </div>
                    <div class="mt-3 d-none" data-subject-import-selection aria-live="polite">
                        <span data-subject-import-filename></span>
                        <button type="button" class="btn btn-link text-danger" data-subject-import-remove>นำไฟล์ออก</button>
                    </div>
                </div>
                <div class="modal-footer">
                    <x-button type="secondary" buttonType="button" text="ยกเลิก" data-bs-dismiss="modal" />
                    <x-button type="primary" buttonType="submit" text="ตรวจสอบข้อมูล" icon="fas fa-search" disabled data-subject-import-submit />
                </div>
            </form>
        </div>
    </div>
</div>
