<?php

test('criteria config index renders data-hook based copy and delete controls', function () {
    $html = view('criteria_config.index')->render();

    expect($html)
        ->toContain('data-copy-criteria-version')
        ->toContain('data-delete-criteria-version')
        ->toContain('window.__criteriaConfigActionHooksBound')
        ->not->toContain('onclick="copyCriteriaVersion(')
        ->not->toContain('onclick="showDeleteModal(');
});
