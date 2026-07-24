<?php

it('constrains the evaluatee dashboard to a centered 1440 pixel width', function () {
    $dashboard = file_get_contents(resource_path('views/evaluatee/dashboard.blade.php'));

    expect($dashboard)
        ->toContain('class="max-w-[1440px] mx-auto space-y-6"')
        ->not->toContain('max-w-7xl')
        ->not->toContain('max-w-8xl');
});

it('aligns evaluatee dashboard cards without forced stretching', function () {
    $unfinishedAssignments = file_get_contents(
        resource_path('views/evaluatee/partials/unfinished-assignments.blade.php')
    );
    $overview = file_get_contents(
        resource_path('views/evaluatee/partials/overview-panel.blade.php')
    );

    expect($unfinishedAssignments)->not->toContain('mx-5');

    expect($overview)
        ->toContain('grid grid-cols-1 items-start gap-6 2xl:grid-cols-[minmax(0,1fr),300px,300px]')
        ->not->toContain('2xl:grid-cols-[minmax(0,1fr),340px,340px]')
        ->not->toContain('items-stretch')
        ->not->toContain('h-full');
});
