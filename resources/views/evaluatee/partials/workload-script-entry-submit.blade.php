    function showWorkloadSaveMessage(message, isError) {
        let messageEl = document.getElementById('workloadSaveMessage');
        if (!messageEl) {
            messageEl = document.createElement('div');
            messageEl.id = 'workloadSaveMessage';
            messageEl.setAttribute('role', 'alert');
            document.body.appendChild(messageEl);
        }

        messageEl.className = isError
            ? 'fixed bottom-4 right-4 z-50 rounded-lg border border-red-200 bg-red-50 px-4 py-3 text-red-800 shadow-lg'
            : 'fixed bottom-4 right-4 z-50 rounded-lg border border-emerald-200 bg-emerald-50 px-4 py-3 text-emerald-800 shadow-lg';
        messageEl.textContent = message;
        messageEl.hidden = false;

        window.clearTimeout(messageEl.hideTimer);
        messageEl.hideTimer = window.setTimeout(function () {
            messageEl.hidden = true;
        }, 5000);
    }

    function initWorkloadEntrySubmit() {
        if (!workloadForm) {
            return;
        }

        let workloadModalSession = 0;
        if (workloadModalEl) {
            workloadModalEl.addEventListener('show.bs.modal', function () {
                workloadModalSession += 1;
            });
        }

        if (!window.WorkloadEntrySubmit || !window.WorkloadEntrySubmit.createWorkloadEntrySubmitCoordinator) {
            workloadForm.addEventListener('submit', function (event) {
                const missingFields = getMissingWorkloadFields(true);
                event.preventDefault();
                if (missingFields.length > 0) {
                    updateWorkloadSubmitState();
                    alert('กรุณากรอกข้อมูลให้ครบก่อนบันทึก: ' + missingFields.join(', '));
                    return;
                }

                workloadForm.submit();
            });
            return;
        }

        const submitCoordinator = window.WorkloadEntrySubmit.createWorkloadEntrySubmitCoordinator({
            form: workloadForm,
            submitButton: workloadSubmitButton,
            savingLabel: 'กำลังบันทึก...',
            getMissingFields: getMissingWorkloadFields,
            getModalSession: function () {
                return workloadModalSession;
            },
            getDropdownItemId: function () {
                return methodField && methodField.value === 'PUT' ? '' : lastDefaultItemId;
            },
            onMissingFields: function (missingFields) {
                updateWorkloadSubmitState();
                alert('กรุณากรอกข้อมูลให้ครบก่อนบันทึก: ' + missingFields.join(', '));
            },
            clearInvalid: function () {
                workloadForm.querySelectorAll('.is-invalid').forEach(function (field) {
                    field.classList.remove('is-invalid');
                });
            },
            requestSave: window.WorkloadEntrySubmit.requestWorkloadEntrySave,
            applyResponse: function (payload, context) {
                window.WorkloadEntrySubmit.applyWorkloadEntrySaveResponse(
                    document,
                    payload,
                    context.dropdownItemId
                );
            },
            markValidationErrors: function (errors) {
                return window.WorkloadEntrySubmit.markWorkloadValidationErrors(workloadForm, errors, {
                    evidenceContainer: evidenceContainer,
                    subjectSection: subjectSection,
                    subjectTrigger: subjectTrigger,
                });
            },
            resetForm: function () {
                workloadForm.reset();
                setFormMode('create');
                clearSubjectSelection();
                setEvidenceLinks(['']);
                selectDefaultForForm(lastDefaultFormId);
            },
            hideModal: function () {
                bootstrap.Modal.getOrCreateInstance(workloadModalEl).hide();
            },
            showMessage: showWorkloadSaveMessage,
        });

        workloadForm.addEventListener('submit', submitCoordinator);
    }

    function initWorkloadEntryDefaults() {
        // ซิงก์ state เริ่มต้นของ modal ให้ตรงกับค่าที่ render มาจาก server
        updateFormFields();
        updateSubjectRequirementState();
        updateSubjectTriggerText();
        filterSubjectOptions();
        updateWorkloadSubmitState();
    }
