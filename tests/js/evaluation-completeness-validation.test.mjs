import assert from 'node:assert/strict';
import { readFileSync } from 'node:fs';
import test from 'node:test';
import vm from 'node:vm';

const extractScript = (relativePath) => readFileSync(
    new URL(relativePath, import.meta.url),
    'utf8',
).match(/<script>\s*([\s\S]*?)<\/script>/)[1];

const sharedFormScript = extractScript(
    '../../resources/views/partials/evaluation-form-script.blade.php',
).replace('@json($confirmStatus)', JSON.stringify('Pending'));

const evaluateeFormScript = extractScript(
    '../../resources/views/partials/evaluatee-evaluation-script.blade.php',
);

class FakeClassList {
    constructor(...classes) {
        this.classes = new Set(classes);
    }

    add(...classes) {
        classes.forEach((name) => this.classes.add(name));
    }

    remove(...classes) {
        classes.forEach((name) => this.classes.delete(name));
    }

    contains(name) {
        return this.classes.has(name);
    }
}

class FakeElement {
    constructor({ value = '', classes = [] } = {}) {
        this.value = value;
        this.textContent = '';
        this.className = '';
        this.classList = new FakeClassList(...classes);
        this.dataset = {};
        this.listeners = new Map();
        this.children = [];
        this.firstChild = null;
        this.submitted = false;
    }

    addEventListener(type, listener) {
        this.listeners.set(type, listener);
    }

    appendChild(child) {
        this.children.push(child);
        return child;
    }

    insertBefore(child) {
        this.children.unshift(child);
        return child;
    }

    prepend(child) {
        this.children.unshift(child);
    }

    querySelectorAll() {
        return [];
    }

    remove() {}

    scrollIntoView() {}

    setAttribute() {}

    submit() {
        this.submitted = true;
    }
}

const createEvent = (target = null) => ({
    target,
    defaultPrevented: false,
    preventDefault() {
        this.defaultPrevented = true;
    },
});

function createHarness({ evaluatee = false } = {}) {
    const domListeners = new Map();
    const elements = new Map();
    const evaluationForm = new FakeElement();
    const openModalBtn = new FakeElement();
    const confirmationModal = new FakeElement({ classes: ['hidden', 'opacity-0'] });
    const modalContent = new FakeElement({ classes: ['scale-95', 'opacity-0'] });
    const cancelModalBtn = new FakeElement();
    const confirmSubmitBtn = new FakeElement();
    const loadingOverlay = new FakeElement({ classes: ['hidden'] });
    const formStatus = new FakeElement({ value: 'Draft' });

    elements.set('evaluationForm', evaluationForm);
    elements.set('openModalBtn', openModalBtn);
    elements.set('confirmationModal', confirmationModal);
    elements.set('modal-content', modalContent);
    elements.set('cancelModalBtn', cancelModalBtn);
    elements.set('confirmSubmitBtn', confirmSubmitBtn);
    elements.set('loading_overlay', loadingOverlay);
    elements.set('formStatus', formStatus);

    const requiredEvidenceContainer = new FakeElement();
    requiredEvidenceContainer.dataset.mainCriteriaName = 'เกณฑ์ที่ต้องมีหลักฐาน';
    requiredEvidenceContainer.closest = () => ({
        querySelectorAll: () => [{ value: '5' }],
    });

    const document = {
        addEventListener(type, listener) {
            domListeners.set(type, listener);
        },
        createElement() {
            return new FakeElement();
        },
        getElementById(id) {
            if (!elements.has(id)) elements.set(id, new FakeElement());
            return elements.get(id);
        },
        querySelector() {
            return null;
        },
        querySelectorAll(selector) {
            if (evaluatee && selector === '[id^="evidence-links-quality-"][data-require-evidence="1"]') {
                return [requiredEvidenceContainer];
            }
            return [];
        },
    };

    const window = {
        validateScoreChangeReasons: () => [],
        validateSupportCriteria: () => ['ข้อมูลสายสนับสนุนยังไม่ครบ'],
    };

    const sandbox = {
        Array,
        Number,
        Set,
        URL,
        console,
        document,
        isNaN,
        parseFloat,
        recalculateSummaryScores() {},
        setTimeout(callback) {
            callback();
        },
        window,
    };
    window.window = window;
    window.document = document;

    return {
        domListeners,
        elements: {
            confirmationModal,
            evaluationForm,
            formStatus,
            openModalBtn,
        },
        sandbox,
    };
}

test('shared evaluation form submits draft despite incomplete support criteria', () => {
    const harness = createHarness();
    vm.runInNewContext(sharedFormScript, harness.sandbox);
    harness.domListeners.get('DOMContentLoaded')();

    const event = createEvent(harness.elements.evaluationForm);
    harness.elements.evaluationForm.listeners.get('submit')(event);

    assert.equal(event.defaultPrevented, false);
});

test('shared evaluation form opens final confirmation despite incomplete support criteria', () => {
    const harness = createHarness();
    vm.runInNewContext(sharedFormScript, harness.sandbox);
    harness.domListeners.get('DOMContentLoaded')();

    const event = createEvent(harness.elements.openModalBtn);
    harness.elements.openModalBtn.listeners.get('click')(event);

    assert.equal(harness.elements.confirmationModal.classList.contains('hidden'), false);
});

test('evaluatee can save draft despite incomplete support criteria', () => {
    const harness = createHarness({ evaluatee: true });
    vm.runInNewContext(evaluateeFormScript, harness.sandbox);
    harness.domListeners.get('DOMContentLoaded')();

    const event = createEvent(harness.elements.evaluationForm);
    harness.elements.evaluationForm.listeners.get('submit')(event);

    assert.equal(event.defaultPrevented, false);
});

test('evaluatee can open final confirmation without configured evidence', () => {
    const harness = createHarness({ evaluatee: true });
    harness.sandbox.window.validateSupportCriteria = () => [];
    vm.runInNewContext(evaluateeFormScript, harness.sandbox);
    harness.domListeners.get('DOMContentLoaded')();

    const event = createEvent(harness.elements.openModalBtn);
    harness.elements.openModalBtn.listeners.get('click')(event);

    assert.equal(harness.elements.confirmationModal.classList.contains('hidden'), false);
});
