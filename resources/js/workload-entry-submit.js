export async function requestWorkloadEntrySave(form, fetchImpl = window.fetch.bind(window)) {
    const response = await fetchImpl(form.action, {
        method: 'POST',
        body: new FormData(form),
        headers: {
            Accept: 'application/json',
            'X-Requested-With': 'XMLHttpRequest',
        },
    });
    let payload;
    try {
        payload = await response.json();
    } catch {
        payload = {};
    }
    if (!response.ok) {
        const error = new Error(payload.message || 'Unable to save workload entry');
        error.status = response.status;
        error.errors = payload.errors || {};
        throw error;
    }
    if (!isWorkloadEntrySavePayload(payload)) {
        const error = new Error('Unable to save workload entry');
        error.status = response.status;
        error.errors = {};
        throw error;
    }
    return payload;
}

function isWorkloadEntrySavePayload(payload) {
    return (
        payload !== null &&
        typeof payload === 'object' &&
        typeof payload.message === 'string' &&
        typeof payload.panels_html === 'string' &&
        typeof payload.summary_html === 'string' &&
        typeof payload.total_score === 'number' &&
        Number.isFinite(payload.total_score)
    );
}

function workloadInputNameForError(errorKey) {
    const keyParts = String(errorKey).split('.');
    if (keyParts.length < 2) {
        return keyParts[0];
    }

    return (
        keyParts[0] +
        keyParts
            .slice(1)
            .map((part) => `[${part}]`)
            .join('')
    );
}

function addInvalidClass(element) {
    element?.classList?.add('is-invalid');
}

function markVisibleWorkloadField(field) {
    if (!field || field.disabled || field.type === 'hidden') {
        return;
    }

    addInvalidClass(field);
    addInvalidClass(field.closest?.('.workload-form-fields, .workload-modal-field'));
}

export function markWorkloadValidationErrors(form, errors, visibleElements = {}) {
    const messages = [];
    Object.entries(errors || {}).forEach(([errorKey, rawMessages]) => {
        if (errorKey === 'field_values') {
            form.querySelectorAll('[name^="field_values["]').forEach(markVisibleWorkloadField);
        } else if (errorKey === 'evidence_links' || errorKey.startsWith('evidence_links.')) {
            form.querySelectorAll('[name="evidence_links[]"]').forEach(markVisibleWorkloadField);
            addInvalidClass(visibleElements.evidenceContainer);
        } else if (errorKey === 'subject_id' || errorKey.startsWith('subject_id.')) {
            addInvalidClass(visibleElements.subjectTrigger);
            addInvalidClass(visibleElements.subjectSection);
        } else {
            const inputName = workloadInputNameForError(errorKey);
            const field = Array.from(form.querySelectorAll('[name]')).find((input) => input.name === inputName);
            markVisibleWorkloadField(field);
        }

        const fieldMessages = Array.isArray(rawMessages) ? rawMessages : [rawMessages];
        fieldMessages.forEach((message) => {
            if (message) {
                messages.push(String(message));
            }
        });
    });

    return messages;
}

const networkRetryMessage = 'ไม่สามารถบันทึกข้อมูลได้ กรุณาตรวจสอบการเชื่อมต่ออินเทอร์เน็ตแล้วลองใหม่อีกครั้ง';
const generalRetryMessage = 'ไม่สามารถบันทึกข้อมูลได้ กรุณาลองใหม่อีกครั้ง';

function workloadSaveFailureMessage(error) {
    if (typeof error.status !== 'number') {
        return networkRetryMessage;
    }
    if (!error.message || error.message === 'Unable to save workload entry') {
        return generalRetryMessage;
    }

    return error.message;
}

export function createWorkloadEntrySubmitCoordinator(options) {
    let isSubmitting = false;

    return async function handleWorkloadEntrySubmit(event) {
        const missingFields = options.getMissingFields(true);
        if (missingFields.length > 0) {
            event.preventDefault();
            options.onMissingFields(missingFields);
            return;
        }

        event.preventDefault();
        if (isSubmitting) {
            return;
        }

        isSubmitting = true;
        const submissionModalSession = options.getModalSession();
        const submissionDropdownItemId = options.getDropdownItemId?.() ?? '';
        const originalButtonLabel = options.submitButton?.textContent ?? '';
        if (options.submitButton) {
            options.submitButton.disabled = true;
            options.submitButton.textContent = options.savingLabel;
        }
        options.clearInvalid();

        try {
            const payload = await options.requestSave(options.form);
            options.applyResponse(payload, {
                dropdownItemId: submissionDropdownItemId,
            });
            hideWorkloadEntryModalIfCurrent(submissionModalSession, options.getModalSession(), () => {
                options.resetForm();
                options.hideModal();
            });
            options.showMessage(payload.message, false);
        } catch (error) {
            if (error.status === 422) {
                const validationMessages = options.markValidationErrors(error.errors || {});
                options.showMessage(validationMessages.join(' ') || error.message || generalRetryMessage, true);
            } else {
                options.showMessage(workloadSaveFailureMessage(error), true);
            }
        } finally {
            if (options.submitButton) {
                options.submitButton.disabled = false;
                options.submitButton.textContent = originalButtonLabel;
            }
            isSubmitting = false;
        }
    };
}

export function hideWorkloadEntryModalIfCurrent(submissionSession, currentSession, hideModal) {
    if (submissionSession !== currentSession) {
        return false;
    }

    hideModal();
    return true;
}

function createTotalUpdatedEvent(documentRef, total) {
    const CustomEventConstructor = documentRef.defaultView?.CustomEvent ?? globalThis.CustomEvent;

    if (typeof CustomEventConstructor === 'function') {
        return new CustomEventConstructor('workload:total-updated', {
            detail: { total },
        });
    }

    if (typeof documentRef.createEvent === 'function') {
        const event = documentRef.createEvent('CustomEvent');
        event.initCustomEvent('workload:total-updated', false, false, { total });
        return event;
    }

    return { type: 'workload:total-updated', detail: { total } };
}

export function applyWorkloadEntrySaveResponse(documentRef, payload, dropdownItemId = '') {
    const panelsRegion = documentRef.getElementById('workloadPanelsLiveRegion');
    const summaryRegion = documentRef.getElementById('workloadSummaryLiveRegion');
    const saveState = documentRef.getElementById('workloadSaveState');
    const total = Number(payload.total_score ?? 0);

    if (panelsRegion) {
        panelsRegion.innerHTML = payload.panels_html ?? '';
        const normalizedItemId = String(dropdownItemId || '');
        const dropdowns = panelsRegion.querySelectorAll?.('[data-workload-item-id]') ?? [];
        dropdowns.forEach((dropdown) => {
            dropdown.open = normalizedItemId !== '' && String(dropdown.dataset.workloadItemId || '') === normalizedItemId;
        });
    }
    if (summaryRegion) {
        summaryRegion.innerHTML = payload.summary_html ?? '';
    }
    if (saveState) {
        saveState.dataset.currentTotal = String(payload.total_score ?? 0);
    }

    documentRef.dispatchEvent(createTotalUpdatedEvent(documentRef, total));
}

if (typeof window !== 'undefined') {
    window.WorkloadEntrySubmit = {
        requestWorkloadEntrySave,
        applyWorkloadEntrySaveResponse,
        createWorkloadEntrySubmitCoordinator,
        hideWorkloadEntryModalIfCurrent,
        markWorkloadValidationErrors,
    };
}
