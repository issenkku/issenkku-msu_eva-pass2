{{-- script ของหน้าแผนก ดูแล modal, validation, submit และ auto-hide message --}}
<script>
    let isFormValid = false;
    const departmentUpdateAction = "{{ route('departments.update', ':id') }}";

    function validateForm() {
        const departmentNameInput = document.getElementById('department_name');
        const departmentNameError = document.getElementById('department_nameError');

        if (!departmentNameInput || !departmentNameError) {
            return false;
        }

        const departmentNameValue = departmentNameInput.value.trim();
        let isValid = true;

        if (departmentNameValue === '') {
            departmentNameInput.classList.add('is-invalid');
            departmentNameError.style.display = 'block';
            departmentNameError.textContent = 'กรุณากรอกชื่อแผนก';
            isValid = false;
        } else {
            departmentNameInput.classList.remove('is-invalid');
            departmentNameError.style.display = 'none';
        }

        updateSubmitButton(isValid);
        isFormValid = isValid;
        return isValid;
    }

    function updateSubmitButton(isValid) {
        const submitBtn = document.getElementById('departmentSubmitBtn');
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

    // เปิด modal สำหรับเพิ่มแผนกใหม่ และรีเซ็ตฟอร์มกลับเป็นสถานะเริ่มต้น
    function openCreateModal() {
        const form = document.getElementById('departmentForm');
        const modalTitle = document.getElementById('departmentModalLabel');
        if (!form || !modalTitle) return;

        resetForm();
        form.action = "{{ route('departments.store') }}";
        document.getElementById('form_method').value = 'POST';
        modalTitle.innerHTML = '<i class="fas fa-plus me-2"></i>เพิ่มแผนก';

        const modalEl = document.getElementById('departmentModal');
        const modal = bootstrap.Modal.getOrCreateInstance(modalEl);
        modal.show();

        modalEl.addEventListener('shown.bs.modal', function() {
            document.getElementById('department_name').focus();
        }, { once: true });
    }

    // ดึงข้อมูลเดิมจากปุ่มในตารางมาเติมใน modal เพื่อแก้ไขแผนกเดิม
    function handleEdit(id, name) {
        const form = document.getElementById('departmentForm');
        const modalTitle = document.getElementById('departmentModalLabel');
        if (!form || !modalTitle) return;

        resetForm();
        form.action = departmentUpdateAction
            .replace(':id', encodeURIComponent(id))
            .replace('%3Aid', encodeURIComponent(id));
        document.getElementById('form_method').value = 'PUT';
        document.getElementById('departmentId').value = id;
        document.getElementById('department_name').value = name || '';
        modalTitle.innerHTML = '<i class="fas fa-edit me-2"></i>แก้ไขแผนก';

        setTimeout(() => validateForm(), 100);

        const modalEl = document.getElementById('departmentModal');
        const modal = bootstrap.Modal.getOrCreateInstance(modalEl);
        modal.show();

        modalEl.addEventListener('shown.bs.modal', function() {
            document.getElementById('department_name').focus();
        }, { once: true });
    }

    window.openCreateModal = openCreateModal;
    window.handleEdit = handleEdit;
    window.submitForm = submitForm;

    function submitForm() {
        if (!validateForm()) {
            return false;
        }

        const form = document.getElementById('departmentForm');
        const modalEl = document.getElementById('departmentModal');

        if (form && modalEl && isFormValid) {
            const submitBtn = document.getElementById('departmentSubmitBtn');
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
        const form = document.getElementById('departmentForm');
        if (!form) return;

        form.reset();
        document.getElementById('departmentId').value = '';
        document.getElementById('form_method').value = 'POST';

        form.querySelectorAll('.form-control').forEach((input) => {
            input.classList.remove('is-invalid');
        });

        const error = document.getElementById('department_nameError');
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

        const departmentNameInput = document.getElementById('department_name');
        if (departmentNameInput) {
            departmentNameInput.addEventListener('input', validateForm);
            departmentNameInput.addEventListener('blur', validateForm);
            departmentNameInput.addEventListener('keypress', function(e) {
                if (e.key === 'Enter') {
                    e.preventDefault();
                    if (validateForm()) {
                        submitForm();
                    }
                }
            });
        }

        const form = document.getElementById('departmentForm');
        if (form) {
            form.addEventListener('submit', function(e) {
                e.preventDefault();
                return validateForm();
            });
        }

        document.querySelectorAll('[data-department-edit]').forEach((button) => {
            button.addEventListener('click', function() {
                handleEdit(this.dataset.departmentId, this.dataset.departmentName || '');
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
