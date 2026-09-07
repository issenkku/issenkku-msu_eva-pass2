<?php

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

test('subject hours migration tolerates columns that already exist', function () {
    $subjectId = DB::table('subjects')->insertGetId([
        'code' => 'MIGRATION-CHECK',
        'name_th' => 'Migration check',
        'credits' => 3,
        'lecture_credits' => 3,
        'lab_credits' => 0,
        'self_study_credits' => 6,
        'lecture_hours' => 3,
        'lab_hours' => 1,
        'self_study_hours' => 6,
    ]);

    $migration = require database_path(
        'migrations/2026_08_06_000001_add_hours_to_subjects_table.php'
    );

    $migration->up();

    expect(Schema::hasColumns('subjects', [
        'lecture_hours',
        'lab_hours',
        'self_study_hours',
    ]))->toBeTrue()
        ->and((array) DB::table('subjects')->find($subjectId))
        ->toMatchArray([
            'lecture_hours' => 3,
            'lab_hours' => 1,
            'self_study_hours' => 6,
        ]);
});
