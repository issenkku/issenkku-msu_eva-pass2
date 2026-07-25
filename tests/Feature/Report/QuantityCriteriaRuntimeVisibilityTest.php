<?php

namespace Tests\Feature\Report;

use App\Models\Category;
use App\Models\CriteriaVersion;
use App\Models\EvaluationList;
use App\Models\QuantityMainCriteria;
use App\Models\QuantitySubCriteria;
use App\Models\User;
use App\Support\EvaluationScoreSummary;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class QuantityCriteriaRuntimeVisibilityTest extends TestCase
{
    use RefreshDatabase;

    public function test_active_scope_excludes_criteria_from_disabled_evaluation_lists(): void
    {
        $owner = User::factory()->create();
        $version = CriteriaVersion::factory()->create([
            'created_by' => $owner->id,
        ]);
        $category = Category::factory()->create([
            'criteria_version_id' => $version->id,
        ]);
        $enabledList = EvaluationList::factory()->create([
            'criteria_version_id' => $version->id,
            'categorie_id' => $category->id,
            'quantity_enabled' => true,
        ]);
        $disabledList = EvaluationList::factory()->create([
            'criteria_version_id' => $version->id,
            'categorie_id' => $category->id,
            'quantity_enabled' => false,
        ]);
        $main = QuantityMainCriteria::factory()->create([
            'criteria_version_id' => $version->id,
        ]);
        $enabledSub = QuantitySubCriteria::factory()->create([
            'criteria_version_id' => $version->id,
            'evaluation_list_id' => $enabledList->id,
            'quantity_main_criteria_id' => $main->id,
        ]);
        $disabledSub = QuantitySubCriteria::factory()->create([
            'criteria_version_id' => $version->id,
            'evaluation_list_id' => $disabledList->id,
            'quantity_main_criteria_id' => $main->id,
        ]);

        $activeIds = QuantitySubCriteria::query()->active()->pluck('id');

        $this->assertTrue($activeIds->contains($enabledSub->id));
        $this->assertFalse($activeIds->contains($disabledSub->id));
    }

    public function test_summary_ignores_stale_quantity_items_when_list_is_disabled(): void
    {
        $summary = EvaluationScoreSummary::fromCategoryItems([[
            'evaluation_lists' => [[
                'quantity_enabled' => false,
                'quantity_items' => [[
                    'sub_criterias' => [['score_d' => 75]],
                ]],
                'quality_items' => [],
                'support_items' => [],
            ]],
        ]]);

        $this->assertFalse($summary['has_quantity']);
        $this->assertSame(0.0, $summary['quantity']);
        $this->assertSame(0.0, $summary['total']);
    }
}
