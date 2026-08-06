import assert from 'node:assert/strict';
import { readFileSync } from 'node:fs';
import test from 'node:test';
import vm from 'node:vm';

test('removing the last workload evidence link clears its value', () => {
    const input = { value: 'https://drive.google.com/example' };
    const row = {
        removed: false,
        querySelector(selector) {
            if (selector === '.workload-evidence-remove-btn') return removeButton;
            if (selector === 'input[name="evidence_links[]"]') return input;
            return null;
        },
        remove() {
            this.removed = true;
        },
    };
    const removeButton = {
        disabled: false,
        closest(selector) {
            if (selector === '.workload-evidence-remove-btn') return this;
            if (selector === '.workload-evidence-row') return row;
            return null;
        },
    };
    let clickHandler;
    const container = {
        querySelectorAll() {
            return [row];
        },
        addEventListener(type, handler) {
            if (type === 'click') clickHandler = handler;
        },
        appendChild() {},
    };
    const addButton = { addEventListener() {} };
    const document = {
        addEventListener(type, handler) {
            if (type === 'DOMContentLoaded') handler();
        },
        getElementById(id) {
            if (id === 'workload-evidence-links') return container;
            if (id === 'workload-add-evidence-link') return addButton;
            return null;
        },
        createElement() {
            return {};
        },
    };

    const blade = readFileSync(new URL('../../resources/views/evaluatee/partials/workload-script-evidence-links.blade.php', import.meta.url), 'utf8');
    const script = blade.match(/<script>([\s\S]*)<\/script>/)?.[1];
    assert.ok(script, 'expected the workload evidence script to be present');
    vm.runInNewContext(script, { document });

    clickHandler({ target: removeButton });

    assert.equal(input.value, '');
    assert.equal(row.removed, false);
    assert.equal(removeButton.disabled, false);
});
