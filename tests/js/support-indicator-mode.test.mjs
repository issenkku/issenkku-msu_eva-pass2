import test from 'node:test';
import assert from 'node:assert/strict';

import { resolveSupportIndicatorMode } from '../../resources/js/support-indicator-mode.js';

test('disables and clears indicator modes when activity entries are disabled', () => {
    assert.deepEqual(
        resolveSupportIndicatorMode({
            allowActivities: false,
            allowEvaluateeIndicator: true,
            grouped: true,
        }),
        {
            allowEvaluateeIndicatorChecked: false,
            allowEvaluateeIndicatorDisabled: true,
            groupedChecked: false,
            groupedDisabled: true,
        },
    );
});

test('evaluatee-owned indicator mode wins over conflicting grouped data', () => {
    assert.deepEqual(
        resolveSupportIndicatorMode({
            allowActivities: true,
            allowEvaluateeIndicator: true,
            grouped: true,
        }),
        {
            allowEvaluateeIndicatorChecked: true,
            allowEvaluateeIndicatorDisabled: false,
            groupedChecked: false,
            groupedDisabled: true,
        },
    );
});

test('grouped mode prevents enabling evaluatee-owned indicators', () => {
    assert.deepEqual(
        resolveSupportIndicatorMode({
            allowActivities: true,
            allowEvaluateeIndicator: false,
            grouped: true,
        }),
        {
            allowEvaluateeIndicatorChecked: false,
            allowEvaluateeIndicatorDisabled: true,
            groupedChecked: true,
            groupedDisabled: false,
        },
    );
});

test('leaves both choices enabled when neither exclusive mode is selected', () => {
    assert.deepEqual(
        resolveSupportIndicatorMode({
            allowActivities: true,
            allowEvaluateeIndicator: false,
            grouped: false,
        }),
        {
            allowEvaluateeIndicatorChecked: false,
            allowEvaluateeIndicatorDisabled: false,
            groupedChecked: false,
            groupedDisabled: false,
        },
    );
});
