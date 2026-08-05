import assert from 'node:assert/strict';
import test from 'node:test';

const {
    applyWebsiteSettingsResponse,
    createWebsiteSettingsSubmitCoordinator,
} = await import('../../resources/js/website-settings.js');

test('website settings response replaces persisted previews and server fragments', () => {
    const revoked = [];
    const elements = {
        '#logoPreview': { src: 'blob:logo' },
        '#backgroundPreview': { src: 'blob:background' },
        '[data-background-library-region]': { outerHTML: '' },
        '[data-settings-info-region]': { outerHTML: '' },
        '#deletedBackgroundInputs': { innerHTML: 'pending' },
        '#selectedBackgroundPath': { value: 'old.webp' },
        'input[name="id"]': { value: '' },
        '#logo': { value: 'logo.png' },
        '#background': { value: 'background.jpg' },
    };
    const documentRef = {
        querySelector(selector) {
            return elements[selector] || null;
        },
    };

    applyWebsiteSettingsResponse(documentRef, {
        data: {
            settings: { id: 7, background_path: 'site-backgrounds/saved.webp', use_white_background: false },
            logo_url: '/storage/site-logos/saved.png',
            background_url: '/storage/site-backgrounds/saved.webp',
        },
        html: {
            background_library: '<div data-background-library-region>saved library</div>',
            info: '<div data-settings-info-region>saved info</div>',
        },
    }, {
        revokeObjectURL(url) { revoked.push(url); },
    });

    assert.deepEqual(revoked, ['blob:logo', 'blob:background']);
    assert.equal(elements['#logoPreview'].src, '/storage/site-logos/saved.png');
    assert.equal(elements['#backgroundPreview'].src, '/storage/site-backgrounds/saved.webp');
    assert.equal(elements['[data-background-library-region]'].outerHTML, '<div data-background-library-region>saved library</div>');
    assert.equal(elements['[data-settings-info-region]'].outerHTML, '<div data-settings-info-region>saved info</div>');
    assert.equal(elements['#deletedBackgroundInputs'].innerHTML, '');
    assert.equal(elements['#logo'].value, '');
    assert.equal(elements['#background'].value, '');
    assert.equal(elements['input[name="id"]'].value, '7');
});

test('website settings coordinator keeps the page and restores the button after failure', async () => {
    const button = { disabled: false, textContent: 'Save' };
    const form = { value: 'keep' };
    const messages = [];
    const coordinator = createWebsiteSettingsSubmitCoordinator({
        applySuccess() {
            throw new Error('must not apply');
        },
        button,
        form,
        request: async () => {
            throw new Error('network failed');
        },
        showMessage(message, isError) {
            messages.push([message, isError]);
        },
    });

    await coordinator({ preventDefault() {} });

    assert.equal(form.value, 'keep');
    assert.equal(button.disabled, false);
    assert.equal(button.textContent, 'Save');
    assert.deepEqual(messages, [['network failed', true]]);
});
