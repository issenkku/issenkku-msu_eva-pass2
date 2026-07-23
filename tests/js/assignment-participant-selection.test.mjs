import assert from 'node:assert/strict';
import { readFileSync } from 'node:fs';
import test from 'node:test';

import { mergeVisibleSelections } from '../../resources/js/assignment-participant-selection.js';

test('keeps selected evaluatees hidden by the current search', () => {
    const result = mergeVisibleSelections(['1', '2'], ['2', '3'], ['2', '3']);

    assert.deepEqual(result, ['1', '2', '3']);
});

test('removes only a visible evaluatee when its checkbox is cleared', () => {
    const result = mergeVisibleSelections(['1', '2', '3'], ['2', '3'], ['3']);

    assert.deepEqual(result, ['1', '3']);
});

test('evaluatee checkbox handler merges the visible selection into the full selection', () => {
    const source = readFileSync(
        new URL('../../resources/views/assignment-data/partials/form-script-interactions.blade.php', import.meta.url),
        'utf8',
    );

    assert.match(source, /AssignmentParticipantSelection\.mergeVisibleSelections/);
});
