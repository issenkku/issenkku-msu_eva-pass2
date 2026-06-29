<?php

test('dashboard back button renders as a link', function () {
    $html = view('dashboard.partials.show-back-button')->render();

    expect($html)
        ->toContain('<a href="' . route('dashboard') . '"')
        ->toContain('class="btn-back"')
        ->toContain('<i class="fas fa-arrow-left"></i>')
        ->toContain('ย้อนกลับ')
        ->not->toContain('<button type="button"');
});

test('evaluator dashboard back button renders as a link', function () {
    $html = view('evaluator_dashboard.partials.show-back-button')->render();

    expect($html)
        ->toContain('<a href="' . route('evaluator.index') . '"')
        ->toContain('class="btn-back"')
        ->toContain('<i class="fas fa-arrow-left"></i>')
        ->toContain('ย้อนกลับ')
        ->not->toContain('<button type="button"');
});
