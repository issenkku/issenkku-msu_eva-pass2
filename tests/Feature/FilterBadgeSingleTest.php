<?php

test('shared filter badge renders a semantic toggle and labelled panel', function () {
    $html = view('components.filter-badge-single', [
        'name' => 'year',
        'options' => [2025 => 2568],
        'value' => 2025,
        'placeholder' => 'ปีประเมิน',
    ])->render();

    expect($html)
        ->toContain('type="button"')
        ->toContain('aria-controls="year-filter-badge-panel"')
        ->toContain('id="year-filter-badge-control"')
        ->toContain('role="radiogroup"')
        ->toContain('aria-labelledby="year-filter-badge-control"')
        ->toContain('data-auto-submit-select');

    $this->assertStringNotContainsString('onchange="this.form.submit()"', $html);
});
