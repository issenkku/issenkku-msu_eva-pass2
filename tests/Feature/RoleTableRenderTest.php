<?php

test('role table components can render repeatedly in the same process', function () {
    $props = [
        'evaluations' => new \Illuminate\Pagination\LengthAwarePaginator(collect(), 0, 10, 1, [
            'path' => url('/'),
        ]),
        'statusCounts' => ['รอการกรอกข้อมูล' => 1],
        'years' => collect(),
    ];

    expect(view('components.director-table', $props)->render())->toContain('aria-current="true"');
    expect(view('components.director-table', $props)->render())->toContain('aria-current="true"');

    expect(view('components.evaluator-table', $props)->render())->toContain('aria-current="true"');
    expect(view('components.evaluator-table', $props)->render())->toContain('aria-current="true"');

    expect(view('components.manager-table', $props)->render())->toContain('aria-current="true"');
    expect(view('components.manager-table', $props)->render())->toContain('aria-current="true"');
});
