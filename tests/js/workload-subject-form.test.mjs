import assert from 'node:assert/strict';
import { readFileSync } from 'node:fs';
import test from 'node:test';
import vm from 'node:vm';

const subjectFormScript = readFileSync(
    new URL(
        '../../resources/views/evaluatee/partials/workload-script-subject-form.blade.php',
        import.meta.url,
    ),
    'utf8',
).match(/<script>\s*([\s\S]*?)<\/script>/)[1];

class FakeElement {
    constructor(value = '') {
        this.value = value;
        this.listeners = new Map();
        this.classList = { toggle() {} };
        this.style = {};
        this.submitted = false;
        this.action = '';
    }

    addEventListener(type, listener) {
        this.listeners.set(type, listener);
    }

    submit() {
        this.submitted = true;
    }

    querySelector() {
        return null;
    }

    reset() {
        this.wasReset = true;
    }
}

test('evaluatee subject save defaults empty credits to zero without native form submission', async () => {
    const domListeners = new Map();
    const dispatchedEvents = [];
    const form = new FakeElement();
    const submitButton = new FakeElement();
    const elements = new Map([
        ['subjectForm', form],
        ['subjectSubmitBtn', submitButton],
        ['subjectRedirectTo', new FakeElement()],
        ['code', new FakeElement('122222')],
        ['name_th', new FakeElement('ผู้ดูแลระบบ')],
        ['lecture_credits', new FakeElement('')],
        ['lab_credits', new FakeElement('')],
        ['self_study_credits', new FakeElement('')],
        ['credits', new FakeElement('')],
    ]);

    const document = {
        addEventListener(type, listener) {
            domListeners.set(type, listener);
        },
        getElementById(id) {
            return elements.get(id) ?? null;
        },
        querySelector() {
            return null;
        },
        dispatchEvent(event) {
            dispatchedEvents.push(event);
        },
    };
    const window = {
        location: { href: 'https://example.test/evaluation-workload' },
        AsyncForm: {
            requestFormMutation() {},
            createAsyncFormCoordinator(options) {
                return async function () {
                    await options.applySuccess({
                        success: true,
                        message: 'saved',
                        data: {
                            subject: {
                                id: 42,
                                code: '122222',
                                name_th: 'ระบบ',
                                credits: 2,
                            },
                        },
                    });
                };
            },
        },
        MasterDataPage: { showMasterDataMessage() {} },
    };

    class CustomEvent {
        constructor(type, options) {
            this.type = type;
            this.detail = options.detail;
        }
    }

    vm.runInNewContext(subjectFormScript, {
        CustomEvent,
        Number,
        document,
        window,
    });
    domListeners.get('DOMContentLoaded')();

    assert.equal(typeof submitButton.listeners.get('click'), 'function');
    await submitButton.listeners.get('click')();
    assert.equal(elements.get('credits').value, '0');
    assert.equal(elements.get('lecture_credits').value, '0');
    assert.equal(elements.get('lab_credits').value, '0');
    assert.equal(elements.get('self_study_credits').value, '0');
    assert.equal(form.submitted, false);
    assert.equal(form.wasReset, true);
    assert.equal(dispatchedEvents[0].type, 'workload:subject-created');
    assert.equal(dispatchedEvents[0].detail.subject.id, 42);
});
