<?php

test('role management header and modal actions render data hooks without inline handlers', function () {
    $headerHtml = view('user.role-management.partials.index-header')->render();
    $modalActionsHtml = view('user.role-management.partials.index-modal-actions')->render();
    $scriptHtml = view('user.role-management.partials.index-script')->render();

    expect($headerHtml)
        ->toContain('data-role-modal-open')
        ->not->toContain('onclick="openCreateModal()"');

    expect($modalActionsHtml)
        ->toContain('data-role-modal-close')
        ->not->toContain('onclick="closeModal()"');

    expect($scriptHtml)
        ->toContain('data-role-modal-open')
        ->toContain('data-role-modal-close');
});
