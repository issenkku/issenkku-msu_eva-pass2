<?php

namespace Tests\Feature\Evaluation;

use App\Models\AssignmentData;
use App\Models\Assignments;
use App\Models\CriteriaVersion;
use App\Models\EvidenceAnswer;
use App\Models\EvaluationList;
use App\Models\ReportData;
use App\Models\Reports;
use App\Models\Setting\Departments;
use App\Models\Setting\Positions;
use App\Models\Subject;
use App\Models\User;
use App\Models\WorkloadEntry;
use App\Models\WorkloadForm;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Route;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class PreviousWorkloadImportTest extends TestCase
{
    use RefreshDatabase;

    private User $evaluatee;
    private ReportData $reportData;

    protected function setUp(): void
    {
        parent::setUp();

        $evaluateeRole = $this->evaluateeRouteRoleName();
        Role::create(['name' => $evaluateeRole]);

        $department = \Database\Factories\DepartmentFactory::new()->create();
        $position = \Database\Factories\PositionFactory::new()->create();

        $this->evaluatee = User::factory()->create([
            'employee_id' => 'EMP001',
            'password' => Hash::make('password'),
            'status' => 'active',
            'department_id' => $department->id,
            'position_id' => $position->id,
        ]);
        $this->evaluatee->assignRole($evaluateeRole);

        $criteriaVersion = CriteriaVersion::factory()->create();
        $this->reportData = ReportData::factory()->create([
            'criteria_version_id' => $criteriaVersion->id,
        ]);
    }

    public function test_evaluatee_imports_only_missing_workload_entries_from_previous_round(): void
    {
        $evaluationList = EvaluationList::factory()->create([
            'criteria_version_id' => $this->reportData->criteria_version_id,
        ]);
        $quantitySubCriteria = \Database\Factories\QuantitySubCriteriaFactory::new()->create([
            'criteria_version_id' => $this->reportData->criteria_version_id,
            'evaluation_list_id' => $evaluationList->id,
        ]);
        $workloadForm = WorkloadForm::create([
            'formula_logic' => 'hours',
            'quantity_sub_criteria_id' => $quantitySubCriteria->id,
        ]);
        $subject = Subject::create([
            'code' => 'CS101',
            'name_th' => 'Programming',
            'credits' => 3,
            'lecture_credits' => 3,
            'lab_credits' => 0,
            'self_study_credits' => 6,
            'is_active' => true,
        ]);

        $previousReport = $this->createAssignedReport('Completed', '2025-08-01', '2025-08-31');
        $currentReport = $this->createAssignedReport('Draft', '2026-08-01', '2026-08-31');

        $alreadyPresentValues = ['hours' => 3, 'title' => 'already copied'];
        $missingValues = ['hours' => 6, 'title' => 'copy me'];

        $previousDuplicate = WorkloadEntry::create([
            'report_id' => $previousReport->id,
            'workload_form_id' => $workloadForm->id,
            'subject_id' => $subject->id,
            'field_values' => $alreadyPresentValues,
            'calculated_score' => 3,
        ]);
        $previousMissing = WorkloadEntry::create([
            'report_id' => $previousReport->id,
            'workload_form_id' => $workloadForm->id,
            'subject_id' => $subject->id,
            'field_values' => $missingValues,
            'calculated_score' => 6,
        ]);
        WorkloadEntry::create([
            'report_id' => $currentReport->id,
            'workload_form_id' => $workloadForm->id,
            'subject_id' => $subject->id,
            'field_values' => $alreadyPresentValues,
            'calculated_score' => 10,
        ]);

        EvidenceAnswer::create([
            'evaluation_list_id' => $evaluationList->id,
            'report_id' => $previousReport->id,
            'workload_entry_id' => $previousDuplicate->id,
            'link' => 'https://example.test/skip',
        ]);
        EvidenceAnswer::create([
            'evaluation_list_id' => $evaluationList->id,
            'report_id' => $previousReport->id,
            'workload_entry_id' => $previousMissing->id,
            'link' => 'https://example.test/copy',
        ]);

        $this->actingAs($this->evaluatee, 'web')
            ->post(route('evaluatee.import-previous-workload', $currentReport))
            ->assertRedirect()
            ->assertSessionHas('success');

        $this->assertSame(2, WorkloadEntry::where('report_id', $currentReport->id)->count());
        $this->assertDatabaseHas('workload_entries', [
            'report_id' => $currentReport->id,
            'workload_form_id' => $workloadForm->id,
            'subject_id' => $subject->id,
            'calculated_score' => '6.0000',
        ]);
        $this->assertDatabaseMissing('evidence_answers', [
            'report_id' => $currentReport->id,
            'link' => 'https://example.test/skip',
        ]);
        $this->assertDatabaseHas('evidence_answers', [
            'report_id' => $currentReport->id,
            'evaluation_list_id' => $evaluationList->id,
            'link' => 'https://example.test/copy',
        ]);
    }

    public function test_evaluatee_can_choose_which_previous_round_to_import(): void
    {
        $evaluationList = EvaluationList::factory()->create([
            'criteria_version_id' => $this->reportData->criteria_version_id,
        ]);
        $quantitySubCriteria = \Database\Factories\QuantitySubCriteriaFactory::new()->create([
            'criteria_version_id' => $this->reportData->criteria_version_id,
            'evaluation_list_id' => $evaluationList->id,
        ]);
        $workloadForm = WorkloadForm::create([
            'formula_logic' => 'hours',
            'quantity_sub_criteria_id' => $quantitySubCriteria->id,
        ]);
        $subject = Subject::create([
            'code' => 'CS102',
            'name_th' => 'Data Structures',
            'credits' => 3,
            'lecture_credits' => 3,
            'lab_credits' => 0,
            'self_study_credits' => 6,
            'is_active' => true,
        ]);

        $olderReport = $this->createAssignedReport('Completed', '2024-08-01', '2024-08-31');
        $latestReport = $this->createAssignedReport('Completed', '2025-08-01', '2025-08-31');
        $currentReport = $this->createAssignedReport('Draft', '2026-08-01', '2026-08-31');

        WorkloadEntry::create([
            'report_id' => $olderReport->id,
            'workload_form_id' => $workloadForm->id,
            'subject_id' => $subject->id,
            'field_values' => ['hours' => 4, 'title' => 'older source'],
            'calculated_score' => 4,
        ]);
        WorkloadEntry::create([
            'report_id' => $latestReport->id,
            'workload_form_id' => $workloadForm->id,
            'subject_id' => $subject->id,
            'field_values' => ['hours' => 8, 'title' => 'latest source'],
            'calculated_score' => 8,
        ]);

        $this->actingAs($this->evaluatee, 'web')
            ->post(route('evaluatee.import-previous-workload', $currentReport), [
                'source_report_id' => $olderReport->id,
            ])
            ->assertRedirect()
            ->assertSessionHas('success');

        $this->assertDatabaseHas('workload_entries', [
            'report_id' => $currentReport->id,
            'calculated_score' => '4.0000',
        ]);
        $this->assertDatabaseMissing('workload_entries', [
            'report_id' => $currentReport->id,
            'calculated_score' => '8.0000',
        ]);
    }

    private function createAssignedReport(string $status, string $startDate, string $endDate): Reports
    {
        $assignmentData = AssignmentData::factory()->create([
            'start_time' => $startDate,
            'end_time' => $endDate,
        ]);
        $report = Reports::factory()->create([
            'report_data_id' => $this->reportData->id,
            'status' => $status,
        ]);
        Assignments::factory()->create([
            'assignment_data_id' => $assignmentData->id,
            'evaluatee_id' => $this->evaluatee->id,
            'report_id' => $report->id,
        ]);

        return $report;
    }

    private function evaluateeRouteRoleName(): string
    {
        $roleMiddleware = collect(Route::getRoutes()->getByName('evaluatee.dashboard')->gatherMiddleware())
            ->first(fn (string $middleware) => str_starts_with($middleware, 'role:'));

        return substr($roleMiddleware, strlen('role:'));
    }
}
