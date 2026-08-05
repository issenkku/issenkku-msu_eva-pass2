import assert from 'node:assert/strict';
import { readFileSync } from 'node:fs';
import test from 'node:test';

const { applyWorkloadEntrySaveResponse, requestWorkloadEntrySave } = await import('../../resources/js/workload-entry-submit.js');

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
                json: async () => ({ message: 'Saved', total_score: 9 }),
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
        total_score: '8.5',
    });

    assert.equal(panels.innerHTML, '<section>New entry</section>');
    assert.equal(summary.innerHTML, '<strong>8.5</strong>');
    assert.equal(saveState.dataset.currentTotal, '8.5');
    assert.equal(events.length, 1);
    assert.equal(events[0].type, 'workload:total-updated');
    assert.deepEqual(events[0].detail, { total: 8.5 });
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
    assert.match(entrySubmit, /isSubmitting/);
    assert.match(entrySubmit, /requestWorkloadEntrySave/);
    assert.match(entrySubmit, /applyWorkloadEntrySaveResponse/);
    assert.match(entrySubmit, /bootstrap\.Modal\.getOrCreateInstance\(workloadModalEl\)\.hide\(\)/);
    assert.match(entrySubmit, /classList\.add\('is-invalid'\)/);
    assert.match(saveReminder, /let currentTotal/);
    assert.match(saveReminder, /workload:total-updated/);
    assert.match(saveReminder, /event\.target\.id === 'workloadEntryForm'/);
});
