<?php

test('assignment data flash message renders an accessible close button', function () {
    $originalSession = session()->all();
    session()->replace(['success' => 'Saved']);

    try {
        $html = view('assignment-data.partials.flash-messages', [
            'errors' => new \Illuminate\Support\ViewErrorBag(),
        ])->render();
    } finally {
        session()->replace($originalSession);
    }

    expect($html)
        ->toContain('Saved')
        ->toContain('type="button"')
        ->toContain('data-flash-close')
        ->toContain('aria-label="ปิดข้อความแจ้งเตือน"')
        ->toContain('title="ปิดข้อความแจ้งเตือน"');

    $this->assertStringNotContainsString('onclick="this.parentElement.parentElement.remove()"', $html);
});

test('import modal flash message renders accessible close buttons for all variants', function () {
    $originalSession = session()->all();
    session()->replace([
        'success' => 'Saved',
        'warning' => 'Watch out',
        'error' => 'Failed',
    ]);

    try {
        $html = view('user.management.partials.import-modal-flash-message')->render();
    } finally {
        session()->replace($originalSession);
    }

    expect($html)
        ->toContain('Saved')
        ->toContain('Watch out')
        ->toContain('Failed')
        ->toContain('type="button"')
        ->toContain('data-flash-close')
        ->toContain('aria-label="ปิดข้อความแจ้งเตือน"')
        ->toContain('title="ปิดข้อความแจ้งเตือน"');

    $this->assertSame(3, substr_count($html, 'aria-label="ปิดข้อความแจ้งเตือน"'));
    $this->assertStringNotContainsString('onclick="this.parentElement.parentElement.remove()"', $html);
});
