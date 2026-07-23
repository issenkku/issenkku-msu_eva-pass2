<?php

test('score summary shows only categories that have criteria', function () {
    $html = view('partials.evaluator-score-summary', [
        'scoreSummary' => [
            'quantity' => 0.0,
            'quality' => 0.0,
            'support' => 1.0,
            'total' => 1.0,
            'has_quantity' => false,
            'has_quality' => false,
            'has_support' => true,
        ],
    ])->render();

    expect($html)
        ->not->toContain('id="quantity-summary"')
        ->not->toContain('id="quality-summary"')
        ->toContain('id="support-summary"')
        ->toContain('>1.00</span>')
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
