<?php

use App\Models\CriteriaVersion;
use App\Models\EvaluationList;
use App\Models\QualityMainCriteria;
use App\Models\QualityScore;
use App\Models\QualitySubCriteria;
use App\Models\QuantityMainCriteria;
use App\Models\QuantityScore;
use App\Models\QuantitySubCriteria;
use App\Models\ReportData;
use App\Models\Reports;
use App\Services\ScoreService;
use App\Support\EvaluationScoreSummary;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;

uses(RefreshDatabase::class);

test('evaluation summary caps the support total at five and achievement at one', function () {
    $summary = EvaluationScoreSummary::fromCategoryItems([[
        'evaluation_lists' => [[
            'quantity_items' => [],
            'quality_items' => [],
            'support_items' => [
                ['weighted_score' => 70],
                ['weighted_score' => 42.5],
                ['weighted_score' => null],
            ],
        ]],
    ]]);

    expect($summary['support_raw'])->toBe(5.0)
        ->and($summary['support'])->toBe(5.0)
        ->and($summary['support_achievement'])->toBe(1.0)
        ->and($summary['total'])->toBe(5.0);
});

test('evaluation summary exposes criterion presence independently from zero scores', function () {
    $summary = EvaluationScoreSummary::fromCategoryItems([[
        'evaluation_lists' => [[
            'quantity_items' => [[
                'sub_criterias' => [['score_d' => 0]],
            ]],
            'quality_items' => [],
            'support_items' => [[
                'weighted_score' => null,
            ]],
            'sum_score' => 0,
        ]],
    ]]);

    expect($summary)
        ->toHaveKeys(['has_quantity', 'has_quality', 'has_support'])
        ->and($summary['has_quantity'])->toBeTrue()
        ->and($summary['has_quality'])->toBeFalse()
        ->and($summary['has_support'])->toBeTrue()
        ->and($summary['quantity'])->toBe(0.0)
        ->and($summary['support'])->toBe(0.0);
});

test('evaluation summary derives support achievement without changing the overall total', function () {
    $summary = EvaluationScoreSummary::fromCategoryItems([[
        'evaluation_lists' => [[
            'quantity_items' => [],
            'quality_items' => [],
            'support_items' => [['weighted_score' => 4.30]],
        ]],
    ]]);

    expect($summary['support'])->toBe(4.3)
        ->and($summary['support_achievement'])->toBe(0.86)
        ->and($summary['support_target_level_count'])->toBe(5)
        ->and($summary['total'])->toBe(4.3);
});

test('evaluation summary caps quantity at each evaluation list sum score', function () {
    $summary = EvaluationScoreSummary::fromCategoryItems([[
        'evaluation_lists' => [
            [
                'quantity_enabled' => true,
                'quantity_items' => [[
                    'sub_criterias' => [
                        ['score_d' => 55.15],
                        ['score_d' => 20],
                    ],
                ]],
                'quality_items' => [],
                'support_items' => [],
                'sum_score' => 40,
            ],
            [
                'quantity_enabled' => false,
                'quantity_items' => [],
                'quality_items' => [[
                    'sub_criterias' => [['score' => 29.65]],
                ]],
                'support_items' => [],
                'sum_score' => 30,
            ],
        ],
    ]]);

    expect($summary['quantity'])->toBe(40.0)
        ->and($summary['quality'])->toBe(29.65)
        ->and($summary['total'])->toBe(69.65);
});

test('bulk quantity score calculation caps each active list at its configured sum score', function () {
    $criteriaVersion = CriteriaVersion::factory()->create();
    $reportData = ReportData::factory()->create([
        'criteria_version_id' => $criteriaVersion->id,
    ]);
    $report = Reports::factory()->create([
        'report_data_id' => $reportData->id,
        'status' => 'Completed',
    ]);
    $main = QuantityMainCriteria::factory()->create([
        'criteria_version_id' => $criteriaVersion->id,
    ]);

    $cappedList = EvaluationList::factory()->create([
        'criteria_version_id' => $criteriaVersion->id,
        'quantity_enabled' => true,
        'sum_score' => 40,
    ]);
    $secondList = EvaluationList::factory()->create([
        'criteria_version_id' => $criteriaVersion->id,
        'quantity_enabled' => true,
        'sum_score' => 10,
    ]);
    $disabledList = EvaluationList::factory()->create([
        'criteria_version_id' => $criteriaVersion->id,
        'quantity_enabled' => false,
        'sum_score' => 100,
    ]);

    foreach ([
        [$cappedList, 75.15, 100],
        [$secondList, 5, 10],
        [$disabledList, 99, 100],
    ] as [$list, $score, $maximum]) {
        $subCriteria = QuantitySubCriteria::factory()->create([
            'criteria_version_id' => $criteriaVersion->id,
            'quantity_main_criteria_id' => $main->id,
            'evaluation_list_id' => $list->id,
            'score_a' => $maximum,
        ]);

        QuantityScore::factory()->create([
            'report_id' => $report->id,
            'quantity_sub_criteria_id' => $subCriteria->id,
            'score_D' => $score,
        ]);
    }

    $scores = ScoreService::calculateQuantityScoresRawByReportIds([$report->id]);

    expect((float) $scores[$report->id])->toBe(45.0);
});

test('dashboard and evaluation summary cap each quantity criterion before calculating the total', function () {
    $criteriaVersion = CriteriaVersion::factory()->create();
    $reportData = ReportData::factory()->create(['criteria_version_id' => $criteriaVersion->id]);
    $report = Reports::factory()->create(['report_data_id' => $reportData->id]);
    $quantityList = EvaluationList::factory()->create([
        'criteria_version_id' => $criteriaVersion->id,
        'quantity_enabled' => true,
        'sum_score' => 40,
    ]);
    $subCriteria = QuantitySubCriteria::factory()->create([
        'criteria_version_id' => $criteriaVersion->id,
        'evaluation_list_id' => $quantityList->id,
        'score_a' => 30,
    ]);
    QuantityScore::factory()->create([
        'report_id' => $report->id,
        'quantity_sub_criteria_id' => $subCriteria->id,
        'score_D' => 40,
    ]);

    $qualityList = EvaluationList::factory()->create([
        'criteria_version_id' => $criteriaVersion->id,
        'quantity_enabled' => false,
        'sum_score' => 30,
    ]);
    $qualitySubCriteria = QualitySubCriteria::factory()->create([
        'criteria_version_id' => $criteriaVersion->id,
        'evaluation_list_id' => $qualityList->id,
        'num_score' => 30,
    ]);
    QualityScore::factory()->create([
        'report_id' => $report->id,
        'quality_sub_criteria_id' => $qualitySubCriteria->id,
        'score' => 29.79,
    ]);

    $quantityScores = ScoreService::calculateQuantityScoresRawByReportIds([$report->id]);
    $qualityScores = ScoreService::calculateQualityScoresRawByReportIds([$report->id]);
    $detailSummary = EvaluationScoreSummary::fromCategoryItems([[
        'evaluation_lists' => [
            [
                'quantity_enabled' => true,
                'quantity_items' => [[
                    'sub_criterias' => [[
                        'score_a' => 30,
                        'score_d' => 40,
                    ]],
                ]],
                'quality_items' => [],
                'support_items' => [],
                'sum_score' => 40,
            ],
            [
                'quantity_enabled' => false,
                'quantity_items' => [],
                'quality_items' => [[
                    'sub_criterias' => [['score' => 29.79]],
                ]],
                'support_items' => [],
                'sum_score' => 30,
            ],
        ],
    ]]);

    expect((float) $quantityScores[$report->id])->toBe(30.0)
        ->and((float) $qualityScores[$report->id])->toBe(29.79)
        ->and($detailSummary['quantity'])->toBe(30.0)
        ->and($detailSummary['quality'])->toBe(29.79)
        ->and($detailSummary['total'])->toBe(59.79)
        ->and(round($quantityScores[$report->id] + $qualityScores[$report->id], 2))->toBe(59.79);
});

test('quality score calculations cap each score at its configured maximum', function () {
    $criteriaVersion = CriteriaVersion::factory()->create();
    $reportData = ReportData::factory()->create(['criteria_version_id' => $criteriaVersion->id]);
    $report = Reports::factory()->create(['report_data_id' => $reportData->id]);
    $list = EvaluationList::factory()->create([
        'criteria_version_id' => $criteriaVersion->id,
        'sum_score' => 30,
    ]);
    $subCriteria = QualitySubCriteria::factory()->create([
        'criteria_version_id' => $criteriaVersion->id,
        'evaluation_list_id' => $list->id,
        'num_score' => 6,
    ]);
    QualityScore::factory()->create([
        'report_id' => $report->id,
        'quality_sub_criteria_id' => $subCriteria->id,
        'score' => 9,
    ]);

    $bulkScores = ScoreService::calculateQualityScoresRawByReportIds([$report->id]);

    expect($bulkScores[$report->id])->toBe(6.0)
        ->and(ScoreService::calculateQualityScoreRaw($report->id))->toBe(6.0);
});

test('bulk quality score calculation matches single report calculation', function () {
    $criteriaVersion = CriteriaVersion::factory()->create();
    $reportData = ReportData::factory()->create([
        'criteria_version_id' => $criteriaVersion->id,
    ]);

    $firstReport = Reports::factory()->create([
        'report_data_id' => $reportData->id,
        'status' => 'Completed',
    ]);
    $secondReport = Reports::factory()->create([
        'report_data_id' => $reportData->id,
        'status' => 'Completed',
    ]);
    $emptyReport = Reports::factory()->create([
        'report_data_id' => $reportData->id,
        'status' => 'Completed',
    ]);

    $firstList = EvaluationList::factory()->create([
        'criteria_version_id' => $criteriaVersion->id,
        'sum_score' => 10,
    ]);
    $secondList = EvaluationList::factory()->create([
        'criteria_version_id' => $criteriaVersion->id,
        'sum_score' => 5,
    ]);

    $firstListSubCriteriaA = QualitySubCriteria::factory()->create([
        'criteria_version_id' => $criteriaVersion->id,
        'evaluation_list_id' => $firstList->id,
        'num_score' => 100,
    ]);
    $firstListSubCriteriaB = QualitySubCriteria::factory()->create([
        'criteria_version_id' => $criteriaVersion->id,
        'evaluation_list_id' => $firstList->id,
        'num_score' => 100,
    ]);
    $secondListSubCriteria = QualitySubCriteria::factory()->create([
        'criteria_version_id' => $criteriaVersion->id,
        'evaluation_list_id' => $secondList->id,
        'num_score' => 100,
    ]);

    QualityScore::factory()->create([
        'report_id' => $firstReport->id,
        'quality_sub_criteria_id' => $firstListSubCriteriaA->id,
        'score' => 8,
    ]);
    QualityScore::factory()->create([
        'report_id' => $firstReport->id,
        'quality_sub_criteria_id' => $firstListSubCriteriaB->id,
        'score' => 7,
    ]);
    QualityScore::factory()->create([
        'report_id' => $firstReport->id,
        'quality_sub_criteria_id' => $secondListSubCriteria->id,
        'score' => 3,
    ]);
    QualityScore::factory()->create([
        'report_id' => $secondReport->id,
        'quality_sub_criteria_id' => $secondListSubCriteria->id,
        'score' => 4,
    ]);

    $bulkScores = ScoreService::calculateQualityScoresRawByReportIds([
        $firstReport->id,
        $secondReport->id,
        $emptyReport->id,
    ]);

    expect($bulkScores[$firstReport->id])->toBe(ScoreService::calculateQualityScoreRaw($firstReport->id))
        ->and($bulkScores[$secondReport->id])->toBe(ScoreService::calculateQualityScoreRaw($secondReport->id))
        ->and($bulkScores[$emptyReport->id])->toBe(0.0);
});

test('average score calculation uses bulk scores for completed reports only', function () {
    $criteriaVersion = CriteriaVersion::factory()->create();
    $reportData = ReportData::factory()->create([
        'criteria_version_id' => $criteriaVersion->id,
    ]);

    $quantityMainCriteria = QuantityMainCriteria::factory()->create([
        'criteria_version_id' => $criteriaVersion->id,
    ]);
    $quantityList = EvaluationList::factory()->create([
        'criteria_version_id' => $criteriaVersion->id,
        'quantity_enabled' => true,
        'sum_score' => 100,
    ]);
    $quantitySubCriteria = QuantitySubCriteria::factory()->create([
        'criteria_version_id' => $criteriaVersion->id,
        'quantity_main_criteria_id' => $quantityMainCriteria->id,
        'evaluation_list_id' => $quantityList->id,
        'score_a' => 100,
    ]);

    $qualityMainCriteria = QualityMainCriteria::factory()->create([
        'criteria_version_id' => $criteriaVersion->id,
    ]);
    $qualityList = EvaluationList::factory()->create([
        'criteria_version_id' => $criteriaVersion->id,
        'sum_score' => 50,
    ]);
    $qualitySubCriteria = QualitySubCriteria::factory()->create([
        'criteria_version_id' => $criteriaVersion->id,
        'quality_main_criteria_id' => $qualityMainCriteria->id,
        'evaluation_list_id' => $qualityList->id,
        'num_score' => 50,
    ]);

    $completedReportA = Reports::factory()->create([
        'report_data_id' => $reportData->id,
        'status' => 'Completed',
    ]);
    $completedReportB = Reports::factory()->create([
        'report_data_id' => $reportData->id,
        'status' => 'Completed',
    ]);
    $completedReportC = Reports::factory()->create([
        'report_data_id' => $reportData->id,
        'status' => 'Completed',
    ]);
    $completedReportD = Reports::factory()->create([
        'report_data_id' => $reportData->id,
        'status' => 'Completed',
    ]);
    $completedReportE = Reports::factory()->create([
        'report_data_id' => $reportData->id,
        'status' => 'Completed',
    ]);
    $draftReport = Reports::factory()->create([
        'report_data_id' => $reportData->id,
        'status' => 'Draft',
    ]);

    QuantityScore::factory()->create([
        'report_id' => $completedReportA->id,
        'quantity_sub_criteria_id' => $quantitySubCriteria->id,
        'score_D' => 10,
    ]);
    QuantityScore::factory()->create([
        'report_id' => $completedReportB->id,
        'quantity_sub_criteria_id' => $quantitySubCriteria->id,
        'score_D' => 4,
    ]);
    QuantityScore::factory()->create([
        'report_id' => $completedReportC->id,
        'quantity_sub_criteria_id' => $quantitySubCriteria->id,
        'score_D' => 7,
    ]);
    QuantityScore::factory()->create([
        'report_id' => $completedReportD->id,
        'quantity_sub_criteria_id' => $quantitySubCriteria->id,
        'score_D' => 2,
    ]);
    QuantityScore::factory()->create([
        'report_id' => $completedReportE->id,
        'quantity_sub_criteria_id' => $quantitySubCriteria->id,
        'score_D' => 5,
    ]);
    QuantityScore::factory()->create([
        'report_id' => $draftReport->id,
        'quantity_sub_criteria_id' => $quantitySubCriteria->id,
        'score_D' => 100,
    ]);

    QualityScore::factory()->create([
        'report_id' => $completedReportA->id,
        'quality_sub_criteria_id' => $qualitySubCriteria->id,
        'score' => 12,
    ]);
    QualityScore::factory()->create([
        'report_id' => $completedReportB->id,
        'quality_sub_criteria_id' => $qualitySubCriteria->id,
        'score' => 6,
    ]);
    QualityScore::factory()->create([
        'report_id' => $completedReportC->id,
        'quality_sub_criteria_id' => $qualitySubCriteria->id,
        'score' => 15,
    ]);
    QualityScore::factory()->create([
        'report_id' => $completedReportD->id,
        'quality_sub_criteria_id' => $qualitySubCriteria->id,
        'score' => 9,
    ]);
    QualityScore::factory()->create([
        'report_id' => $completedReportE->id,
        'quality_sub_criteria_id' => $qualitySubCriteria->id,
        'score' => 8,
    ]);
    QualityScore::factory()->create([
        'report_id' => $draftReport->id,
        'quality_sub_criteria_id' => $qualitySubCriteria->id,
        'score' => 99,
    ]);

    DB::flushQueryLog();
    DB::enableQueryLog();
    $queryCount = 0;
    $thrownException = null;

    try {
        $averageScore = ScoreService::calculateAverageScore([
            $completedReportA,
            $completedReportB,
            $completedReportC,
            $completedReportD,
            $completedReportE,
            $draftReport,
        ]);

        $queryCount = count(DB::getQueryLog());
    } catch (Throwable $exception) {
        $thrownException = $exception;
    } finally {
        DB::disableQueryLog();
    }

    if ($thrownException) {
        throw $thrownException;
    }

    expect($averageScore)->toBe(15.6)
        ->and($queryCount)->toBeLessThanOrEqual(4);
});

test('highest score calculation uses bulk scores for completed reports only', function () {
    $criteriaVersion = CriteriaVersion::factory()->create();
    $reportData = ReportData::factory()->create([
        'criteria_version_id' => $criteriaVersion->id,
    ]);

    $quantityMainCriteria = QuantityMainCriteria::factory()->create([
        'criteria_version_id' => $criteriaVersion->id,
    ]);
    $quantityList = EvaluationList::factory()->create([
        'criteria_version_id' => $criteriaVersion->id,
        'quantity_enabled' => true,
        'sum_score' => 100,
    ]);
    $quantitySubCriteria = QuantitySubCriteria::factory()->create([
        'criteria_version_id' => $criteriaVersion->id,
        'quantity_main_criteria_id' => $quantityMainCriteria->id,
        'evaluation_list_id' => $quantityList->id,
        'score_a' => 100,
    ]);

    $qualityMainCriteria = QualityMainCriteria::factory()->create([
        'criteria_version_id' => $criteriaVersion->id,
    ]);
    $qualityList = EvaluationList::factory()->create([
        'criteria_version_id' => $criteriaVersion->id,
        'sum_score' => 50,
    ]);
    $qualitySubCriteria = QualitySubCriteria::factory()->create([
        'criteria_version_id' => $criteriaVersion->id,
        'quality_main_criteria_id' => $qualityMainCriteria->id,
        'evaluation_list_id' => $qualityList->id,
        'num_score' => 50,
    ]);

    $completedReportA = Reports::factory()->create([
        'report_data_id' => $reportData->id,
        'status' => 'Completed',
    ]);
    $completedReportB = Reports::factory()->create([
        'report_data_id' => $reportData->id,
        'status' => 'Completed',
    ]);
    $completedReportC = Reports::factory()->create([
        'report_data_id' => $reportData->id,
        'status' => 'Completed',
    ]);
    $draftReport = Reports::factory()->create([
        'report_data_id' => $reportData->id,
        'status' => 'Draft',
    ]);

    QuantityScore::factory()->create([
        'report_id' => $completedReportA->id,
        'quantity_sub_criteria_id' => $quantitySubCriteria->id,
        'score_D' => 10,
    ]);
    QuantityScore::factory()->create([
        'report_id' => $completedReportB->id,
        'quantity_sub_criteria_id' => $quantitySubCriteria->id,
        'score_D' => 4,
    ]);
    QuantityScore::factory()->create([
        'report_id' => $draftReport->id,
        'quantity_sub_criteria_id' => $quantitySubCriteria->id,
        'score_D' => 100,
    ]);

    QualityScore::factory()->create([
        'report_id' => $completedReportA->id,
        'quality_sub_criteria_id' => $qualitySubCriteria->id,
        'score' => 12,
    ]);
    QualityScore::factory()->create([
        'report_id' => $completedReportB->id,
        'quality_sub_criteria_id' => $qualitySubCriteria->id,
        'score' => 6,
    ]);
    QualityScore::factory()->create([
        'report_id' => $draftReport->id,
        'quality_sub_criteria_id' => $qualitySubCriteria->id,
        'score' => 99,
    ]);

    DB::flushQueryLog();
    DB::enableQueryLog();
    $queryCount = 0;
    $thrownException = null;

    try {
        $highestScore = ScoreService::calculateHighestScore([
            $completedReportA,
            $completedReportB,
            $completedReportC,
            $draftReport,
        ]);

        $queryCount = count(DB::getQueryLog());
    } catch (Throwable $exception) {
        $thrownException = $exception;
    } finally {
        DB::disableQueryLog();
    }

    if ($thrownException) {
        throw $thrownException;
    }

    expect($highestScore)->toBe(22.0)
        ->and($queryCount)->toBeLessThanOrEqual(4);
});

test('dashboard aggregate scores include capped support totals', function () {
    $reportWithOverCapSupport = Reports::factory()->create([
        'status' => 'Completed',
        'support_score_total' => 112.5,
    ]);
    $reportWithSupport = Reports::factory()->create([
        'status' => 'Completed',
        'support_score_total' => 4.3,
    ]);

    $reports = [$reportWithOverCapSupport, $reportWithSupport];

    expect(ScoreService::calculateAverageScore($reports))->toBe(4.65)
        ->and(ScoreService::calculateHighestScore($reports))->toBe(5.0);
});
