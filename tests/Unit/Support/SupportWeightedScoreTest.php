<?php

use App\Support\SupportWeightedScore;

test('it calculates and rounds a support entry weighted score', function () {
    expect(SupportWeightedScore::calculate(40, 80))->toBe(32.0)
        ->and(SupportWeightedScore::calculate(33.33, 66.67))->toBe(22.22);
});
