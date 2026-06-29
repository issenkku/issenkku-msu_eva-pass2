<?php

test('login form renders a data-hook based password toggle', function () {
    $siteSetting = (object) [
        'faculty' => 'คณะทดสอบ',
        'university' => 'มหาวิทยาลัยทดสอบ',
        'logo_url' => asset('favicon-msu.png'),
        'background_url' => asset('images/workload-background.jpg'),
        'use_white_background' => false,
    ];

    $html = view('user.management.loginForm', [
        'siteSetting' => $siteSetting,
    ])->render();

    expect($html)
        ->toContain('method="POST"')
        ->toContain('action="' . route('login') . '"')
        ->toContain('data-password-toggle')
        ->toContain('aria-pressed="false"')
        ->not->toContain('onclick="togglePassword()"');
});
