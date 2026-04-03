{{-- script ของหน้ารายวิชา ดูแล modal, validation, submit และ auto-hide message --}}
<script>
    let isFormValid = false;

    // คำนวณหน่วยกิตรวมจากบรรยาย ปฏิบัติ และศึกษาด้วยตนเองทุกครั้งที่กรอกข้อมูล
    function updateTotalCredits() {
        const lectureInput = document.getElementById('lecture_credits');
        const labInput = document.getElementById('lab_credits');
        const selfStudyInput = document.getElementById('self_study_credits');
        const totalInput = document.getElementById('credits');

        if (!lectureInput || !labInput || !selfStudyInput || !totalInput) {
            return 0;
        }

        const lecture = Number(lectureInput.value || 0);
        const lab = Number(labInput.value || 0);
        const selfStudy = Number(selfStudyInput.value || 0);
        const total = lecture + lab + selfStudy;

        totalInput.value = Number.isNaN(total) ? 0 : total;
        return totalInput.value;
    }

    // ตรวจฟอร์มก่อนเปิดให้กดบันทึก เพื่อคุมทั้ง create และ edit ใช้กติกาเดียวกัน
    function validateForm() {
        const codeInput = document.getElementById('code');
        const nameThInput = document.getElementById('name_th');
        const lectureCreditsInput = document.getElementById('lecture_credits');
        const labCreditsInput = document.getElementById('lab_credits');
        const selfStudyCreditsInput = document.getElementById('self_study_credits');
        const creditsInput = document.getElementById('credits');
        const codeError = document.getElementById('codeError');
        const nameThError = document.getElementById('nameThError');
        const lectureCreditsError = document.getElementById('lectureCreditsError');
        const labCreditsError = document.getElementById('labCreditsError');
        const selfStudyCreditsError = document.getElementById('selfStudyCreditsError');
        const creditsError = document.getElementById('creditsError');
        const submitBtn = document.getElementById('subjectSubmitBtn');

        if (!codeInput || !nameThInput || !lectureCreditsInput || !labCreditsInput || !selfStudyCreditsInput || !creditsInput ||
            !codeError || !nameThError || !lectureCreditsError || !labCreditsError || !selfStudyCreditsError || !creditsError || !submitBtn) {
            return false;
        }

        const codeValue = codeInput.value.trim();
        const nameThValue = nameThInput.value.trim();
        const lectureCreditsValue = lectureCreditsInput.value.trim();
        const labCreditsValue = labCreditsInput.value.trim();
        const selfStudyCreditsValue = selfStudyCreditsInput.value.trim();
        const creditsValue = creditsInput.value.trim();
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

        if (nameThValue === '') {
            nameThInput.classList.add('is-invalid');
            nameThError.style.display = 'block';
            nameThError.textContent = 'กรุณากรอกชื่อรายวิชา';
            isValid = false;
        } else {
            nameThInput.classList.remove('is-invalid');
            nameThError.style.display = 'none';
        }

        if (lectureCreditsValue === '' || Number.isNaN(Number(lectureCreditsValue))) {
            lectureCreditsInput.classList.add('is-invalid');
            lectureCreditsError.style.display = 'block';
            lectureCreditsError.textContent = 'กรุณากรอกหน่วยกิตบรรยาย';
            isValid = false;
        } else {
            lectureCreditsInput.classList.remove('is-invalid');
            lectureCreditsError.style.display = 'none';
        }

        if (labCreditsValue === '' || Number.isNaN(Number(labCreditsValue))) {
            labCreditsInput.classList.add('is-invalid');
            labCreditsError.style.display = 'block';
            labCreditsError.textContent = 'กรุณากรอกหน่วยกิตปฏิบัติ';
            isValid = false;
        } else {
            labCreditsInput.classList.remove('is-invalid');
            labCreditsError.style.display = 'none';
        }

        if (selfStudyCreditsValue === '' || Number.isNaN(Number(selfStudyCreditsValue))) {
            selfStudyCreditsInput.classList.add('is-invalid');
            selfStudyCreditsError.style.display = 'block';
            selfStudyCreditsError.textContent = 'กรุณากรอกหน่วยกิตศึกษาด้วยตนเอง';
            isValid = false;
        } else {
            selfStudyCreditsInput.classList.remove('is-invalid');
            selfStudyCreditsError.style.display = 'none';
        }

        if (creditsValue === '' || Number.isNaN(Number(creditsValue))) {
            creditsInput.classList.add('is-invalid');
            creditsError.style.display = 'block';
            creditsError.textContent = 'กรุณากรอกหน่วยกิต';
            isValid = false;
        } else {
            creditsInput.classList.remove('is-invalid');
            creditsError.style.display = 'none';
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
        clearModalBackdrop();

        const form = document.getElementById('subjectForm');
        const modalTitle = document.getElementById('subjectModalLabel');
        if (!form || !modalTitle) return;

        resetForm();
        form.action = "{{ route('subjects.store') }}";
        document.getElementById('form_method').value = 'POST';
        modalTitle.innerHTML = '<i class="fas fa-plus me-2"></i>เพิ่มรายวิชา';

        const modalEl = document.getElementById('subjectModal');
        const modal = new bootstrap.Modal(modalEl);
        modal.show();

        modalEl.addEventListener('shown.bs.modal', function() {
            document.getElementById('code').focus();
        }, { once: true });
    }

    // เติมข้อมูลเดิมลง modal เพื่อแก้ไขรายวิชาโดยใช้ dataset จากปุ่มในตาราง
    function handleEdit(id, code, nameTh, nameEn, credits, lectureCredits, labCredits, selfStudyCredits) {
        clearModalBackdrop();

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
        document.getElementById('credits').value = credits ?? '';
        modalTitle.innerHTML = '<i class="fas fa-edit me-2"></i>แก้ไขรายวิชา';

        setTimeout(() => {
            validateForm();
        }, 100);

        const modalEl = document.getElementById('subjectModal');
        const modal = new bootstrap.Modal(modalEl);
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
        const form = document.getElementById('subjectForm');
        if (!form) return;

        form.reset();
        document.getElementById('subjectId').value = '';
        document.getElementById('form_method').value = 'POST';

        form.querySelectorAll('.form-control').forEach((input) => {
            input.classList.remove('is-invalid');
        });

        ['codeError', 'nameThError', 'lectureCreditsError', 'labCreditsError', 'selfStudyCreditsError', 'creditsError'].forEach((errorId) => {
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

        const creditsLabel = document.querySelector('#subjectModal label[for="credits"]');
        if (creditsLabel) {
            creditsLabel.innerHTML = 'หน่วยกิต <span class="text-danger">*</span>';
        }

        const creditsError = document.getElementById('creditsError');
        if (creditsError) {
            creditsError.textContent = 'กรุณากรอกหน่วยกิต';
        }

        document.querySelectorAll('[data-role="subject-edit-trigger"]').forEach((button) => {
            button.addEventListener('click', function() {
                handleEdit(
                    this.dataset.id,
                    this.dataset.code || '',
                    this.dataset.nameTh || '',
                    this.dataset.nameEn || '',
                    this.dataset.credits || 0,
                    this.dataset.lectureCredits || 0,
                    this.dataset.labCredits || 0,
                    this.dataset.selfStudyCredits || 0
                );
            });
        });

        const codeInput = document.getElementById('code');
        const nameThInput = document.getElementById('name_th');
        const lectureCreditsInput = document.getElementById('lecture_credits');
        const labCreditsInput = document.getElementById('lab_credits');
        const selfStudyCreditsInput = document.getElementById('self_study_credits');
        const creditsInput = document.getElementById('credits');

        if (codeInput) {
            codeInput.addEventListener('input', validateForm);
            codeInput.addEventListener('blur', validateForm);
        }

        if (nameThInput) {
            nameThInput.addEventListener('input', validateForm);
            nameThInput.addEventListener('blur', validateForm);
        }

        [lectureCreditsInput, labCreditsInput, selfStudyCreditsInput].forEach((input) => {
            if (!input) return;

            input.addEventListener('input', function() {
                updateTotalCredits();
                validateForm();
            });

            input.addEventListener('blur', function() {
                updateTotalCredits();
                validateForm();
            });

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
