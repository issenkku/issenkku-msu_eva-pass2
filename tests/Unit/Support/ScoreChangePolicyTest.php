<?php

use App\Support\ScoreChangePolicy;
use Illuminate\Validation\ValidationException;
use Tests\TestCase;

uses(TestCase::class);

test('numeric comparison treats equivalent values as unchanged', function () {
    expect(ScoreChangePolicy::numbersDiffer('4', '4.00'))->toBeFalse()
        ->and(ScoreChangePolicy::numbersDiffer(null, ''))->toBeFalse()
        ->and(ScoreChangePolicy::numbersDiffer(null, 4))->toBeTrue()
        ->and(ScoreChangePolicy::numbersDiffer(4, null))->toBeTrue();
});

test('text comparison trims values before comparison', function () {
    expect(ScoreChangePolicy::textsDiffer(' note ', 'note'))->toBeFalse()
        ->and(ScoreChangePolicy::textsDiffer(null, ''))->toBeFalse()
        ->and(ScoreChangePolicy::textsDiffer('old', 'new'))->toBeTrue();
});

test('reviewer change requires a non blank reason', function () {
    try {
        ScoreChangePolicy::validatedReason(true, ' ', true, 'quantity_list.7.modification_reason');
        $this->fail('Expected ValidationException');
    } catch (ValidationException $exception) {
        expect($exception->errors())->toHaveKey('quantity_list.7.modification_reason');
    }
});

test('evaluatee change may omit reason and unchanged input discards reason', function () {
    expect(ScoreChangePolicy::validatedReason(true, null, false, 'x'))->toBeNull()
        ->and(ScoreChangePolicy::validatedReason(false, 'unused', true, 'x'))->toBeNull();
});

test('reason is trimmed and limited to two thousand characters', function () {
    expect(ScoreChangePolicy::validatedReason(true, ' เหตุผล ', true, 'x'))->toBe('เหตุผล');

    try {
        ScoreChangePolicy::validatedReason(true, str_repeat('ก', 2001), true, 'x');
        $this->fail('Expected ValidationException');
    } catch (ValidationException $exception) {
        expect($exception->errors())->toHaveKey('x');
    }
});
