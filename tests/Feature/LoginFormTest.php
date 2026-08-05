<?php

use Illuminate\Support\MessageBag;
use Illuminate\Support\ViewErrorBag;

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
        'errors' => new ViewErrorBag,
    ])->render();

    expect($html)
        ->toContain('method="POST"')
        ->toContain('action="'.route('login').'"')
        ->toContain('data-password-toggle')
        ->toContain('aria-pressed="false"')
        ->not->toContain('onclick="togglePassword()"')
        ->not->toContain('cdn.jsdelivr.net/npm/axios')
        ->not->toContain('window.axios');
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
        'errors' => new ViewErrorBag,
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

test('login form renders a server error and keeps only the employee id', function () {
    $errors = new ViewErrorBag;
    $errors->put(
        'default',
        new MessageBag([
            'employee_id' => ['กรุณากรอกหมายเลขประจำตัวและรหัสผ่านให้ถูกต้อง'],
        ]),
    );

    $html = $this
        ->withSession([
            '_old_input' => ['employee_id' => 'EMP001'],
            'errors' => $errors,
        ])
        ->get('/login')
        ->assertOk()
        ->getContent();

    expect($html)
        ->toContain('role="alert"')
        ->toContain('aria-live="assertive"')
        ->toContain('กรุณากรอกหมายเลขประจำตัวและรหัสผ่านให้ถูกต้อง')
        ->toContain('value="EMP001"')
        ->not->toContain('value="wrongpassword"');
});
