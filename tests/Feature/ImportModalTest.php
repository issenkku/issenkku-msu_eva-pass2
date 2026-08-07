<?php

use Illuminate\Pagination\LengthAwarePaginator;

test('user management toolbar renders data hooks for import and create actions', function () {
    $html = view('user.management.partials.index-toolbar', [
        'users' => new LengthAwarePaginator([], 0, 15),
    ])->render();

    expect($html)
        ->toContain('data-import-modal-open')
        ->toContain('data-create-modal-open')
        ->toContain('data-action="' . route('users.import') . '"')
        ->toContain('data-action="' . route('users.store') . '"')
        ->not->toContain('onclick="openImportModal(this)"')
        ->not->toContain('onclick="openCreateModal(this)"');
});

test('import user modal renders import hooks without inline handlers', function () {
    $html = view('user.management.import-user-modal', [
        'errors' => new \Illuminate\Support\ViewErrorBag(),
    ])->render();

    expect($html)
        ->toContain('id="importUserModal"')
        ->toContain('data-import-modal-close')
        ->toContain('data-import-drop-zone')
        ->toContain('data-import-file-input')
        ->toContain('data-import-remove-file')
        ->toContain('data-import-modal-open')
        ->toContain('type="button"')
        ->not->toContain('onclick="closeImportModal()"')
        ->not->toContain('ondrop="handleDrop(event)"')
        ->not->toContain('ondragover="handleDragOver(event)"')
        ->not->toContain('ondragleave="handleDragLeave(event)"')
        ->not->toContain('onclick="document.getElementById(\'fileInput\').click()"')
        ->not->toContain('onchange="handleFileSelect(event)"')
        ->not->toContain('onclick="removeFile()"');

    expect($html)
        ->toContain('data-import-modal-panel')
        ->toContain('data-import-modal-header')
        ->toContain('data-import-modal-body')
        ->toContain('data-import-modal-footer')
        ->toContain('h-[100dvh]')
        ->toContain('sm:max-h-[calc(100dvh-2rem)]')
        ->toContain('flex-1 overflow-y-auto overscroll-contain')
        ->toContain('flex-none');
});
