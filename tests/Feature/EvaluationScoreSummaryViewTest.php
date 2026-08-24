<?php

test('score summary shows only categories that have criteria', function () {
    $html = view('partials.evaluator-score-summary', [
        'scoreSummary' => [
            'quantity' => 0.0,
            'quality' => 0.0,
            'support' => 4.3,
            'total' => 4.3,
            'has_quantity' => false,
            'has_quality' => false,
            'has_support' => true,
            'support_achievement' => 0.86,
            'support_target_level_count' => 5,
        ],
    ])->render();

    expect($html)
        ->not->toContain('id="quantity-summary"')
        ->not->toContain('id="quality-summary"')
        ->toContain('data-testid="support-score-pair"')
        ->toContain('id="support-summary"')
        ->toContain('ผลรวมคะแนนถ่วงน้ำหนัก')
        ->toContain('id="support-achievement-summary"')
        ->toContain('data-support-target-level-count="5"')
        ->toContain('ผลรวมคะแนนถ่วงน้ำหนัก ÷ จำนวนระดับค่าเป้าหมาย: 5')
        ->toContain('>4.30</span>')
        ->toContain('id="total-summary"');
});

test('score summary keeps a zero score visible when its category has criteria', function () {
    $html = view('partials.evaluator-score-summary', [
        'scoreSummary' => [
            'quantity' => 0.0,
            'quality' => 0.0,
            'support' => 0.0,
            'total' => 0.0,
            'has_quantity' => true,
            'has_quality' => false,
            'has_support' => false,
        ],
    ])->render();

    expect($html)
        ->toContain('id="quantity-summary"')
        ->toContain('>0.00</span>')
        ->not->toContain('id="quality-summary"')
        ->not->toContain('id="support-summary"')
        ->not->toContain('id="support-achievement-summary"')
        ->toContain('id="total-summary"');
});

test('director component delegates score summary rendering to the shared read model and partial', function () {
    $source = file_get_contents(resource_path('views/components/unified-director.blade.php'));

    expect($source)
        ->toContain('EvaluationScoreSummary::fromCategoryItems($categoryItems)')
        ->toContain("@include('partials.evaluator-score-summary', ['scoreSummary' => \$scoreSummary])")
        ->not->toContain('$totalQuantityScore = 0')
        ->not->toContain('<span id="quantity-summary"');
});

test('editable evaluation forms expose quantity list caps to the live summary scripts', function () {
    foreach (['unified-evaluator', 'unified-director'] as $component) {
        $template = file_get_contents(resource_path("views/components/{$component}.blade.php"));
        $script = file_get_contents(resource_path("views/components/{$component}-script.blade.php"));

        expect($template)
            ->toContain('data-evaluation-list-id="{{ $evaluationList[\'id\'] }}"')
            ->toContain('data-list-max="{{ $evaluationList[\'sum_score\'] ?? 0 }}"')
            ->and($script)
            ->toContain('quantityListTotals')
            ->toContain('cappedSum');
    }
});
