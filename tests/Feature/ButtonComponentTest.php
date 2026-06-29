<?php

test('shared button renders the secondary variant classes', function () {
    $html = view('components.button', [
        'type' => 'secondary',
        'text' => 'ย้อนกลับ',
        'href' => '/dashboard',
        'icon' => 'fas fa-arrow-left',
    ])->render();

    expect($html)
        ->toContain('<a href="/dashboard"')
        ->toContain('bg-white')
        ->toContain('border-purple-500')
        ->toContain('text-purple-500')
        ->toContain('aria-hidden="true"')
        ->toContain('focusable="false"')
        ->toContain('ย้อนกลับ');
});

test('shared button renders the primary button element when no href is provided', function () {
    $html = view('components.button', [
        'type' => 'primary',
        'text' => 'บันทึก',
        'buttonType' => 'submit',
        'icon' => 'fas fa-save',
    ])->render();

    expect($html)
        ->toContain('<button')
        ->toContain('type="submit"')
        ->toContain('bg-purple-600')
        ->toContain('text-white')
        ->toContain('aria-hidden="true"')
        ->toContain('focusable="false"')
        ->toContain('บันทึก');
});
