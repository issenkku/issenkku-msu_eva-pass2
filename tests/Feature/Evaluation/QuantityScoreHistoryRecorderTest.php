<?php

use App\Models\QuantityScore;
use App\Models\QuantitySubCriteria;
use App\Models\Reports;
use App\Support\QuantityScoreHistoryRecorder;
use Illuminate\Validation\ValidationException;

test('quantity recorder tracks add change and removal including a reason', function () {
    $report = Reports::factory()->create();
    $added = QuantitySubCriteria::factory()->create();
    $changed = QuantitySubCriteria::factory()->create();
    $removed = QuantitySubCriteria::factory()->create();

    QuantityScore::factory()->create([
        'report_id' => $report->id,
        'quantity_sub_criteria_id' => $changed->id,
        'score_C' => 5,
        'description' => 'เดิม',
    ]);
    QuantityScore::factory()->create([
        'report_id' => $report->id,
        'quantity_sub_criteria_id' => $removed->id,
        'score_C' => 3,
    ]);

    $oldScores = QuantityScore::where('report_id', $report->id)->get();

    QuantityScoreHistoryRecorder::record(
        $report->id,
        $oldScores,
        [
            [
                'subCriteriaId' => $added->id,
                'scoreC' => 2,
                'description' => null,
                'modificationReason' => 'เพิ่มตามหลักฐาน',
                'inputKey' => $added->id,
            ],
            [
                'subCriteriaId' => $changed->id,
                'scoreC' => 8,
                'description' => 'ใหม่',
                'modificationReason' => 'ปรับตามหลักฐาน',
                'inputKey' => $changed->id,
            ],
            [
                'subCriteriaId' => $removed->id,
                'scoreC' => null,
                'description' => null,
                'modificationReason' => 'ยกเลิกรายการ',
                'inputKey' => $removed->id,
            ],
        ],
        null,
        'ผู้ประเมิน',
        true
    );

    $this->assertDatabaseHas('quantity_score_histories', [
        'quantity_sub_criteria_id' => $added->id,
        'previous_score_c' => null,
        'new_score_c' => '2.00',
        'reason' => 'เพิ่มตามหลักฐาน',
    ]);
    $this->assertDatabaseHas('quantity_score_histories', [
        'quantity_sub_criteria_id' => $changed->id,
        'previous_score_c' => '5.00',
        'new_score_c' => '8.00',
        'reason' => 'ปรับตามหลักฐาน',
    ]);
    $this->assertDatabaseHas('quantity_score_histories', [
        'quantity_sub_criteria_id' => $removed->id,
        'previous_score_c' => '3.00',
        'new_score_c' => null,
        'reason' => 'ยกเลิกรายการ',
    ]);
});

test('quantity recorder requires reviewer reason but not evaluatee reason', function () {
    $report = Reports::factory()->create();
    $criterion = QuantitySubCriteria::factory()->create();
    $snapshot = [[
        'subCriteriaId' => $criterion->id,
        'scoreC' => 4,
        'description' => null,
        'modificationReason' => null,
        'inputKey' => 0,
    ]];

    expect(fn () => QuantityScoreHistoryRecorder::record(
        $report->id,
        collect(),
        $snapshot,
        null,
        'ผู้ประเมิน',
        true
    ))->toThrow(ValidationException::class);

    QuantityScoreHistoryRecorder::record(
        $report->id,
        collect(),
        $snapshot,
        null,
        null,
        false
    );

    $this->assertDatabaseHas('quantity_score_histories', [
        'quantity_sub_criteria_id' => $criterion->id,
        'previous_score_c' => null,
        'new_score_c' => '4.00',
        'reason' => null,
    ]);
});

test('quantity recorder ignores unchanged values', function () {
    $report = Reports::factory()->create();
    $criterion = QuantitySubCriteria::factory()->create();
    $old = QuantityScore::factory()->create([
        'report_id' => $report->id,
        'quantity_sub_criteria_id' => $criterion->id,
        'score_C' => 4,
        'description' => 'เดิม',
    ]);

    QuantityScoreHistoryRecorder::record(
        $report->id,
        collect([$old]),
        [[
            'subCriteriaId' => $criterion->id,
            'scoreC' => '4.00',
            'description' => ' เดิม ',
            'modificationReason' => 'ไม่ควรถูกใช้',
            'inputKey' => $criterion->id,
        ]],
        null,
        'ผู้ประเมิน',
        true
    );

    $this->assertDatabaseCount('quantity_score_histories', 0);
});
