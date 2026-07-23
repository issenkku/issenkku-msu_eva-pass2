<?php

use App\Exports\ReportsExport;
use App\Models\Assignments;
use App\Models\CriteriaVersion;
use App\Models\EvaluationList;
use App\Models\QualityScore;
use App\Models\QualitySubCriteria;
use App\Models\QuantityScore;
use App\Models\QuantitySubCriteria;
use App\Models\ReportData;
use App\Models\Reports;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('dashboard export includes support score summaries and role comments', function () {
    $report = Reports::factory()->create([
        'status' => 'Completed',
        'support_score_total' => 112.50,
        'support_achievement_score' => 22.50,
        'comment' => 'ความเห็นรวม',
        'evaluator_comment' => 'ความเห็นผู้ประเมิน',
        'director_comment' => 'ความเห็นกรรมการ',
        'manager_comment' => 'ความเห็นผู้บริหาร',
    ]);
    $assignment = Assignments::factory()->create(['report_id' => $report->id]);

    $export = new ReportsExport(
        Assignments::query()->where('report_id', $report->id)
    );
    $headings = $export->headings();
    $row = $export->collection()->first();

    expect($headings)
        ->toHaveCount(18)
        ->toContain('ผลรวมคะแนนถ่วงน้ำหนักสายสนับสนุน')
        ->toContain('คะแนนผลสัมฤทธิ์ของงาน')
        ->toContain('ความเห็นผู้ประเมิน')
        ->toContain('ความเห็นกรรมการ')
        ->toContain('ความเห็นผู้บริหาร')
        ->and($row[6])->toBe(100.0)
        ->and($row[9])->toBe(112.5)
        ->and($row[10])->toBe(22.5)
        ->and($row)->toContain('ความเห็นผู้ประเมิน', 'ความเห็นกรรมการ', 'ความเห็นผู้บริหาร');
});

test('dashboard export grand total includes quantity quality and capped support', function () {
    $criteriaVersion = CriteriaVersion::factory()->create();
    $reportData = ReportData::factory()->create([
        'criteria_version_id' => $criteriaVersion->id,
    ]);
    $report = Reports::factory()->create([
        'report_data_id' => $reportData->id,
        'status' => 'Completed',
        'support_score_total' => 4.30,
        'support_achievement_score' => 0.86,
    ]);
    $evaluationList = EvaluationList::factory()->create([
        'criteria_version_id' => $criteriaVersion->id,
        'sum_score' => 10,
    ]);
    $quantitySubCriteria = QuantitySubCriteria::factory()->create([
        'criteria_version_id' => $criteriaVersion->id,
        'evaluation_list_id' => $evaluationList->id,
    ]);
    $qualitySubCriteria = QualitySubCriteria::factory()->create([
        'criteria_version_id' => $criteriaVersion->id,
        'evaluation_list_id' => $evaluationList->id,
    ]);
    QuantityScore::factory()->create([
        'report_id' => $report->id,
        'quantity_sub_criteria_id' => $quantitySubCriteria->id,
        'score_D' => 2,
    ]);
    QualityScore::factory()->create([
        'report_id' => $report->id,
        'quality_sub_criteria_id' => $qualitySubCriteria->id,
        'score' => 3,
    ]);
    $assignment = Assignments::factory()->create(['report_id' => $report->id]);

    $row = (new ReportsExport(
        Assignments::query()->where('report_id', $report->id)
    ))->collection()->first();

    expect($row[6])->toBe(9.3)
        ->and($row[7])->toBe(2.0)
        ->and($row[8])->toBe(3.0)
        ->and($row[9])->toBe(4.3)
        ->and($row[10])->toBe(0.86);
});
