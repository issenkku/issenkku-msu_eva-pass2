<?php

use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\ViewErrorBag;

function userManagementTestPaginator(array $users): LengthAwarePaginator
{
    return new LengthAwarePaginator(
        collect($users),
        count($users),
        10,
        1,
        ['path' => '/users'],
    );
}

test('user management modal renders data-hook based shell controls', function () {
    $html = view('user.management.user-form-modal', [
        'departments' => collect([(object) ['id' => 1, 'department_name' => 'IT']]),
        'positions' => collect([(object) ['id' => 2, 'name' => 'Lecturer']]),
        'jobLevels' => collect([(object) ['id' => 3, 'name' => 'Senior']]),
        'roles' => collect([
            (object) ['name' => 'admin'],
            (object) ['name' => 'user'],
        ]),
        'errors' => new ViewErrorBag,
    ])->render();

    expect($html)
        ->toContain('id="userModal"')
        ->toContain('data-user-modal-close')
        ->toContain('data-user-education-add')
        ->toContain('data-user-education-remove')
        ->not->toContain('onclick="closeModal()"')
        ->not->toContain('onclick="addEducationHistoryRow()"')
        ->not->toContain('onclick="closeModal()"');
});

test('user table actions render data-hook based triggers', function () {
    $payload = base64_encode(json_encode([
        'id' => 12,
        'name' => 'Test User',
        'roles' => [],
        'education_history' => [],
    ], JSON_UNESCAPED_UNICODE));

    $html = view('user.management.partials.user-table-actions', [
        'employee' => ['id' => 12],
        'editUserPayloadEncoded' => $payload,
    ])->render();

    expect($html)
        ->toContain('data-user-edit-trigger')
        ->toContain('data-user-delete-trigger')
        ->toContain('data-user-id="12"')
        ->not->toContain('onclick="openEditModalFromButton(this)"')
        ->not->toContain('onclick="confirmDelete(12)"');
});

test('user management table renders users without job level id', function () {
    $html = view('user.management.partials.index-table', [
        'users' => userManagementTestPaginator([
            [
                'id' => 12,
                'prefix' => 'Dr.',
                'name' => 'Test User',
                'employee_id' => 'EMP012',
                'position' => ['name' => 'Lecturer'],
                'job_level' => null,
                'personnel_type' => 'staff',
                'phone' => '0812345678',
                'email' => 'test@example.com',
                'bio' => null,
                'education_history_entries' => [],
                'status' => 'active',
                'position_id' => 2,
                'department_id' => 1,
                'role_names' => [],
            ],
        ]),
    ])->render();

    expect($html)
        ->toContain('Test User')
        ->toContain('EMP012');
});

test('user management table renders bulk delete selection hooks', function () {
    $html = view('user.management.partials.index-table', [
        'users' => userManagementTestPaginator([
            [
                'id' => 12,
                'prefix' => 'Dr.',
                'name' => 'Test User',
                'employee_id' => 'EMP012',
                'position' => ['name' => 'Lecturer'],
                'job_level' => null,
                'personnel_type' => 'staff',
                'phone' => '0812345678',
                'email' => 'test@example.com',
                'bio' => null,
                'education_history_entries' => [],
                'status' => 'active',
                'position_id' => 2,
                'department_id' => 1,
                'role_names' => [],
            ],
        ]),
    ])->render();

    expect($html)
        ->toContain('data-user-bulk-select-all')
        ->toContain('data-user-bulk-checkbox')
        ->toContain('value="12"');
});

test('user management bulk delete modal renders form hooks', function () {
    $html = view('user.management.partials.bulk-delete-modal')->render();

    expect($html)
        ->toContain('id="bulkDeleteUsersModal"')
        ->toContain('action="'.route('users.bulk-destroy').'"')
        ->toContain('data-user-bulk-delete-form')
        ->toContain('data-user-bulk-delete-selected-count')
        ->toContain('data-user-bulk-delete-selected-inputs');
});

test('user management bulk delete script waits for the table dom before binding controls', function () {
    $html = view('user.management.partials.bulk-delete-script')->render();

    expect($html)
        ->toContain('DOMContentLoaded')
        ->toContain('initializeUserBulkDelete');
});
