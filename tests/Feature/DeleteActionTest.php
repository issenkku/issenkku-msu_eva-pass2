<?php

use Illuminate\Pagination\LengthAwarePaginator;

test('shared delete modal script listens for data delete triggers', function () {
    $html = view('components.delete-warning-modal-script', [
        'formAction' => route('users.destroy', ':id'),
    ])->render();

    expect($html)
        ->toContain('data-delete-trigger')
        ->toContain('window.confirmDelete')
        ->toContain('deleteId')
        ->not->toContain('onclick="confirmDelete(');
});

test('department rows render delete triggers without inline handlers', function () {
    $departments = new LengthAwarePaginator([
        (object) ['id' => 1, 'department_name' => 'IT'],
    ], 1, 15, 1, ['path' => url('/departments')]);

    $html = view('departments.partials.index-table-section', [
        'departments' => $departments,
    ])->render();

    expect($html)
        ->toContain('data-bulk-select-all')
        ->toContain('data-bulk-checkbox')
        ->toContain('value="1"')
        ->toContain('data-delete-trigger')
        ->toContain('data-delete-id="1"')
        ->not->toContain('onclick="confirmDelete(1)"');
});

test('job level rows render delete triggers without inline handlers', function () {
    $jobLevels = new LengthAwarePaginator([
        (object) ['id' => 2, 'name' => 'Senior'],
    ], 1, 15, 1, ['path' => url('/job-level')]);

    $html = view('Job Level.partials.index-table-section', [
        'jobLevels' => $jobLevels,
    ])->render();

    expect($html)
        ->toContain('data-bulk-select-all')
        ->toContain('data-bulk-checkbox')
        ->toContain('value="2"')
        ->toContain('data-delete-trigger')
        ->toContain('data-delete-id="2"')
        ->not->toContain('onclick="confirmDelete(2)"');
});

test('position rows render delete triggers without inline handlers', function () {
    $positions = new LengthAwarePaginator([
        (object) ['id' => 3, 'name' => 'Lecturer'],
    ], 1, 15, 1, ['path' => url('/positions')]);

    $html = view('positions.partials.index-table-section', [
        'positions' => $positions,
    ])->render();

    expect($html)
        ->toContain('data-bulk-select-all')
        ->toContain('data-bulk-checkbox')
        ->toContain('value="3"')
        ->toContain('data-delete-trigger')
        ->toContain('data-delete-id="3"')
        ->not->toContain('onclick="confirmDelete(3)"');
});

test('subject rows render delete triggers without inline handlers', function () {
    $subjects = new LengthAwarePaginator([
        (object) [
            'id' => 4,
            'code' => 'TH101',
            'name_th' => 'ภาษาไทย',
            'name_en' => 'Thai',
            'credits' => 3,
            'lecture_credits' => 2,
            'lab_credits' => 1,
            'self_study_credits' => 0,
        ],
    ], 1, 15, 1, ['path' => url('/subjects')]);

    $html = view('subjects.partials.index-table-section', [
        'subjects' => $subjects,
    ])->render();

    expect($html)
        ->toContain('data-bulk-select-all')
        ->toContain('data-bulk-checkbox')
        ->toContain('value="4"')
        ->toContain('data-delete-trigger')
        ->toContain('data-delete-id="4"')
        ->not->toContain('onclick="confirmDelete(4)"');
});

test('settings pages render shared bulk delete modals and scripts', function () {
    $departments = new LengthAwarePaginator([(object) ['id' => 1, 'department_name' => 'IT', 'user_count' => 0]], 1, 15, 1, ['path' => url('/departments')]);
    $positions = new LengthAwarePaginator([(object) ['id' => 2, 'name' => 'Lecturer', 'user_count' => 0]], 1, 15, 1, ['path' => url('/positions')]);
    $jobLevels = new LengthAwarePaginator([(object) ['id' => 3, 'name' => 'Senior']], 1, 15, 1, ['path' => url('/job-level')]);
    $subjects = new LengthAwarePaginator([(object) ['id' => 4, 'code' => 'TH101', 'name_th' => 'Thai', 'name_en' => 'Thai', 'credits' => 3, 'lecture_credits' => 2, 'lab_credits' => 1, 'self_study_credits' => 0]], 1, 15, 1, ['path' => url('/subjects')]);

    $html = view('departments.index', ['departments' => $departments])->render()
        .view('positions.index', ['positions' => $positions])->render()
        .view('Job Level.index', ['jobLevels' => $jobLevels])->render()
        .view('subjects.index', ['subjects' => $subjects])->render();

    expect($html)
        ->toContain('data-bulk-delete-open')
        ->toContain('data-bulk-delete-form')
        ->toContain('data-bulk-delete-selected-inputs')
        ->toContain('initializeBulkDelete')
        ->toContain('departments/bulk-destroy')
        ->toContain('positions/bulk-destroy')
        ->toContain('job-level/bulk-destroy')
        ->toContain('subjects/bulk-destroy');
});

test('workload row actions render delete triggers without inline handlers', function () {
    $html = view('evaluatee.partials.workload-row-actions', [
        'readonly' => false,
        'rowView' => [
            'id' => 5,
        ],
    ])->render();

    expect($html)
        ->toContain('data-delete-trigger')
        ->toContain('data-delete-id="5"')
        ->not->toContain('onclick="confirmDelete(5)"');
});
