<?php

it('renders a support score card and support project rows in the confirmation modal', function () {
    $source = file_get_contents(resource_path('views/partials/evaluatee-confirmation-modal.blade.php'));

    expect($source)
        ->toContain('id="modal-support-summary"')
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
        ->toContain('data-summary-support-main')
        ->toContain('row.dataset.supportWeight')
        ->toContain('summary-support-status-')
        ->toContain('summary-support-score-')
        ->toContain('supportTotal')
        ->toContain('modal-total-summary');
});
