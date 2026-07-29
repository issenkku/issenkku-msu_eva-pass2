import assert from 'node:assert/strict';
import test from 'node:test';

import {
    calculateEntryWeightedScore,
    calculateSupportAchievement,
    isCriterionScoreValid,
} from '../../resources/js/support-score-calculator.js';

test('calculates support achievement from the weighted total and fixed level count', () => {
    assert.equal(calculateSupportAchievement(4.3, 5), 0.86);
    assert.equal(calculateSupportAchievement(0, 5), 0);
});

test('calculates and rounds weighted scores for one support project', () => {
    assert.equal(calculateEntryWeightedScore(40, 80), 32);
    assert.equal(calculateEntryWeightedScore(33.33, 66.67), 22.22);
    assert.equal(calculateEntryWeightedScore('', 80), null);
    assert.equal(calculateEntryWeightedScore(40, ''), null);
});

test('accepts only nullable whole criterion scores from one through five within target', () => {
    assert.equal(isCriterionScoreValid('', 5), true);
    assert.equal(isCriterionScoreValid('1', 5), true);
    assert.equal(isCriterionScoreValid('5', 5), true);
    assert.equal(isCriterionScoreValid('3', 3.5), true);
});

test('rejects criterion scores outside the integer range or above target', () => {
    assert.equal(isCriterionScoreValid('0', 5), false);
    assert.equal(isCriterionScoreValid('6', 6), false);
    assert.equal(isCriterionScoreValid('3.5', 5), false);
    assert.equal(isCriterionScoreValid('4', 3.5), false);
    assert.equal(isCriterionScoreValid('1', 0.5), false);
});
