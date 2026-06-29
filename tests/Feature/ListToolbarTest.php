<?php

test('shared list toolbar renders a clear action when search is present', function () {
    $request = request();
    $originalQuery = $request->query->all();
    $request->query->replace(['search' => 'department']);

    try {
        $html = view('components.list-toolbar', [
            'action' => url('/departments'),
            'searchPlaceholder' => 'ค้นหาชื่อแผนก...',
            'sortOptions' => [],
            'filters' => [],
            'resetLabel' => 'ล้างตัวกรอง',
        ])->render();
    } finally {
        $request->query->replace($originalQuery);
    }

    expect($html)
        ->toContain('data-auto-search-form')
        ->toContain('data-auto-search-input')
        ->toContain('data-auto-search-clear')
        ->toContain('data-auto-submit-select')
        ->toContain('aria-label="ล้างคำค้นหา"');
});

test('shared list toolbar hides the clear action when search is empty', function () {
    $request = request();
    $originalQuery = $request->query->all();
    $request->query->replace([]);

    try {
        $html = view('components.list-toolbar', [
            'action' => url('/departments'),
            'searchPlaceholder' => 'ค้นหาชื่อแผนก...',
            'sortOptions' => [],
            'filters' => [],
            'resetLabel' => 'ล้างตัวกรอง',
        ])->render();
    } finally {
        $request->query->replace($originalQuery);
    }

    expect($html)
        ->toContain('data-auto-search-form')
        ->toContain('data-auto-search-input');

    $this->assertStringNotContainsString('aria-label="ล้างคำค้นหา"', $html);
    $this->assertStringNotContainsString('type="button" class="btn btn-link position-absolute top-50 end-0 translate-middle-y pe-3 text-muted text-decoration-none" aria-label="ล้างคำค้นหา" title="ล้างคำค้นหา" data-auto-search-clear', $html);
    $this->assertStringNotContainsString('onchange="this.form.submit()"', $html);
});
