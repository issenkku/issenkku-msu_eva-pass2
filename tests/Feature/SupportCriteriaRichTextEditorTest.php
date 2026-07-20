<?php

it('renders both support criteria fields as rich text textareas', function () {
    $html = view('criteria_config.partials.support-criteria-template')->render();

    expect($html)
        ->toContain('class="support_activity_name richtext-editor')
        ->toContain('class="support_indicator richtext-editor')
        ->toContain('<textarea')
        ->not->toContain('<input type="text"');
});

it('keeps summernote lifecycle hooks for dynamic support criteria blocks', function () {
    $create = view('criteria_config.partials.create-script')->render();
    $edit = view('criteria_config.partials.script-edit-summernote-helpers')->render()
        .view('criteria_config.partials.script-edit-support-handlers')->render();

    expect($create.$edit)
        ->toContain('resetSummernoteClone')
        ->toContain("lang: 'th-TH'")
        ->toContain("['insert', ['link', 'hr']]");
});
