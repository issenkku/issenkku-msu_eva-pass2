import assert from 'node:assert/strict';
import test from 'node:test';

const {
    applyMasterDataValidationErrors,
    createMasterDataSubmitCoordinator,
    reconcileMasterDataMutation,
} = await import('../../resources/js/master-data-page.js');

function event() {
    return { preventDefault() {} };
}

test('master data coordinator applies the server row and closes only after success', async () => {
    const calls = { apply: [], hide: 0, reset: 0 };
    const payload = { html: { row: '<tr>saved</tr>' }, message: 'Saved', state: { id: 8 }, success: true };
    const coordinator = createMasterDataSubmitCoordinator({
        applyMutation(result) {
            calls.apply.push(result);
        },
        button: { disabled: false, textContent: 'Save' },
        form: {},
        hideModal() {
            calls.hide += 1;
        },
        request: async () => payload,
        resetForm() {
            calls.reset += 1;
        },
        showMessage() {},
    });

    await coordinator(event());

    assert.deepEqual(calls.apply, [payload]);
    assert.equal(calls.hide, 1);
    assert.equal(calls.reset, 1);
});

test('master data coordinator keeps the modal and values after validation failure', async () => {
    const calls = { apply: 0, errors: [], hide: 0, reset: 0 };
    const form = { value: 'keep this' };
    const coordinator = createMasterDataSubmitCoordinator({
        applyMutation() {
            calls.apply += 1;
        },
        applyValidationErrors(errors) {
            calls.errors.push(errors);
        },
        button: { disabled: false, textContent: 'Save' },
        form,
        hideModal() {
            calls.hide += 1;
        },
        request: async () => {
            throw Object.assign(new Error('Invalid'), { errors: { name: ['Required'] }, status: 422 });
        },
        resetForm() {
            calls.reset += 1;
        },
        showMessage() {},
    });

    await coordinator(event());

    assert.equal(form.value, 'keep this');
    assert.equal(calls.apply, 0);
    assert.equal(calls.hide, 0);
    assert.equal(calls.reset, 0);
    assert.deepEqual(calls.errors, [{ name: ['Required'] }]);
});

test('master data validation marks the field and writes the first server message', () => {
    const input = { classList: { add(value) { this.value = value; } }, focusCalled: 0, focus() { this.focusCalled += 1; } };
    const error = { style: {}, textContent: '' };
    const form = {
        querySelector(selector) {
            return selector === '[name="department_name"]' ? input : null;
        },
    };
    const documentRef = { getElementById: (id) => id === 'department_nameError' ? error : null };

    applyMasterDataValidationErrors(form, { department_name: ['ชื่อซ้ำ'] }, {}, documentRef);

    assert.equal(input.classList.value, 'is-invalid');
    assert.equal(input.focusCalled, 1);
    assert.equal(error.textContent, 'ชื่อซ้ำ');
    assert.equal(error.style.display, 'block');
});

test('master data create refreshes only the table region when the page was empty', async () => {
    const calls = [];
    const documentRef = { querySelector: () => null };
    const payload = { html: { row: '<tr>first row</tr>' }, state: { id: 1 }, success: true };

    await reconcileMasterDataMutation(documentRef, payload, {
        refresh: async (...args) => calls.push(args),
        url: '/departments?sort=manual',
    });

    assert.equal(calls.length, 1);
    assert.equal(calls[0][0], '/departments?sort=manual');
    assert.equal(calls[0][1], '[data-async-table-region]');
});

test('master data mutation refreshes only the table region when filters are active', async () => {
    const calls = [];
    const documentRef = { querySelector: (selector) => selector === '[data-resource-rows]' ? {} : null };

    await reconcileMasterDataMutation(documentRef, { html: { row: '<tr>filtered</tr>' }, state: { id: 2 }, success: true }, {
        refresh: async (...args) => calls.push(args),
        url: '/subjects?search=math&status=active',
    });

    assert.equal(calls.length, 1);
    assert.equal(calls[0][0], '/subjects?search=math&status=active');
});
