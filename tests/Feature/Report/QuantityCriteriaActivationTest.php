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
}
