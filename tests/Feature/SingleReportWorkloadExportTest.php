<?php

use App\Exports\ReportsExport;
use App\Exports\SingleReportExport;
use App\Models\Assignments;
use App\Models\Category;
use App\Models\CriteriaVersion;
use App\Models\EvaluationList;
use App\Models\QuantityScore;
use App\Models\QuantitySubCriteria;
use App\Models\QuantitySubCriteriaGroup;
use App\Models\QuantitySubCriteriaItem;
use App\Models\ReportData;
use App\Models\Reports;
use App\Models\WorkloadEntry;
use App\Models\WorkloadForm;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Maatwebsite\Excel\Excel as ExcelFormat;
use Maatwebsite\Excel\Facades\Excel;

uses(RefreshDatabase::class);

test('single report category exports a separate total for each workload item and caps the list total', function () {
    $criteriaVersion = CriteriaVersion::factory()->create();
    $reportData = ReportData::factory()->create([
        'criteria_version_id' => $criteriaVersion->id,
    ]);
    $report = Reports::factory()->create([
        'report_data_id' => $reportData->id,
        'status' => 'Completed',
    ]);
    $category = Category::factory()->create([
        'criteria_version_id' => $criteriaVersion->id,
        'sequence' => 1,
    ]);
    $evaluationList = EvaluationList::factory()->create([
        'criteria_version_id' => $criteriaVersion->id,
        'categorie_id' => $category->id,
        'quantity_enabled' => true,
        'sum_score' => 10,
        'sequence' => 1,
        'name' => 'หัวข้องานสอน',
    ]);
    $subCriteria = QuantitySubCriteria::factory()->create([
        'criteria_version_id' => $criteriaVersion->id,
        'evaluation_list_id' => $evaluationList->id,
        'score_a' => 1,
        'score_b' => 1,
        'sequence' => 1,
        'name' => 'ภาระงานด้านการสอน',
    ]);
    $group = QuantitySubCriteriaGroup::create([
        'name' => 'การสอนปฏิบัติ',
        'sequence' => 1,
        'quantity_sub_criteria_id' => $subCriteria->id,
        'criteria_version_id' => $criteriaVersion->id,
        'evaluation_list_id' => $evaluationList->id,
    ]);
    $firstItem = QuantitySubCriteriaItem::create([
        'name' => 'สอนรายวิชาปฏิบัติ',
        'sequence' => 1,
        'score_a' => 1,
        'score_b' => 1,
        'quantity_sub_criteria_group_id' => $group->id,
        'criteria_version_id' => $criteriaVersion->id,
        'evaluation_list_id' => $evaluationList->id,
    ]);
    $secondItem = QuantitySubCriteriaItem::create([
        'name' => 'ควบคุมสัมมนา',
        'sequence' => 2,
        'score_a' => 1,
        'score_b' => 1,
        'quantity_sub_criteria_group_id' => $group->id,
        'criteria_version_id' => $criteriaVersion->id,
        'evaluation_list_id' => $evaluationList->id,
    ]);
    $firstForm = WorkloadForm::create([
        'formula_logic' => 'hours',
        'quantity_sub_criteria_id' => $subCriteria->id,
        'quantity_sub_criteria_item_id' => $firstItem->id,
    ]);
    $secondForm = WorkloadForm::create([
        'formula_logic' => 'hours',
        'quantity_sub_criteria_id' => $subCriteria->id,
        'quantity_sub_criteria_item_id' => $secondItem->id,
    ]);

    WorkloadEntry::create([
        'field_values' => [],
        'calculated_score' => 3.50,
        'report_id' => $report->id,
        'workload_form_id' => $firstForm->id,
        'subject_id' => null,
    ]);
    WorkloadEntry::create([
        'field_values' => [],
        'calculated_score' => 2.25,
        'report_id' => $report->id,
        'workload_form_id' => $firstForm->id,
        'subject_id' => null,
    ]);
    WorkloadEntry::create([
        'field_values' => [],
        'calculated_score' => 1.75,
        'report_id' => $report->id,
        'workload_form_id' => $secondForm->id,
        'subject_id' => null,
    ]);
    QuantityScore::factory()->create([
        'report_id' => $report->id,
        'quantity_sub_criteria_id' => $subCriteria->id,
        'score_C' => 12,
        'score_D' => 12,
    ]);

    $assignment = Assignments::factory()->create(['report_id' => $report->id]);
    $assignment->load('report', 'assignmentData', 'evaluateeUser');

    $rows = (new SingleReportExport($assignment))
        ->sheets()[1]
        ->array();

    expect($rows)
        ->toContain(['หัวข้อ: หัวข้องานสอน', 10.0])
        ->toContain(['    คะแนนรวมภาระงาน: การสอนปฏิบัติ / สอนรายวิชาปฏิบัติ', 5.75])
        ->toContain(['    คะแนนรวมภาระงาน: การสอนปฏิบัติ / ควบคุมสัมมนา', 1.75]);
});

test('overview export keeps separate workload columns when labels are duplicated', function () {
    $criteriaVersion = CriteriaVersion::factory()->create();
    $reportData = ReportData::factory()->create([
        'criteria_version_id' => $criteriaVersion->id,
    ]);
    $category = Category::factory()->create([
        'criteria_version_id' => $criteriaVersion->id,
        'sequence' => 1,
    ]);
    $evaluationList = EvaluationList::factory()->create([
        'criteria_version_id' => $criteriaVersion->id,
        'categorie_id' => $category->id,
        'quantity_enabled' => true,
        'sequence' => 1,
    ]);
    $subCriteria = QuantitySubCriteria::factory()->create([
        'criteria_version_id' => $criteriaVersion->id,
        'evaluation_list_id' => $evaluationList->id,
        'sequence' => 1,
        'name' => 'ภาระงานด้านการสอน',
    ]);
    $group = QuantitySubCriteriaGroup::create([
        'name' => 'การสอนปฏิบัติ',
        'sequence' => 1,
        'quantity_sub_criteria_id' => $subCriteria->id,
        'criteria_version_id' => $criteriaVersion->id,
        'evaluation_list_id' => $evaluationList->id,
    ]);
    $item = QuantitySubCriteriaItem::create([
        'name' => 'สอนรายวิชาปฏิบัติ',
        'sequence' => 1,
        'score_a' => 1,
        'score_b' => 1,
        'quantity_sub_criteria_group_id' => $group->id,
        'criteria_version_id' => $criteriaVersion->id,
        'evaluation_list_id' => $evaluationList->id,
    ]);
    $duplicateItem = QuantitySubCriteriaItem::create([
        'name' => $item->name,
        'sequence' => 2,
        'score_a' => 1,
        'score_b' => 1,
        'quantity_sub_criteria_group_id' => $group->id,
        'criteria_version_id' => $criteriaVersion->id,
        'evaluation_list_id' => $evaluationList->id,
    ]);
    $form = WorkloadForm::create([
        'formula_logic' => 'hours',
        'quantity_sub_criteria_id' => $subCriteria->id,
        'quantity_sub_criteria_item_id' => $item->id,
    ]);
    $duplicateForm = WorkloadForm::create([
        'formula_logic' => 'hours',
        'quantity_sub_criteria_id' => $subCriteria->id,
        'quantity_sub_criteria_item_id' => $duplicateItem->id,
    ]);
    $firstReport = Reports::factory()->create([
        'report_data_id' => $reportData->id,
        'status' => 'Completed',
    ]);
    $secondReport = Reports::factory()->create([
        'report_data_id' => $reportData->id,
        'status' => 'Completed',
    ]);
    Assignments::factory()->create(['report_id' => $firstReport->id]);
    Assignments::factory()->create(['report_id' => $secondReport->id]);

    WorkloadEntry::create([
        'field_values' => [],
        'calculated_score' => 3.50,
        'report_id' => $firstReport->id,
        'workload_form_id' => $form->id,
        'subject_id' => null,
    ]);
    WorkloadEntry::create([
        'field_values' => [],
        'calculated_score' => 1.25,
        'report_id' => $firstReport->id,
        'workload_form_id' => $duplicateForm->id,
        'subject_id' => null,
    ]);
    WorkloadEntry::create([
        'field_values' => [],
        'calculated_score' => 2.25,
        'report_id' => $firstReport->id,
        'workload_form_id' => $form->id,
        'subject_id' => null,
    ]);

    $export = new ReportsExport(Assignments::query()->whereIn('report_id', [
        $firstReport->id,
        $secondReport->id,
    ])->orderBy('id'));
    $heading = 'คะแนนภาระงาน: ภาระงานด้านการสอน / การสอนปฏิบัติ / สอนรายวิชาปฏิบัติ';
    $headings = $export->headings();
    $rows = $export->collection();
    $workloadColumnIndexes = collect($headings)
        ->filter(fn ($candidate) => $candidate === $heading)
        ->keys()
        ->values();
    $workbook = Excel::raw(
        new ReportsExport(Assignments::query()->whereIn('report_id', [
            $firstReport->id,
            $secondReport->id,
        ])->orderBy('id')),
        ExcelFormat::XLSX,
    );

    expect($workloadColumnIndexes)->toHaveCount(2)
        ->and($rows[0][$workloadColumnIndexes[0]])->toBe(5.75)
        ->and($rows[0][$workloadColumnIndexes[1]])->toBe(1.25)
        ->and($rows[1][$workloadColumnIndexes[0]])->toBe(0.0)
        ->and($rows[1][$workloadColumnIndexes[1]])->toBe(0.0)
        ->and(strlen($workbook))->toBeGreaterThan(0);
});
