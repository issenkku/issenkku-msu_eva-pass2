import assert from 'node:assert/strict';
import { readFileSync } from 'node:fs';
import test from 'node:test';
import vm from 'node:vm';

const scriptSource = readFileSync(
    new URL('../../resources/views/components/unified-evaluation-script.blade.php', import.meta.url),
    'utf8',
)
    .replace(/^\s*\{\{--[\s\S]*?--\}\}\s*/, '')
    .replace(/^\s*<script>\s*/, '')
    .replace(/\s*<\/script>\s*$/, '');

test('evaluatee live summary caps quantity at its evaluation list maximum', () => {
    const listSummary = {
        dataset: {
            listId: '268',
            listMax: '40',
        },
    };
    const rows = {
        1: {
            closest: () => listSummary,
            dataset: { scoreA: '1', scoreB: '1' },
        },
        2: {
            closest: () => listSummary,
            dataset: { scoreA: '1', scoreB: '1' },
        },
    };
    const quantityInputs = [
        { name: 'quantity_list[1][score_C]', value: '150' },
        { name: 'quantity_list[2][score_C]', value: '63.49' },
    ];
    const qualityInputs = [
        {
            dataset: { evaluationListId: '268', listMax: '40' },
            value: '29.80',
        },
    ];
    const elements = {
        'quantity-summary': { textContent: '40.00' },
        'quality-summary': { textContent: '29.80' },
        'support-summary': { textContent: '0.00' },
        'total-summary': { textContent: '69.80' },
    };
    const document = {
        addEventListener() {},
        getElementById(id) {
            return elements[id] ?? null;
        },
        querySelector(selector) {
            const match = selector.match(/data-sub-id="(\d+)"/);
            return match ? rows[match[1]] ?? null : null;
        },
        querySelectorAll(selector) {
            if (selector === 'input[name^="quantity_list"][name$="[score_C]"]') {
                return quantityInputs;
            }
            if (selector === 'input[name^="quality_list"][name$="[score]"]') {
                return qualityInputs;
            }

            return [];
        },
    };
    const sandbox = {
        URL,
        document,
        setTimeout() {},
        window: {},
    };

    vm.runInNewContext(scriptSource, sandbox);
    sandbox.recalculateSummaryScores();

    assert.equal(elements['quantity-summary'].textContent, '40.00');
    assert.equal(elements['total-summary'].textContent, '69.80');
});
