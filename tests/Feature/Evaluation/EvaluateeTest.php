<?php

namespace Tests\Feature\Evaluation;

use App\Models\AssignmentData;
use App\Models\Assignments;
use App\Models\Category;
use App\Models\CriteriaVersion;
use App\Models\EvaluationList;
use App\Models\EvidenceAnswer;
use App\Models\QualitySubCriteria;
use App\Models\QuantityScore;
use App\Models\QuantitySubCriteria;
use App\Models\ReportData;
use App\Models\Reports;
use App\Models\Setting\Departments;
use App\Models\Setting\Positions;
use App\Models\Subject;
use App\Models\SupportActivityEntry;
use App\Models\SupportCriteria;
use App\Models\User;
use App\Models\WorkloadEntry;
use App\Models\WorkloadForm;
use Database\Factories\DepartmentFactory;
use Database\Factories\PositionFactory;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Schema;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

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

    protected SupportCriteria $supportCriterion;

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
        $supportCategory = Category::factory()->create([
            'criteria_version_id' => $this->criteriaVersion->id,
        ]);
        $supportEvaluationList = EvaluationList::factory()->create([
            'criteria_version_id' => $this->criteriaVersion->id,
            'categorie_id' => $supportCategory->id,
        ]);
        $this->supportCriterion = SupportCriteria::create([
            'evaluation_list_id' => $supportEvaluationList->id,
            'sequence' => 1,
            'activity_name' => 'จัดทำรายงาน',
            'indicator' => 'ส่งตรงเวลา',
            'target_value' => 100,
            'weight' => 20,
            'require_evidence' => false,
            'allow_activity_entries' => true,
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
                ],
            ],
            'quality_list' => [
                [
                    'quality_sub_criteria_id' => $this->qualitySubCriteria->id,
                    'score' => 5,
                ],
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
                ],
            ],
            'quality_list' => [
                [
                    'quality_sub_criteria_id' => $this->qualitySubCriteria->id,
                    'score' => 4,
                ],
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
                ],
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
                ],
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
                ],
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
                ],
            ],
            'status' => 'Draft',
        ];

        $response = $this->actingAs($this->evaluatee, 'web')
            ->postJson(route('evaluation_score.store', ['id' => $report->id]), $payload);

        $response->assertStatus(403);
    }

    public function test_evaluatee_can_save_support_score_and_evidence_as_draft(): void
    {
        $report = $this->createReportWithStatus('Assigned');

        $response = $this->actingAs($this->evaluatee, 'web')->post(
            route('evaluation_score.store', ['id' => $report->id]),
            $this->supportPayload('Draft', '125.50', [
                ['content' => '<p>โครงการพร้อมหลักฐาน</p>'],
            ])
        );

        $response->assertRedirect('/evaluatee-dashboard');
        $this->assertDatabaseHas('support_scores', [
            'report_id' => $report->id,
            'support_criteria_id' => $this->supportCriterion->id,
            'achieved_score' => '125.50',
            'weighted_score' => '25.10',
        ]);
        $this->assertDatabaseHas('evidence_answers', [
            'report_id' => $report->id,
            'support_criteria_id' => $this->supportCriterion->id,
            'link' => 'https://example.com/support-evidence',
        ]);
        $this->assertDatabaseHas('reports', [
            'id' => $report->id,
            'status' => 'Draft',
            'support_score_total' => '25.10',
        ]);
    }

    public function test_evaluatee_can_submit_with_blank_support_score_when_required_evidence_exists(): void
    {
        $this->supportCriterion->update(['require_evidence' => true]);
        $report = $this->createReportWithStatus('Draft');

        $this->actingAs($this->evaluatee, 'web')
            ->post(route('evaluation_score.store', ['id' => $report->id]), $this->supportPayload('Pending', null, [
                ['content' => '<p>โครงการพร้อมหลักฐาน</p>'],
            ]))
            ->assertRedirect('/evaluatee-dashboard');

        $this->assertDatabaseHas('support_scores', [
            'report_id' => $report->id,
            'support_criteria_id' => $this->supportCriterion->id,
            'achieved_score' => null,
            'weighted_score' => null,
        ]);
        $this->assertDatabaseHas('reports', [
            'id' => $report->id,
            'status' => 'Pending',
        ]);
    }

    public function test_support_evidence_is_required_for_both_draft_and_submit(): void
    {
        $this->supportCriterion->update(['require_evidence' => true]);

        foreach ([['Assigned', 'Draft'], ['Draft', 'Pending']] as [$initialStatus, $requestedStatus]) {
            $report = $this->createReportWithStatus($initialStatus);
            $payload = $this->supportPayload($requestedStatus, null, [
                ['content' => '<p>โครงการไม่มีหลักฐาน</p>', 'evidence_links' => []],
            ]);

            $this->actingAs($this->evaluatee, 'web')
                ->postJson(route('evaluation_score.store', ['id' => $report->id]), $payload)
                ->assertUnprocessable()
                ->assertJsonValidationErrors([
                    "support_list.{$this->supportCriterion->id}.activity_entries",
                ]);
        }
    }

    public function test_support_score_cannot_be_saved_after_evaluatee_stage(): void
    {
        $report = $this->createReportWithStatus('Pending');

        $this->actingAs($this->evaluatee, 'web')
            ->postJson(
                route('evaluation_score.store', ['id' => $report->id]),
                $this->supportPayload('Draft', 50)
            )
            ->assertForbidden();

        $this->assertDatabaseMissing('support_scores', ['report_id' => $report->id]);
    }

    public function test_saving_support_evidence_preserves_quality_and_workload_evidence(): void
    {
        $report = $this->createReportWithStatus('Draft');
        $qualityMainId = $this->qualitySubCriteria->quality_main_criteria_id;
        $qualityEvidence = EvidenceAnswer::create([
            'evaluation_list_id' => $this->qualitySubCriteria->evaluation_list_id,
            'quality_main_criteria_id' => $qualityMainId,
            'report_id' => $report->id,
            'link' => 'https://example.com/quality-evidence',
        ]);
        $workloadForm = WorkloadForm::create([
            'quantity_sub_criteria_id' => $this->quantitySubCriteria->id,
            'formula_logic' => '1',
        ]);
        $subject = Subject::create([
            'code' => 'SUP101',
            'name_th' => 'วิชาทดสอบ',
            'credits' => 3,
        ]);
        $workloadEntry = WorkloadEntry::create([
            'field_values' => [],
            'report_id' => $report->id,
            'workload_form_id' => $workloadForm->id,
            'subject_id' => $subject->id,
        ]);
        $workloadEvidence = EvidenceAnswer::create([
            'evaluation_list_id' => $this->supportCriterion->evaluation_list_id,
            'report_id' => $report->id,
            'workload_entry_id' => $workloadEntry->id,
            'link' => 'https://example.com/workload-evidence',
        ]);
        $payload = $this->supportPayload('Draft', 100, [
            ['content' => '<p>โครงการพร้อมหลักฐาน</p>'],
        ]);
        $payload['evidence_list'] = [
            $qualityMainId => [
                'evaluation_list_id' => $this->qualitySubCriteria->evaluation_list_id,
                'quality_main_criteria_id' => $qualityMainId,
                'links' => ['https://example.com/quality-evidence'],
            ],
        ];

        $this->actingAs($this->evaluatee, 'web')
            ->post(route('evaluation_score.store', ['id' => $report->id]), $payload)
            ->assertRedirect('/evaluatee-dashboard');

        $this->assertDatabaseHas('evidence_answers', [
            'quality_main_criteria_id' => $qualityMainId,
            'link' => $qualityEvidence->link,
        ]);
        $this->assertDatabaseHas('evidence_answers', [
            'workload_entry_id' => $workloadEntry->id,
            'link' => $workloadEvidence->link,
        ]);
    }

    public function test_evaluatee_can_add_update_and_delete_support_activities_across_draft_and_submit(): void
    {
        $report = $this->createReportWithStatus('Assigned');
        $draftPayload = $this->supportPayload('Draft', 100, [
            ['content' => '<p>โครงการแรก</p>'],
            ['content' => '<p>โครงการที่จะลบ</p>'],
        ]);

        $this->actingAs($this->evaluatee, 'web')
            ->post(route('evaluation_score.store', ['id' => $report->id]), $draftPayload)
            ->assertRedirect('/evaluatee-dashboard');

        $first = SupportActivityEntry::query()
            ->where('report_id', $report->id)
            ->orderBy('sequence')
            ->firstOrFail();
        $submitPayload = $this->supportPayload('Pending', 100, [
            ['id' => $first->id, 'content' => '<p>แก้ไขโครงการแรก</p>'],
            ['content' => '<p>โครงการใหม่</p>'],
        ]);

        $this->actingAs($this->evaluatee, 'web')
            ->post(route('evaluation_score.store', ['id' => $report->id]), $submitPayload)
            ->assertRedirect('/evaluatee-dashboard');

        $this->assertSame(
            ['<p>แก้ไขโครงการแรก</p>', '<p>โครงการใหม่</p>'],
            SupportActivityEntry::query()
                ->where('report_id', $report->id)
                ->orderBy('sequence')
                ->pluck('content')
                ->all()
        );
        $this->assertDatabaseCount('support_activity_entry_histories', 0);
        $this->assertSame('Pending', $report->fresh()->status);
    }

    public function test_evaluatee_can_save_and_submit_projects_grouped_by_indicator_item(): void
    {
        $this->supportCriterion->update([
            'indicator' => null,
            'group_activity_entries_by_indicator' => true,
        ]);
        $first = $this->supportCriterion->indicatorItems()->create([
            'sequence' => 1, 'code' => '2.1', 'description' => '<p>วิจัย</p>',
        ]);
        $second = $this->supportCriterion->indicatorItems()->create([
            'sequence' => 2, 'code' => '2.2', 'description' => '<p>เผยแพร่</p>',
        ]);
        $this->supportCriterion->indicatorItems()->create([
            'sequence' => 3, 'code' => '2.3', 'description' => '<p>กลุ่มว่าง</p>',
        ]);
        $report = $this->createReportWithStatus('Assigned');
        $draftPayload = $this->supportPayload('Draft', 100, [
            ['support_indicator_item_id' => $first->id, 'content' => '<p>โครงการ A</p>'],
            ['support_indicator_item_id' => $first->id, 'content' => '<p>โครงการ B</p>'],
            ['support_indicator_item_id' => $second->id, 'content' => '<p>โครงการ C</p>'],
        ]);

        $this->actingAs($this->evaluatee, 'web')
            ->post(route('evaluation_score.store', ['id' => $report->id]), $draftPayload)
            ->assertRedirect('/evaluatee-dashboard')
            ->assertSessionHasNoErrors();

        $savedEntries = SupportActivityEntry::query()
            ->where('report_id', $report->id)
            ->orderBy('sequence')
            ->get();
        $submitPayload = $this->supportPayload('Pending', 100, $savedEntries
            ->map(fn (SupportActivityEntry $entry) => [
                'id' => $entry->id,
                'support_indicator_item_id' => $entry->support_indicator_item_id,
                'content' => $entry->content,
            ])->all());

        $this->actingAs($this->evaluatee, 'web')
            ->post(route('evaluation_score.store', ['id' => $report->id]), $submitPayload)
            ->assertRedirect('/evaluatee-dashboard')
            ->assertSessionHasNoErrors();

        $this->assertSame(
            [$first->id, $first->id, $second->id],
            SupportActivityEntry::query()
                ->where('report_id', $report->id)
                ->orderBy('sequence')
                ->pluck('support_indicator_item_id')
                ->all()
        );
        $this->assertSame('Pending', $report->fresh()->status);
    }

    public function test_grouped_project_rejects_missing_or_foreign_indicator_assignment(): void
    {
        $this->supportCriterion->update([
            'indicator' => null,
            'group_activity_entries_by_indicator' => true,
        ]);
        $this->supportCriterion->indicatorItems()->create([
            'sequence' => 1, 'code' => '2.1', 'description' => '<p>วิจัย</p>',
        ]);
        $otherCriterion = SupportCriteria::create([
            'evaluation_list_id' => $this->supportCriterion->evaluation_list_id,
            'sequence' => 2,
            'activity_name' => '<p>เกณฑ์อื่น</p>',
            'indicator' => null,
            'target_value' => 100,
            'weight' => 10,
            'allow_activity_entries' => true,
            'group_activity_entries_by_indicator' => true,
        ]);
        $foreign = $otherCriterion->indicatorItems()->create([
            'sequence' => 1, 'code' => '9.1', 'description' => '<p>ต่างเกณฑ์</p>',
        ]);
        $report = $this->createReportWithStatus('Assigned');

        foreach ([null, $foreign->id] as $indicatorItemId) {
            $payload = $this->supportPayload('Draft', 100, [[
                'support_indicator_item_id' => $indicatorItemId,
                'content' => '<p>โครงการ</p>',
            ]]);

            $this->actingAs($this->evaluatee, 'web')
                ->postJson(route('evaluation_score.store', ['id' => $report->id]), $payload)
                ->assertUnprocessable()
                ->assertJsonValidationErrors(
                    "support_list.{$this->supportCriterion->id}.activity_entries.0.support_indicator_item_id"
                );
        }

        $this->assertDatabaseMissing('support_activity_entries', ['report_id' => $report->id]);
        $this->assertSame('Assigned', $report->fresh()->status);
    }

    public function test_invalid_support_activity_rolls_back_the_evaluatee_submission(): void
    {
        $report = $this->createReportWithStatus('Assigned');
        $this->actingAs($this->evaluatee, 'web')
            ->post(route('evaluation_score.store', ['id' => $report->id]), $this->supportPayload('Draft', 80, [
                ['content' => '<p>ข้อมูลเดิม</p>'],
            ]))
            ->assertRedirect('/evaluatee-dashboard')
            ->assertSessionHasNoErrors();

        $payload = $this->supportPayload('Pending', 90, [
            ['content' => '<p><br></p>'],
        ]);
        $this->actingAs($this->evaluatee, 'web')
            ->postJson(route('evaluation_score.store', ['id' => $report->id]), $payload)
            ->assertUnprocessable()
            ->assertJsonValidationErrors([
                "support_list.{$this->supportCriterion->id}.activity_entries.0.content",
            ]);

        $this->assertDatabaseHas('support_scores', [
            'report_id' => $report->id,
            'support_criteria_id' => $this->supportCriterion->id,
            'achieved_score' => '80.00',
        ]);
        $this->assertDatabaseHas('support_activity_entries', [
            'report_id' => $report->id,
            'content' => '<p>ข้อมูลเดิม</p>',
        ]);
        $this->assertSame('Draft', $report->fresh()->status);
    }

    /** @param array<int, array<string, mixed>> $activityEntries */
    private function supportPayload(string $status, string|int|null $score, array $activityEntries = []): array
    {
        $activityEntries = array_values($activityEntries);
        if ($activityEntries !== []
            && ! array_key_exists('evidence_links', $activityEntries[0])) {
            $activityEntries[0]['evidence_links'] = ['https://example.com/support-evidence'];
        }

        return [
            'support_list' => [
                $this->supportCriterion->id => [
                    'support_criteria_id' => $this->supportCriterion->id,
                    'achieved_score' => $score,
                    'modification_reason' => null,
                    'evidence_links' => [],
                    'activity_entries' => $activityEntries,
                ],
            ],
            'status' => $status,
        ];
    }
}
