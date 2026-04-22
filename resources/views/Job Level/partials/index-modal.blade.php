{{-- modal กลางสำหรับเพิ่มและแก้ไขระดับตำแหน่งงาน ใช้ฟอร์มเดียวกันเพื่อลดโค้ดซ้ำ --}}
<div class="modal fade" id="positionModal" tabindex="-1" aria-labelledby="positionModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content modal-content-custom">
            <div class="modal-header modal-header-custom">
                <h5 class="modal-title" id="positionModalLabel">
                    <i class="fas fa-plus me-2"></i>เพิ่มระดับตำแหน่งงาน
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body modal-body-custom">
                <form id="positionForm" method="POST">
                    @csrf
                    <input type="hidden" name="_method" id="form_method" value="POST">
                    <input type="hidden" id="positionId" name="id">

                    <div class="mb-3">
                        <label for="name" class="form-label">ชื่อระดับตำแหน่งงาน <span class="text-danger">*</span></label>
                        <input type="text" id="name" name="name" class="form-control" required placeholder="กรุณาระบุชื่อระดับตำแหน่งงาน">
                        <div class="text-red-500 text-sm mt-1 hidden" id="nameError">กรุณากรอกชื่อระดับตำแหน่งงาน</div>
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
                    onclick="submitForm()"
                    icon="fas fa-save"
                    id="positionSubmitBtn"
                    class="btn-disabled transition-colors disabled:opacity-50 disabled:cursor-not-allowed" />
            </div>
        </div>
    </div>
</div>
