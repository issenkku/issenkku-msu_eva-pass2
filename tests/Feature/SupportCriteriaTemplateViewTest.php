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
        ->toContain('support_require_evidence')
        ->toContain('support_allow_activity_entries')
        ->toContain('อนุญาตให้ผู้ถูกประเมินเพิ่มกิจกรรม/โครงการ')
        ->toContain('support_group_by_indicator')
        ->toContain('แยกโครงการตามตัวชี้วัดย่อย')
        ->toContain('support_indicator_items')
        ->toContain('support_indicator_item_block')
        ->toContain('support_indicator_code')
        ->not->toContain('support_indicator_description')
        ->toContain('เพิ่มตัวชี้วัดย่อย')
        ->toContain('คะแนนถ่วงน้ำหนัก = น้ำหนัก × คะแนนที่ผู้ถูกประเมินกรอก ÷ 100');

    expect(strpos($html, 'quality_criteria_type'))
        ->toBeLessThan(strpos($html, 'support_criteria_type'));
});

test('support indicator code input does not impose a character limit', function () {
    $html = view('criteria_config.partials.create-evaluation-template')->render();

    expect($html)
        ->toContain('support_indicator_code')
        ->not->toContain('type="text" maxlength="50"');
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
        ->toContain('support_weight')
        ->toContain('support_require_evidence')
        ->toContain('require_evidence')
        ->toContain('support_allow_activity_entries')
        ->toContain('allow_activity_entries')
        ->toContain('group_activity_entries_by_indicator')
        ->toContain('indicator_items')
        ->toContain('support_indicator_item_id');
});

test('edit template loads toggles and collects support criteria', function () {
    $template = view('criteria_config.partials.edit-evaluation-template')->render();
    $editScript = file_get_contents(resource_path('views/criteria_config/partials/edit-script.blade.php'));
    $criteriaHandler = file_get_contents(resource_path('views/criteria_config/partials/script-edit-criteria-type-handler.blade.php'));
    $supportHandler = file_get_contents(resource_path('views/criteria_config/partials/script-edit-support-handlers.blade.php'));
    $populate = file_get_contents(resource_path('views/criteria_config/partials/script-edit-populate-helpers.blade.php'));
    $collect = file_get_contents(resource_path('views/criteria_config/partials/script-edit-collect-form-data.blade.php'));

    expect($template)
        ->toContain('support_criteria_type')
        ->toContain('support_criterias_container');
    expect($editScript)->toContain("@include('criteria_config.partials.script-edit-support-handlers')");
    expect($criteriaHandler)->toContain("querySelector('.support_criteria_type')");
    expect($populate)
        ->toContain('evalData.support_criterias')
        ->toContain('populateSupportCriteria');
    expect($supportHandler)
        ->toContain('support_require_evidence')
        ->toContain('require_evidence')
        ->toContain('support_allow_activity_entries')
        ->toContain('allow_activity_entries')
        ->toContain('support_group_by_indicator')
        ->not->toContain('support_indicator_description');
    expect($supportHandler.$collect)
        ->toContain('evalData.support_criterias = []')
        ->toContain('support_criteria_id')
        ->toContain('require_evidence')
        ->toContain('allow_activity_entries')
        ->toContain('group_activity_entries_by_indicator')
        ->toContain('indicator_items')
        ->toContain('support_indicator_item_id');
});
