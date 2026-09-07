<?php

use App\Support\EvaluationScoreSummary;

test('quantity summary reports completed criteria without changing the score cap', function () {
    $summary = EvaluationScoreSummary::fromCategoryItems([[
        'evaluation_lists' => [[
            'quantity_enabled' => true,
            'quantity_items' => [[
                'sub_criterias' => [
                    ['tor_compliant' => 120, 'score_d' => 6],
                    ['tor_compliant' => 800, 'score_d' => 40],
                    ['tor_compliant' => '', 'score_d' => ''],
                    ['tor_compliant' => '', 'score_d' => ''],
                    ['tor_compliant' => '', 'score_d' => ''],
                    ['tor_compliant' => '', 'score_d' => ''],
                    ['tor_compliant' => '', 'score_d' => ''],
                ],
            ]],
            'quality_items' => [],
            'support_items' => [],
            'sum_score' => 40,
        ]],
    ]]);

    expect($summary['quantity'])->toBe(40.0)
        ->and($summary['quantity_completed_count'])->toBe(2)
        ->and($summary['quantity_total_count'])->toBe(7);
});
