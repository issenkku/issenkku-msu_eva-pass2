<?php

use App\Models\AssignmentData;
use App\Models\Assignments;
use App\Models\CriteriaVersion;
use App\Models\EvaluationList;
use App\Models\QualityScore;
use App\Models\QualitySubCriteria;
use App\Models\QuantityScore;
use App\Models\QuantitySubCriteria;
use App\Models\ReportData;
use App\Models\Reports;
use App\Models\User;
use Database\Factories\DepartmentFactory;
use Database\Factories\PositionFactory;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;

uses(RefreshDatabase::class);

beforeEach(function () {
    Role::create(['name' => 'ผู้ประเมิน']);
    $department = DepartmentFactory::new()->create();
    $evaluateePosition = PositionFactory::new()->create();
    $evaluatorPosition = PositionFactory::new()->create();

    $this->evaluatee = User::factory()->create([
        'department_id' => $department->id,
        'position_id' => $evaluateePosition->id,
        'status' => 'active',
    ]);
    $this->evaluator = User::factory()->create([
        'department_id' => $department->id,
        'position_id' => $evaluatorPosition->id,
        'status' => 'active',
    ]);
    $this->evaluator->assignRole('ผู้ประเมิน');

    $this->criteriaVersion = CriteriaVersion::factory()->create();
    $this->evaluationList = EvaluationList::factory()->create([
        'criteria_version_id' => $this->criteriaVersion->id,
        'sum_score' => 10,
        'quantity_enabled' => true,
    ]);
    $this->quantity = QuantitySubCriteria::factory()->create([
        'criteria_version_id' => $this->criteriaVersion->id,
        'evaluation_list_id' => $this->evaluationList->id,
        'score_a' => 10,
        'score_b' => 5,
    ]);
    $this->quality = QualitySubCriteria::factory()->create([
        'criteria_version_id' => $this->criteriaVersion->id,
        'evaluation_list_id' => $this->evaluationList->id,
        'num_score' => 5,
    ]);

    $reportData = ReportData::factory()->create([
        'criteria_version_id' => $this->criteriaVersion->id,
    ]);
    $this->report = Reports::factory()->create([
        'report_data_id' => $reportData->id,
        'status' => 'Pending',
        'comment' => 'เดิม',
        'evaluator_comment' => 'เดิม',
    ]);
    $assignmentData = AssignmentData::factory()->create([
        'evaluator_id' => $this->evaluator->id,
        'evaluatee_position_id' => $evaluateePosition->id,
        'evaluator_position_id' => $evaluatorPosition->id,
        'start_time' => now(),
        'end_time' => now()->addMonth(),
    ]);
    Assignments::factory()->create([
        'assignment_data_id' => $assignmentData->id,
        'evaluatee_id' => $this->evaluatee->id,
        'report_id' => $this->report->id,
    ]);
});

test('a missing reviewer reason rolls back earlier score and history writes', function () {
    QuantityScore::create([
        'report_id' => $this->report->id,
        'quantity_sub_criteria_id' => $this->quantity->id,
        'score_C' => 6,
        'score_D' => 12,
    ]);
    QualityScore::create([
        'report_id' => $this->report->id,
        'quality_sub_criteria_id' => $this->quality->id,
        'score' => 2,
    ]);

    $this->actingAs($this->evaluator)
        ->from('/evaluator/review')
        ->post(route('evaluator.evaluator_score.store', $this->report->id), [
            'quantity_list' => [[
                'quantity_sub_criteria_id' => $this->quantity->id,
                'score_C' => 7,
                'modification_reason' => 'ปรับตามหลักฐาน',
            ]],
            'quality_list' => [[
                'quality_sub_criteria_id' => $this->quality->id,
                'score' => 3,
                'modification_reason' => '',
            ]],
            'status' => 'Evaluator_draft',
            'comment' => 'ค่าใหม่',
        ])
        ->assertRedirect('/evaluator/review')
        ->assertSessionHasErrors('quality_list.0.modification_reason');

    $this->assertDatabaseHas('quantity_scores', [
        'report_id' => $this->report->id,
        'quantity_sub_criteria_id' => $this->quantity->id,
        'score_C' => '6.00',
    ]);
    $this->assertDatabaseHas('quality_scores', [
        'report_id' => $this->report->id,
        'quality_sub_criteria_id' => $this->quality->id,
        'score' => '2.00',
    ]);
    $this->assertDatabaseCount('quantity_score_histories', 0);
    $this->assertDatabaseCount('quality_score_histories', 0);
    $this->assertDatabaseHas('reports', [
        'id' => $this->report->id,
        'status' => 'Pending',
        'comment' => 'เดิม',
        'evaluator_comment' => 'เดิม',
    ]);
});

test('criteria IDs from another version are rejected without persistence', function () {
    $otherVersion = CriteriaVersion::factory()->create();
    $otherList = EvaluationList::factory()->create([
        'criteria_version_id' => $otherVersion->id,
    ]);
    $otherQuantity = QuantitySubCriteria::factory()->create([
        'criteria_version_id' => $otherVersion->id,
        'evaluation_list_id' => $otherList->id,
    ]);
    $otherQuality = QualitySubCriteria::factory()->create([
        'criteria_version_id' => $otherVersion->id,
        'evaluation_list_id' => $otherList->id,
    ]);

    $this->actingAs($this->evaluator)
        ->from('/evaluator/review')
        ->post(route('evaluator.evaluator_score.store', $this->report->id), [
            'quantity_list' => [[
                'quantity_sub_criteria_id' => $otherQuantity->id,
                'score_C' => 7,
                'modification_reason' => 'ไม่ควรบันทึก',
            ]],
            'quality_list' => [[
                'quality_sub_criteria_id' => $otherQuality->id,
                'score' => 3,
                'modification_reason' => 'ไม่ควรบันทึก',
            ]],
            'status' => 'Evaluator_draft',
        ])
        ->assertRedirect('/evaluator/review')
        ->assertSessionHasErrors([
            'quantity_list.0.quantity_sub_criteria_id',
            'quality_list.0.quality_sub_criteria_id',
        ]);

    $this->assertDatabaseCount('quantity_scores', 0);
    $this->assertDatabaseCount('quality_scores', 0);
    $this->assertDatabaseCount('quantity_score_histories', 0);
    $this->assertDatabaseCount('quality_score_histories', 0);
});
