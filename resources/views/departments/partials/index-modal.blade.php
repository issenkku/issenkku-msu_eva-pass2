{{-- modal กลางสำหรับเพิ่มและแก้ไขแผนก ใช้ฟอร์มเดียวกันเพื่อลดโค้ดซ้ำ --}}
<div class="modal fade" id="departmentModal" tabindex="-1" aria-labelledby="departmentModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content modal-content-custom">
            <div class="modal-header modal-header-custom">
                <h5 class="modal-title" id="departmentModalLabel">
                    <i class="fas fa-plus me-2"></i>เพิ่มแผนก
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body modal-body-custom">
                <form id="departmentForm" method="POST">
                    @csrf
                    <input type="hidden" name="_method" id="form_method" value="POST">
                    <input type="hidden" id="departmentId" name="id">

                    <div class="mb-3">
                        <label for="department_name" class="form-label">ชื่อแผนก <span class="text-danger">*</span></label>
                        <input type="text" id="department_name" name="department_name" class="form-control" required placeholder="กรุณาระบุชื่อแผนก">
                        <div class="text-red-500 text-sm mt-1 hidden" id="department_nameError">กรุณากรอกชื่อแผนก</div>
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
                    id="departmentSubmitBtn"
                    class="btn-disabled transition-colors disabled:opacity-50 disabled:cursor-not-allowed" />
            </div>
        </div>
    </div>
</div>
