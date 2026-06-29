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
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class EvaluateeTest extends TestCase
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
        $this->department = \Database\Factories\DepartmentFactory::new()->create();
        $this->directorPosition = \Database\Factories\PositionFactory::new()->create(['name' => 'Director']);
        $this->managerPosition = \Database\Factories\PositionFactory::new()->create(['name' => 'Manager']);
        $this->evaluatorPosition = \Database\Factories\PositionFactory::new()->create(['name' => 'Evaluator']);
        $this->evaluateePosition = \Database\Factories\PositionFactory::new()->create(['name' => 'Staff']);

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
            'score_a' => 10,
            'score_b' => 5,
        ]);
        $qualityEvaluationList = \App\Models\EvaluationList::factory()->create([
            'criteria_version_id' => $this->criteriaVersion->id,
            'sum_score' => 10,
        ]);
        $this->qualitySubCriteria = QualitySubCriteria::factory()->create([
            'criteria_version_id' => $this->criteriaVersion->id,
            'evaluation_list_id' => $qualityEvaluationList->id,
            'num_score' => 5,
        ]);

        Mail::fake();
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

    public function test_evaluatee_can_submit_evaluation_to_evaluator(): void
    {
        $report = $this->createReportWithStatus('Draft');

        $payload = [
            'quantity_list' => [
                [
                    'quantity_sub_criteria_id' => $this->quantitySubCriteria->id,
                    'score_C' => 9,
                ]
            ],
            'quality_list' => [
                [
                    'quality_sub_criteria_id' => $this->qualitySubCriteria->id,
                    'score' => 5,
                ]
            ],
            'status' => 'Pending', // Submit to evaluator
            'change_status' => 1,
        ];

        $response = $this->actingAs($this->evaluatee, 'web')
            ->post(route('evaluation_score.store', ['id' => $report->id]), $payload);

        $response->assertRedirect('/evaluatee-dashboard');
        $response->assertSessionHas('success', 'ส่งรายงานเรียบร้อยแล้ว');

        // Assert report moved to evaluator phase
        $this->assertDatabaseHas('reports', [
            'id' => $report->id,
            'status' => 'Pending',
        ]);
    }

    public function test_evaluatee_can_submit_when_quality_main_criteria_require_evidence_column_is_missing(): void
    {
        Schema::table('quality_main_criterias', function ($table) {
            $table->dropColumn('require_evidence');
        });

        $this->assertFalse(Schema::hasColumn('quality_main_criterias', 'require_evidence'));

        $queries = [];
        DB::listen(function ($query) use (&$queries) {
            $queries[] = $query->sql;
        });

        $report = $this->createReportWithStatus('Draft');

        $payload = [
            'quantity_list' => [
                [
                    'quantity_sub_criteria_id' => $this->quantitySubCriteria->id,
                    'score_C' => 9,
                ],
            ],
            'quality_list' => [
                [
                    'quality_sub_criteria_id' => $this->qualitySubCriteria->id,
                    'score' => 5,
                ],
            ],
            'status' => 'Pending',
            'change_status' => 1,
        ];

        $this->actingAs($this->evaluatee, 'web')
            ->post(route('evaluation_score.store', ['id' => $report->id]), $payload)
            ->assertRedirect('/evaluatee-dashboard');

        $this->assertDatabaseHas('reports', [
            'id' => $report->id,
            'status' => 'Pending',
        ]);

        $this->assertFalse(collect($queries)->contains(
            fn (string $sql) => str_contains($sql, 'require_evidence')
        ));
    }

    public function test_evaluatee_can_save_draft_scores(): void
    {
        $report = $this->createReportWithStatus('Assigned');

        $payload = [
            'quantity_list' => [
                [
                    'quantity_sub_criteria_id' => $this->quantitySubCriteria->id,
                    'score_C' => 8,
                ]
            ],
            'quality_list' => [
                [
                    'quality_sub_criteria_id' => $this->qualitySubCriteria->id,
                    'score' => 4,
                ]
            ],
            'status' => 'Draft',
        ];

        $response = $this->actingAs($this->evaluatee, 'web')
            ->post(route('evaluation_score.store', ['id' => $report->id]), $payload);

        $response->assertRedirect('/evaluatee-dashboard');
        $response->assertSessionHas('success', 'บันทึกข้อมูลเรียบร้อยแล้ว');

        // Assert report status updated
        $this->assertDatabaseHas('reports', [
            'id' => $report->id,
            'status' => 'Draft',
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

    public function test_evaluatee_score_change_creates_quantity_score_history(): void
    {
        $report = $this->createReportWithStatus('Draft');

        QuantityScore::create([
            'report_id' => $report->id,
            'quantity_sub_criteria_id' => $this->quantitySubCriteria->id,
            'score_C' => 5,
            'score_D' => 10,
            'description' => 'ค่าเดิม',
        ]);

        $payload = [
            'quantity_list' => [[
                'quantity_sub_criteria_id' => $this->quantitySubCriteria->id,
                'score_C' => 8,
                'description' => 'ปรับตามผลงานล่าสุด',
            ]],
            'status' => 'Draft',
        ];

        $this->actingAs($this->evaluatee, 'web')
            ->post(route('evaluation_score.store', ['id' => $report->id]), $payload)
            ->assertRedirect('/evaluatee-dashboard');

        $this->assertDatabaseHas('quantity_score_histories', [
            'report_id' => $report->id,
            'quantity_sub_criteria_id' => $this->quantitySubCriteria->id,
            'previous_score_c' => '5.00',
            'new_score_c' => '8.00',
            'previous_description' => 'ค่าเดิม',
            'new_description' => 'ปรับตามผลงานล่าสุด',
            'modifier_user_id' => null,
        ]);
    }

    public function test_evaluatee_cannot_edit_evaluator_phase_report(): void
    {
        $report = $this->createReportWithStatus('Pending');

        $payload = [
            'quantity_list' => [
                [
                    'quantity_sub_criteria_id' => $this->quantitySubCriteria->id,
                    'score_C' => 8,
                ]
            ],
            'status' => 'Draft',
        ];

        $response = $this->actingAs($this->evaluatee, 'web')
            ->postJson(route('evaluation_score.store', ['id' => $report->id]), $payload);

        $response->assertStatus(403);
        $response->assertJson([
            'message' => 'Cannot process evaluation scores. Report must be in Assigned or Draft status.',
        ]);
    }

    public function test_evaluatee_cannot_edit_director_phase_report(): void
    {
        $report = $this->createReportWithStatus('Director_assigned');

        $payload = [
            'quantity_list' => [
                [
                    'quantity_sub_criteria_id' => $this->quantitySubCriteria->id,
                    'score_C' => 8,
                ]
            ],
            'status' => 'Draft',
        ];

        $response = $this->actingAs($this->evaluatee, 'web')
            ->postJson(route('evaluation_score.store', ['id' => $report->id]), $payload);

        $response->assertStatus(403);
        $response->assertJson([
            'message' => 'Cannot process evaluation scores. Report must be in Assigned or Draft status.',
        ]);
    }

    public function test_evaluatee_cannot_edit_manager_phase_report(): void
    {
        $report = $this->createReportWithStatus('Manager_assign');

        $payload = [
            'quantity_list' => [
                [
                    'quantity_sub_criteria_id' => $this->quantitySubCriteria->id,
                    'score_C' => 8,
                ]
            ],
            'status' => 'Draft',
        ];

        $response = $this->actingAs($this->evaluatee, 'web')
            ->postJson(route('evaluation_score.store', ['id' => $report->id]), $payload);

        $response->assertStatus(403);
        $response->assertJson([
            'message' => 'Cannot process evaluation scores. Report must be in Assigned or Draft status.',
        ]);
    }

    public function test_evaluatee_cannot_edit_completed_report(): void
    {
        $report = $this->createReportWithStatus('Completed');

        $payload = [
            'quantity_list' => [
                [
                    'quantity_sub_criteria_id' => $this->quantitySubCriteria->id,
                    'score_C' => 8,
                ]
            ],
            'status' => 'Draft',
        ];

        $response = $this->actingAs($this->evaluatee, 'web')
            ->postJson(route('evaluation_score.store', ['id' => $report->id]), $payload);

        $response->assertStatus(403);
    }
}
