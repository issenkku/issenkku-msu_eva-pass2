<?php

use App\Http\Controllers\Evaluatee\EvaluationWorkloadController;
use App\Models\Assignments;
use App\Models\CriteriaVersion;
use App\Models\EvaluationList;
use App\Models\QuantityScore;
use App\Models\QuantitySubCriteria;
use App\Models\QuantitySubCriteriaGroup;
use App\Models\QuantitySubCriteriaItem;
use App\Models\ReportData;
use App\Models\Reports;
use App\Models\Subject;
use App\Models\User;
use App\Models\WorkloadEntry;
use App\Models\WorkloadForm;
use App\Models\WorkloadFormField;
use App\Support\EvaluateeWorkloadLiveData;
use Illuminate\Http\Request;

test('builds live workload data', function () {
    $evaluatee = User::factory()->create();
    $criteriaVersion = CriteriaVersion::factory()->create();
    $evaluationList = EvaluationList::factory()->create([
        'criteria_version_id' => $criteriaVersion->id,
    ]);
    $report = Reports::factory()->create([
        'status' => 'Draft',
        'report_data_id' => ReportData::factory()->create([
            'criteria_version_id' => $criteriaVersion->id,
        ])->id,
    ]);
    Assignments::factory()->create([
        'report_id' => $report->id,
        'evaluatee_id' => $evaluatee->id,
    ]);
    $quantitySubCriteria = QuantitySubCriteria::factory()->create([
        'criteria_version_id' => $criteriaVersion->id,
        'evaluation_list_id' => $evaluationList->id,
    ]);
    $group = QuantitySubCriteriaGroup::create([
        'name' => 'Teaching',
        'sequence' => 1,
        'quantity_sub_criteria_id' => $quantitySubCriteria->id,
        'criteria_version_id' => $criteriaVersion->id,
        'evaluation_list_id' => $evaluationList->id,
    ]);
    $item = QuantitySubCriteriaItem::create([
        'name' => 'Lecture',
        'sequence' => 1,
        'quantity_sub_criteria_group_id' => $group->id,
        'criteria_version_id' => $criteriaVersion->id,
        'evaluation_list_id' => $evaluationList->id,
    ]);
    $form = WorkloadForm::create([
        'formula_logic' => 'hours * rate',
        'quantity_sub_criteria_id' => $quantitySubCriteria->id,
        'quantity_sub_criteria_item_id' => $item->id,
    ]);
    foreach (['hours', 'rate'] as $variableName) {
        WorkloadFormField::create([
            'label' => ucfirst($variableName),
            'variable_name' => $variableName,
            'field_type' => 'number',
            'workload_form_id' => $form->id,
        ]);
    }
    $subject = Subject::create([
        'code' => 'CS101',
        'name_th' => 'Programming',
        'credits' => 3,
        'is_active' => true,
    ]);
    WorkloadEntry::create([
        'field_values' => ['hours' => 3, 'rate' => 2],
        'calculated_score' => 6,
        'report_id' => $report->id,
        'workload_form_id' => $form->id,
        'subject_id' => $subject->id,
    ]);

    $liveData = EvaluateeWorkloadLiveData::build(
        $quantitySubCriteria->load('groups.items'),
        collect([$form->load(['fields', 'items', 'subCriteriaItem.group'])]),
        $report->id,
    );

    expect($liveData['workloadTotalScore'])->toBe(6.0)
        ->and($liveData['workloadView']['total_display'])->toBe('6.00')
        ->and($liveData['workloadView']['groups']->first()['items']->first()['rows'])->toHaveCount(1);
});

test('stores the aggregate workload score', function () {
    $criteriaVersion = CriteriaVersion::factory()->create();
    $evaluationList = EvaluationList::factory()->create([
        'criteria_version_id' => $criteriaVersion->id,
        'quantity_enabled' => true,
    ]);
    $report = Reports::factory()->create([
        'status' => 'Draft',
        'report_data_id' => ReportData::factory()->create([
            'criteria_version_id' => $criteriaVersion->id,
        ])->id,
    ]);
    $quantitySubCriteria = QuantitySubCriteria::factory()->create([
        'criteria_version_id' => $criteriaVersion->id,
        'evaluation_list_id' => $evaluationList->id,
        'score_a' => 10,
        'score_b' => 20,
    ]);
    $form = WorkloadForm::create([
        'formula_logic' => 'hours * rate',
        'quantity_sub_criteria_id' => $quantitySubCriteria->id,
    ]);
    $subject = Subject::create([
        'code' => 'CS102',
        'name_th' => 'Data Structures',
        'credits' => 3,
        'is_active' => true,
    ]);
    WorkloadEntry::create([
        'field_values' => ['hours' => 3, 'rate' => 2],
        'calculated_score' => 6,
        'report_id' => $report->id,
        'workload_form_id' => $form->id,
        'subject_id' => $subject->id,
    ]);

    $request = Request::create('/evaluation-workload/score', 'POST', [
        'report_id' => $report->id,
        'quantity_sub_criteria_id' => $quantitySubCriteria->id,
    ]);
    app(EvaluationWorkloadController::class)->storeWorkloadScore($request);

    $quantityScore = QuantityScore::query()
        ->where('report_id', $report->id)
        ->where('quantity_sub_criteria_id', $quantitySubCriteria->id)
        ->firstOrFail();

    expect((float) $quantityScore->score_C)->toBe(6.0)
        ->and((float) $quantityScore->score_D)->toBe(3.0);
});
