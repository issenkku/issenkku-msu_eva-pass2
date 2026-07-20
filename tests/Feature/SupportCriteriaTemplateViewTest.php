<?php

test('create evaluation template exposes support criteria controls after quality', function () {
    $html = view('criteria_config.partials.create-evaluation-template')->render();

    expect($html)
        ->toContain('quality_criteria_type')
        ->toContain('support_criteria_type')
        ->toContain('เกณฑ์สำหรับสายสนับสนุน')
        ->toContain('support_criterias_container')
        ->toContain('support_activity_name')
        ->toContain('support_indicator')
        ->toContain('support_target_value')
        ->toContain('support_weight')
        ->toContain('คะแนนถ่วงน้ำหนัก = น้ำหนัก × คะแนนที่ผู้ถูกประเมินกรอก ÷ 100');

    expect(strpos($html, 'quality_criteria_type'))
        ->toBeLessThan(strpos($html, 'support_criteria_type'));
});

test('create script toggles collects and reorders support criteria', function () {
    $script = file_get_contents(resource_path('views/criteria_config/partials/create-script.blade.php'));

    expect($script)
        ->toContain("querySelector('.support_criteria_type')")
        ->toContain("querySelector('.support_criterias_container')")
        ->toContain("querySelectorAll('.support_criteria_block')")
        ->toContain('evalList.support_criterias = []')
        ->toContain('support_criteria_id')
        ->toContain('support_activity_name')
        ->toContain('support_target_value')
        ->toContain('support_weight');
});
