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
use Laravel\Sanctum\Sanctum;
use Spatie\Permission\Models\Role;

function workloadAsyncSaveContext(): array
{
    Role::findOrCreate('ผู้รับการประเมิน');

    $evaluatee = User::factory()->create();
    $evaluatee->assignRole('ผู้รับการประเมิน');
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
        'require_subject' => false,
        'require_evidence' => false,
    ]);
    $group = QuantitySubCriteriaGroup::create([
        'name' => 'Async teaching group',
        'sequence' => 1,
        'quantity_sub_criteria_id' => $quantitySubCriteria->id,
        'criteria_version_id' => $criteriaVersion->id,
        'evaluation_list_id' => $evaluationList->id,
    ]);
    $item = QuantitySubCriteriaItem::create([
        'name' => 'Async teaching entry',
        'sequence' => 1,
        'quantity_sub_criteria_group_id' => $group->id,
        'criteria_version_id' => $criteriaVersion->id,
        'evaluation_list_id' => $evaluationList->id,
        'require_subject' => false,
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

    return compact('evaluatee', 'report', 'form', 'item');
}

function workloadAsyncSavePayload(Reports $report, WorkloadForm $form, int $hours = 2, int $rate = 3): array
{
    return [
        'report_id' => $report->id,
        'workload_form_id' => $form->id,
        'field_values' => [
            'hours' => $hours,
            'rate' => $rate,
        ],
    ];
}

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

test('JSON create returns saved workload fragments and total', function () {
    ['evaluatee' => $evaluatee, 'report' => $report, 'form' => $form, 'item' => $item] = workloadAsyncSaveContext();
    Sanctum::actingAs($evaluatee);

    $response = $this->postJson(
        route('evaluatee.workload-entries.store'),
        workloadAsyncSavePayload($report, $form),
    );

    $response
        ->assertOk()
        ->assertJsonStructure(['message', 'panels_html', 'summary_html', 'total_score', 'active_item_id'])
        ->assertJsonPath('total_score', 6)
        ->assertJsonPath('active_item_id', $item->id);

    expect($response->json('total_score'))->toBeNumeric()
        ->and($response->json('panels_html'))->toBeString()->toContain('Async teaching entry')->toContain('6.00')
        ->toContain('data-workload-item-id="'.$item->id.'"')
        ->toContain('data-item-id="'.$item->id.'"')
        ->and($response->json('summary_html'))->toBeString()->toContain('6.00');
});

test('redirect fallback keeps the existing create response', function () {
    ['evaluatee' => $evaluatee, 'report' => $report, 'form' => $form] = workloadAsyncSaveContext();
    Sanctum::actingAs($evaluatee);

    $this
        ->post(route('evaluatee.workload-entries.store'), workloadAsyncSavePayload($report, $form))
        ->assertRedirect()
        ->assertSessionHas('success');
});

test('JSON update returns updated workload fragments and total', function () {
    ['evaluatee' => $evaluatee, 'report' => $report, 'form' => $form] = workloadAsyncSaveContext();
    $entry = WorkloadEntry::create([
        ...workloadAsyncSavePayload($report, $form),
        'calculated_score' => 6,
    ]);
    Sanctum::actingAs($evaluatee);

    $response = $this->putJson(
        route('evaluatee.workload-entries.update', $entry->id),
        workloadAsyncSavePayload($report, $form, 4, 3),
    );

    $response
        ->assertOk()
        ->assertJsonStructure(['message', 'panels_html', 'summary_html', 'total_score', 'active_item_id'])
        ->assertJsonPath('total_score', 12)
        ->assertJsonPath('active_item_id', null);

    expect($response->json('panels_html'))->toBeString()->toContain('Async teaching entry')->toContain('12.00')
        ->and($response->json('summary_html'))->toBeString()->toContain('12.00');

    expect(WorkloadEntry::where('report_id', $report->id)->count())->toBe(1);
});

test('JSON update returns not found instead of following a redirect', function () {
    ['evaluatee' => $evaluatee, 'report' => $report, 'form' => $form] = workloadAsyncSaveContext();
    Sanctum::actingAs($evaluatee);

    $this
        ->putJson(
            route('evaluatee.workload-entries.update', 999999999),
            workloadAsyncSavePayload($report, $form),
        )
        ->assertNotFound()
        ->assertJsonPath('message', 'ไม่พบข้อมูลภาระงานที่ต้องการแก้ไข');
});
