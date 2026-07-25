<?php

namespace Tests\Feature\Report;

use App\Models\Category;
use App\Models\CriteriaVersion;
use App\Models\EvaluationList;
use App\Models\QuantityMainCriteria;
use App\Models\QuantityScore;
use App\Models\QuantityScoreHistory;
use App\Models\QuantitySubCriteria;
use App\Models\ReportData;
use App\Models\Reports;
use App\Models\User;
use App\Models\WorkloadForm;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class QuantityCriteriaDeletionTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;

    protected function setUp(): void
    {
        parent::setUp();

        Role::create(['name' => 'admin']);
        Role::create(['name' => 'staff']);
        $this->admin = User::factory()->create();
        $this->admin->assignRole('admin');
    }

    public function test_permanent_delete_requires_disabled_evaluation_list(): void
    {
        [$version, $evaluationList] = $this->createQuantityStructure(true);

        $this->actingAs($this->admin)
            ->deleteJson($this->deleteUrl($version, $evaluationList))
            ->assertUnprocessable()
            ->assertJsonPath('message', 'กรุณาปิดเกณฑ์ด้านปริมาณก่อนลบถาวร');
    }

    public function test_permanent_delete_returns_dependency_counts_without_deleting(): void
    {
        [$version, $evaluationList, $main, $sub] = $this->createQuantityStructure(false);
        $reportData = ReportData::factory()->create([
            'criteria_version_id' => $version->id,
        ]);
        $report = Reports::factory()->create([
            'report_data_id' => $reportData->id,
        ]);

        WorkloadForm::create([
            'quantity_sub_criteria_id' => $sub->id,
            'formula_logic' => 'A * C / B',
        ]);
        QuantityScore::factory()->create([
            'report_id' => $report->id,
            'quantity_sub_criteria_id' => $sub->id,
        ]);
        QuantityScoreHistory::create([
            'report_id' => $report->id,
            'quantity_sub_criteria_id' => $sub->id,
            'new_score_c' => 1,
            'modifier_user_id' => $this->admin->id,
        ]);

        $this->actingAs($this->admin)
            ->deleteJson($this->deleteUrl($version, $evaluationList))
            ->assertConflict()
            ->assertJsonPath('dependencies.workload_forms', 1)
            ->assertJsonPath('dependencies.quantity_scores', 1)
            ->assertJsonPath('dependencies.quantity_score_histories', 1);

        $this->assertDatabaseHas('quantity_main_criterias', ['id' => $main->id]);
        $this->assertDatabaseHas('quantity_sub_criterias', ['id' => $sub->id]);
    }

    public function test_permanent_delete_rejects_mismatched_version_and_list(): void
    {
        [$version] = $this->createQuantityStructure(false);
        [, $otherEvaluationList] = $this->createQuantityStructure(false);

        $this->actingAs($this->admin)
            ->deleteJson($this->deleteUrl($version, $otherEvaluationList))
            ->assertNotFound();
    }

    public function test_permanent_delete_is_admin_only(): void
    {
        [$version, $evaluationList] = $this->createQuantityStructure(false);
        $staff = User::factory()->create();
        $staff->assignRole('staff');

        $this->actingAs($staff)
            ->deleteJson($this->deleteUrl($version, $evaluationList))
            ->assertForbidden();
    }

    public function test_permanent_delete_removes_only_target_configuration_and_orphaned_mains(): void
    {
        [$version, $evaluationList, $orphanedMain, $targetSub] =
            $this->createQuantityStructure(false);
        $otherList = EvaluationList::factory()->create([
            'criteria_version_id' => $version->id,
            'categorie_id' => $evaluationList->categorie_id,
            'quantity_enabled' => false,
        ]);
        $sharedMain = QuantityMainCriteria::factory()->create([
            'criteria_version_id' => $version->id,
        ]);
        $targetSharedSub = QuantitySubCriteria::factory()->create([
            'criteria_version_id' => $version->id,
            'evaluation_list_id' => $evaluationList->id,
            'quantity_main_criteria_id' => $sharedMain->id,
        ]);
        $otherSharedSub = QuantitySubCriteria::factory()->create([
            'criteria_version_id' => $version->id,
            'evaluation_list_id' => $otherList->id,
            'quantity_main_criteria_id' => $sharedMain->id,
        ]);

        $this->actingAs($this->admin)
            ->deleteJson($this->deleteUrl($version, $evaluationList))
            ->assertOk()
            ->assertJsonPath('deleted.main_criteria', 1)
            ->assertJsonPath('deleted.sub_criteria', 2);

        $this->assertDatabaseMissing('quantity_sub_criterias', ['id' => $targetSub->id]);
        $this->assertDatabaseMissing('quantity_sub_criterias', ['id' => $targetSharedSub->id]);
        $this->assertDatabaseMissing('quantity_main_criterias', ['id' => $orphanedMain->id]);
        $this->assertDatabaseHas('quantity_main_criterias', ['id' => $sharedMain->id]);
        $this->assertDatabaseHas('quantity_sub_criterias', ['id' => $otherSharedSub->id]);
        $this->assertDatabaseHas('activity_log', [
            'log_name' => 'report_structure',
            'description' => 'ลบข้อมูลเกณฑ์ปริมาณทั้งหมด',
            'subject_id' => $evaluationList->id,
            'causer_id' => $this->admin->id,
        ]);
    }

    public function test_editor_exposes_a_separate_guarded_permanent_delete_action(): void
    {
        $template = file_get_contents(resource_path(
            'views/criteria_config/partials/edit-evaluation-template.blade.php',
        ));
        $handler = file_get_contents(resource_path(
            'views/criteria_config/partials/script-edit-quantity-handlers.blade.php',
        ));

        $this->assertStringContainsString('delete_all_quantity_criteria_btn', $template);
        $this->assertStringContainsString('ลบข้อมูลเกณฑ์ปริมาณทั้งหมด', $template);
        $this->assertStringContainsString('handleDeleteAllQuantityCriteria', $handler);
        $this->assertStringContainsString('response.status === 409', $handler);
        $this->assertStringContainsString('quantity_score_histories', $handler);
    }

    /**
     * @return array{CriteriaVersion, EvaluationList, QuantityMainCriteria, QuantitySubCriteria}
     */
    private function createQuantityStructure(bool $enabled): array
    {
        $version = CriteriaVersion::factory()->create([
            'created_by' => $this->admin->id,
        ]);
        $category = Category::factory()->create([
            'criteria_version_id' => $version->id,
        ]);
        $evaluationList = EvaluationList::factory()->create([
            'criteria_version_id' => $version->id,
            'categorie_id' => $category->id,
            'quantity_enabled' => $enabled,
        ]);
        $main = QuantityMainCriteria::factory()->create([
            'criteria_version_id' => $version->id,
        ]);
        $sub = QuantitySubCriteria::factory()->create([
            'criteria_version_id' => $version->id,
            'evaluation_list_id' => $evaluationList->id,
            'quantity_main_criteria_id' => $main->id,
        ]);

        return [$version, $evaluationList, $main, $sub];
    }

    private function deleteUrl(
        CriteriaVersion $version,
        EvaluationList $evaluationList,
    ): string {
        return route('report-structure.quantity-criteria.destroy', [
            'criteriaVersion' => $version,
            'evaluationList' => $evaluationList,
        ]);
    }
}
