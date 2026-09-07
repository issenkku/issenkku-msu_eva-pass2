import assert from 'node:assert/strict';
import { readFileSync } from 'node:fs';
import test from 'node:test';
import vm from 'node:vm';

import * as supportActivityEntries from '../../resources/js/support-activity-entries.js';

const scriptSource = readFileSync(
    new URL('../../resources/views/components/support-criteria-table-script.blade.php', import.meta.url),
    'utf8',
)
    .replace(/^\s*<script>\s*/, '')
    .replace(/\s*<\/script>\s*$/, '');

class FakeClassList {
    constructor(...classes) {
        this.classes = new Set(classes);
    }

    add(...classes) {
        classes.forEach((className) => this.classes.add(className));
    }

    contains(className) {
        return this.classes.has(className);
    }

    remove(...classes) {
        classes.forEach((className) => this.classes.delete(className));
    }

    toggle(className, force) {
        const shouldAdd = force ?? !this.contains(className);
        if (shouldAdd) this.add(className);
        else this.remove(className);
        return shouldAdd;
    }
}

class FakeElement {
    constructor({ dataset = {}, selectors = [] } = {}) {
        this.attributes = new Map();
        this.classList = new FakeClassList();
        this.dataset = dataset;
        this.isConnected = true;
        this.listeners = new Map();
        this.matchingSelectors = new Set(selectors);
        this.style = {};
        this.textContent = '';
        this.value = '';
    }

    addEventListener(type, listener) {
        this.listeners.set(type, listener);
    }

    appendChild(child) {
        child.parentElement = this;
        return child;
    }

    closest(selector) {
        if (this.matchingSelectors.has(selector)) return this;
        return this.parentElement?.closest(selector) ?? null;
    }

    cloneNode() {
        const clone = new FakeElement();
        clone.ownerDocument = this.ownerDocument;
        return clone;
    }

    focus() {
        this.ownerDocument.activeElement = this;
    }

    getAttribute(name) {
        return this.attributes.get(name) ?? null;
    }

    querySelector() {
        return null;
    }

    querySelectorAll() {
        return [];
    }

    removeAttribute(name) {
        this.attributes.delete(name);
    }

    replaceChildren() {}

    replaceWith() {}

    setAttribute(name, value) {
        this.attributes.set(name, String(value));
    }
}

function createHarness({ includeActivitySection = false, includeActivityTools = true } = {}) {
    const listeners = new Map();
    const modal = new FakeElement();
    const modalBody = new FakeElement();
    const editorStore = new FakeElement();
    const modalErrors = new FakeElement();
    const modalSave = new FakeElement({ selectors: ['[data-support-modal-save]'] });
    const modalCancel = new FakeElement({ selectors: ['[data-support-modal-cancel]'] });
    const manageButton = new FakeElement({
        dataset: { supportManageOpen: '7' },
        selectors: ['[data-support-manage-open]'],
    });
    const textarea = new FakeElement();
    textarea.value = '<p>โครงการทดสอบ</p>';
    const activitySection = new FakeElement();
    const activityEntry = new FakeElement({ selectors: ['[data-support-activity-entry]'] });
    const evidenceInput = new FakeElement();
    evidenceInput.parentElement = activityEntry;
    activityEntry.querySelectorAll = (selector) => selector === '[data-support-evidence-input]'
        ? [evidenceInput]
        : [];

    const item = new FakeElement({
        dataset: {
            supportActivity: 'ภาระงานหลัก-งานรอง',
            supportActivityRole: 'evaluatee',
            supportId: '7',
            supportSequence: '1',
        },
    });
    item.querySelector = (selector) => {
        if (selector === '[data-support-activity-section]') {
            return includeActivitySection ? activitySection : null;
        }
        if (selector.includes('[data-support-activity-content]')) return textarea;
        return null;
    };
    item.querySelectorAll = (selector) => {
        if (selector === '.support-activity-richtext' || selector === '[data-support-activity-content]') {
            return [textarea];
        }
        if (selector === '[data-support-activity-entry]') {
            return includeActivitySection ? [activityEntry] : [];
        }
        if (selector === '[data-support-evidence-input]') {
            return includeActivitySection ? [evidenceInput] : [];
        }
        return [];
    };

    const modalSequence = new FakeElement();
    const modalTitle = new FakeElement();
    modal.querySelector = (selector) => ({
        '[data-support-modal-body]': modalBody,
        '[data-support-modal-errors]': modalErrors,
        '[data-support-modal-save]': modalSave,
        '[data-support-modal-sequence]': modalSequence,
        '[data-support-modal-title]': modalTitle,
    })[selector] ?? null;
    modal.classList.add('hidden');

    const document = {
        activeElement: manageButton,
        body: new FakeElement(),
        readyState: 'complete',
        addEventListener(type, listener) {
            listeners.set(type, listener);
        },
        createElement() {
            const element = new FakeElement();
            element.ownerDocument = document;
            return element;
        },
        getElementById() {
            return null;
        },
        querySelector(selector) {
            if (selector === '[data-support-modal]') return modal;
            if (selector === '[data-support-editor-store]') return editorStore;
            if (selector === '[data-support-item][data-support-id="7"]') return item;
            return null;
        },
        querySelectorAll() {
            return [];
        },
    };

    [
        modal,
        modalBody,
        editorStore,
        modalErrors,
        modalSave,
        modalCancel,
        manageButton,
        textarea,
        item,
        activitySection,
        activityEntry,
        evidenceInput,
    ]
        .forEach((element) => {
            element.ownerDocument = document;
        });

    let editorInitialized = false;
    const editorWrapper = {
        data(name, value) {
            if (value === undefined) return name === 'summernoteInitialized' && editorInitialized;
            if (name === 'summernoteInitialized') editorInitialized = value;
            return this;
        },
        next() {
            return {
                length: editorInitialized ? 1 : 0,
                remove() {
                    editorInitialized = false;
                },
            };
        },
        removeData() {
            editorInitialized = false;
            return this;
        },
        summernote(command) {
            if (typeof command === 'object') {
                editorInitialized = true;
                return this;
            }
            if (command === 'code') throw new Error('broken Summernote instance');
            if (command === 'destroy') {
                editorInitialized = false;
                return this;
            }
            return this;
        },
    };
    const jQuery = () => editorWrapper;
    jQuery.fn = { summernote() {} };

    const window = {
        document,
        jQuery,
        requestAnimationFrame(callback) {
            callback();
        },
    };
    if (includeActivityTools) window.SupportActivityEntries = supportActivityEntries;
    window.window = window;

    const sandbox = {
        Array,
        Math,
        Number,
        Set,
        String,
        URL,
        console,
        document,
        window,
    };

    return { listeners, manageButton, modal, modalCancel, modalSave, sandbox, item };
}

test('submitted support criteria preserve saved weighted scores when entry inputs are read only', () => {
    const { sandbox, item } = createHarness({ includeActivitySection: true });
    Object.assign(item.dataset, {
        supportActivityRole: 'readonly',
        supportAllowEntryWeight: '1',
        supportExistingWeighted: '4.60',
    });
    const display = new FakeElement();
    display.textContent = '4.60';
    const summary = new FakeElement();
    sandbox.document.querySelectorAll = (selector) => {
        if (selector === '[data-support-item]') return [item];
        if (selector === '[data-support-weighted-display="7"]') return [display];
        return [];
    };
    sandbox.document.getElementById = (id) => id === 'support-summary' ? summary : null;

    vm.runInNewContext(scriptSource, sandbox);

    assert.equal(display.textContent, '4.60');
    assert.equal(summary.textContent, '4.60');
});

for (const role of ['evaluatee', 'reviewer']) {
    test(`${role} support entries still recalculate edits and clear removed entries`, () => {
        const { sandbox, item } = createHarness();
        Object.assign(item.dataset, {
            supportActivityRole: role,
            supportAllowEntryWeight: '1',
            supportExistingWeighted: '4.60',
        });
        const weight = { value: '20' };
        const score = { value: '4' };
        const entry = new FakeElement();
        entry.querySelector = (selector) => ({
            '[data-support-entry-weight]': weight,
            '[data-support-entry-score]': score,
        })[selector] ?? null;
        let entries = [entry];
        item.querySelectorAll = (selector) => selector === '[data-support-activity-entry]' ? entries : [];
        const display = new FakeElement();
        sandbox.document.querySelectorAll = (selector) => {
            if (selector === '[data-support-item]') return [item];
            if (selector === '[data-support-weighted-display="7"]') return [display];
            return [];
        };

        vm.runInNewContext(scriptSource, sandbox);
        assert.equal(display.textContent, '0.80');
        score.value = '5';
        sandbox.window.recalculateSupportScores();
        assert.equal(display.textContent, '1.00');
        entries = [];
        sandbox.window.recalculateSupportScores();
        assert.equal(display.textContent, '0.00');
    });
}

test('cancel closes the support modal when the rich text editor teardown fails', () => {
    const harness = createHarness();
    vm.runInNewContext(scriptSource, harness.sandbox);

    harness.listeners.get('click')({ target: harness.manageButton });
    assert.equal(harness.modal.classList.contains('flex'), true);

    assert.doesNotThrow(() => {
        harness.listeners.get('click')({ target: harness.modalCancel });
    });
    assert.equal(harness.modal.classList.contains('hidden'), true);
});

test('save closes the support modal when the rich text editor teardown fails', () => {
    const harness = createHarness();
    vm.runInNewContext(scriptSource, harness.sandbox);

    harness.listeners.get('click')({ target: harness.manageButton });

    assert.doesNotThrow(() => {
        harness.modalSave.listeners.get('click')();
    });
    assert.equal(harness.modal.classList.contains('hidden'), true);
});

test('escape closes the support modal when the rich text editor teardown fails', () => {
    const harness = createHarness();
    vm.runInNewContext(scriptSource, harness.sandbox);

    harness.listeners.get('click')({ target: harness.manageButton });

    assert.doesNotThrow(() => {
        harness.listeners.get('keydown')({ key: 'Escape' });
    });
    assert.equal(harness.modal.classList.contains('hidden'), true);
});

test('cancel closes the support modal without the compiled activity helper bundle', () => {
    const harness = createHarness({ includeActivitySection: true, includeActivityTools: false });
    vm.runInNewContext(scriptSource, harness.sandbox);

    harness.listeners.get('click')({ target: harness.manageButton });

    assert.doesNotThrow(() => {
        harness.listeners.get('click')({ target: harness.modalCancel });
    });
    assert.equal(harness.modal.classList.contains('hidden'), true);
});

test('save closes the support modal without the compiled activity helper bundle', () => {
    const harness = createHarness({ includeActivityTools: false });
    vm.runInNewContext(scriptSource, harness.sandbox);

    harness.listeners.get('click')({ target: harness.manageButton });

    assert.doesNotThrow(() => {
        harness.modalSave.listeners.get('click')();
    });
    assert.equal(harness.modal.classList.contains('hidden'), true);
});
