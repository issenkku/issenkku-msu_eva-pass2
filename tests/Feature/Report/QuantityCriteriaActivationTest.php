<?php

namespace Tests\Feature\Report;

use App\Models\Category;
use App\Models\CriteriaVersion;
use App\Models\EvaluationList;
use App\Models\QuantityMainCriteria;
use App\Models\QuantitySubCriteria;
use App\Models\ReportData;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class QuantityCriteriaActivationTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;

    protected function setUp(): void
    {
        parent::setUp();

        Role::create(['name' => 'admin']);

        $this->admin = User::factory()->create();
        $this->admin->assignRole('admin');
        $this->actingAs($this->admin, 'web');
    }

    public function test_show_exposes_quantity_enabled_independently_from_saved_rows(): void
    {
        $version = CriteriaVersion::factory()->create([
            'created_by' => $this->admin->id,
        ]);
        ReportData::factory()->create([
            'criteria_version_id' => $version->id,
        ]);
        $category = Category::factory()->create([
            'criteria_version_id' => $version->id,
            'sequence' => 1,
        ]);
        EvaluationList::factory()->create([
            'criteria_version_id' => $version->id,
            'categorie_id' => $category->id,
            'sequence' => 1,
        ]);

        $this->getJson(route('report-structure.show', $version->id))
            ->assertOk()
            ->assertJsonPath(
                'data.categories.0.evaluation_lists.0.quantity_enabled',
                false,
            )
            ->assertJsonCount(
                0,
                'data.categories.0.evaluation_lists.0.quantity_main_criterias',
            );
    }

    public function test_migration_enables_only_lists_with_existing_quantity_rows(): void
    {
        $version = CriteriaVersion::factory()->create([
            'created_by' => $this->admin->id,
        ]);
        $category = Category::factory()->create([
            'criteria_version_id' => $version->id,
        ]);
        $withQuantity = EvaluationList::factory()->create([
            'criteria_version_id' => $version->id,
            'categorie_id' => $category->id,
            'quantity_enabled' => false,
        ]);
        $withoutQuantity = EvaluationList::factory()->create([
            'criteria_version_id' => $version->id,
            'categorie_id' => $category->id,
            'quantity_enabled' => false,
        ]);
        $main = QuantityMainCriteria::factory()->create([
            'criteria_version_id' => $version->id,
        ]);
        QuantitySubCriteria::factory()->create([
            'criteria_version_id' => $version->id,
            'evaluation_list_id' => $withQuantity->id,
            'quantity_main_criteria_id' => $main->id,
        ]);

        $migration = require database_path(
            'migrations/2026_07_25_000001_add_quantity_enabled_to_evaluation_lists.php',
        );
        $migration->down();
        $migration->up();

        $this->assertDatabaseHas('evaluation_lists', [
            'id' => $withQuantity->id,
            'quantity_enabled' => true,
        ]);
        $this->assertDatabaseHas('evaluation_lists', [
            'id' => $withoutQuantity->id,
            'quantity_enabled' => false,
        ]);
    }

    public function test_store_requires_quantity_enabled_for_each_evaluation_list(): void
    {
        $payload = [
            'version_name' => 'Activation validation',
            'created_by' => $this->admin->id,
            'report_datas' => [[
                'report_title' => 'Activation validation report',
                'report_description' => null,
                'assessment_type' => 'mixed',
                'comment' => null,
            ]],
            'categories' => [[
                'main_categories' => 'Category',
                'sub_categories' => 'Subcategory',
                'sequence' => 1,
                'evaluation_lists' => [[
                    'name' => 'Evaluation',
                    'sum_score' => 100,
                    'sequence' => 1,
                    'annotation' => null,
                    'quantity_main_criterias' => [],
                    'quality_main_criterias' => [],
                ]],
            ]],
        ];

        $this->postJson(route('report-structure.store'), $payload)
            ->assertUnprocessable()
            ->assertJsonValidationErrors(
                'categories.0.evaluation_lists.0.quantity_enabled',
            );
    }

    public function test_disabling_quantity_preserves_saved_configuration(): void
    {
        $version = CriteriaVersion::factory()->create([
            'created_by' => $this->admin->id,
        ]);
        $reportData = ReportData::factory()->create([
            'criteria_version_id' => $version->id,
        ]);
        $category = Category::factory()->create([
            'criteria_version_id' => $version->id,
            'sequence' => 1,
        ]);
        $evaluationList = EvaluationList::factory()->create([
            'criteria_version_id' => $version->id,
            'categorie_id' => $category->id,
            'sequence' => 1,
            'quantity_enabled' => true,
        ]);
        $main = QuantityMainCriteria::factory()->create([
            'criteria_version_id' => $version->id,
        ]);
        $sub = QuantitySubCriteria::factory()->create([
            'criteria_version_id' => $version->id,
            'evaluation_list_id' => $evaluationList->id,
            'quantity_main_criteria_id' => $main->id,
        ]);
        $formulaId = DB::table('formulas')->insertGetId([
            'condition' => 'D = A x C / B',
            'quantity_main_criteria_id' => $main->id,
        ]);

        $payload = [
            'version_name' => $version->version_name,
            'created_by' => $this->admin->id,
            'report_datas' => [[
                'report_data_id' => $reportData->id,
                'report_title' => $reportData->report_title,
                'report_description' => $reportData->report_description,
                'assessment_type' => $reportData->assessment_type,
                'comment' => $reportData->comment,
            ]],
            'categories' => [[
                'categorie_id' => $category->id,
                'main_categories' => $category->main_categories,
                'sub_categories' => $category->sub_categories,
                'sequence' => 1,
                'evaluation_lists' => [[
                    'evaluation_id' => $evaluationList->id,
                    'name' => $evaluationList->name,
                    'sum_score' => $evaluationList->sum_score,
                    'sequence' => 1,
                    'annotation' => $evaluationList->annotation,
                    'quantity_enabled' => false,
                    'quality_main_criterias' => [],
                ]],
            ]],
        ];

        $this->putJson(route('report-structure.update', $version->id), $payload)
            ->assertOk();

        $this->assertDatabaseHas('evaluation_lists', [
            'id' => $evaluationList->id,
            'quantity_enabled' => false,
        ]);
        $this->assertDatabaseHas('quantity_main_criterias', ['id' => $main->id]);
        $this->assertDatabaseHas('quantity_sub_criterias', ['id' => $sub->id]);
        $this->assertDatabaseHas('formulas', ['id' => $formulaId]);

        $this->getJson(route('report-structure.show', $version->id))
            ->assertOk()
            ->assertJsonPath(
                'data.categories.0.evaluation_lists.0.quantity_enabled',
                false,
            )
            ->assertJsonPath(
                'data.categories.0.evaluation_lists.0.quantity_main_criterias.0.name',
                $main->name,
            );

        $payload['categories'][0]['evaluation_lists'][0]['quantity_enabled'] = true;
        $payload['categories'][0]['evaluation_lists'][0]['quantity_main_criterias'] = [[
            'quantity_main_criteria_id' => $main->id,
            'name' => $main->name,
            'tooltips' => $main->tooltips,
            'formula' => 'D = A x C / B',
            'quantity_sub_criterias' => [[
                'quantity_sub_criteria_id' => $sub->id,
                'name' => $sub->name,
                'sequence' => 1,
                'score_a' => $sub->score_a,
                'score_b' => $sub->score_b,
            ]],
        ]];

        $this->putJson(route('report-structure.update', $version->id), $payload)
            ->assertOk();

        $this->assertDatabaseHas('evaluation_lists', [
            'id' => $evaluationList->id,
            'quantity_enabled' => true,
        ]);
        $this->assertDatabaseHas('quantity_sub_criterias', [
            'id' => $sub->id,
            'name' => $sub->name,
        ]);
    }

    public function test_enabled_quantity_requires_at_least_one_main_criterion(): void
    {
        $payload = [
            'version_name' => 'Enabled quantity validation',
            'created_by' => $this->admin->id,
            'report_datas' => [[
                'report_title' => 'Enabled quantity report',
                'report_description' => null,
                'assessment_type' => 'quantity',
                'comment' => null,
            ]],
            'categories' => [[
                'main_categories' => 'Category',
                'sub_categories' => 'Subcategory',
                'sequence' => 1,
                'evaluation_lists' => [[
                    'name' => 'Evaluation',
                    'sum_score' => 100,
                    'sequence' => 1,
                    'annotation' => null,
                    'quantity_enabled' => true,
                    'quantity_main_criterias' => [],
                    'quality_main_criterias' => [],
                ]],
            ]],
        ];

        $this->postJson(route('report-structure.store'), $payload)
            ->assertUnprocessable()
            ->assertJsonValidationErrors(
                'categories.0.evaluation_lists.0.quantity_main_criterias',
            );
    }
}
