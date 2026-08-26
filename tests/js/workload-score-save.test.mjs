import assert from 'node:assert/strict';
import { readFileSync } from 'node:fs';
import test from 'node:test';
import vm from 'node:vm';

const scriptSource = readFileSync(
    new URL('../../resources/views/evaluatee/partials/workload-script-save-reminder.blade.php', import.meta.url),
    'utf8',
)
    .replace(/^\s*<script>\s*/, '')
    .replace(/\s*<\/script>\s*$/, '');

class FakeElement {
    constructor() {
        this.classList = { add() {}, remove() {} };
        this.dataset = {};
        this.hidden = false;
        this.listeners = new Map();
        this.textContent = '';
    }

    addEventListener(type, listener) {
        this.listeners.set(type, listener);
    }

    querySelector() {
        return null;
    }
}

function createHarness({ waitForFetch } = {}) {
    const documentListeners = new Map();
    const windowListeners = new Map();
    const state = new FakeElement();
    state.dataset.currentTotal = '10';
    state.dataset.savedTotal = '';
    const reminder = new FakeElement();
    const reminderText = new FakeElement();
    const submitButton = new FakeElement();
    submitButton.disabled = false;
    submitButton.textContent = 'บันทึก';
    const form = new FakeElement();
    form.action = '/evaluation-workload/score';
    form.method = 'post';
    form.nativeSubmitCount = 0;
    form.querySelector = () => submitButton;
    form.submit = () => {
        form.nativeSubmitCount += 1;
    };

    const document = {
        body: new FakeElement(),
        addEventListener(type, listener) {
            documentListeners.set(type, listener);
        },
        createElement() {
            return new FakeElement();
        },
        getElementById(id) {
            return {
                workloadSaveState: state,
                workloadSaveReminder: reminder,
                workloadSaveReminderText: reminderText,
                workloadScoreForm: form,
            }[id] ?? null;
        },
        querySelector() {
            return null;
        },
        querySelectorAll() {
            return [];
        },
    };

    let fetchCount = 0;
    const window = {
        document,
        fetch: async (url, options) => {
            fetchCount += 1;
            assert.equal(url, form.action);
            assert.equal(options.method, 'POST');
            assert.equal(options.headers.Accept, 'application/json');
            await waitForFetch;
            return {
                ok: true,
                redirected: false,
                status: 200,
                async json() {
                    return {
                        success: true,
                        message: 'บันทึกแล้ว',
                        saved_total: 10,
                        total_score: 10,
                    };
                },
            };
        },
        addEventListener(type, listener) {
            windowListeners.set(type, listener);
        },
    };
    window.window = window;

    const sandbox = {
        FormData: class FormData {},
        Math,
        Number,
        console,
        document,
        setTimeout(callback) {
            callback();
        },
        window,
    };

    return {
        documentListeners,
        fetchCount: () => fetchCount,
        form,
        sandbox,
        windowListeners,
    };
}

test('workload score save stays on the page when async bundles are unavailable', async () => {
    const harness = createHarness();
    vm.runInNewContext(scriptSource, harness.sandbox);
    harness.documentListeners.get('DOMContentLoaded')();

    await harness.form.listeners.get('submit')({ preventDefault() {} });

    assert.equal(harness.fetchCount(), 1);
    assert.equal(harness.form.nativeSubmitCount, 0);

    const beforeUnloadEvent = {
        defaultPrevented: false,
        preventDefault() {
            this.defaultPrevented = true;
        },
        returnValue: undefined,
    };
    harness.windowListeners.get('beforeunload')(beforeUnloadEvent);
    assert.equal(beforeUnloadEvent.defaultPrevented, false);
});

test('workload score fallback blocks a duplicate submission while the request is pending', async () => {
    let releaseFetch;
    const waitForFetch = new Promise((resolve) => {
        releaseFetch = resolve;
    });
    const harness = createHarness({ waitForFetch });
    vm.runInNewContext(scriptSource, harness.sandbox);
    harness.documentListeners.get('DOMContentLoaded')();

    const submit = harness.form.listeners.get('submit');
    const firstSubmission = submit({ preventDefault() {} });
    const duplicateSubmission = submit({ preventDefault() {} });

    assert.equal(harness.fetchCount(), 1);

    releaseFetch();
    await Promise.all([firstSubmission, duplicateSubmission]);
    assert.equal(harness.fetchCount(), 1);
});
