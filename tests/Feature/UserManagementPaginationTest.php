<?php

use Illuminate\Pagination\LengthAwarePaginator;

function paginationTestUser(int $id, string $name): array
{
    return [
        'id' => $id,
        'prefix' => '',
        'name' => $name,
        'employee_id' => "EMP{$id}",
        'position' => ['name' => 'Officer'],
        'job_level' => null,
        'personnel_type' => 'staff',
        'phone' => '0812345678',
        'email' => "user{$id}@example.com",
        'bio' => null,
        'education_history_entries' => [],
        'status' => 'active',
        'position_id' => 1,
        'job_level_id' => null,
        'department_id' => 1,
        'role_names' => [],
    ];
}

test('staff row numbers continue on the second pagination page', function () {
    $users = new LengthAwarePaginator(
        collect([
            paginationTestUser(11, 'Eleventh User'),
            paginationTestUser(12, 'Twelfth User'),
        ]),
        12,
        10,
        2,
        ['path' => '/users'],
    );

    $html = view('user.management.partials.index-table', compact('users'))->render();

    expect($html)
        ->toContain('<td class="p-4 text-center">11</td>')
        ->toContain('<td class="p-4 text-center">12</td>')
        ->not->toContain('<td class="p-4 text-center">1</td>')
        ->not->toContain('<td class="p-4 text-center">2</td>');
});
