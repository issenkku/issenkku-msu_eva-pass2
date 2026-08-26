<?php

test('quantity score columns are widened for workload totals above 999', function () {
    $migrationPath = database_path(
        'migrations/2026_08_25_000001_widen_quantity_score_columns.php'
    );

    expect($migrationPath)->toBeFile();

    $migrationSource = file_get_contents($migrationPath);

    expect($migrationSource)
        ->toContain("decimal('score_C', 10, 4)->nullable()->change()")
        ->toContain("decimal('score_D', 10, 4)->nullable()->change()");
});
