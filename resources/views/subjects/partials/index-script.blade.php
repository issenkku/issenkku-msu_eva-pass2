{{-- script ของหน้ารายวิชา ดูแล modal, validation, submit และ auto-hide message --}}
<script>
    let isFormValid = false;

    // ตรวจฟอร์มก่อนเปิดให้กดบันทึก เพื่อคุมทั้ง create และ edit ใช้กติกาเดียวกัน
    function validateForm() {
        const codeInput = document.getElementById('code');
        const nameThInput = document.getElementById('name_th');
        const nameEnInput = document.getElementById('name_en');
        const lectureCreditsInput = document.getElementById('lecture_credits');
        const labCreditsInput = document.getElementById('lab_credits');
        const selfStudyCreditsInput = document.getElementById('self_study_credits');
        const lectureHoursInput = document.getElementById('lecture_hours');
        const labHoursInput = document.getElementById('lab_hours');
        const selfStudyHoursInput = document.getElementById('self_study_hours');
        const creditsInput = document.getElementById('credits');
        const codeError = document.getElementById('codeError');
        const subjectNameError = document.getElementById('subjectNameError');
        const lectureCreditsError = document.getElementById('lectureCreditsError');
        const labCreditsError = document.getElementById('labCreditsError');
        const selfStudyCreditsError = document.getElementById('selfStudyCreditsError');
        const lectureHoursError = document.getElementById('lectureHoursError');
        const labHoursError = document.getElementById('labHoursError');
        const selfStudyHoursError = document.getElementById('selfStudyHoursError');
        const creditsError = document.getElementById('creditsError');
        const submitBtn = document.getElementById('subjectSubmitBtn');

        if (!codeInput || !nameThInput || !nameEnInput || !lectureCreditsInput || !labCreditsInput || !selfStudyCreditsInput ||
            !lectureHoursInput || !labHoursInput || !selfStudyHoursInput || !creditsInput || !codeError || !subjectNameError ||
            !lectureCreditsError || !labCreditsError || !selfStudyCreditsError || !lectureHoursError || !labHoursError ||
            !selfStudyHoursError || !creditsError || !submitBtn) {
            return false;
        }

        const codeValue = codeInput.value.trim();
        const nameThValue = nameThInput.value.trim();
        const nameEnValue = nameEnInput.value.trim();
        const lectureCreditsValue = lectureCreditsInput.value.trim();
        const labCreditsValue = labCreditsInput.value.trim();
        const selfStudyCreditsValue = selfStudyCreditsInput.value.trim();
        const creditsValue = creditsInput.value.trim();
        const lectureHoursValue = lectureHoursInput.value.trim();
        const labHoursValue = labHoursInput.value.trim();
        const selfStudyHoursValue = selfStudyHoursInput.value.trim();
        let isValid = true;

        if (codeValue === '') {
            codeInput.classList.add('is-invalid');
            codeError.style.display = 'block';
            codeError.textContent = 'กรุณากรอกรหัสรายวิชา';
            isValid = false;
        } else {
            codeInput.classList.remove('is-invalid');
            codeError.style.display = 'none';
        }

        if (nameThValue === '' && nameEnValue === '') {
            nameThInput.classList.add('is-invalid');
            nameEnInput.classList.add('is-invalid');
            nameThInput.setAttribute('aria-invalid', 'true');
            nameEnInput.setAttribute('aria-invalid', 'true');
            subjectNameError.style.display = 'block';
            subjectNameError.textContent = 'กรุณากรอกชื่อรายวิชาภาษาไทยหรือภาษาอังกฤษอย่างน้อยหนึ่งช่อง';
            isValid = false;
        } else {
            nameThInput.classList.remove('is-invalid');
            nameEnInput.classList.remove('is-invalid');
            nameThInput.removeAttribute('aria-invalid');
            nameEnInput.removeAttribute('aria-invalid');
            subjectNameError.style.display = 'none';
        }

        if (!Number.isInteger(Number(lectureCreditsValue)) || Number(lectureCreditsValue) < 0) {
            lectureCreditsInput.classList.add('is-invalid');
            lectureCreditsError.style.display = 'block';
            lectureCreditsError.textContent = 'กรุณากรอกหน่วยกิตบรรยาย';
            isValid = false;
        } else {
            lectureCreditsInput.classList.remove('is-invalid');
            lectureCreditsError.style.display = 'none';
        }

        if (!Number.isInteger(Number(labCreditsValue)) || Number(labCreditsValue) < 0) {
            labCreditsInput.classList.add('is-invalid');
            labCreditsError.style.display = 'block';
            labCreditsError.textContent = 'กรุณากรอกหน่วยกิตปฏิบัติ';
            isValid = false;
        } else {
            labCreditsInput.classList.remove('is-invalid');
            labCreditsError.style.display = 'none';
        }

        if (!Number.isInteger(Number(selfStudyCreditsValue)) || Number(selfStudyCreditsValue) < 0) {
            selfStudyCreditsInput.classList.add('is-invalid');
            selfStudyCreditsError.style.display = 'block';
            selfStudyCreditsError.textContent = 'กรุณากรอกหน่วยกิตศึกษาด้วยตนเอง';
            isValid = false;
        } else {
            selfStudyCreditsInput.classList.remove('is-invalid');
            selfStudyCreditsError.style.display = 'none';
        }

        if (!Number.isInteger(Number(creditsValue)) || Number(creditsValue) < 0) {
            creditsInput.classList.add('is-invalid');
            creditsError.style.display = 'block';
            creditsError.textContent = 'กรุณากรอกหน่วยกิต';
            isValid = false;
        } else {
            creditsInput.classList.remove('is-invalid');
            creditsError.style.display = 'none';
        }

        if (!Number.isInteger(Number(lectureHoursValue)) || Number(lectureHoursValue) < 0) {
            lectureHoursInput.classList.add('is-invalid');
            lectureHoursError.style.display = 'block';
            isValid = false;
        } else {
            lectureHoursInput.classList.remove('is-invalid');
            lectureHoursError.style.display = 'none';
        }

        if (!Number.isInteger(Number(labHoursValue)) || Number(labHoursValue) < 0) {
            labHoursInput.classList.add('is-invalid');
            labHoursError.style.display = 'block';
            isValid = false;
        } else {
            labHoursInput.classList.remove('is-invalid');
            labHoursError.style.display = 'none';
        }

        if (!Number.isInteger(Number(selfStudyHoursValue)) || Number(selfStudyHoursValue) < 0) {
            selfStudyHoursInput.classList.add('is-invalid');
            selfStudyHoursError.style.display = 'block';
            isValid = false;
        } else {
            selfStudyHoursInput.classList.remove('is-invalid');
            selfStudyHoursError.style.display = 'none';
        }

        updateSubmitButton(isValid);
        isFormValid = isValid;
        return isValid;
    }

    function updateSubmitButton(isValid) {
        const submitBtn = document.getElementById('subjectSubmitBtn');
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

    // เปิด modal เพิ่มรายวิชาโดยรีเซ็ตสถานะทั้งหมดกลับเป็นค่าเริ่มต้น
    function openCreateModal() {
        const form = document.getElementById('subjectForm');
        const modalTitle = document.getElementById('subjectModalLabel');
        if (!form || !modalTitle) return;

        resetForm();
        form.action = "{{ route('subjects.store') }}";
        document.getElementById('form_method').value = 'POST';
        modalTitle.innerHTML = '<i class="fas fa-plus me-2"></i>เพิ่มรายวิชา';

        const modalEl = document.getElementById('subjectModal');
        const modal = bootstrap.Modal.getOrCreateInstance(modalEl);
        modal.show();

        modalEl.addEventListener('shown.bs.modal', function() {
            document.getElementById('code').focus();
        }, { once: true });
    }

    // เติมข้อมูลเดิมลง modal เพื่อแก้ไขรายวิชาโดยใช้ dataset จากปุ่มในตาราง
    function handleEdit(id, code, nameTh, nameEn, credits, lectureCredits, labCredits, selfStudyCredits, lectureHours, labHours, selfStudyHours) {
        const form = document.getElementById('subjectForm');
        const modalTitle = document.getElementById('subjectModalLabel');
        if (!form || !modalTitle) return;

        resetForm();

        form.action = `/subjects/${id}`;
        document.getElementById('form_method').value = 'PUT';
        document.getElementById('subjectId').value = id;
        document.getElementById('code').value = code || '';
        document.getElementById('name_th').value = nameTh || '';
        document.getElementById('name_en').value = nameEn || '';
        document.getElementById('lecture_credits').value = lectureCredits ?? 0;
        document.getElementById('lab_credits').value = labCredits ?? 0;
        document.getElementById('self_study_credits').value = selfStudyCredits ?? 0;
        document.getElementById('lecture_hours').value = lectureHours ?? 0;
        document.getElementById('lab_hours').value = labHours ?? 0;
        document.getElementById('self_study_hours').value = selfStudyHours ?? 0;
        document.getElementById('credits').value = credits ?? '';
        modalTitle.innerHTML = '<i class="fas fa-edit me-2"></i>แก้ไขรายวิชา';

        setTimeout(() => {
            validateForm();
        }, 100);

        const modalEl = document.getElementById('subjectModal');
        const modal = bootstrap.Modal.getOrCreateInstance(modalEl);
        modal.show();

        modalEl.addEventListener('shown.bs.modal', function() {
            document.getElementById('code').focus();
        }, { once: true });
    }

    function submitForm() {
        if (!validateForm()) {
            return false;
        }

        const form = document.getElementById('subjectForm');
        const modalEl = document.getElementById('subjectModal');

        if (form && modalEl && isFormValid) {
            const submitBtn = document.getElementById('subjectSubmitBtn');
            if (submitBtn && !window.MasterDataPage) {
                const originalText = submitBtn.innerHTML;
                submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin me-2"></i>กำลังบันทึก...';
                submitBtn.disabled = true;

                setTimeout(() => {
                    submitBtn.innerHTML = originalText;
                    submitBtn.disabled = false;
                }, 5000);
            }

            if (!window.MasterDataPage) {
                bootstrap.Modal.getInstance(modalEl)?.hide();
                form.submit();
                return;
            }

            window.MasterDataPage.submitMasterDataForm({
                applyMutation: (payload) => window.MasterDataPage.reconcileMasterDataMutation(document, payload),
                applyValidationErrors: (errors) => window.MasterDataPage.applyMasterDataValidationErrors(form, errors, {
                    name_th: 'subjectNameError',
                }),
                button: submitBtn,
                form,
                hideModal: () => bootstrap.Modal.getInstance(modalEl)?.hide(),
                resetForm,
            });
        }
    }

    function resetForm() {
        const form = document.getElementById('subjectForm');
        if (!form) return;

        form.reset();
        document.getElementById('subjectId').value = '';
        document.getElementById('form_method').value = 'POST';

        form.querySelectorAll('.form-control').forEach((input) => {
            input.classList.remove('is-invalid');
            input.removeAttribute('aria-invalid');
        });

        [
            'codeError', 'subjectNameError', 'lectureCreditsError', 'labCreditsError', 'selfStudyCreditsError',
            'lectureHoursError', 'labHoursError', 'selfStudyHoursError', 'creditsError',
        ].forEach((errorId) => {
            const errorEl = document.getElementById(errorId);
            if (errorEl) {
                errorEl.style.display = 'none';
            }
        });

        updateSubmitButton(false);
        isFormValid = false;
    }

    // ล้าง backdrop ค้างเพื่อกัน modal ซ้อนหรือปิดไม่สุดหลังเปลี่ยนหน้า
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

        const creditsError = document.getElementById('creditsError');
        if (creditsError) {
            creditsError.textContent = 'กรุณากรอกหน่วยกิต';
        }

        document.addEventListener('click', function(event) {
            const button = event.target.closest('[data-role="subject-edit-trigger"]');
            if (button) {
                handleEdit(
                    button.dataset.id,
                    button.dataset.code || '',
                    button.dataset.nameTh || '',
                    button.dataset.nameEn || '',
                    button.dataset.credits || 0,
                    button.dataset.lectureCredits || 0,
                    button.dataset.labCredits || 0,
                    button.dataset.selfStudyCredits || 0,
                    button.dataset.lectureHours || 0,
                    button.dataset.labHours || 0,
                    button.dataset.selfStudyHours || 0
                );
            }
        });

        const codeInput = document.getElementById('code');
        const nameThInput = document.getElementById('name_th');
        const nameEnInput = document.getElementById('name_en');
        const lectureCreditsInput = document.getElementById('lecture_credits');
        const labCreditsInput = document.getElementById('lab_credits');
        const selfStudyCreditsInput = document.getElementById('self_study_credits');
        const lectureHoursInput = document.getElementById('lecture_hours');
        const labHoursInput = document.getElementById('lab_hours');
        const selfStudyHoursInput = document.getElementById('self_study_hours');
        const creditsInput = document.getElementById('credits');

        if (codeInput) {
            codeInput.addEventListener('input', validateForm);
            codeInput.addEventListener('blur', validateForm);
        }

        [nameThInput, nameEnInput].forEach((input) => {
            if (!input) return;
            input.addEventListener('input', validateForm);
            input.addEventListener('blur', validateForm);
        });

        [
            lectureCreditsInput, labCreditsInput, selfStudyCreditsInput,
            lectureHoursInput, labHoursInput, selfStudyHoursInput,
        ].forEach((input) => {
            if (!input) return;

            input.addEventListener('input', validateForm);
            input.addEventListener('blur', validateForm);

            input.addEventListener('keypress', function(e) {
                if (e.key === 'Enter') {
                    e.preventDefault();
                    if (validateForm()) {
                        submitForm();
                    }
                }
            });
        });

        if (creditsInput) {
            creditsInput.addEventListener('input', validateForm);
            creditsInput.addEventListener('blur', validateForm);
        }

        const form = document.getElementById('subjectForm');
        if (form) {
            form.addEventListener('submit', function(e) {
                e.preventDefault();
                return validateForm();
            });
        }

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
