import assert from 'node:assert/strict';
import test from 'node:test';

import {
    calculateEntryWeightedScore,
    calculateSupportAchievement,
    getSupportScoreValidationState,
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

test('keeps an empty criterion score valid but requires an activity score', () => {
    assert.deepEqual(getSupportScoreValidationState('', 5), {
        valid: true,
        message: '',
        helpVisible: true,
        errorVisible: false,
    });
    assert.deepEqual(getSupportScoreValidationState('', 5, { required: true }), {
        valid: false,
        message: 'กรอกเฉพาะจำนวนเต็มตั้งแต่ 1–5 และต้องไม่เกินระดับค่าเป้าหมาย 5',
        helpVisible: false,
        errorVisible: true,
    });
});

test('returns inline feedback for an invalid score without changing its value', () => {
    assert.deepEqual(getSupportScoreValidationState('7', '5.00', { required: true }), {
        valid: false,
        message: 'กรอกเฉพาะจำนวนเต็มตั้งแต่ 1–5 และต้องไม่เกินระดับค่าเป้าหมาย 5.00',
        helpVisible: false,
        errorVisible: true,
    });
    assert.deepEqual(getSupportScoreValidationState('4', '3.50', { required: true }), {
        valid: false,
        message: 'กรอกเฉพาะจำนวนเต็มตั้งแต่ 1–5 และต้องไม่เกินระดับค่าเป้าหมาย 3.50',
        helpVisible: false,
        errorVisible: true,
    });
    assert.deepEqual(getSupportScoreValidationState('5', '5.00', { required: true }), {
        valid: true,
        message: '',
        helpVisible: true,
        errorVisible: false,
    });
});
