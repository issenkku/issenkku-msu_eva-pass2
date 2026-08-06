<?php

use App\Models\AssignmentData;
use App\Models\Assignments;
use App\Models\CriteriaVersion;
use App\Models\EvaluationList;
use App\Models\QuantitySubCriteria;
use App\Models\QuantitySubCriteriaGroup;
use App\Models\QuantitySubCriteriaItem;
use App\Models\ReportData;
use App\Models\Reports;
use App\Models\User;
use App\Models\WorkloadEntry;
use App\Models\WorkloadForm;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Route;
use Spatie\Permission\Models\Role;

uses(RefreshDatabase::class);

function workloadPageAsyncContext(): array
{
    $roleMiddleware = collect(Route::getRoutes()->getByName('evaluatee.dashboard')->gatherMiddleware())
        ->first(fn (string $middleware) => str_starts_with($middleware, 'role:'));
    $roleName = substr($roleMiddleware, strlen('role:'));
    Role::findOrCreate($roleName, 'web');

    $evaluatee = User::factory()->create(['employee_id' => 'WORKLOADASYNC']);
    $evaluatee->assignRole($roleName);
    $criteriaVersion = CriteriaVersion::factory()->create();
    $reportData = ReportData::factory()->create(['criteria_version_id' => $criteriaVersion->id]);
    $evaluationList = EvaluationList::factory()->create([
        'criteria_version_id' => $criteriaVersion->id,
        'quantity_enabled' => true,
    ]);
    $quantitySubCriteria = QuantitySubCriteria::factory()->create([
        'criteria_version_id' => $criteriaVersion->id,
        'evaluation_list_id' => $evaluationList->id,
        'score_a' => 1,
        'score_b' => 1,
    ]);
    $group = QuantitySubCriteriaGroup::create([
        'name' => 'Async group',
        'sequence' => 1,
        'quantity_sub_criteria_id' => $quantitySubCriteria->id,
        'criteria_version_id' => $criteriaVersion->id,
        'evaluation_list_id' => $evaluationList->id,
    ]);
    $item = QuantitySubCriteriaItem::create([
        'name' => 'Async item',
        'sequence' => 1,
        'quantity_sub_criteria_group_id' => $group->id,
        'criteria_version_id' => $criteriaVersion->id,
        'evaluation_list_id' => $evaluationList->id,
        'require_subject' => false,
    ]);
    $form = WorkloadForm::create([
        'formula_logic' => 'hours',
        'quantity_sub_criteria_id' => $quantitySubCriteria->id,
        'quantity_sub_criteria_item_id' => $item->id,
    ]);

    return compact(
        'evaluatee',
        'criteriaVersion',
        'reportData',
        'quantitySubCriteria',
        'item',
        'form',
    );
}

function assignedWorkloadReport(array $context, string $status, string $start, string $end): Reports
{
    $assignmentData = AssignmentData::factory()->create([
        'start_time' => $start,
        'end_time' => $end,
    ]);
    $report = Reports::factory()->create([
        'report_data_id' => $context['reportData']->id,
        'status' => $status,
    ]);
    Assignments::factory()->create([
        'assignment_data_id' => $assignmentData->id,
        'report_id' => $report->id,
        'evaluatee_id' => $context['evaluatee']->id,
    ]);

    return $report;
}

test('evaluatee subject creation returns subject data without redirecting', function () {
    $context = workloadPageAsyncContext();

    $this->actingAs($context['evaluatee'], 'web')
        ->postJson(route('subjects.store.evaluatee'), [
            'code' => 'ASYNC-SUBJECT',
            'name_th' => 'วิชาแบบไม่รีเฟรช',
            'credits' => 3,
            'lecture_credits' => 3,
            'lab_credits' => 0,
            'self_study_credits' => 6,
            'lecture_hours' => 2,
            'lab_hours' => 3,
            'self_study_hours' => 1,
        ])
        ->assertCreated()
        ->assertJsonPath('success', true)
        ->assertJsonPath('data.subject.code', 'ASYNC-SUBJECT')
        ->assertJsonPath('data.subject.lecture_hours', 2)
        ->assertJsonPath('data.subject.lab_hours', 3)
        ->assertJsonPath('data.subject.self_study_hours', 1);
});

test('previous workload import returns live fragments without redirecting', function () {
    $context = workloadPageAsyncContext();
    $previous = assignedWorkloadReport($context, 'Completed', '2025-08-01', '2025-08-31');
    $current = assignedWorkloadReport($context, 'Draft', '2026-08-01', '2026-08-31');
    WorkloadEntry::create([
        'report_id' => $previous->id,
        'workload_form_id' => $context['form']->id,
        'field_values' => ['hours' => 7],
        'calculated_score' => 7,
    ]);

    $this->actingAs($context['evaluatee'], 'web')
        ->postJson(route('evaluatee.import-previous-workload', $current), [
            'source_report_id' => $previous->id,
            'quantity_sub_criteria_id' => $context['quantitySubCriteria']->id,
        ])
        ->assertOk()
        ->assertJsonPath('success', true)
        ->assertJsonPath('copied_entries', 1)
        ->assertJsonPath('total_score', 7)
        ->assertJsonStructure(['panels_html', 'summary_html', 'active_item_id']);
});

test('aggregate workload score save returns the saved total without redirecting', function () {
    $context = workloadPageAsyncContext();
    $report = assignedWorkloadReport($context, 'Draft', '2026-08-01', '2026-08-31');
    WorkloadEntry::create([
        'report_id' => $report->id,
        'workload_form_id' => $context['form']->id,
        'field_values' => ['hours' => 9],
        'calculated_score' => 9,
    ]);

    $this->actingAs($context['evaluatee'], 'web')
        ->postJson(route('evaluatee.workload-score.store'), [
            'report_id' => $report->id,
            'quantity_sub_criteria_id' => $context['quantitySubCriteria']->id,
        ])
        ->assertOk()
        ->assertJsonPath('success', true)
        ->assertJsonPath('total_score', 9)
        ->assertJsonPath('saved_total', 9);
});
