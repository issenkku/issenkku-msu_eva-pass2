<div class="modal fade" id="subjectModal" tabindex="-1" aria-labelledby="subjectModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg subject-modal-dialog">
        <div class="modal-content modal-content-custom">
            <div class="modal-header modal-header-custom">
                <h5 class="modal-title" id="subjectModalLabel">
                    <i class="fas fa-plus me-2"></i>เพิ่มรายวิชา
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body modal-body-custom">
                <form id="subjectForm" method="POST" action="{{ request()->routeIs('evaluatee.workload') ? route('subjects.store.evaluatee') : route('subjects.store') }}">
                    @csrf
                    <input type="hidden" name="_method" id="form_method" value="POST">
                    <input type="hidden" id="subjectId" name="id">

                    <div class="mb-3">
                        <label for="code" class="form-label">รหัสรายวิชา <span class="text-danger">*</span></label>
                        <input type="text" id="code" name="code" class="form-control" required placeholder="กรุณาระบุรหัสรายวิชา">
                        <div class="text-red-500 text-sm mt-1 hidden" id="codeError">กรุณากรอกรหัสรายวิชา</div>
                    </div>

                    <div class="mb-3">
                        <label for="name_th" class="form-label">ชื่อรายวิชา (ไทย)</label>
                        <input type="text" id="name_th" name="name_th" class="form-control" aria-describedby="subjectNameHelp subjectNameError" placeholder="กรุณาระบุชื่อรายวิชาภาษาไทย">
                    </div>

                    <div class="mb-3">
                        <label for="name_en" class="form-label">ชื่อรายวิชา (อังกฤษ)</label>
                        <input type="text" id="name_en" name="name_en" class="form-control" aria-describedby="subjectNameHelp subjectNameError" placeholder="กรุณาระบุชื่อรายวิชาภาษาอังกฤษ (ถ้ามี)">
                        <div id="subjectNameHelp" class="form-text">
                            กรอกชื่อรายวิชาภาษาไทยหรือภาษาอังกฤษอย่างน้อยหนึ่งช่อง
                        </div>
                        <div class="text-red-500 text-sm mt-1 hidden" id="subjectNameError" role="alert">
                            กรุณากรอกชื่อรายวิชาภาษาไทยหรือภาษาอังกฤษอย่างน้อยหนึ่งช่อง
                        </div>
                    </div>

                    <div class="mb-3">
                        <label for="credits" class="form-label">หน่วยกิต</label>
                        <input type="number" id="credits" name="credits" class="form-control" min="0" value="0" placeholder="0">
                        <div class="text-red-500 text-sm mt-1 hidden" id="creditsError">กรุณากรอกหน่วยกิต</div>
                    </div>

                    <div class="row g-3">
                        <div class="col-md-4">
                            <label for="lecture_credits" class="form-label">หน่วยกิตบรรยาย</label>
                            <input type="number" id="lecture_credits" name="lecture_credits" class="form-control" min="0" value="0" placeholder="0">
                            <div class="text-red-500 text-sm mt-1 hidden" id="lectureCreditsError">กรุณากรอกหน่วยกิตบรรยาย</div>
                        </div>
                        <div class="col-md-4">
                            <label for="lab_credits" class="form-label">หน่วยกิตปฏิบัติ</label>
                            <input type="number" id="lab_credits" name="lab_credits" class="form-control" min="0" value="0" placeholder="0">
                            <div class="text-red-500 text-sm mt-1 hidden" id="labCreditsError">กรุณากรอกหน่วยกิตปฏิบัติ</div>
                        </div>
                        <div class="col-md-4">
                            <label for="self_study_credits" class="form-label">หน่วยกิตศึกษาด้วยตนเอง</label>
                            <input type="number" id="self_study_credits" name="self_study_credits" class="form-control" min="0" value="0" placeholder="0">
                            <div class="text-red-500 text-sm mt-1 hidden" id="selfStudyCreditsError">กรุณากรอกหน่วยกิตศึกษาด้วยตนเอง</div>
                        </div>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <x-button
                    type="secondary"
                    text="ยกเลิก"
                    icon="fas fa-times"
                    data-bs-dismiss="modal" />
                <x-button
                    type="primary"
                    buttonType="button"
                    text="บันทึก"
                    data-modal-submit-trigger
                    icon="fas fa-save"
                    id="subjectSubmitBtn"
                    class="btn-disabled transition-colors disabled:opacity-50 disabled:cursor-not-allowed" />
            </div>
        </div>
    </div>
</div>
