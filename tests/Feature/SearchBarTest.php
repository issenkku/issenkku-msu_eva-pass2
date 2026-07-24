<?php

test('shared search bar shows a clear action when a search term is present', function () {
    $request = request();
    $originalQuery = $request->query->all();
    $request->query->replace(['search' => 'annual']);

    try {
        $html = view('components.search-bar', [
            'placeholder' => 'ค้นหาในหน้านี้: ชื่อ, ชื่องาน, ผู้ประเมิน...',
        ])->render();
    } finally {
        $request->query->replace($originalQuery);
    }

    expect($html)
        ->toContain('role="search"')
        ->toContain('data-auto-search-form')
        ->toContain('data-auto-search-input')
        ->toContain('name="search"')
        ->toContain('aria-label="ค้นหาในหน้านี้: ชื่อ, ชื่องาน, ผู้ประเมิน..."')
        ->toContain('search-bar-clear')
        ->toContain('aria-label="ล้างคำค้นหา"')
        ->toContain('data-auto-search-clear')
        ->not->toContain('onclick="this.form.search.value=\'\'; this.form.submit();"');
});

test('shared search bar hides the clear action when search is empty', function () {
    $request = request();
    $originalQuery = $request->query->all();
    $request->query->replace([]);

    try {
        $html = view('components.search-bar', [
            'placeholder' => 'ค้นหา, รายงาน...',
        ])->render();
    } finally {
        $request->query->replace($originalQuery);
    }

    expect($html)
        ->toContain('role="search"')
        ->toContain('data-auto-search-form')
        ->toContain('data-auto-search-input')
        ->toContain('aria-label="ค้นหา, รายงาน..."');

    $this->assertStringNotContainsString('search-bar-clear', $html);
    $this->assertStringNotContainsString('ล้างคำค้นหา', $html);
});

test('shared search bar accepts an optional input class', function () {
    $html = view('components.search-bar', [
        'placeholder' => 'ค้นหา',
        'inputClass' => 'bg-white',
    ])->render();

    expect($html)
        ->toContain('search-bar-input')
        ->toContain('bg-white');
});

test('user management search opts into a white input background', function () {
    $source = file_get_contents(
        resource_path('views/user/management/partials/index-search-section.blade.php')
    );

    expect($source)->toContain('input-class="bg-white"');
});
