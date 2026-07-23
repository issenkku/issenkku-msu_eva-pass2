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

test('login form adapts its complete layout to the viewport height', function () {
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
        ->toContain('height: 100dvh')
        ->toContain('--login-page-padding-y: clamp(')
        ->toContain('--login-logo-size: clamp(')
        ->toContain('--login-card-padding: clamp(')
        ->toContain('@media (max-height: 760px)')
        ->toContain('@media (prefers-reduced-motion: reduce)')
        ->not->toContain('zoom:');
});
