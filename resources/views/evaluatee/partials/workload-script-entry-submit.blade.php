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

    function workloadInputNameForError(errorKey) {
        const keyParts = String(errorKey).split('.');
        if (keyParts.length < 2) {
            return keyParts[0];
        }

        return keyParts[0] + keyParts.slice(1).map(function (part) {
            return '[' + part + ']';
        }).join('');
    }

    function showWorkloadValidationErrors(errors) {
        const messages = [];
        Object.keys(errors || {}).forEach(function (errorKey) {
            const field = Array.from(workloadForm.querySelectorAll('[name]')).find(function (input) {
                return input.name === workloadInputNameForError(errorKey);
            });
            if (field) {
                field.classList.add('is-invalid');
            }

            const fieldMessages = Array.isArray(errors[errorKey]) ? errors[errorKey] : [errors[errorKey]];
            fieldMessages.forEach(function (message) {
                if (message) {
                    messages.push(String(message));
                }
            });
        });

        return messages;
    }

    function initWorkloadEntrySubmit() {
        if (!workloadForm) {
            return;
        }

        let isSubmitting = false;
        workloadForm.addEventListener('submit', async function (event) {
            const missingFields = getMissingWorkloadFields(true);
            if (missingFields.length > 0) {
                event.preventDefault();
                updateWorkloadSubmitState();
                alert('กรุณากรอกข้อมูลให้ครบก่อนบันทึก: ' + missingFields.join(', '));
                return;
            }

            event.preventDefault();
            if (isSubmitting) {
                return;
            }

            if (!window.WorkloadEntrySubmit) {
                workloadForm.submit();
                return;
            }

            isSubmitting = true;
            const originalButtonLabel = workloadSubmitButton ? workloadSubmitButton.textContent : '';
            if (workloadSubmitButton) {
                workloadSubmitButton.disabled = true;
                workloadSubmitButton.textContent = 'กำลังบันทึก...';
            }
            workloadForm.querySelectorAll('.is-invalid').forEach(function (field) {
                field.classList.remove('is-invalid');
            });

            try {
                const payload = await window.WorkloadEntrySubmit.requestWorkloadEntrySave(workloadForm);
                window.WorkloadEntrySubmit.applyWorkloadEntrySaveResponse(document, payload);
                bootstrap.Modal.getOrCreateInstance(workloadModalEl).hide();
                showWorkloadSaveMessage(payload.message || 'บันทึกข้อมูลภาระงานเรียบร้อยแล้ว', false);
            } catch (error) {
                if (error.status === 422) {
                    const validationMessages = showWorkloadValidationErrors(error.errors);
                    showWorkloadSaveMessage(
                        validationMessages.join(' ') || error.message || 'กรุณาตรวจสอบข้อมูลแล้วลองใหม่อีกครั้ง',
                        true,
                    );
                } else {
                    showWorkloadSaveMessage(error.message || 'ไม่สามารถบันทึกข้อมูลได้ กรุณาลองใหม่อีกครั้ง', true);
                }
            } finally {
                if (workloadSubmitButton) {
                    workloadSubmitButton.disabled = false;
                    workloadSubmitButton.textContent = originalButtonLabel;
                }
                isSubmitting = false;
            }
        });
    }

    function initWorkloadEntryDefaults() {
        // ซิงก์ state เริ่มต้นของ modal ให้ตรงกับค่าที่ render มาจาก server
        updateFormFields();
        updateSubjectRequirementState();
        updateSubjectTriggerText();
        filterSubjectOptions();
        updateWorkloadSubmitState();
    }
