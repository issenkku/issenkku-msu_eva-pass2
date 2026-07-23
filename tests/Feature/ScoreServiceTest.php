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

test('evaluation summary caps only the support component at one hundred', function () {
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

    expect($summary['support_raw'])->toBe(112.5)
        ->and($summary['support'])->toBe(100.0)
        ->and($summary['support_achievement'])->toBe(22.5)
        ->and($summary['total'])->toBe(100.0);
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
    ]);
    $firstListSubCriteriaB = QualitySubCriteria::factory()->create([
        'criteria_version_id' => $criteriaVersion->id,
        'evaluation_list_id' => $firstList->id,
    ]);
    $secondListSubCriteria = QualitySubCriteria::factory()->create([
        'criteria_version_id' => $criteriaVersion->id,
        'evaluation_list_id' => $secondList->id,
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
    ]);
    $quantitySubCriteria = QuantitySubCriteria::factory()->create([
        'criteria_version_id' => $criteriaVersion->id,
        'quantity_main_criteria_id' => $quantityMainCriteria->id,
        'evaluation_list_id' => $quantityList->id,
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
    ]);
    $quantitySubCriteria = QuantitySubCriteria::factory()->create([
        'criteria_version_id' => $criteriaVersion->id,
        'quantity_main_criteria_id' => $quantityMainCriteria->id,
        'evaluation_list_id' => $quantityList->id,
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

    expect(ScoreService::calculateAverageScore($reports))->toBe(52.15)
        ->and(ScoreService::calculateHighestScore($reports))->toBe(100.0);
});
