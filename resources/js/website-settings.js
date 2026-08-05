import { createAsyncFormCoordinator, requestFormMutation } from './async-form.js';
import { applyMasterDataValidationErrors, showMasterDataMessage } from './master-data-page.js';

function revokePreviewObjectUrl(preview, revokeObjectURL) {
    const source = preview?.src || '';
    if (source.startsWith('blob:')) revokeObjectURL?.(source);
}

function setBackgroundImage(documentRef, imageUrl) {
    if (!imageUrl) return;

    const formContainer = documentRef.querySelector?.('.form-container');
    const appBackgroundShell = documentRef.querySelector?.('.app-background-shell');
    formContainer?.style?.setProperty('--settings-background-image', `url('${imageUrl}')`);
    appBackgroundShell?.style?.setProperty('--app-background-image', `url('${imageUrl}')`);
    formContainer?.classList?.remove('is-white-background');
    appBackgroundShell?.classList?.remove('is-white-background');
}

export function applyWebsiteSettingsResponse(documentRef, payload, {
    revokeObjectURL = globalThis.URL?.revokeObjectURL?.bind(globalThis.URL),
} = {}) {
    const settings = payload?.data?.settings || {};
    const logoPreview = documentRef.querySelector?.('#logoPreview');
    const backgroundPreview = documentRef.querySelector?.('#backgroundPreview');

    if (logoPreview && payload?.data?.logo_url) {
        revokePreviewObjectUrl(logoPreview, revokeObjectURL);
        logoPreview.src = payload.data.logo_url;
    }
    if (backgroundPreview && payload?.data?.background_url) {
        revokePreviewObjectUrl(backgroundPreview, revokeObjectURL);
        backgroundPreview.src = payload.data.background_url;
        setBackgroundImage(documentRef, payload.data.background_url);
    }

    const libraryRegion = documentRef.querySelector?.('[data-background-library-region]');
    if (libraryRegion && payload?.html?.background_library) {
        libraryRegion.outerHTML = payload.html.background_library;
    }
    const infoRegion = documentRef.querySelector?.('[data-settings-info-region]');
    if (infoRegion && payload?.html?.info) infoRegion.outerHTML = payload.html.info;

    const deletedInputs = documentRef.querySelector?.('#deletedBackgroundInputs');
    if (deletedInputs) deletedInputs.innerHTML = '';

    ['#logo', '#background'].forEach((selector) => {
        const input = documentRef.querySelector?.(selector);
        if (input) input.value = '';
    });

    const selectedPath = documentRef.querySelector?.('#selectedBackgroundPath');
    if (selectedPath) selectedPath.value = settings.background_path || '';

    let idInput = documentRef.querySelector?.('input[name="id"]');
    if (!idInput && settings.id) {
        const form = documentRef.querySelector?.('[data-async-settings-form]');
        if (form && documentRef.createElement) {
            idInput = documentRef.createElement('input');
            idInput.type = 'hidden';
            idInput.name = 'id';
            form.appendChild(idInput);
        }
    }
    if (idInput && settings.id != null) idInput.value = String(settings.id);

    const whiteBackground = Boolean(settings.use_white_background);
    const whiteInput = documentRef.querySelector?.('#useWhiteBackground');
    if (whiteInput) whiteInput.checked = whiteBackground;
    documentRef.querySelector?.('.form-container')?.classList?.toggle('is-white-background', whiteBackground);
    documentRef.querySelector?.('.app-background-shell')?.classList?.toggle('is-white-background', whiteBackground);
}

export function createWebsiteSettingsSubmitCoordinator({
    applySuccess,
    button,
    form,
    request = requestFormMutation,
    showMessage = showMasterDataMessage,
    applyValidationErrors = () => {},
}) {
    return createAsyncFormCoordinator({
        applySuccess,
        applyValidationErrors,
        form,
        getSubmitButton: () => button,
        request,
        savingLabel: 'กำลังบันทึก...',
        showMessage,
    });
}

function bindPreview(input, preview, removeInput, defaultKey, onChange) {
    if (!input || !preview || input.dataset.settingsPreviewBound === '1') return;
    input.dataset.settingsPreviewBound = '1';
    input.addEventListener('change', () => {
        const file = input.files?.[0];
        if (!file) return;
        if (removeInput) removeInput.checked = false;
        revokePreviewObjectUrl(preview, globalThis.URL?.revokeObjectURL?.bind(globalThis.URL));
        const imageUrl = globalThis.URL?.createObjectURL?.(file);
        if (!imageUrl) return;
        preview.src = imageUrl;
        onChange?.(imageUrl);
    });

    removeInput?.addEventListener('change', () => {
        if (!removeInput.checked) return;
        input.value = '';
        const defaultUrl = preview.dataset?.[defaultKey];
        if (defaultUrl) {
            preview.src = defaultUrl;
            onChange?.(defaultUrl);
        }
    });
}

function initializeSettingsInteractions(form, documentRef) {
    const clearSelectedBackground = () => {
        const selectedPath = documentRef.querySelector('#selectedBackgroundPath');
        if (selectedPath) selectedPath.value = '';
        documentRef.querySelectorAll('.background-library-item').forEach((item) => item.classList.remove('is-active'));
    };

    bindPreview(
        documentRef.querySelector('#logo'),
        documentRef.querySelector('#logoPreview'),
        documentRef.querySelector('input[name="remove_logo"]'),
        'defaultLogo'
    );
    bindPreview(
        documentRef.querySelector('#background'),
        documentRef.querySelector('#backgroundPreview'),
        documentRef.querySelector('input[name="remove_background"]'),
        'defaultBackground',
        (imageUrl) => {
            setBackgroundImage(documentRef, imageUrl);
            const whiteInput = documentRef.querySelector('#useWhiteBackground');
            if (whiteInput) whiteInput.checked = false;
            clearSelectedBackground();
        }
    );

    if (form.dataset.settingsLibraryBound !== '1') {
        form.dataset.settingsLibraryBound = '1';
        form.addEventListener('click', (event) => {
            const deleteButton = event.target?.closest?.('.background-library-delete');
            const item = event.target?.closest?.('.background-library-item');
            if (!item) return;

            if (deleteButton) {
                event.preventDefault();
                event.stopPropagation();
                const path = item.dataset.backgroundPath;
                if (!path || item.classList.contains('is-pending-delete')) return;
                const wasSelected = documentRef.querySelector('#selectedBackgroundPath')?.value === path
                    || item.classList.contains('is-active');
                item.classList.add('is-pending-delete');
                item.style.display = 'none';
                const holder = documentRef.querySelector('#deletedBackgroundInputs');
                if (holder) {
                    const input = documentRef.createElement('input');
                    input.type = 'hidden';
                    input.name = 'delete_background_paths[]';
                    input.value = path;
                    holder.appendChild(input);
                }
                if (wasSelected) {
                    clearSelectedBackground();
                    const preview = documentRef.querySelector('#backgroundPreview');
                    if (preview?.dataset?.defaultBackground) {
                        preview.src = preview.dataset.defaultBackground;
                        setBackgroundImage(documentRef, preview.dataset.defaultBackground);
                    }
                }
                return;
            }

            if (item.classList.contains('is-pending-delete') || !item.dataset.backgroundUrl) return;
            const selectedPath = documentRef.querySelector('#selectedBackgroundPath');
            if (selectedPath) selectedPath.value = item.dataset.backgroundPath || '';
            const backgroundInput = documentRef.querySelector('#background');
            if (backgroundInput) backgroundInput.value = '';
            const removeInput = documentRef.querySelector('input[name="remove_background"]');
            if (removeInput) removeInput.checked = false;
            const whiteInput = documentRef.querySelector('#useWhiteBackground');
            if (whiteInput) whiteInput.checked = false;
            const preview = documentRef.querySelector('#backgroundPreview');
            if (preview) preview.src = item.dataset.backgroundUrl;
            setBackgroundImage(documentRef, item.dataset.backgroundUrl);
            documentRef.querySelectorAll('.background-library-item').forEach((candidate) => {
                candidate.classList.toggle('is-active', candidate === item);
            });
        });

        form.addEventListener('keydown', (event) => {
            if (event.key !== 'Enter' && event.key !== ' ') return;
            const item = event.target?.closest?.('.background-library-item');
            if (!item) return;
            event.preventDefault();
            item.click();
        });
    }

    const whiteInput = documentRef.querySelector('#useWhiteBackground');
    if (whiteInput && whiteInput.dataset.settingsWhiteBound !== '1') {
        whiteInput.dataset.settingsWhiteBound = '1';
        whiteInput.addEventListener('change', () => {
            documentRef.querySelector('.form-container')?.classList.toggle('is-white-background', whiteInput.checked);
            documentRef.querySelector('.app-background-shell')?.classList.toggle('is-white-background', whiteInput.checked);
        });
    }
}

export function initializeWebsiteSettings(documentRef = globalThis.document, fetchImpl = globalThis.fetch) {
    if (!documentRef?.querySelectorAll) return;
    documentRef.querySelectorAll('[data-async-settings-form]').forEach((form) => {
        if (form.dataset.asyncSettingsBound === '1') return;
        form.dataset.asyncSettingsBound = '1';
        initializeSettingsInteractions(form, documentRef);

        const coordinator = createWebsiteSettingsSubmitCoordinator({
            applySuccess: (payload) => {
                applyWebsiteSettingsResponse(documentRef, payload);
                initializeSettingsInteractions(form, documentRef);
            },
            applyValidationErrors: (errors) => applyMasterDataValidationErrors(form, errors, {}, documentRef),
            button: form.querySelector('[type="submit"]'),
            form,
            request: (targetForm) => requestFormMutation(targetForm, fetchImpl),
            showMessage: (message, isError) => showMasterDataMessage(message, isError, documentRef),
        });
        form.addEventListener('submit', coordinator);
    });
}

if (typeof window !== 'undefined') {
    const initialize = () => initializeWebsiteSettings(window.document, window.fetch.bind(window));
    if (window.document.readyState === 'loading') {
        window.document.addEventListener('DOMContentLoaded', initialize, { once: true });
    } else {
        initialize();
    }
}

