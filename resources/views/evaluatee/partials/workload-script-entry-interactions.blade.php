    function initWorkloadEntryInteractions() {
        if (itemSelect) {
            itemSelect.addEventListener('change', function () {
                const selected = itemSelect.selectedOptions[0];
                if (selected && selected.dataset.formId) {
                    setActiveFormId(selected.dataset.formId);
                }
                if (selected && selected.dataset.groupId) {
                    setActiveGroup(selected.dataset.groupId, groupLabelInput ? groupLabelInput.value : '');
                }
                updateFormFields();
            });
        }

        // ชุด interaction ของ subject picker แบบ custom dropdown
        if (subjectTrigger) {
            subjectTrigger.addEventListener('click', function () {
                if (subjectDropdown && !subjectDropdown.hidden) {
                    closeSubjectDropdown();
                } else {
                    openSubjectDropdown();
                    filterSubjectOptions();
                }
            });
        }

        if (subjectSearchInput) {
            subjectSearchInput.addEventListener('input', function () {
                filterSubjectOptions();
            });
        }

        if (subjectClearBtn) {
            subjectClearBtn.addEventListener('click', function () {
                clearSubjectSelection();
                if (subjectSearchInput) {
                    subjectSearchInput.focus();
                }
                updateWorkloadSubmitState();
            });
        }

        subjectOptions.forEach(function (option) {
            option.addEventListener('click', function () {
                selectSubjectOption(option, false);
                updateWorkloadSubmitState();
            });
        });

        document.addEventListener('click', function (event) {
            if (!subjectPicker || !subjectDropdown || subjectDropdown.hidden) {
                return;
            }

            if (!subjectPicker.contains(event.target)) {
                closeSubjectDropdown();
            }
        });

        if (detailFieldsContainer) {
            // เมื่อผู้ใช้แก้ field หน่วยกิตเอง ให้หยุดสถานะ autofill เพื่อไม่ให้โดนทับ
            detailFieldsContainer.addEventListener('input', function (event) {
                const target = event.target;
                if (!target || target.tagName !== 'INPUT') {
                    return;
                }

                const name = target.getAttribute('name') || '';
                const labelText = target.closest('.workload-modal-subfield')?.querySelector('.workload-modal-sub-label')?.textContent || '';
                if (/credit/i.test(name) || labelText.includes('\u0e2b\u0e19\u0e48\u0e27\u0e22\u0e01\u0e34\u0e15')) {
                    if (target.dataset) {
                        target.dataset.autofill = 'false';
                    }
                }
            });

            // ล้าง invalid state ทันทีเมื่อกรอกถูก และคำนวณสถานะปุ่มบันทึกใหม่
            detailFieldsContainer.addEventListener('input', function (event) {
                const target = event.target;
                if (!target || typeof target.matches !== 'function' || !target.matches('input, select, textarea')) {
                    return;
                }

                if (target.classList.contains('is-invalid') && isFilledInput(target)) {
                    target.classList.remove('is-invalid');
                }

                updateWorkloadSubmitState();
            });
        }

        if (formSelect) {
            formSelect.addEventListener('change', function () {
                setActiveFormId(formSelect.value);
                updateFormFields();
            });
        }

        if (evidenceContainer) {
            evidenceContainer.addEventListener('input', function () {
                updateWorkloadSubmitState();
            });
        }
    }
