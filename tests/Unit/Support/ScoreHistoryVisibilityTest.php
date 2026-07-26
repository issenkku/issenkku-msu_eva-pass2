<?php

use App\Support\ScoreHistoryVisibility;

test('it hides history written by the report evaluatee', function () {
    expect(ScoreHistoryVisibility::shouldDisplay(10, null, 10))->toBeFalse();
});

test('it hides legacy history without a modifier or role', function () {
    expect(ScoreHistoryVisibility::shouldDisplay(null, null, 10))->toBeFalse();
});

test('it shows reviewer history', function () {
    expect(ScoreHistoryVisibility::shouldDisplay(20, 'ผู้ประเมิน', 10))->toBeTrue();
});

test('it shows retained reviewer history after its user is deleted', function () {
    expect(ScoreHistoryVisibility::shouldDisplay(null, 'ผู้ประเมิน', 10))->toBeTrue();
});
