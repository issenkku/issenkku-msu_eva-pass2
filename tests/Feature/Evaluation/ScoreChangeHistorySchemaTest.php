<?php

use App\Models\QualityScoreHistory;
use App\Models\QuantityScoreHistory;
use Illuminate\Support\Facades\Schema;

test('score change history schema stores per item reasons', function () {
    expect(Schema::hasColumn('quantity_score_histories', 'reason'))->toBeTrue()
        ->and(Schema::hasTable('quality_score_histories'))->toBeTrue();

    foreach ([
        'report_id',
        'quality_sub_criteria_id',
        'previous_score',
        'new_score',
        'reason',
        'modifier_user_id',
        'modifier_role',
    ] as $column) {
        expect(Schema::hasColumn('quality_score_histories', $column))->toBeTrue();
    }

    expect((new QuantityScoreHistory)->getFillable())->toContain('reason')
        ->and((new QualityScoreHistory)->getFillable())->toContain(
            'previous_score',
            'new_score',
            'reason'
        );
});
