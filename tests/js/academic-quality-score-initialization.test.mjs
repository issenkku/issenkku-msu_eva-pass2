import assert from 'node:assert/strict';
import { readFileSync } from 'node:fs';
import test from 'node:test';
import vm from 'node:vm';

const extractScript = (relativePath) => readFileSync(
    new URL(relativePath, import.meta.url),
    'utf8',
).match(/<script>\s*([\s\S]*?)<\/script>/)[1];

const scripts = [
    '../../resources/views/components/unified-evaluator-script.blade.php',
    '../../resources/views/components/unified-director-script.blade.php',
];

function createHarness() {
    const domContentLoadedListeners = [];
    const documentListeners = new Map();
    const scoreInput = {
        value: '2.14',
        dataset: { evaluationListId: '10', listMax: '5' },
        addEventListener() {},
    };
    const checkbox = {
        checked: true,
        dataset: {
            score: '5',
            subCriteriaId: '17',
            mainCriteriaId: '11',
            allowMultiple: '0',
        },
    };
    const elements = new Map([
        ['quality-readonly', { value: '0' }],
        ['quality-max-score', { value: '5' }],
        ['quality-score-17', scoreInput],
        ['quantity-summary', { textContent: '40.00' }],
        ['quality-summary', { textContent: '2.14' }],
        ['support-summary', { textContent: '0.00' }],
        ['total-summary', { textContent: '42.14' }],
    ]);

    const document = {
        addEventListener(type, listener) {
            if (type === 'DOMContentLoaded') {
                domContentLoadedListeners.push(listener);
                return;
            }

            documentListeners.set(type, listener);
        },
        getElementById(id) {
            return elements.get(id) ?? null;
        },
        querySelectorAll(selector) {
            if (selector === '[data-quality-checkbox]') return [checkbox];
            if (selector.startsWith('input[name*="quality_criteria"')) return [checkbox];
            if (selector === 'input[name^="quality_list"][name$="[score]"]') return [scoreInput];
            return [];
        },
    };
    const window = {};

    return {
        domContentLoadedListeners,
        documentListeners,
        checkbox,
        elements,
        sandbox: {
            document,
            isNaN,
            parseFloat,
            window,
        },
        scoreInput,
    };
}

for (const scriptPath of scripts) {
    test(`${scriptPath} updates quantity scores and caps the live total after editing`, () => {
        const harness = createHarness();
        const scoreD = { value: '', dataset: { evaluationListId: '20', listMax: '40' } };
        const scoreC = {
            value: '2',
            dataset: { subCriteriaId: '21' },
            closest() {
                return {
                    querySelector(selector) {
                        return { value: selector.includes('[score_A]') ? '30' : '1' };
                    },
                };
            },
        };
        harness.elements.set('score-D-21', scoreD);
        harness.elements.set('quantity-progress', { textContent: '' });
        const originalQuery = harness.sandbox.document.querySelectorAll;
        harness.sandbox.document.querySelectorAll = (selector) => {
            if (selector === '[data-quantity-score-input]') return [scoreC];
            if (selector === 'input[name^="quantity_list"][name$="[score_D]"]') return [scoreD];
            return originalQuery(selector);
        };

        vm.runInNewContext(extractScript(scriptPath), harness.sandbox);
        harness.domContentLoadedListeners.forEach((listener) => listener());
        assert.equal(scoreD.value, '60.00');
        assert.equal(harness.elements.get('quantity-summary').textContent, '40.00');
        assert.equal(harness.elements.get('total-summary').textContent, '42.14');
        assert.equal(harness.elements.get('quantity-progress').textContent, 'กรอกแล้ว 1/1 ข้อ');

        scoreC.value = '1';
        harness.documentListeners.get('input')({ target: { closest: () => scoreC } });
        assert.equal(scoreD.value, '30.00');
        assert.equal(harness.elements.get('total-summary').textContent, '32.14');
    });

    test(`${scriptPath} preserves a persisted quality score when the page loads`, () => {
        const harness = createHarness();

        vm.runInNewContext(extractScript(scriptPath), harness.sandbox);
        harness.domContentLoadedListeners.forEach((listener) => listener());

        assert.equal(harness.scoreInput.value, '2.14');

        harness.documentListeners.get('change')({
            target: {
                closest(selector) {
                    return selector === '[data-quality-checkbox]' ? harness.checkbox : null;
                },
            },
        });

        assert.equal(harness.scoreInput.value, 5);

        harness.checkbox.checked = false;
        harness.documentListeners.get('change')({
            target: { closest: () => harness.checkbox },
        });
        assert.equal(harness.scoreInput.value, '');
    });
}
