<?php

test('public profile view renders a data-hook based print button', function () {
    $user = (object) [
        'display_name' => 'Test User',
        'employee_id' => 'EMP001',
        'position' => (object) ['name' => 'Lecturer'],
        'jobLevel' => (object) ['name' => 'Senior'],
        'personnel_type' => 'Academic',
        'department' => (object) ['department_name' => 'IT'],
        'email' => 'test@example.com',
        'phone' => '0812345678',
        'education_history_entries' => [],
        'portfolio' => null,
    ];

    $html = view('user.profile.show-profile-public', [
        'user' => $user,
    ])->render();

    expect($html)
        ->toContain('data-profile-print')
        ->toContain('window.print();')
        ->not->toContain('onclick="window.print()"');
});
