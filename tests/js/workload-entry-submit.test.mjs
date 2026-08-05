import assert from 'node:assert/strict';
import { readFileSync } from 'node:fs';
import test from 'node:test';

const {
    applyWorkloadEntrySaveResponse,
    createWorkloadEntrySubmitCoordinator,
    hideWorkloadEntryModalIfCurrent,
    markWorkloadValidationErrors,
    requestWorkloadEntrySave,
} = await import('../../resources/js/workload-entry-submit.js');

class TestFormData {
    constructor(form) {
        this.form = form;
        this.values = new Map(form.fields);
    }

    get(name) {
        return this.values.get(name);
    }
}

function withFormData(testBody) {
    const originalFormData = globalThis.FormData;
    globalThis.FormData = TestFormData;

    return Promise.resolve(testBody()).finally(() => {
        globalThis.FormData = originalFormData;
    });
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

function fakeClassList() {
    const values = new Set();
    return {
        add(value) {
            values.add(value);
        },
        contains(value) {
            return values.has(value);
        },
        remove(value) {
            values.delete(value);
        },
    };
}

function submitCoordinatorHarness(requestSave) {
    let modalSession = 1;
    let dropdownItemId = '';
    const calls = { apply: [], applyContext: [], hide: 0, mark: [], messages: [], requests: 0, reset: 0 };
    const form = {
        value: 'visible form value',
        reset() {
            calls.reset += 1;
            this.value = '';
        },
    };
    const submitButton = { disabled: false, textContent: 'Save' };
    const coordinator = createWorkloadEntrySubmitCoordinator({
        applyResponse(payload, context) {
            calls.apply.push(payload);
            calls.applyContext.push(context);
        },
        clearInvalid() {},
        form,
        getMissingFields: () => [],
        getDropdownItemId: () => dropdownItemId,
        getModalSession: () => modalSession,
        hideModal() {
            calls.hide += 1;
        },
        markValidationErrors(errors) {
            calls.mark.push(errors);
            return Object.values(errors).flat();
        },
        onMissingFields() {},
        requestSave: async (submittedForm) => {
            calls.requests += 1;
            assert.equal(submittedForm, form);
            return requestSave();
        },
        resetForm() {
            form.reset();
        },
        savingLabel: 'Saving...',
        showMessage(message, isError) {
            calls.messages.push({ isError, message });
        },
        submitButton,
    });

    return {
        calls,
        coordinator,
        form,
        reopenModal() {
            modalSession += 1;
        },
        setDropdownItemId(value) {
            dropdownItemId = value;
        },
        submitButton,
    };
}

function submitEvent() {
    return {
        prevented: 0,
        preventDefault() {
            this.prevented += 1;
        },
    };
}

test('requestWorkloadEntrySave posts the form action with its complete FormData payload', async () => {
    await withFormData(async () => {
        const form = {
            action: 'https://example.test/workload-entries/12',
            fields: [
                ['_token', 'csrf-token'],
                ['_method', 'PUT'],
                ['field_values[hours]', '4'],
            ],
        };
        let request;

        const payload = await requestWorkloadEntrySave(form, async (url, options) => {
            request = { url, options };

            return {
                ok: true,
                status: 200,
                json: async () => ({
                    message: 'Saved',
                    panels_html: '<section>Saved entry</section>',
                    summary_html: '<strong>9</strong>',
                    total_score: 9,
                }),
            };
        });

        assert.equal(request.url, form.action);
        assert.equal(request.options.method, 'POST');
        assert.ok(request.options.body instanceof TestFormData);
        assert.equal(request.options.body.form, form);
        assert.equal(request.options.body.get('_method'), 'PUT');
        assert.deepEqual(request.options.headers, {
            Accept: 'application/json',
            'X-Requested-With': 'XMLHttpRequest',
        });
        assert.equal(payload.total_score, 9);
    });
});

test('requestWorkloadEntrySave exposes Laravel validation errors for HTTP 422', async () => {
    await withFormData(async () => {
        await assert.rejects(
            requestWorkloadEntrySave({ action: 'https://example.test/workload-entries' }, async () => ({
                ok: false,
                status: 422,
                json: async () => ({
                    message: 'The given data was invalid.',
                    errors: { 'field_values.hours': ['Hours is required.'] },
                }),
            })),
            (error) => {
                assert.equal(error.status, 422);
                assert.deepEqual(error.errors, {
                    'field_values.hours': ['Hours is required.'],
                });
                return true;
            },
        );
    });
});

test('requestWorkloadEntrySave propagates network failures', async () => {
    await withFormData(async () => {
        const networkError = new Error('Network unavailable');

        await assert.rejects(
            requestWorkloadEntrySave({ action: 'https://example.test/workload-entries' }, async () => Promise.reject(networkError)),
            networkError,
        );
    });
});

test('requestWorkloadEntrySave rejects a followed HTML response before any live update', async () => {
    await withFormData(async () => {
        const panels = { innerHTML: 'Existing panels' };
        const documentRef = {
            getElementById(id) {
                return id === 'workloadPanelsLiveRegion' ? panels : null;
            },
            dispatchEvent() {},
        };

        await assert.rejects(
            async () => {
                const payload = await requestWorkloadEntrySave({ action: 'https://example.test/workload-entries/999', fields: [] }, async () => ({
                    ok: true,
                    status: 200,
                    redirected: true,
                    json: async () => {
                        throw new SyntaxError('Unexpected token < in JSON');
                    },
                }));
                applyWorkloadEntrySaveResponse(documentRef, payload);
            },
            (error) => {
                assert.equal(error.status, 200);
                assert.equal(error.message, 'Unable to save workload entry');
                return true;
            },
        );

        assert.equal(panels.innerHTML, 'Existing panels');
    });
});

test('requestWorkloadEntrySave rejects malformed total_score values', async (t) => {
    const invalidTotals = [
        ['null', null, true],
        ['empty string', '', true],
        ['whitespace string', '   ', true],
        ['true', true, true],
        ['false', false, true],
        ['omitted', undefined, false],
        ['NaN', Number.NaN, true],
        ['object', {}, true],
    ];

    for (const [label, total, includeTotal] of invalidTotals) {
        await t.test(label, async () => {
            await withFormData(async () => {
                const payload = {
                    message: 'Saved',
                    panels_html: '<section>Saved</section>',
                    summary_html: '<strong>Saved</strong>',
                };
                if (includeTotal) {
                    payload.total_score = total;
                }

                await assert.rejects(
                    requestWorkloadEntrySave({ action: 'https://example.test/workload-entries', fields: [] }, async () => ({
                        ok: true,
                        status: 200,
                        json: async () => payload,
                    })),
                    /Unable to save workload entry/,
                );
            });
        });
    }
});

test('a save completion does not hide a modal session reopened after submission', () => {
    let hideCount = 0;

    const didHide = hideWorkloadEntryModalIfCurrent(1, 2, () => {
        hideCount += 1;
    });

    assert.equal(didHide, false);
    assert.equal(hideCount, 0);
});

test('submit coordinator prevents navigation and blocks duplicate requests', async () => {
    const pending = deferred();
    const harness = submitCoordinatorHarness(() => pending.promise);
    const firstEvent = submitEvent();
    const duplicateEvent = submitEvent();

    const firstSubmit = harness.coordinator(firstEvent);
    const duplicateSubmit = harness.coordinator(duplicateEvent);

    assert.equal(firstEvent.prevented, 1);
    assert.equal(duplicateEvent.prevented, 1);
    assert.equal(harness.calls.requests, 1);
    pending.resolve({ message: 'Saved', panels_html: 'panels', summary_html: 'summary', total_score: 4 });
    await Promise.all([firstSubmit, duplicateSubmit]);
});

test('submit coordinator applies success, resets, restores, and closes the initiating session', async () => {
    const payload = { message: 'Saved', panels_html: 'panels', summary_html: 'summary', total_score: 4 };
    const harness = submitCoordinatorHarness(async () => payload);
    const event = submitEvent();

    await harness.coordinator(event);

    assert.equal(event.prevented, 1);
    assert.deepEqual(harness.calls.apply, [payload]);
    assert.equal(harness.calls.reset, 1);
    assert.equal(harness.calls.hide, 1);
    assert.equal(harness.submitButton.disabled, false);
    assert.equal(harness.submitButton.textContent, 'Save');
    assert.deepEqual(harness.calls.messages, [{ message: 'Saved', isError: false }]);
});

test('submit coordinator applies an earlier success without resetting or closing a reopened session', async () => {
    const pending = deferred();
    const payload = { message: 'Saved', panels_html: 'panels', summary_html: 'summary', total_score: 4 };
    const harness = submitCoordinatorHarness(() => pending.promise);
    const submission = harness.coordinator(submitEvent());

    harness.reopenModal();
    harness.form.value = 'newly reopened value';
    pending.resolve(payload);
    await submission;

    assert.deepEqual(harness.calls.apply, [payload]);
    assert.equal(harness.calls.reset, 0);
    assert.equal(harness.calls.hide, 0);
    assert.equal(harness.form.value, 'newly reopened value');
    assert.equal(harness.submitButton.disabled, false);
    assert.equal(harness.submitButton.textContent, 'Save');
});

test('submit coordinator snapshots the originating dropdown when submission starts', async () => {
    const pending = deferred();
    const payload = { message: 'Saved', panels_html: 'panels', summary_html: 'summary', total_score: 4 };
    const harness = submitCoordinatorHarness(() => pending.promise);
    harness.setDropdownItemId('12');

    const submission = harness.coordinator(submitEvent());
    harness.setDropdownItemId('13');
    pending.resolve(payload);
    await submission;

    assert.deepEqual(harness.calls.applyContext, [{ dropdownItemId: '12' }]);
});

test('submit coordinator keeps values and restores the button for 422, network, and malformed failures', async (t) => {
    const cases = [
        {
            label: '422',
            error: Object.assign(new Error('Invalid'), {
                errors: { 'field_values.hours': ['Hours is required'] },
                status: 422,
            }),
            expectedMessage: 'Hours is required',
            marksValidation: true,
        },
        {
            label: 'network',
            error: new TypeError('Failed to fetch'),
            expectedMessage: 'ไม่สามารถบันทึกข้อมูลได้ กรุณาตรวจสอบการเชื่อมต่ออินเทอร์เน็ตแล้วลองใหม่อีกครั้ง',
            marksValidation: false,
        },
        {
            label: 'malformed 2xx',
            error: Object.assign(new Error('Unable to save workload entry'), { errors: {}, status: 200 }),
            expectedMessage: 'ไม่สามารถบันทึกข้อมูลได้ กรุณาลองใหม่อีกครั้ง',
            marksValidation: false,
        },
    ];

    for (const testCase of cases) {
        await t.test(testCase.label, async () => {
            const harness = submitCoordinatorHarness(async () => {
                throw testCase.error;
            });

            await harness.coordinator(submitEvent());

            assert.equal(harness.form.value, 'visible form value');
            assert.equal(harness.calls.reset, 0);
            assert.equal(harness.calls.hide, 0);
            assert.equal(harness.submitButton.disabled, false);
            assert.equal(harness.submitButton.textContent, 'Save');
            assert.deepEqual(harness.calls.messages.at(-1), {
                message: testCase.expectedMessage,
                isError: true,
            });
            assert.equal(harness.calls.mark.length, testCase.marksValidation ? 1 : 0);
        });
    }
});

test('markWorkloadValidationErrors marks broad field, evidence, and visible subject controls', () => {
    const fieldContainer = { classList: fakeClassList() };
    const activeField = {
        classList: fakeClassList(),
        closest: () => fieldContainer,
        disabled: false,
        name: 'field_values[hours]',
        type: 'number',
    };
    const disabledField = { classList: fakeClassList(), disabled: true, name: 'field_values[rate]', type: 'number' };
    const evidence = { classList: fakeClassList(), disabled: false, name: 'evidence_links[]', type: 'text' };
    const hiddenSubject = { classList: fakeClassList(), disabled: false, name: 'subject_id', type: 'hidden' };
    const evidenceContainer = { classList: fakeClassList() };
    const subjectSection = { classList: fakeClassList() };
    const subjectTrigger = { classList: fakeClassList() };
    const controls = [activeField, disabledField, evidence, hiddenSubject];
    const form = {
        querySelectorAll(selector) {
            if (selector === '[name^="field_values["]') return [activeField, disabledField];
            if (selector === '[name="evidence_links[]"]') return [evidence];
            if (selector === '[name]') return controls;
            return [];
        },
    };

    const messages = markWorkloadValidationErrors(
        form,
        {
            field_values: ['Check workload fields'],
            evidence_links: ['Evidence required'],
            subject_id: ['Subject required'],
        },
        { evidenceContainer, subjectSection, subjectTrigger },
    );

    assert.equal(activeField.classList.contains('is-invalid'), true);
    assert.equal(fieldContainer.classList.contains('is-invalid'), true);
    assert.equal(disabledField.classList.contains('is-invalid'), false);
    assert.equal(evidence.classList.contains('is-invalid'), true);
    assert.equal(evidenceContainer.classList.contains('is-invalid'), true);
    assert.equal(subjectTrigger.classList.contains('is-invalid'), true);
    assert.equal(subjectSection.classList.contains('is-invalid'), true);
    assert.equal(hiddenSubject.classList.contains('is-invalid'), false);
    assert.deepEqual(messages, ['Check workload fields', 'Evidence required', 'Subject required']);
});

test('applyWorkloadEntrySaveResponse refreshes workload HTML and broadcasts the numeric total', () => {
    const panels = { innerHTML: '' };
    const summary = { innerHTML: '' };
    const saveState = { dataset: { currentTotal: '0' } };
    const events = [];
    const documentRef = {
        defaultView: {
            CustomEvent: class {
                constructor(type, init) {
                    this.type = type;
                    this.detail = init.detail;
                }
            },
        },
        getElementById(id) {
            return (
                {
                    workloadPanelsLiveRegion: panels,
                    workloadSummaryLiveRegion: summary,
                    workloadSaveState: saveState,
                }[id] ?? null
            );
        },
        dispatchEvent(event) {
            events.push(event);
        },
    };

    applyWorkloadEntrySaveResponse(documentRef, {
        panels_html: '<section>New entry</section>',
        summary_html: '<strong>8.5</strong>',
        total_score: 8.5,
    });

    assert.equal(panels.innerHTML, '<section>New entry</section>');
    assert.equal(summary.innerHTML, '<strong>8.5</strong>');
    assert.equal(saveState.dataset.currentTotal, '8.5');
    assert.equal(events.length, 1);
    assert.equal(events[0].type, 'workload:total-updated');
    assert.deepEqual(events[0].detail, { total: 8.5 });
});

test('applyWorkloadEntrySaveResponse opens only the originating workload dropdown', () => {
    const dropdowns = ['11', '12', '13'].map((id) => ({
        dataset: { workloadItemId: id },
        open: true,
    }));
    const panels = {
        innerHTML: '',
        querySelectorAll(selector) {
            assert.equal(selector, '[data-workload-item-id]');
            return dropdowns;
        },
    };
    const documentRef = {
        getElementById(id) {
            return id === 'workloadPanelsLiveRegion' ? panels : null;
        },
        dispatchEvent() {},
    };
    const payload = {
        message: 'Saved',
        panels_html: '<details></details>',
        summary_html: '',
        total_score: 8.5,
    };

    applyWorkloadEntrySaveResponse(documentRef, payload, '12');

    assert.deepEqual(
        dropdowns.map((dropdown) => dropdown.open),
        [false, true, false],
    );
});

test('applyWorkloadEntrySaveResponse uses the server item id when client origin state is missing', () => {
    const dropdowns = ['11', '12', '13'].map((id) => ({
        dataset: { workloadItemId: id },
        open: false,
    }));
    const panels = {
        innerHTML: '',
        querySelectorAll() {
            return dropdowns;
        },
    };
    const documentRef = {
        getElementById(id) {
            return id === 'workloadPanelsLiveRegion' ? panels : null;
        },
        dispatchEvent() {},
    };

    applyWorkloadEntrySaveResponse(documentRef, {
        message: 'Saved',
        panels_html: '<details></details>',
        summary_html: '',
        total_score: 8.5,
        active_item_id: 12,
    });

    assert.deepEqual(
        dropdowns.map((dropdown) => dropdown.open),
        [false, true, false],
    );
});

test('applyWorkloadEntrySaveResponse opens no dropdown for an absent or unknown origin', () => {
    for (const itemId of ['', '999']) {
        const dropdowns = ['11', '12'].map((id) => ({
            dataset: { workloadItemId: id },
            open: true,
        }));
        const panels = {
            innerHTML: '',
            querySelectorAll() {
                return dropdowns;
            },
        };
        const documentRef = {
            getElementById(id) {
                return id === 'workloadPanelsLiveRegion' ? panels : null;
            },
            dispatchEvent() {},
        };

        applyWorkloadEntrySaveResponse(documentRef, { message: 'Saved', panels_html: '', summary_html: '', total_score: 8.5 }, itemId);

        assert.deepEqual(
            dropdowns.map((dropdown) => dropdown.open),
            [false, false],
        );
    }
});

test('workload entry partials retain the async submission contract', () => {
    const entrySubmit = readFileSync(
        new URL('../../resources/views/evaluatee/partials/workload-script-entry-submit.blade.php', import.meta.url),
        'utf8',
    );
    const saveReminder = readFileSync(
        new URL('../../resources/views/evaluatee/partials/workload-script-save-reminder.blade.php', import.meta.url),
        'utf8',
    );

    assert.match(entrySubmit, /event\.preventDefault\(\)/);
    assert.match(entrySubmit, /createWorkloadEntrySubmitCoordinator/);
    assert.match(entrySubmit, /requestWorkloadEntrySave/);
    assert.match(entrySubmit, /applyWorkloadEntrySaveResponse/);
    assert.match(entrySubmit, /markWorkloadValidationErrors/);
    assert.match(entrySubmit, /workloadForm\.reset\(\)/);
    assert.match(entrySubmit, /bootstrap\.Modal\.getOrCreateInstance\(workloadModalEl\)\.hide\(\)/);
    assert.match(saveReminder, /let currentTotal/);
    assert.match(saveReminder, /workload:total-updated/);
    assert.match(saveReminder, /event\.target\.id === 'workloadEntryForm'/);
});
