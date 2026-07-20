<?php

namespace Tests\Feature\Report;

use App\Models\Category;
use App\Models\CriteriaVersion;
use App\Models\EvaluationList;
use App\Models\SupportCriteria;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class SupportCriteriaTemplateTest extends TestCase
{
    use RefreshDatabase;

    public function test_support_criteria_schema_exists(): void
    {
        $this->assertTrue(Schema::hasColumns('support_criterias', [
            'id',
            'evaluation_list_id',
            'sequence',
            'activity_name',
            'indicator',
            'target_value',
            'weight',
            'created_at',
            'updated_at',
        ]));
    }

    public function test_evaluation_list_owns_ordered_support_criteria_and_cascades_deletes(): void
    {
        $version = CriteriaVersion::factory()->create();
        $category = Category::factory()->create(['criteria_version_id' => $version->id]);
        $evaluationList = EvaluationList::factory()->create([
            'criteria_version_id' => $version->id,
            'categorie_id' => $category->id,
        ]);

        SupportCriteria::create([
            'evaluation_list_id' => $evaluationList->id,
            'sequence' => 2,
            'activity_name' => 'กิจกรรมที่สอง',
            'indicator' => 'ตัวชี้วัดที่สอง',
            'target_value' => 80,
            'weight' => 40,
        ]);
        SupportCriteria::create([
            'evaluation_list_id' => $evaluationList->id,
            'sequence' => 1,
            'activity_name' => 'กิจกรรมแรก',
            'indicator' => 'ตัวชี้วัดแรก',
            'target_value' => 90.5,
            'weight' => 60,
        ]);

        $this->assertSame(
            ['กิจกรรมแรก', 'กิจกรรมที่สอง'],
            $evaluationList->supportCriterias->pluck('activity_name')->all()
        );
        $this->assertSame('90.50', $evaluationList->supportCriterias->first()->target_value);

        $evaluationList->delete();

        $this->assertDatabaseCount('support_criterias', 0);
    }
}
