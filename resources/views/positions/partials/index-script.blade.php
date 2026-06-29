{{-- script ของหน้าตำแหน่ง ดูแล modal, validation, submit และ auto-hide message --}}
<script>
    let isFormValid = false;
    const positionUpdateAction = "{{ route('positions.update', ':id') }}";

    function validateForm() {
        const nameInput = document.getElementById('name');
        const nameError = document.getElementById('nameError');

        if (!nameInput || !nameError) {
            return false;
        }

        const nameValue = nameInput.value.trim();
        let isValid = true;

        if (nameValue === '') {
            nameInput.classList.add('is-invalid');
            nameError.style.display = 'block';
            nameError.textContent = 'กรุณากรอกชื่อตำแหน่ง';
            isValid = false;
        } else {
            nameInput.classList.remove('is-invalid');
            nameError.style.display = 'none';
        }

        updateSubmitButton(isValid);
        isFormValid = isValid;
        return isValid;
    }

    function updateSubmitButton(isValid) {
        const submitBtn = document.getElementById('positionSubmitBtn');
        if (!submitBtn) return;

        if (isValid) {
            submitBtn.classList.remove('btn-disabled');
            submitBtn.disabled = false;
            submitBtn.style.pointerEvents = 'auto';
        } else {
            submitBtn.classList.add('btn-disabled');
            submitBtn.disabled = true;
            submitBtn.style.pointerEvents = 'none';
        }
    }

    // เปิด modal สำหรับเพิ่มตำแหน่งใหม่ และรีเซ็ตฟอร์มกลับเป็นสถานะเริ่มต้น
    function openCreateModal() {
        clearModalBackdrop();

        const form = document.getElementById('positionForm');
        const modalTitle = document.getElementById('positionModalLabel');
        if (!form || !modalTitle) return;

        resetForm();
        form.action = "{{ route('positions.store') }}";
        document.getElementById('form_method').value = 'POST';
        modalTitle.innerHTML = '<i class="fas fa-plus me-2"></i>เพิ่มตำแหน่ง';

        const modalEl = document.getElementById('positionModal');
        const modal = new bootstrap.Modal(modalEl);
        modal.show();

        modalEl.addEventListener('shown.bs.modal', function() {
            document.getElementById('name').focus();
        }, { once: true });
    }

    // ดึงข้อมูลเดิมจากปุ่มในตารางมาเติมใน modal เพื่อแก้ไขตำแหน่งเดิม
    function handleEdit(id, name) {
        clearModalBackdrop();

        const form = document.getElementById('positionForm');
        const modalTitle = document.getElementById('positionModalLabel');
        if (!form || !modalTitle) return;

        resetForm();
        form.action = positionUpdateAction
            .replace(':id', encodeURIComponent(id))
            .replace('%3Aid', encodeURIComponent(id));
        document.getElementById('form_method').value = 'PUT';
        document.getElementById('positionId').value = id;
        document.getElementById('name').value = name || '';
        modalTitle.innerHTML = '<i class="fas fa-edit me-2"></i>แก้ไขตำแหน่ง';

        setTimeout(() => validateForm(), 100);

        const modalEl = document.getElementById('positionModal');
        const modal = new bootstrap.Modal(modalEl);
        modal.show();

        modalEl.addEventListener('shown.bs.modal', function() {
            document.getElementById('name').focus();
        }, { once: true });
    }

    window.openCreateModal = openCreateModal;
    window.handleEdit = handleEdit;
    window.submitForm = submitForm;

    function submitForm() {
        if (!validateForm()) {
            return false;
        }

        const form = document.getElementById('positionForm');
        const modalEl = document.getElementById('positionModal');

        if (form && modalEl && isFormValid) {
            const submitBtn = document.getElementById('positionSubmitBtn');
            if (submitBtn) {
                const originalText = submitBtn.innerHTML;
                submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin me-2"></i>กำลังบันทึก...';
                submitBtn.disabled = true;

                setTimeout(() => {
                    submitBtn.innerHTML = originalText;
                    submitBtn.disabled = false;
                }, 5000);
            }

            const modal = bootstrap.Modal.getInstance(modalEl);
            if (modal) {
                modal.hide();
            }

            form.submit();
        }
    }

    function resetForm() {
        const form = document.getElementById('positionForm');
        if (!form) return;

        form.reset();
        document.getElementById('positionId').value = '';
        document.getElementById('form_method').value = 'POST';

        form.querySelectorAll('.form-control').forEach((input) => {
            input.classList.remove('is-invalid');
        });

        const error = document.getElementById('nameError');
        if (error) {
            error.style.display = 'none';
        }

        updateSubmitButton(false);
        isFormValid = false;
    }

    // ล้าง backdrop และสถานะ modal ค้าง เพื่อกัน modal ซ้อนหลัง navigate หรือ reload หน้า
    function clearModalBackdrop() {
        document.querySelectorAll('.modal.show').forEach((modal) => {
            const modalInstance = bootstrap.Modal.getInstance(modal);
            if (modalInstance) {
                modalInstance.hide();
            }
        });

        document.querySelectorAll('.modal-backdrop').forEach((backdrop) => {
            backdrop.remove();
        });

        document.body.classList.remove('modal-open');
        document.body.style.removeProperty('overflow');
        document.body.style.removeProperty('padding-right');
    }

    document.addEventListener('DOMContentLoaded', function() {
        clearModalBackdrop();

        document.querySelectorAll('[data-create-modal-open]').forEach((button) => {
            button.addEventListener('click', openCreateModal);
        });

        document.querySelectorAll('[data-modal-submit-trigger]').forEach((button) => {
            button.addEventListener('click', submitForm);
        });

        const nameInput = document.getElementById('name');
        if (nameInput) {
            nameInput.addEventListener('input', validateForm);
            nameInput.addEventListener('blur', validateForm);
            nameInput.addEventListener('keypress', function(e) {
                if (e.key === 'Enter') {
                    e.preventDefault();
                    if (validateForm()) {
                        submitForm();
                    }
                }
            });
        }

        const form = document.getElementById('positionForm');
        if (form) {
            form.addEventListener('submit', function(e) {
                e.preventDefault();
                return validateForm();
            });
        }

        document.querySelectorAll('[data-position-edit]').forEach((button) => {
            button.addEventListener('click', function() {
                handleEdit(this.dataset.positionId, this.dataset.positionName || '');
            });
        });

        const messages = document.querySelectorAll('#successMessage, #warningMessage, #errorMessage');
        messages.forEach(function(message) {
            setTimeout(function() {
                if (message.parentElement) {
                    message.style.transform = 'translateX(100%)';
                    setTimeout(function() {
                        if (message.parentElement) {
                            message.remove();
                        }
                    }, 300);
                }
            }, 5000);
        });
    });

    window.addEventListener('pageshow', clearModalBackdrop);
    window.addEventListener('load', clearModalBackdrop);
</script>
