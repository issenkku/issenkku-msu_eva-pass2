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
use App\Services\GraphDataService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;

uses(RefreshDatabase::class);

test('scatter data uses bulk scores for completed reports only', function () {
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
        $scatterData = GraphDataService::scatterData([
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

    expect($scatterData)->toBe([
        ['x' => 1, 'y' => 22.0, 'quantity' => 10.0, 'quality' => 12.0],
        ['x' => 2, 'y' => 10.0, 'quantity' => 4.0, 'quality' => 6.0],
        ['x' => 3, 'y' => 0.0, 'quantity' => 0.0, 'quality' => 0.0],
    ])->and($queryCount)->toBeLessThanOrEqual(4);
});
