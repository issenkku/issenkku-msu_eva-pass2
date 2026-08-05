import assert from 'node:assert/strict';
import test from 'node:test';

const {
    AsyncMutationError,
    createAsyncFormCoordinator,
    requestFormMutation,
} = await import('../../resources/js/async-form.js');

class TestFormData {
    constructor(form) {
        this.form = form;
    }
}

function withFormData(body) {
    const original = globalThis.FormData;
    globalThis.FormData = TestFormData;

    return Promise.resolve(body()).finally(() => {
        globalThis.FormData = original;
    });
}

function response(status, payload, overrides = {}) {
    return {
        headers: { get: () => 'application/json' },
        json: async () => payload,
        ok: status >= 200 && status < 300,
        redirected: false,
        status,
        ...overrides,
    };
}

function deferred() {
    let resolve;
    let reject;
    const promise = new Promise((resolvePromise, rejectPromise) => {
        resolve = resolvePromise;
        reject = rejectPromise;
    });

    return { promise, reject, resolve };
}

function submitEvent() {
    return {
        prevented: 0,
        preventDefault() {
            this.prevented += 1;
        },
    };
}

test('requestFormMutation sends complete FormData and requests JSON', async () => {
    await withFormData(async () => {
        const form = { action: '/departments/store', method: 'post' };
        let request;

        const payload = await requestFormMutation(form, async (url, options) => {
            request = { options, url };
            return response(201, { success: true, state: { id: 7 } });
        });

        assert.equal(request.url, '/departments/store');
        assert.equal(request.options.method, 'POST');
        assert.ok(request.options.body instanceof TestFormData);
        assert.equal(request.options.body.form, form);
        assert.deepEqual(request.options.headers, {
            Accept: 'application/json',
            'X-Requested-With': 'XMLHttpRequest',
        });
        assert.equal(payload.state.id, 7);
    });
});

test('requestFormMutation exposes Laravel validation errors', async () => {
    await withFormData(async () => {
        await assert.rejects(
            requestFormMutation({ action: '/subjects', method: 'post' }, async () => response(422, {
                errors: { code: ['รหัสรายวิชาซ้ำ'] },
                message: 'The given data was invalid.',
            })),
            (error) => {
                assert.ok(error instanceof AsyncMutationError);
                assert.equal(error.status, 422);
                assert.deepEqual(error.errors, { code: ['รหัสรายวิชาซ้ำ'] });
                return true;
            },
        );
    });
});

test('requestFormMutation rejects redirected or malformed successful responses', async (t) => {
    await t.test('redirected', async () => {
        await withFormData(async () => {
            await assert.rejects(
                requestFormMutation({ action: '/roles', method: 'post' }, async () => response(200, {}, { redirected: true })),
                (error) => error instanceof AsyncMutationError && error.status === 200,
            );
        });
    });

    await t.test('missing success contract', async () => {
        await withFormData(async () => {
            await assert.rejects(
                requestFormMutation({ action: '/roles', method: 'post' }, async () => response(200, { message: 'HTML fallback' })),
                (error) => error instanceof AsyncMutationError && error.status === 200,
            );
        });
    });
});

test('coordinator blocks duplicate submits and preserves values after validation failure', async () => {
    const pending = deferred();
    const calls = { success: 0, validation: [], messages: [], requests: 0 };
    const form = { value: 'unchanged' };
    const button = { disabled: false, textContent: 'บันทึก' };
    const coordinator = createAsyncFormCoordinator({
        applySuccess() {
            calls.success += 1;
        },
        applyValidationErrors(errors) {
            calls.validation.push(errors);
        },
        form,
        getSubmitButton: () => button,
        request: async () => {
            calls.requests += 1;
            return pending.promise;
        },
        savingLabel: 'กำลังบันทึก...',
        showMessage(message, isError) {
            calls.messages.push({ isError, message });
        },
    });

    const firstEvent = submitEvent();
    const duplicateEvent = submitEvent();
    const first = coordinator(firstEvent);
    const duplicate = coordinator(duplicateEvent);

    assert.equal(calls.requests, 1);
    assert.equal(button.disabled, true);
    pending.reject(Object.assign(new Error('Invalid'), {
        errors: { name: ['Required'] },
        status: 422,
    }));
    await Promise.all([first, duplicate]);

    assert.equal(firstEvent.prevented, 1);
    assert.equal(duplicateEvent.prevented, 1);
    assert.equal(form.value, 'unchanged');
    assert.equal(button.disabled, false);
    assert.equal(button.textContent, 'บันทึก');
    assert.equal(calls.success, 0);
    assert.deepEqual(calls.validation, [{ name: ['Required'] }]);
});

test('coordinator applies success once and restores the active control', async () => {
    const calls = [];
    const form = {};
    const button = { disabled: false, textContent: 'ลบ' };
    const payload = { message: 'ลบเรียบร้อยแล้ว', success: true, state: { deleted_ids: [3] } };
    const coordinator = createAsyncFormCoordinator({
        applySuccess(result) {
            calls.push(result);
        },
        form,
        getSubmitButton: () => button,
        request: async () => payload,
        savingLabel: 'กำลังลบ...',
        showMessage() {},
    });

    await coordinator(submitEvent());

    assert.deepEqual(calls, [payload]);
    assert.equal(button.disabled, false);
    assert.equal(button.textContent, 'ลบ');
});
