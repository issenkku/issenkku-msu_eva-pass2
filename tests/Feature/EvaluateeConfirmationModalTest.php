<?php

it('renders a support score card and support project rows in the confirmation modal', function () {
    $source = file_get_contents(resource_path('views/partials/evaluatee-confirmation-modal.blade.php'));

    expect($source)
        ->toContain('id="modal-support-summary"')
        ->toContain('id="modal-support-achievement-summary"')
        ->toContain('คะแนนผลสัมฤทธิ์ของงาน')
        ->toContain('ผลรวมคะแนนถ่วงน้ำหนัก ÷ จำนวนระดับค่าเป้าหมาย: 5')
        ->toContain('data-summary-support-main')
        ->toContain('data-support-id')
        ->toContain('summary-support-score-')
        ->toContain('summary-support-status-')
        ->toContain('support_items');
});

it('does not render a support section when an evaluation list has no support items', function () {
    $source = file_get_contents(resource_path('views/partials/evaluatee-confirmation-modal.blade.php'));

    expect($source)->toContain("count(\$evaluationList['support_items']) > 0");
});

it('updates support summary values without changing the existing total summary', function () {
    $source = file_get_contents(resource_path('views/partials/evaluatee-evaluation-script.blade.php'));

    expect($source)
        ->toContain('modal-support-summary')
        ->toContain("'support-achievement-summary'")
        ->toContain("'modal-support-achievement-summary'")
        ->toContain('supportAchievementSummary.toFixed(2)')
        ->toContain('data-summary-support-main')
        ->toContain('row.dataset.supportWeight')
        ->toContain('summary-support-status-')
        ->toContain('summary-support-score-')
        ->toContain('supportTotal')
        ->toContain('modal-total-summary');
});

it('shows only score categories that have criteria in the confirmation modal', function () {
    $html = view('partials.evaluatee-confirmation-modal', [
        'categoryItems' => [],
        'scoreSummary' => [
            'has_quantity' => false,
            'has_quality' => false,
            'has_support' => true,
        ],
    ])->render();

    expect($html)
        ->not->toContain('id="modal-quantity-summary"')
        ->not->toContain('id="modal-quality-summary"')
        ->toContain('id="modal-support-summary"')
        ->toContain('id="modal-support-achievement-summary"')
        ->toContain('id="modal-total-summary"')
        ->toContain('sm:grid-cols-3');
});

it('keeps a zero score card visible when its category has criteria', function () {
    $html = view('partials.evaluatee-confirmation-modal', [
        'categoryItems' => [],
        'scoreSummary' => [
            'quantity' => 0.0,
            'has_quantity' => true,
            'has_quality' => false,
            'has_support' => false,
        ],
    ])->render();

    expect($html)
        ->toContain('id="modal-quantity-summary"')
        ->toMatch('/id="modal-quantity-summary"[^>]*>0\.00<\/div>/')
        ->not->toContain('id="modal-quality-summary"')
        ->not->toContain('id="modal-support-summary"')
        ->not->toContain('id="modal-support-achievement-summary"')
        ->toContain('id="modal-total-summary"')
        ->toContain('sm:grid-cols-2');
});

it('uses a five-column layout when every score category is visible', function () {
    $html = view('partials.evaluatee-confirmation-modal', [
        'categoryItems' => [],
        'scoreSummary' => [
            'has_quantity' => true,
            'has_quality' => true,
            'has_support' => true,
        ],
    ])->render();

    expect($html)
        ->toContain('id="modal-quantity-summary"')
        ->toContain('id="modal-quality-summary"')
        ->toContain('id="modal-support-summary"')
        ->toContain('id="modal-support-achievement-summary"')
        ->toContain('id="modal-total-summary"')
        ->toContain('sm:grid-cols-2 lg:grid-cols-5');
});
