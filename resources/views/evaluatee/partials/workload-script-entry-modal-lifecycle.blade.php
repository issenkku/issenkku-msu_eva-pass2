    function initWorkloadEntryModalLifecycle() {
        if (!workloadModalEl) {
            return;
        }

        workloadModalEl.addEventListener('show.bs.modal', function (event) {
            const trigger = event.relatedTarget;
            const isEdit = trigger && trigger.classList && trigger.classList.contains('workload-edit-btn');

            clearSubjectSelection();

            if (isEdit) {
                const entryId = trigger.dataset.entryId;
                const formId = trigger.dataset.formId || '';
                const itemId = trigger.dataset.itemId || '';
                const subjectId = trigger.dataset.subjectId || '';
                const groupId = trigger.dataset.groupId || '';
                const groupName = trigger.dataset.groupName || '';
                const fieldValues = parseDatasetJson(trigger.dataset.fieldValues) || {};
                const evidenceLinks = parseDatasetJson(trigger.dataset.evidenceLinks) || [];

                setFormMode('edit', entryId);
                setActiveGroup(groupId, groupName);
                if (subjectIdField) {
                    subjectIdField.value = subjectId || '';
                }
                updateSubjectTriggerText();
                updateSubjectRequirementState();
                setActiveFormId(formId);
                updateFormFields();

                if (itemSelect && itemId) {
                    itemSelect.value = itemId;
                }

                updateSelectedItemField();
                fillFields(fieldValues, formId, true);
                setEvidenceLinks(evidenceLinks);
                pendingEditPayload = {
                    fieldValues: fieldValues,
                    evidenceLinks: evidenceLinks,
                    formId: formId,
                };
                updateWorkloadSubmitState();
                return;
            }

            setFormMode('create');
            if (workloadForm) {
                workloadForm.reset();
            }
            clearSubjectSelection();
            updateSubjectRequirementState();
            fillFields({}, '', true);
            setEvidenceLinks([]);
            pendingEditPayload = null;

            const defaultFormId = trigger && trigger.dataset ? trigger.dataset.defaultFormId : (lastDefaultFormId || '');
            const defaultGroupId = trigger && trigger.dataset ? (trigger.dataset.groupId || '') : (lastDefaultGroupId || '');
            const defaultGroupName = trigger && trigger.dataset ? (trigger.dataset.groupName || '') : (lastDefaultGroupName || '');
            setActiveGroup(defaultGroupId, defaultGroupName);
            selectDefaultForForm(defaultFormId || '');

            const presetItemId = trigger && trigger.dataset ? (trigger.dataset.itemId || '') : '';
            if (itemSelect && presetItemId) {
                itemSelect.value = presetItemId;
                const selected = itemSelect.selectedOptions[0];
                if (selected && selected.dataset.formId) {
                    setActiveFormId(selected.dataset.formId);
                }
                updateFormFields();
                itemSelect.value = presetItemId;
                updateSelectedItemField();
            }

            updateWorkloadSubmitState();
        });

        workloadModalEl.addEventListener('shown.bs.modal', function () {
            if (!pendingEditPayload) {
                return;
            }

            fillFields(pendingEditPayload.fieldValues || {}, pendingEditPayload.formId, true);
            setEvidenceLinks(pendingEditPayload.evidenceLinks || []);
            updateWorkloadSubmitState();
        });
    }
