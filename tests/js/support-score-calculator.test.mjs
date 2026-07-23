import test from 'node:test';
import assert from 'node:assert/strict';

import { calculateSupportAchievement } from '../../resources/js/support-score-calculator.js';

test('calculates support achievement from the weighted total and fixed level count', () => {
    assert.equal(calculateSupportAchievement(4.3, 5), 0.86);
    assert.equal(calculateSupportAchievement(0, 5), 0);
});
