<?php

use App\Support\ReportScoreSummary;

test('report score summary caps support totals at five and achievement at one', function () {
    $summary = ReportScoreSummary::fromTotals(2.0, 3.0, 112.5);

    expect($summary)->toMatchArray([
        'quantity' => 2.0,
        'quality' => 3.0,
        'support_raw' => 5.0,
        'support' => 5.0,
        'support_achievement' => 1.0,
        'total' => 10.0,
    ]);
});

test('report score summary rounds exported values to two decimal places', function () {
    $summary = ReportScoreSummary::fromTotals(1.234, 2.345, 4.306);

    expect($summary)->toMatchArray([
        'quantity' => 1.23,
        'quality' => 2.35,
        'support_raw' => 4.31,
        'support' => 4.31,
        'support_achievement' => 0.86,
        'total' => 7.89,
    ]);
});
