<?php

namespace Tests\Feature\Evaluation;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use App\Models\User;
use App\Models\Reports;
use App\Models\Setting\Departments;
use App\Models\Setting\Positions;
use App\Models\ReportData;
use App\Models\CriteriaVersion;
use App\Models\QuantityScore;
use App\Models\QualityScore;
use App\Models\QuantitySubCriteria;
use App\Models\QualitySubCriteria;
use App\Models\AssignmentData;
use App\Models\Assignments;
use Spatie\Permission\Models\Role;

class ManagerTest extends TestCase
{
    use RefreshDatabase;

    protected User $manager;
    protected User $director;
    protected User $evaluator;
    protected User $evaluatee;
    protected Departments $department;
    protected Positions $managerPosition;
    protected Positions $directorPosition;
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
        Role::create(['name' => 'กรรมการ']); // manager role
        Role::create(['name' => 'ผู้บริหาร']); // Manager role
        Role::create(['name' => 'ผู้ประเมิน']); // Evaluator role
        Role::create(['name' => 'ผู้รับการประเมิน']); // Evaluatee role

        // Create department and positions
        $this->department = \Database\Factories\DepartmentFactory::new()->create();
        $this->managerPosition = \Database\Factories\PositionFactory::new()->create(['name' => 'manager']);
        $this->managerPosition = \Database\Factories\PositionFactory::new()->create(['name' => 'Manager']);
        $this->evaluatorPosition = \Database\Factories\PositionFactory::new()->create(['name' => 'Evaluator']);
        $this->evaluateePosition = \Database\Factories\PositionFactory::new()->create(['name' => 'Staff']);

        // Create users for the assessment flow: evaluatee -> evaluator -> manager -> manager
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

        $this->manager = User::factory()->create([
            'employee_id' => 'DIR001',
            'password' => Hash::make('password'),
            'status' => 'active',
            'department_id' => $this->department->id,
            'position_id' => $this->managerPosition->id,
        ]);
        $this->manager->assignRole('กรรมการ');

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
            'score_a' => 10,
            'score_b' => 5,
        ]);
        $this->qualitySubCriteria = QualitySubCriteria::factory()->create();
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

    public function test_manager_can_submit_evaluation(): void
    {
        $report = $this->createReportWithStatus('Manager_draft');

        $payload = [
            'quantity_list' => [
                [
                    'quantity_sub_criteria_id' => $this->quantitySubCriteria->id,
                    'score_C' => 9,
                    'modification_reason' => 'ปรับตามหลักฐาน',
                ]
            ],
            'quality_list' => [
                [
                    'quality_sub_criteria_id' => $this->qualitySubCriteria->id,
                    'score' => 5,
                ]
            ],
            'status' => 'Completed', // Submit to manager
            'comment' => 'Manager evaluation completed',
        ];

        $response = $this->actingAs($this->manager, 'web')
            ->post(route('manager_score.store', ['id' => $report->id]), $payload);

        $response->assertRedirect('/manager-dashboard');
        $response->assertSessionHas('success', 'ส่งรายงานเรียบร้อยแล้ว');

        // Assert report moved to manager phase
        $this->assertDatabaseHas('reports', [
            'id' => $report->id,
            'status' => 'Completed',
            'comment' => 'Manager evaluation completed',
        ]);
    }

    public function test_manager_can_save_draft_scores(): void
    {
        $report = $this->createReportWithStatus('Manager_assign');

        $payload = [
            'quantity_list' => [
                [
                    'quantity_sub_criteria_id' => $this->quantitySubCriteria->id,
                    'score_C' => 8,
                    'modification_reason' => 'ปรับตามหลักฐาน',
                ]
            ],
            'quality_list' => [
                [
                    'quality_sub_criteria_id' => $this->qualitySubCriteria->id,
                    'score' => 4,
                ]
            ],
            'status' => 'Manager_draft',
            'comment' => 'Manager reviewing - work in progress',
        ];

        $response = $this->actingAs($this->manager, 'web')
            ->post(route('manager_score.store', ['id' => $report->id]), $payload);

        $response->assertRedirect('/manager-dashboard');
        $response->assertSessionHas('success', 'บันทึกข้อมูลเรียบร้อยแล้ว');

        // Assert report status updated
        $this->assertDatabaseHas('reports', [
            'id' => $report->id,
            'status' => 'Manager_draft',
            'comment' => 'Manager reviewing - work in progress',
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

    public function test_manager_score_change_creates_quantity_score_history(): void
    {
        $report = $this->createReportWithStatus('Manager_assign');

        QuantityScore::create([
            'report_id' => $report->id,
            'quantity_sub_criteria_id' => $this->quantitySubCriteria->id,
            'score_C' => 7,
            'score_D' => 14,
            'description' => 'ก่อนผู้บริหารแก้',
        ]);

        $payload = [
            'quantity_list' => [[
                'quantity_sub_criteria_id' => $this->quantitySubCriteria->id,
                'score_C' => 9,
                'description' => 'ผู้บริหารปรับคะแนน',
                'modification_reason' => 'ปรับตามหลักฐาน',
            ]],
            'status' => 'Manager_draft',
            'comment' => 'updated',
        ];

        $this->actingAs($this->manager, 'web')
            ->post(route('manager_score.store', ['id' => $report->id]), $payload)
            ->assertRedirect('/manager-dashboard');

        $this->assertDatabaseHas('quantity_score_histories', [
            'report_id' => $report->id,
            'quantity_sub_criteria_id' => $this->quantitySubCriteria->id,
            'previous_score_c' => '7.00',
            'new_score_c' => '9.00',
            'previous_description' => 'ก่อนผู้บริหารแก้',
            'new_description' => 'ผู้บริหารปรับคะแนน',
            'reason' => 'ปรับตามหลักฐาน',
            'modifier_user_id' => $this->manager->id,
            'modifier_role' => 'ผู้บริหาร',
        ]);
    }

    public function test_manager_can_access_report_with_manager_assigned_status(): void
    {
        $report = $this->createReportWithStatus('Manager_assign');

        $response = $this->actingAs($this->manager, 'web')
            ->get(route('manager.show', ['id' => $report->id]));

        $response->assertStatus(200);
        $response->assertViewIs('manager_dashboard.manager');
        $response->assertViewHas('readonly', false);
    }

    public function test_manager_can_access_report_with_manager_draft_status(): void
    {
        $report = $this->createReportWithStatus('Manager_draft');

        $response = $this->actingAs($this->manager, 'web')
            ->get(route('manager.show', ['id' => $report->id]));

        $response->assertStatus(200);
        $response->assertViewIs('manager_dashboard.manager');
        $response->assertViewHas('readonly', false);
    }

    public function test_manager_cannot_access_report_with_assigned_status(): void
    {
        $report = $this->createReportWithStatus('Assigned'); // Still with evaluatee

        $response = $this->actingAs($this->manager, 'web')
            ->get(route('manager.show', ['id' => $report->id]));

        $response->assertStatus(403);
    }

    public function test_manager_cannot_access_report_with_draft_status(): void
    {
        $report = $this->createReportWithStatus('Draft'); // Evaluatee working on it

        $response = $this->actingAs($this->manager, 'web')
            ->get(route('manager.show', ['id' => $report->id]));

        $response->assertStatus(403);
    }

    public function test_manager_cannot_access_report_with_pending_status(): void
    {
        $report = $this->createReportWithStatus('Pending'); // Submitted by evaluatee, waiting for evaluator

        $response = $this->actingAs($this->manager, 'web')
            ->get(route('manager.show', ['id' => $report->id]));

        $response->assertStatus(403);
    }

    public function test_manager_cannot_access_report_with_evaluator_draft_status(): void
    {
        $report = $this->createReportWithStatus('Evaluator_draft'); // Evaluator working on it

        $response = $this->actingAs($this->manager, 'web')
            ->get(route('manager.show', ['id' => $report->id]));

        $response->assertStatus(403);
    }

    public function test_manager_cannot_access_report_with_director_assigned_status(): void
    {
        $report = $this->createReportWithStatus('Director_assigned'); // Evaluator working on it

        $response = $this->actingAs($this->manager, 'web')
            ->get(route('manager.show', ['id' => $report->id]));

        $response->assertStatus(403);
    }

    public function test_manager_cannot_access_report_with_director_draft_status(): void
    {
        $report = $this->createReportWithStatus('Director_draft'); // Evaluator working on it

        $response = $this->actingAs($this->manager, 'web')
            ->get(route('manager.show', ['id' => $report->id]));

        $response->assertStatus(403);
    }

    public function test_manager_can_view_readonly_completed_report(): void
    {
        $report = $this->createReportWithStatus('Completed');

        $response = $this->actingAs($this->manager, 'web')
            ->get(route('manager.show', ['id' => $report->id, 'readonly' => 1]));

        $response->assertStatus(200);
        $response->assertViewIs('manager_dashboard.manager');
        $response->assertViewHas('readonly', true);
    }

    public function test_manager_cannot_edit_evaluator_phase_report(): void
    {
        $report = $this->createReportWithStatus('Evaluator_draft'); // Still in evaluator phase

        $payload = [
            'quantity_list' => [
                [
                    'quantity_sub_criteria_id' => $this->quantitySubCriteria->id,
                    'score_C' => 8,
                    'modification_reason' => 'ปรับตามหลักฐาน',
                ]
            ],
            'status' => 'Manager_draft',
            'comment' => 'Should not work - wrong phase',
        ];

        $response = $this->actingAs($this->manager, 'web')
            ->postJson(route('manager_score.store', ['id' => $report->id]), $payload);

        $response->assertStatus(403);
        $response->assertJson([
            'message' => 'Cannot process evaluation scores. Report must be in Manager_assign or Manager_draft status.',
        ]);

        // Assert no changes were made
        $this->assertDatabaseMissing('quantity_scores', [
            'report_id' => $report->id,
        ]);
    }

    public function test_manager_cannot_edit_director_phase_report(): void
    {
        $report = $this->createReportWithStatus('Director_assigned'); // Already in manager phase

        $payload = [
            'quantity_list' => [
                [
                    'quantity_sub_criteria_id' => $this->quantitySubCriteria->id,
                    'score_C' => 8,
                    'modification_reason' => 'ปรับตามหลักฐาน',
                ]
            ],
            'status' => 'Manager_draft',
            'comment' => 'Should not work - already submitted to manager',
        ];

        $response = $this->actingAs($this->manager, 'web')
            ->postJson(route('manager_score.store', ['id' => $report->id]), $payload);

        $response->assertStatus(403);
        $response->assertJson([
            'message' => 'Cannot process evaluation scores. Report must be in Manager_assign or Manager_draft status.',
        ]);
    }

    public function test_manager_cannot_edit_completed_report(): void
    {
        $report = $this->createReportWithStatus('Completed');

        $payload = [
            'quantity_list' => [
                [
                    'quantity_sub_criteria_id' => $this->quantitySubCriteria->id,
                    'score_C' => 8,
                    'modification_reason' => 'ปรับตามหลักฐาน',
                ]
            ],
            'status' => 'Manager_draft',
            'comment' => 'Should not work - evaluation completed',
        ];

        $response = $this->actingAs($this->manager, 'web')
            ->postJson(route('manager_score.store', ['id' => $report->id]), $payload);

        $response->assertStatus(403);
    }
}
