<?php

use App\Services\WorkloadFormulaEvaluator;

function evaluateWorkloadFormula(string $formula): float
{
    $fields = [
        ['variable_name' => 'a', 'field_type' => 'number'],
        ['variable_name' => 'b', 'field_type' => 'number'],
        ['variable_name' => 'c', 'field_type' => 'number'],
    ];

    return app(WorkloadFormulaEvaluator::class)->evaluate($formula, $fields, [
        'a' => 2,
        'b' => 5,
        'c' => 9,
    ]);
}

test('workload formula evaluator supports sum max and min functions', function () {
    expect(evaluateWorkloadFormula('sum(a,b,c)'))->toBe(16.0)
        ->and(evaluateWorkloadFormula('max(a,b,c)'))->toBe(9.0)
        ->and(evaluateWorkloadFormula('min(a,b,c)'))->toBe(2.0);
});

test('workload formula functions accept expressions and are case insensitive', function () {
    expect(evaluateWorkloadFormula('SUM(a, b * 2, IF(c > 3, c, 0))'))->toBe(21.0)
        ->and(evaluateWorkloadFormula('MAX(a + b, c - 1)'))->toBe(8.0)
        ->and(evaluateWorkloadFormula('Min(a + b, c - 1)'))->toBe(7.0);
});
