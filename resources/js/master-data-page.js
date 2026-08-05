import { createAsyncFormCoordinator, requestFormMutation } from './async-form.js';
import { applyResourceMutation, refreshTableRegion } from './async-resource-table.js';

export function showMasterDataMessage(message, isError = false, documentRef = globalThis.document) {
    if (!documentRef?.body) return;
    let element = documentRef.getElementById?.('asyncMutationMessage');
    if (!element) {
        element = documentRef.createElement('div');
        element.id = 'asyncMutationMessage';
        element.setAttribute('role', 'alert');
        documentRef.body.append(element);
    }
    element.className = isError
        ? 'fixed bottom-4 right-4 z-[10000] rounded-lg border border-red-200 bg-red-50 px-4 py-3 text-red-800 shadow-lg'
        : 'fixed bottom-4 right-4 z-[10000] rounded-lg border border-emerald-200 bg-emerald-50 px-4 py-3 text-emerald-800 shadow-lg';
    element.textContent = message;
    element.hidden = false;
    globalThis.clearTimeout?.(element.hideTimer);
    element.hideTimer = globalThis.setTimeout?.(() => {
        element.hidden = true;
    }, 5000);
}

export function applyMasterDataValidationErrors(form, errors, aliases = {}, documentRef = globalThis.document) {
    let firstInput = null;
    Object.entries(errors || {}).forEach(([field, messages]) => {
        const selectorName = String(field).replace(/["\\]/g, '\\$&');
        const input = form.querySelector?.(`[name="${selectorName}"]`);
        if (!input) return;
        input.classList?.add('is-invalid');
        input.setAttribute?.('aria-invalid', 'true');
        firstInput ||= input;

        const errorId = aliases[field] || `${input.id || field}Error`;
        const errorElement = documentRef?.getElementById?.(errorId);
        if (errorElement) {
            errorElement.textContent = Array.isArray(messages) ? messages[0] : String(messages);
            errorElement.style.display = 'block';
        }
    });
    firstInput?.focus?.();
}

export async function reconcileMasterDataMutation(
    documentRef,
    payload,
    { refresh = refreshTableRegion, url = globalThis.location?.href || '' } = {},
) {
    const hasQuery = new URL(url || '/', globalThis.location?.origin || 'https://example.test').searchParams.size > 0;
    if (payload?.html?.row && (!documentRef.querySelector('[data-resource-rows]') || hasQuery)) {
        await refresh(url, '[data-async-table-region]', globalThis.fetch, documentRef);
        return;
    }
    applyResourceMutation(documentRef, payload);
}

export function createMasterDataSubmitCoordinator({
    applyMutation,
    applyValidationErrors = () => {},
    button,
    form,
    hideModal,
    request = requestFormMutation,
    resetForm,
    showMessage = showMasterDataMessage,
}) {
    return createAsyncFormCoordinator({
        applySuccess: async (payload) => {
            await applyMutation(payload);
            resetForm?.();
            hideModal?.();
        },
        applyValidationErrors,
        form,
        getSubmitButton: () => button,
        request,
        savingLabel: 'กำลังบันทึก...',
        showMessage,
    });
}

export function submitMasterDataForm(options) {
    return createMasterDataSubmitCoordinator(options)({ preventDefault() {} });
}

function renumberRows(documentRef) {
    documentRef.querySelectorAll?.('[data-resource-row] [data-sequence]').forEach((cell, index) => {
        cell.textContent = String(index + 1);
    });
}

export function initializeAsyncDeleteForms(documentRef = globalThis.document) {
    if (!documentRef?.querySelectorAll) return;

    documentRef.querySelectorAll('form[data-async-delete-form], form[data-async-bulk-delete-form]').forEach((form) => {
        if (form.dataset.asyncDeleteBound === '1') return;
        form.dataset.asyncDeleteBound = '1';

        const coordinator = createAsyncFormCoordinator({
            applySuccess: async (payload) => {
                applyResourceMutation(documentRef, payload);
                renumberRows(documentRef);
                const modalElement = form.closest?.('.modal');
                if (modalElement && globalThis.bootstrap?.Modal) {
                    globalThis.bootstrap.Modal.getOrCreateInstance(modalElement).hide();
                }
                if (!documentRef.querySelector('[data-resource-row]')) {
                    await refreshTableRegion(globalThis.location?.href || '', '[data-async-table-region]', globalThis.fetch, documentRef);
                }
            },
            form,
            getSubmitButton: () => form.querySelector('[type="submit"]'),
            request: requestFormMutation,
            savingLabel: 'กำลังลบ...',
            showMessage: (message, isError) => showMasterDataMessage(message, isError, documentRef),
        });
        form.addEventListener('submit', coordinator);
    });
}

const browserApi = {
    applyMasterDataValidationErrors,
    applyResourceMutation,
    createMasterDataSubmitCoordinator,
    initializeAsyncDeleteForms,
    reconcileMasterDataMutation,
    showMasterDataMessage,
    submitMasterDataForm,
};

if (typeof window !== 'undefined') {
    window.MasterDataPage = browserApi;
    const initialize = () => initializeAsyncDeleteForms(window.document);
    if (window.document.readyState === 'loading') {
        window.document.addEventListener('DOMContentLoaded', initialize, { once: true });
    } else {
        initialize();
    }
}

export default browserApi;
