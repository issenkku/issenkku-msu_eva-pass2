<?php

test('shared checkbox filter renders a semantic toggle and labelled panel', function () {
    $html = view('components.filter', [
        'name' => 'department_id',
        'label' => 'หน่วยงาน',
        'options' => [
            1 => 'IT',
            2 => 'HR',
        ],
        'value' => [2],
        'placeholder' => 'ทั้งหมด',
    ])->render();

    expect($html)
        ->toContain('type="button"')
        ->toContain('aria-haspopup="group"')
        ->toContain('aria-controls="department_id_filter_panel"')
        ->toContain('id="department_id_filter_control"')
        ->toContain('aria-labelledby="department_id_filter_label"')
        ->toContain('role="group"')
        ->toContain('id="department_id_filter_panel"')
        ->toContain('data-auto-submit-select')
        ->toContain('name="department_id[]"')
        ->toContain('HR');

    $this->assertStringNotContainsString('onchange="this.form.submit()"', $html);
});
