<?php

namespace Tests\Feature\Evaluation;

use App\Models\AssignmentData;
use App\Models\Assignments;
use App\Models\CriteriaVersion;
use App\Models\EvaluationList;
use App\Models\QualitySubCriteria;
use App\Models\QuantityScore;
use App\Models\QuantitySubCriteria;
use App\Models\ReportData;
use App\Models\Reports;
use App\Models\Setting\Departments;
use App\Models\Setting\Positions;
use App\Models\User;
use Database\Factories\DepartmentFactory;
use Database\Factories\PositionFactory;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class EvaluatorTest extends TestCase
{
    use RefreshDatabase;

    protected User $director;

    protected User $manager;

    protected User $evaluator;

    protected User $evaluatee;

    protected Departments $department;

    protected Positions $directorPosition;

    protected Positions $managerPosition;

    protected Positions $evaluatorPosition;

    protected Positions $evaluateePosition;

    protected ReportData $reportData;

    protected CriteriaVersion $criteriaVersion;

    protected AssignmentData $assignmentData;

    protected QuantitySubCriteria $quantitySubCriteria;

    protected QualitySubCriteria $qualitySubCriteria;

    protected function setUp(): void
    {
        parent::setUp();

        // Create roles
        Role::create(['name' => 'admin']);
        Role::create(['name' => 'staff']);
        Role::create(['name' => 'กรรมการ']); // Director role
        Role::create(['name' => 'ผู้บริหาร']); // Manager role
        Role::create(['name' => 'ผู้ประเมิน']); // Evaluator role
        Role::create(['name' => 'ผู้รับการประเมิน']); // Evaluatee role

        // Create department and positions
        $this->department = DepartmentFactory::new()->create();
        $this->directorPosition = PositionFactory::new()->create(['name' => 'Director']);
        $this->managerPosition = PositionFactory::new()->create(['name' => 'Manager']);
        $this->evaluatorPosition = PositionFactory::new()->create(['name' => 'Evaluator']);
        $this->evaluateePosition = PositionFactory::new()->create(['name' => 'Staff']);

        // Create users for the assessment flow: evaluatee -> evaluator -> director -> manager
        $this->evaluatee = User::factory()->create([
            'employee_id' => 'EMP001',
            'password' => Hash::make('password'),
            'status' => 'active',
            'department_id' => $this->department->id,
            'position_id' => $this->evaluateePosition->id,
        ]);
        $this->evaluatee->assignRole('ผู้รับการประเมิน');

        $this->evaluator = User::factory()->create([
            'employee_id' => 'EVA001',
            'password' => Hash::make('password'),
            'status' => 'active',
            'department_id' => $this->department->id,
            'position_id' => $this->evaluatorPosition->id,
        ]);
        $this->evaluator->assignRole('ผู้ประเมิน');

        $this->director = User::factory()->create([
            'employee_id' => 'DIR001',
            'password' => Hash::make('password'),
            'status' => 'active',
            'department_id' => $this->department->id,
            'position_id' => $this->directorPosition->id,
        ]);
        $this->director->assignRole('กรรมการ');

        $this->manager = User::factory()->create([
            'employee_id' => 'MGR001',
            'password' => Hash::make('password'),
            'status' => 'active',
            'department_id' => $this->department->id,
            'position_id' => $this->managerPosition->id,
        ]);
        $this->manager->assignRole('ผู้บริหาร');

        // Create criteria and report data
        $this->criteriaVersion = CriteriaVersion::factory()->create();
        $this->reportData = ReportData::factory()->create([
            'criteria_version_id' => $this->criteriaVersion->id,
        ]);

        // Create assignment data (evaluatee -> evaluator assignment)
        $this->assignmentData = AssignmentData::factory()->create([
            'evaluatee_position_id' => $this->evaluateePosition->id,
            'evaluator_position_id' => $this->evaluatorPosition->id,
            'start_time' => now(),
            'end_time' => now()->addDays(30),
        ]);

        // Create sub-criteria for testing
        $this->quantitySubCriteria = QuantitySubCriteria::factory()->create([
            'criteria_version_id' => $this->criteriaVersion->id,
            'score_a' => 10,
            'score_b' => 5,
        ]);
        $this->quantitySubCriteria->evaluationList()->update([
            'quantity_enabled' => true,
        ]);
        $qualityEvaluationList = EvaluationList::factory()->create([
            'criteria_version_id' => $this->criteriaVersion->id,
            'sum_score' => 10,
        ]);
        $this->qualitySubCriteria = QualitySubCriteria::factory()->create([
            'criteria_version_id' => $this->criteriaVersion->id,
            'evaluation_list_id' => $qualityEvaluationList->id,
            'num_score' => 5,
        ]);
    }

    private function createReportWithStatus(string $status): Reports
    {
        $report = Reports::factory()->create([
            'report_data_id' => $this->reportData->id,
            'status' => $status,
        ]);

        // Create assignment record for evaluatee
        Assignments::factory()->create([
            'assignment_data_id' => $this->assignmentData->id,
            'evaluatee_id' => $this->evaluatee->id,
            'report_id' => $report->id,
        ]);

        return $report;
    }

    public function test_evaluator_can_submit_evaluation_to_director(): void
    {
        $report = $this->createReportWithStatus('Evaluator_draft');

        $payload = [
            'quantity_list' => [
                [
                    'quantity_sub_criteria_id' => $this->quantitySubCriteria->id,
                    'score_C' => 9,
                    'modification_reason' => 'ปรับตามหลักฐาน',
                ],
            ],
            'quality_list' => [
                [
                    'quality_sub_criteria_id' => $this->qualitySubCriteria->id,
                    'score' => 5,
                    'modification_reason' => 'ปรับตามผลการตรวจ',
                ],
            ],
            'status' => 'Pending',
            'comment' => 'Evaluator evaluation completed, forwarding to director',
        ];

        $response = $this->actingAs($this->evaluator, 'web')
            ->post(route('evaluator.evaluator_score.store', ['id' => $report->id]), $payload);

        $response->assertRedirect('/evaluator-dashboard');
        $response->assertSessionHas('success', 'ส่งรายงานเรียบร้อยแล้ว');

        // Assert report moved to evaluator phase
        $this->assertDatabaseHas('reports', [
            'id' => $report->id,
            'status' => 'Director_assigned',
            'comment' => 'Evaluator evaluation completed, forwarding to director',
        ]);
    }

    public function test_evaluator_can_save_draft_scores(): void
    {
        $report = $this->createReportWithStatus('Pending');

        $payload = [
            'quantity_list' => [
                [
                    'quantity_sub_criteria_id' => $this->quantitySubCriteria->id,
                    'score_C' => 8,
                    'modification_reason' => 'ปรับตามหลักฐาน',
                ],
            ],
            'quality_list' => [
                [
                    'quality_sub_criteria_id' => $this->qualitySubCriteria->id,
                    'score' => 4,
                    'modification_reason' => 'ปรับตามผลการตรวจ',
                ],
            ],
            'status' => 'Evaluator_draft',
            'comment' => 'Evaluator reviewing - work in progress',
        ];

        $response = $this->actingAs($this->evaluator, 'web')
            ->post(route('evaluator.evaluator_score.store', ['id' => $report->id]), $payload);

        $response->assertRedirect('/evaluator-dashboard');
        $response->assertSessionHas('success', 'บันทึกข้อมูลเรียบร้อยแล้ว');

        // Assert report status updated
        $this->assertDatabaseHas('reports', [
            'id' => $report->id,
            'status' => 'Evaluator_draft',
            'comment' => 'Evaluator reviewing - work in progress',
        ]);

        // Assert quantity score saved with calculation
        $this->assertDatabaseHas('quantity_scores', [
            'report_id' => $report->id,
            'quantity_sub_criteria_id' => $this->quantitySubCriteria->id,
            'score_C' => 8,
            'score_D' => 16, // (10 * 8) / 5 = 16
        ]);

        // Assert quality score saved
        $this->assertDatabaseHas('quality_scores', [
            'report_id' => $report->id,
            'quality_sub_criteria_id' => $this->qualitySubCriteria->id,
            'score' => 4,
        ]);
    }

    public function test_evaluator_cannot_submit_a_disabled_quantity_criterion(): void
    {
        $report = $this->createReportWithStatus('Pending');
        $this->quantitySubCriteria->evaluationList()->update([
            'quantity_enabled' => false,
        ]);

        $response = $this->actingAs($this->evaluator, 'web')
            ->from('/evaluator-dashboard')
            ->post(route('evaluator.evaluator_score.store', ['id' => $report->id]), [
                'quantity_list' => [[
                    'quantity_sub_criteria_id' => $this->quantitySubCriteria->id,
                    'score_C' => 8,
                    'modification_reason' => 'ตรวจสอบข้อมูลแล้ว',
                ]],
                'status' => 'Evaluator_draft',
            ]);

        $response->assertRedirect('/evaluator-dashboard');
        $response->assertSessionHasErrors('quantity_list.0.quantity_sub_criteria_id');
        $this->assertDatabaseMissing('quantity_scores', [
            'report_id' => $report->id,
            'quantity_sub_criteria_id' => $this->quantitySubCriteria->id,
        ]);
    }

    public function test_saving_active_scores_preserves_existing_disabled_quantity_scores(): void
    {
        $report = $this->createReportWithStatus('Pending');
        $disabledSubCriteria = QuantitySubCriteria::factory()->create([
            'criteria_version_id' => $this->criteriaVersion->id,
        ]);
        QuantityScore::create([
            'report_id' => $report->id,
            'quantity_sub_criteria_id' => $disabledSubCriteria->id,
            'score_C' => 7,
            'score_D' => 7,
        ]);

        $this->actingAs($this->evaluator, 'web')
            ->post(route('evaluator.evaluator_score.store', ['id' => $report->id]), [
                'quantity_list' => [[
                    'quantity_sub_criteria_id' => $this->quantitySubCriteria->id,
                    'score_C' => 8,
                    'modification_reason' => 'ตรวจสอบข้อมูลแล้ว',
                ]],
                'status' => 'Evaluator_draft',
            ])
            ->assertRedirect('/evaluator-dashboard');

        $this->assertDatabaseHas('quantity_scores', [
            'report_id' => $report->id,
            'quantity_sub_criteria_id' => $disabledSubCriteria->id,
            'score_C' => 7,
        ]);
    }

    public function test_evaluator_score_change_creates_quantity_score_history(): void
    {
        $report = $this->createReportWithStatus('Pending');

        QuantityScore::create([
            'report_id' => $report->id,
            'quantity_sub_criteria_id' => $this->quantitySubCriteria->id,
            'score_C' => 6,
            'score_D' => 12,
            'description' => 'ก่อนตรวจ',
        ]);

        $payload = [
            'quantity_list' => [[
                'quantity_sub_criteria_id' => $this->quantitySubCriteria->id,
                'score_C' => 7,
                'description' => 'ผู้ประเมินปรับคะแนน',
                'modification_reason' => 'ปรับตามหลักฐาน',
            ]],
            'status' => 'Evaluator_draft',
            'comment' => 'updated',
        ];

        $this->actingAs($this->evaluator, 'web')
            ->post(route('evaluator.evaluator_score.store', ['id' => $report->id]), $payload)
            ->assertRedirect('/evaluator-dashboard');

        $this->assertDatabaseHas('quantity_score_histories', [
            'report_id' => $report->id,
            'quantity_sub_criteria_id' => $this->quantitySubCriteria->id,
            'previous_score_c' => '6.00',
            'new_score_c' => '7.00',
            'previous_description' => 'ก่อนตรวจ',
            'new_description' => 'ผู้ประเมินปรับคะแนน',
            'reason' => 'ปรับตามหลักฐาน',
            'modifier_user_id' => $this->evaluator->id,
            'modifier_role' => 'ผู้ประเมิน',
        ]);
    }

    public function test_evaluator_cannot_access_report_with_assigned_status(): void
    {
        $report = $this->createReportWithStatus('Assigned'); // Still with evaluatee

        $response = $this->actingAs($this->evaluator, 'web')
            ->get(route('evaluator.evaluator.show', ['id' => $report->id]));

        $response->assertStatus(403);
    }

    public function test_evaluator_cannot_access_report_with_draft_status(): void
    {
        $report = $this->createReportWithStatus('Draft'); // Evaluatee working on it

        $response = $this->actingAs($this->evaluator, 'web')
            ->get(route('evaluator.evaluator.show', ['id' => $report->id]));

        $response->assertStatus(403);
    }

    public function test_evaluator_can_view_readonly_director_assigned_report(): void
    {
        $report = $this->createReportWithStatus('Director_assigned'); // Moved to manager

        $response = $this->actingAs($this->evaluator, 'web')
            ->get(route('evaluator.evaluator.show', ['id' => $report->id, 'readonly' => 1]));

        $response->assertStatus(200);
        $response->assertViewIs('evaluator_dashboard.evaluator');
        $response->assertViewHas('readonly', true);
        $response->assertSee('data-quantity-score-input', false);
        $response->assertDontSee('oninput="calculateScoreD(this)"', false);
    }

    public function test_evaluator_can_view_readonly_director_draft_report(): void
    {
        $report = $this->createReportWithStatus('Director_draft'); // Moved to manager

        $response = $this->actingAs($this->evaluator, 'web')
            ->get(route('evaluator.evaluator.show', ['id' => $report->id, 'readonly' => 1]));

        $response->assertStatus(200);
        $response->assertViewIs('evaluator_dashboard.evaluator');
        $response->assertViewHas('readonly', true);
    }

    public function test_evaluator_can_view_readonly_manager_assigned_report(): void
    {
        $report = $this->createReportWithStatus('Manager_assign'); // Moved to manager

        $response = $this->actingAs($this->evaluator, 'web')
            ->get(route('evaluator.evaluator.show', ['id' => $report->id, 'readonly' => 1]));

        $response->assertStatus(200);
        $response->assertViewIs('evaluator_dashboard.evaluator');
        $response->assertViewHas('readonly', true);
    }

    public function test_evaluator_can_view_readonly_completed_report(): void
    {
        $report = $this->createReportWithStatus('Completed');

        $response = $this->actingAs($this->evaluator, 'web')
            ->get(route('evaluator.evaluator.show', ['id' => $report->id, 'readonly' => 1]));

        $response->assertStatus(200);
        $response->assertViewIs('evaluator_dashboard.evaluator');
        $response->assertViewHas('readonly', true);
    }

    public function test_evaluator_cannot_edit_manager_phase_report(): void
    {
        $report = $this->createReportWithStatus('Manager_assign'); // Already in manager phase

        $payload = [
            'quantity_list' => [
                [
                    'quantity_sub_criteria_id' => $this->quantitySubCriteria->id,
                    'score_C' => 8,
                    'modification_reason' => 'ปรับตามหลักฐาน',
                ],
            ],
            'status' => 'Evaluator_draft',
            'comment' => 'Should not work - already submitted to manager',
        ];

        $response = $this->actingAs($this->evaluator, 'web')
            ->postJson(route('evaluator.evaluator_score.store', ['id' => $report->id]), $payload);

        $response->assertStatus(403);
        $response->assertJson([
            'message' => 'Cannot process evaluation scores. Report must be in Pending or Evaluator_draft status.',
        ]);
    }

    public function test_evaluator_cannot_edit_completed_report(): void
    {
        $report = $this->createReportWithStatus('Completed');

        $payload = [
            'quantity_list' => [
                [
                    'quantity_sub_criteria_id' => $this->quantitySubCriteria->id,
                    'score_C' => 8,
                    'modification_reason' => 'ปรับตามหลักฐาน',
                ],
            ],
            'status' => 'Evaluator_draft',
            'comment' => 'Should not work - evaluation completed',
        ];

        $response = $this->actingAs($this->evaluator, 'web')
            ->postJson(route('evaluator.evaluator_score.store', ['id' => $report->id]), $payload);

        $response->assertStatus(403);
    }

    public function test_evaluator_cannot_edit_director_phase_report(): void
    {
        $report = $this->createReportWithStatus('Director_assigned'); // Already in manager phase

        $payload = [
            'quantity_list' => [
                [
                    'quantity_sub_criteria_id' => $this->quantitySubCriteria->id,
                    'score_C' => 8,
                    'modification_reason' => 'ปรับตามหลักฐาน',
                ],
            ],
            'status' => 'Evaluator_draft',
            'comment' => 'Should not work - already submitted to manager',
        ];

        $response = $this->actingAs($this->evaluator, 'web')
            ->postJson(route('evaluator.evaluator_score.store', ['id' => $report->id]), $payload);

        $response->assertStatus(403);
        $response->assertJson([
            'message' => 'Cannot process evaluation scores. Report must be in Pending or Evaluator_draft status.',
        ]);
    }
}
