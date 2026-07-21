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

it('collects support criteria HTML from create and edit scripts', function () {
    $html = view('criteria_config.partials.create-script')->render()
        .file_get_contents(resource_path('views/criteria_config/partials/script-edit-collect-form-data.blade.php'));

    expect($html)
        ->toContain("getRichTextValue(supportBlock.querySelector('.support_activity_name'))")
        ->toContain("getRichTextValue(supportBlock.querySelector('.support_indicator'))");
});

it('restores list markers inside summernote and rendered rich text', function () {
    $styles = file_get_contents(resource_path('views/partials/layout-app-styles.blade.php'));

    expect($styles)
        ->toContain('.note-editor .note-editable ul')
        ->toContain('.note-editor .note-editable ol')
        ->toContain('list-style-type: disc')
        ->toContain('list-style-type: decimal')
        ->toContain('.support-criteria-rich-text ul')
        ->toContain('.support-criteria-rich-text ol');
});

it('uses the admin summernote toolbar for evaluatee activity entries', function () {
    $script = file_get_contents(resource_path('views/components/support-criteria-table-script.blade.php'));

    expect($script)
        ->toContain("['style', ['style']]")
        ->toContain("['font', ['bold', 'italic', 'underline', 'clear']]")
        ->toContain("['insert', ['link', 'hr']]")
        ->toContain("lang: 'th-TH'")
        ->toContain('support-activity-richtext');
});
