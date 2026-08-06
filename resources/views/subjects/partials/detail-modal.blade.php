<div class="modal fade" id="subjectDetailModal" tabindex="-1" aria-labelledby="subjectDetailModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered modal-dialog-scrollable modal-fullscreen-sm-down">
        <div class="modal-content border-0 shadow-lg">
            <div class="modal-header border-bottom">
                <h5 class="modal-title fw-bold" id="subjectDetailModalLabel">
                    <i class="fas fa-eye me-2 text-primary" aria-hidden="true"></i>รายละเอียดรายวิชา
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="ปิด"></button>
            </div>

            <div class="modal-body p-4">
                <div class="d-flex align-items-start justify-content-between gap-3 mb-4">
                    <div>
                        <h5 class="fw-bold mb-1" data-subject-detail-code-name></h5>
                        <div class="text-muted" data-subject-detail-secondary-name></div>
                    </div>
                    <span class="badge rounded-pill px-3 py-2" data-subject-detail-status></span>
                </div>

                <section class="border rounded-3 p-3 mb-3" aria-labelledby="subjectDetailCreditsTitle">
                    <h6 class="fw-bold mb-3" id="subjectDetailCreditsTitle">
                        <i class="fas fa-graduation-cap me-2 text-primary" aria-hidden="true"></i>ข้อมูลหน่วยกิต
                    </h6>
                    <div class="row g-3 text-center">
                        <div class="col-6 col-md-3">
                            <div class="small text-muted">หน่วยกิตรวม</div>
                            <div class="fs-4 fw-bold" data-subject-detail-credits></div>
                        </div>
                        <div class="col-6 col-md-3 border-start">
                            <div class="small text-muted">บรรยาย</div>
                            <div class="fs-4 fw-bold" data-subject-detail-lecture-credits></div>
                        </div>
                        <div class="col-6 col-md-3 border-start">
                            <div class="small text-muted">ปฏิบัติ</div>
                            <div class="fs-4 fw-bold" data-subject-detail-lab-credits></div>
                        </div>
                        <div class="col-6 col-md-3 border-start">
                            <div class="small text-muted">ศึกษาด้วยตนเอง</div>
                            <div class="fs-4 fw-bold" data-subject-detail-self-study-credits></div>
                        </div>
                    </div>
                </section>

                <section class="border rounded-3 p-3 mb-3" aria-labelledby="subjectDetailHoursTitle">
                    <h6 class="fw-bold mb-3" id="subjectDetailHoursTitle">
                        <i class="far fa-clock me-2 text-primary" aria-hidden="true"></i>ข้อมูลชั่วโมง
                    </h6>
                    <div class="row g-3 text-center">
                        <div class="col-4">
                            <div class="small text-muted">บรรยาย</div>
                            <div class="fs-4 fw-bold" data-subject-detail-lecture-hours></div>
                        </div>
                        <div class="col-4 border-start">
                            <div class="small text-muted">ปฏิบัติ</div>
                            <div class="fs-4 fw-bold" data-subject-detail-lab-hours></div>
                        </div>
                        <div class="col-4 border-start">
                            <div class="small text-muted">ศึกษาด้วยตนเอง</div>
                            <div class="fs-4 fw-bold" data-subject-detail-self-study-hours></div>
                        </div>
                    </div>
                </section>

                <div class="alert alert-primary d-flex align-items-center mb-0" role="status">
                    <i class="fas fa-info-circle me-2" aria-hidden="true"></i>
                    <span data-subject-detail-source></span>
                </div>
            </div>

            <div class="modal-footer border-top">
                <button type="button" class="btn btn-secondary px-4" data-bs-dismiss="modal">ปิด</button>
            </div>
        </div>
    </div>
</div>
