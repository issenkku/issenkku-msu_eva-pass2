<?php

use App\Models\QualityScore;
use App\Models\QualitySubCriteria;
use App\Models\Reports;
use App\Support\QualityScoreHistoryRecorder;
use Illuminate\Validation\ValidationException;

test('quality recorder tracks add change and removal including reasons', function () {
    $report = Reports::factory()->create();
    $added = QualitySubCriteria::factory()->create();
    $changed = QualitySubCriteria::factory()->create();
    $removed = QualitySubCriteria::factory()->create();

    QualityScore::factory()->create([
        'report_id' => $report->id,
        'quality_sub_criteria_id' => $changed->id,
        'score' => 3,
    ]);
    QualityScore::factory()->create([
        'report_id' => $report->id,
        'quality_sub_criteria_id' => $removed->id,
        'score' => 2,
    ]);

    QualityScoreHistoryRecorder::record(
        $report->id,
        QualityScore::where('report_id', $report->id)->get(),
        [
            [
                'subCriteriaId' => $added->id,
                'score' => 4,
                'modificationReason' => 'เพิ่มเกณฑ์',
                'inputKey' => $added->id,
            ],
            [
                'subCriteriaId' => $changed->id,
                'score' => 5,
                'modificationReason' => 'ปรับคะแนน',
                'inputKey' => $changed->id,
            ],
            [
                'subCriteriaId' => $removed->id,
                'score' => null,
                'modificationReason' => 'ยกเลิกเกณฑ์',
                'inputKey' => $removed->id,
            ],
        ],
        null,
        'ผู้ประเมิน',
        true
    );

    $this->assertDatabaseHas('quality_score_histories', [
        'quality_sub_criteria_id' => $added->id,
        'previous_score' => null,
        'new_score' => '4.00',
        'reason' => 'เพิ่มเกณฑ์',
    ]);
    $this->assertDatabaseHas('quality_score_histories', [
        'quality_sub_criteria_id' => $changed->id,
        'previous_score' => '3.00',
        'new_score' => '5.00',
        'reason' => 'ปรับคะแนน',
    ]);
    $this->assertDatabaseHas('quality_score_histories', [
        'quality_sub_criteria_id' => $removed->id,
        'previous_score' => '2.00',
        'new_score' => null,
        'reason' => 'ยกเลิกเกณฑ์',
    ]);
});

test('quality recorder enforces reviewer reason and allows evaluatee without one', function () {
    $report = Reports::factory()->create();
    $criterion = QualitySubCriteria::factory()->create();
    $snapshot = [[
        'subCriteriaId' => $criterion->id,
        'score' => 4,
        'modificationReason' => null,
        'inputKey' => 0,
    ]];

    expect(fn () => QualityScoreHistoryRecorder::record(
        $report->id,
        collect(),
        $snapshot,
        null,
        'ผู้ประเมิน',
        true
    ))->toThrow(ValidationException::class);

    QualityScoreHistoryRecorder::record(
        $report->id,
        collect(),
        $snapshot,
        null,
        null,
        false
    );

    $this->assertDatabaseHas('quality_score_histories', [
        'quality_sub_criteria_id' => $criterion->id,
        'previous_score' => null,
        'new_score' => '4.00',
        'reason' => null,
    ]);
});

test('quality recorder ignores unchanged scores', function () {
    $report = Reports::factory()->create();
    $criterion = QualitySubCriteria::factory()->create();
    $old = QualityScore::factory()->create([
        'report_id' => $report->id,
        'quality_sub_criteria_id' => $criterion->id,
        'score' => 4,
    ]);

    QualityScoreHistoryRecorder::record(
        $report->id,
        collect([$old]),
        [[
            'subCriteriaId' => $criterion->id,
            'score' => '4.00',
            'modificationReason' => 'ไม่ควรถูกใช้',
            'inputKey' => $criterion->id,
        ]],
        null,
        'ผู้ประเมิน',
        true
    );

    $this->assertDatabaseCount('quality_score_histories', 0);
});
