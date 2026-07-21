<?php

namespace Tests\Feature\Report;

use App\Models\Category;
use App\Models\CriteriaVersion;
use App\Models\EvaluationList;
use App\Models\Reports;
use App\Models\SupportActivityEntry;
use App\Models\SupportCriteria;
use App\Models\User;
use Database\Factories\DepartmentFactory;
use Database\Factories\PositionFactory;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Schema;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class SupportCriteriaTemplateTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;

    protected function setUp(): void
    {
        parent::setUp();

        Role::create(['name' => 'admin']);
        $department = DepartmentFactory::new()->create();
        $position = PositionFactory::new()->create();
        $this->admin = User::factory()->create([
            'employee_id' => 'SUPPORT-ADMIN',
            'password' => Hash::make('password'),
            'status' => 'active',
            'department_id' => $department->id,
            'position_id' => $position->id,
        ]);
        $this->admin->assignRole('admin');
        $this->actingAs($this->admin, 'web');
    }

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
            'require_evidence',
            'allow_activity_entries',
            'created_at',
            'updated_at',
        ]));
    }

    public function test_activity_name_supports_rich_text_storage(): void
    {
        $this->assertSame('text', Schema::getColumnType('support_criterias', 'activity_name'));
    }

    public function test_support_criteria_disables_activity_entries_by_default(): void
    {
        $version = CriteriaVersion::factory()->create();
        $category = Category::factory()->create(['criteria_version_id' => $version->id]);
        $evaluationList = EvaluationList::factory()->create([
            'criteria_version_id' => $version->id,
            'categorie_id' => $category->id,
        ]);

        $criterion = SupportCriteria::create([
            'evaluation_list_id' => $evaluationList->id,
            'sequence' => 1,
            'activity_name' => 'งานตามหน้าที่',
            'indicator' => 'ส่งงานตรงเวลา',
            'target_value' => 100,
            'weight' => 20,
        ]);

        $this->assertFalse($criterion->fresh()->allow_activity_entries);
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

    public function test_admin_can_create_and_show_support_criteria_template(): void
    {
        $response = $this->postJson(route('report-structure.store'), $this->payload([
            [
                'sequence' => 1,
                'activity_name' => 'พัฒนาระบบบริการ',
                'indicator' => 'งานเสร็จตามแผน',
                'target_value' => 95.5,
                'weight' => 60,
                'require_evidence' => true,
                'allow_activity_entries' => true,
            ],
            [
                'sequence' => 2,
                'activity_name' => 'สนับสนุนผู้ใช้งาน',
                'indicator' => 'แก้ปัญหาภายใน SLA',
                'target_value' => 90,
                'weight' => 40,
            ],
        ]));

        $response->assertCreated()->assertJson(['success' => true]);
        $versionId = $response->json('data.id');

        $this->assertDatabaseHas('support_criterias', [
            'activity_name' => 'พัฒนาระบบบริการ',
            'target_value' => 95.5,
            'weight' => 60,
            'require_evidence' => true,
            'allow_activity_entries' => true,
        ]);

        $this->getJson(route('report-structure.show', $versionId))
            ->assertOk()
            ->assertJsonPath('data.categories.0.evaluation_lists.0.support_criterias.0.activity_name', 'พัฒนาระบบบริการ')
            ->assertJsonPath('data.categories.0.evaluation_lists.0.support_criterias.0.target_value', 95.5)
            ->assertJsonPath('data.categories.0.evaluation_lists.0.support_criterias.0.require_evidence', true)
            ->assertJsonPath('data.categories.0.evaluation_lists.0.support_criterias.0.allow_activity_entries', true)
            ->assertJsonPath('data.categories.0.evaluation_lists.0.support_criterias.1.sequence', 2);
    }

    public function test_admin_can_create_and_show_grouped_support_indicator_items(): void
    {
        $response = $this->postJson(route('report-structure.store'), $this->payload([[
            'sequence' => 1,
            'activity_name' => '<p>งานวิจัย</p>',
            'indicator' => null,
            'target_value' => 100,
            'weight' => 100,
            'allow_activity_entries' => true,
            'group_activity_entries_by_indicator' => true,
            'indicator_items' => [
                ['sequence' => 1, 'code' => '2.1', 'description' => '<p>ดำเนินการวิจัย</p>'],
                ['sequence' => 2, 'code' => '2.2', 'description' => '<p>เผยแพร่งานวิจัย</p>'],
            ],
        ]]))->assertCreated();

        $criterion = SupportCriteria::with('indicatorItems')->firstOrFail();
        $this->assertNull($criterion->indicator);
        $this->assertTrue($criterion->group_activity_entries_by_indicator);
        $this->assertSame(['2.1', '2.2'], $criterion->indicatorItems->pluck('code')->all());

        $this->getJson(route('report-structure.show', $response->json('data.id')))
            ->assertOk()
            ->assertJsonPath(
                'data.categories.0.evaluation_lists.0.support_criterias.0.indicator_items.1.code',
                '2.2'
            );
    }

    public function test_grouped_support_indicator_configuration_is_validated(): void
    {
        $base = [
            'sequence' => 1,
            'activity_name' => '<p>งานวิจัย</p>',
            'indicator' => null,
            'target_value' => 100,
            'weight' => 100,
            'allow_activity_entries' => true,
            'group_activity_entries_by_indicator' => true,
            'indicator_items' => [[
                'sequence' => 1,
                'code' => '2.1',
                'description' => '<p>ดำเนินการวิจัย</p>',
            ]],
        ];

        $cases = [
            'missing items' => [
                fn (array $criterion) => array_replace($criterion, ['indicator_items' => []]),
                'categories.0.evaluation_lists.0.support_criterias.0.indicator_items',
            ],
            'duplicate codes' => [
                fn (array $criterion) => array_replace($criterion, ['indicator_items' => [
                    ['sequence' => 1, 'code' => '2.1', 'description' => '<p>หนึ่ง</p>'],
                    ['sequence' => 2, 'code' => '2.1', 'description' => '<p>สอง</p>'],
                ]]),
                'categories.0.evaluation_lists.0.support_criterias.0.indicator_items.1.code',
            ],
            'activities disabled' => [
                fn (array $criterion) => array_replace($criterion, ['allow_activity_entries' => false]),
                'categories.0.evaluation_lists.0.support_criterias.0.group_activity_entries_by_indicator',
            ],
            'legacy indicator missing' => [
                fn (array $criterion) => array_replace($criterion, [
                    'group_activity_entries_by_indicator' => false,
                    'indicator_items' => [],
                ]),
                'categories.0.evaluation_lists.0.support_criterias.0.indicator',
            ],
        ];

        foreach ($cases as [$mutate, $errorKey]) {
            $this->postJson(
                route('report-structure.store'),
                $this->payload([$mutate($base)])
            )->assertUnprocessable()->assertJsonValidationErrors($errorKey);
        }
    }

    public function test_admin_can_store_formatted_support_criteria_content(): void
    {
        $activityName = '<p><strong>กิจกรรม</strong> '.str_repeat('รายละเอียด ', 40).'</p>';
        $indicator = '<ul><li>ทำครบตามแผน</li></ul>';

        $response = $this->postJson(route('report-structure.store'), $this->payload([[
            'sequence' => 1,
            'activity_name' => $activityName,
            'indicator' => $indicator,
            'target_value' => 90,
            'weight' => 100,
        ]]));

        $response->assertCreated();
        $this->assertDatabaseHas('support_criterias', [
            'activity_name' => $activityName,
            'indicator' => $indicator,
        ]);
    }

    public function test_support_criteria_rejects_rich_text_without_visible_text(): void
    {
        $response = $this->postJson(route('report-structure.store'), $this->payload([[
            'sequence' => 1,
            'activity_name' => '<p><br></p>',
            'indicator' => '<p>เกณฑ์</p>',
            'target_value' => 90,
            'weight' => 100,
        ]]));

        $response->assertUnprocessable()
            ->assertJsonValidationErrors('categories.0.evaluation_lists.0.support_criterias.0.activity_name');
    }

    public function test_support_template_rejects_invalid_numeric_values(): void
    {
        $response = $this->postJson(route('report-structure.store'), $this->payload([[
            'sequence' => 1,
            'activity_name' => 'งานสนับสนุน',
            'indicator' => 'ตัวชี้วัด',
            'target_value' => -1,
            'weight' => 101,
        ]]));

        $response->assertUnprocessable()->assertJsonValidationErrors([
            'categories.0.evaluation_lists.0.support_criterias.0.target_value',
            'categories.0.evaluation_lists.0.support_criterias.0.weight',
        ]);
    }

    public function test_admin_can_update_add_reorder_and_remove_support_criteria(): void
    {
        $created = $this->postJson(route('report-structure.store'), $this->payload([
            ['sequence' => 1, 'activity_name' => 'เดิมหนึ่ง', 'indicator' => 'ตัวชี้วัดหนึ่ง', 'target_value' => 80, 'weight' => 50],
            ['sequence' => 2, 'activity_name' => 'เดิมสอง', 'indicator' => 'ตัวชี้วัดสอง', 'target_value' => 90, 'weight' => 50],
        ]))->assertCreated();

        $versionId = $created->json('data.id');
        $version = CriteriaVersion::with([
            'reportDatas',
            'categories.evaluationLists.supportCriterias',
        ])->findOrFail($versionId);
        $category = $version->categories->first();
        $evaluationList = $category->evaluationLists->first();
        $kept = $evaluationList->supportCriterias->first();
        $removed = $evaluationList->supportCriterias->last();

        $payload = $this->payload([
            [
                'support_criteria_id' => $kept->id,
                'sequence' => 2,
                'activity_name' => 'แก้ไขรายการเดิม',
                'indicator' => 'ตัวชี้วัดใหม่',
                'target_value' => 99,
                'weight' => 70,
                'require_evidence' => true,
                'allow_activity_entries' => true,
            ],
            [
                'sequence' => 1,
                'activity_name' => 'เพิ่มรายการใหม่',
                'indicator' => 'ตัวชี้วัดรายการใหม่',
                'target_value' => 75,
                'weight' => 30,
            ],
        ]);
        $payload['version_name'] = $version->version_name;
        $payload['report_datas'][0]['report_data_id'] = $version->reportDatas->first()->id;
        $payload['categories'][0]['categorie_id'] = $category->id;
        $payload['categories'][0]['evaluation_lists'][0]['evaluation_id'] = $evaluationList->id;

        $this->putJson(route('report-structure.update', $versionId), $payload)
            ->assertOk()
            ->assertJson(['success' => true]);

        $this->assertDatabaseHas('support_criterias', [
            'id' => $kept->id,
            'sequence' => 2,
            'activity_name' => 'แก้ไขรายการเดิม',
            'require_evidence' => true,
            'allow_activity_entries' => true,
        ]);
        $this->assertDatabaseHas('support_criterias', [
            'evaluation_list_id' => $evaluationList->id,
            'sequence' => 1,
            'activity_name' => 'เพิ่มรายการใหม่',
        ]);
        $this->assertDatabaseMissing('support_criterias', ['id' => $removed->id]);
    }

    public function test_admin_can_disable_activity_entries_when_updating_a_support_criterion(): void
    {
        $created = $this->postJson(route('report-structure.store'), $this->payload([[
            'sequence' => 1,
            'activity_name' => 'งานที่เปิดให้เพิ่มกิจกรรม',
            'indicator' => 'ตัวชี้วัด',
            'target_value' => 100,
            'weight' => 100,
            'allow_activity_entries' => true,
        ]]))->assertCreated();

        $versionId = $created->json('data.id');
        $version = CriteriaVersion::with([
            'reportDatas',
            'categories.evaluationLists.supportCriterias',
        ])->findOrFail($versionId);
        $category = $version->categories->first();
        $evaluationList = $category->evaluationLists->first();
        $criterion = $evaluationList->supportCriterias->first();
        $payload = $this->payload([[
            'support_criteria_id' => $criterion->id,
            'sequence' => 1,
            'activity_name' => $criterion->activity_name,
            'indicator' => $criterion->indicator,
            'target_value' => $criterion->target_value,
            'weight' => $criterion->weight,
            'allow_activity_entries' => false,
        ]]);
        $payload['version_name'] = $version->version_name;
        $payload['report_datas'][0]['report_data_id'] = $version->reportDatas->first()->id;
        $payload['categories'][0]['categorie_id'] = $category->id;
        $payload['categories'][0]['evaluation_lists'][0]['evaluation_id'] = $evaluationList->id;

        $this->putJson(route('report-structure.update', $versionId), $payload)->assertOk();

        $this->assertDatabaseHas('support_criterias', [
            'id' => $criterion->id,
            'allow_activity_entries' => false,
        ]);
    }

    public function test_admin_cannot_remove_or_disable_grouped_indicator_items_with_projects(): void
    {
        $created = $this->postJson(route('report-structure.store'), $this->payload([[
            'sequence' => 1,
            'activity_name' => '<p>งานวิจัย</p>',
            'indicator' => null,
            'target_value' => 100,
            'weight' => 100,
            'allow_activity_entries' => true,
            'group_activity_entries_by_indicator' => true,
            'indicator_items' => [
                ['sequence' => 1, 'code' => '2.1', 'description' => '<p>ดำเนินการวิจัย</p>'],
                ['sequence' => 2, 'code' => '2.2', 'description' => '<p>เผยแพร่งานวิจัย</p>'],
            ],
        ]]))->assertCreated();

        $version = CriteriaVersion::with([
            'reportDatas',
            'categories.evaluationLists.supportCriterias.indicatorItems',
        ])->findOrFail($created->json('data.id'));
        $category = $version->categories->first();
        $evaluationList = $category->evaluationLists->first();
        $criterion = $evaluationList->supportCriterias->first();
        $first = $criterion->indicatorItems->first();
        $second = $criterion->indicatorItems->last();
        $report = Reports::factory()->create([
            'report_data_id' => $version->reportDatas->first()->id,
        ]);
        $entry = SupportActivityEntry::create([
            'report_id' => $report->id,
            'support_criteria_id' => $criterion->id,
            'support_indicator_item_id' => $first->id,
            'sequence' => 1,
            'content' => '<p>โครงการที่อ้างข้อ 2.1</p>',
        ]);

        $payload = $this->payload([[
            'support_criteria_id' => $criterion->id,
            'sequence' => 1,
            'activity_name' => $criterion->activity_name,
            'indicator' => null,
            'target_value' => $criterion->target_value,
            'weight' => $criterion->weight,
            'allow_activity_entries' => true,
            'group_activity_entries_by_indicator' => true,
            'indicator_items' => [[
                'support_indicator_item_id' => $second->id,
                'sequence' => 1,
                'code' => $second->code,
                'description' => $second->description,
            ]],
        ]]);
        $payload['version_name'] = $version->version_name;
        $payload['report_datas'][0]['report_data_id'] = $version->reportDatas->first()->id;
        $payload['categories'][0]['categorie_id'] = $category->id;
        $payload['categories'][0]['evaluation_lists'][0]['evaluation_id'] = $evaluationList->id;

        $this->putJson(route('report-structure.update', $version->id), $payload)
            ->assertUnprocessable();

        $disablePayload = $payload;
        $disablePayload['categories'][0]['evaluation_lists'][0]['support_criterias'][0][
            'group_activity_entries_by_indicator'
        ] = false;
        $disablePayload['categories'][0]['evaluation_lists'][0]['support_criterias'][0]['indicator'] =
            '<p>เกณฑ์เดิม</p>';
        $disablePayload['categories'][0]['evaluation_lists'][0]['support_criterias'][0]['indicator_items'] = [];

        $this->putJson(route('report-structure.update', $version->id), $disablePayload)
            ->assertUnprocessable();

        $this->assertDatabaseHas('support_criterias', [
            'id' => $criterion->id,
            'group_activity_entries_by_indicator' => true,
        ]);
        $this->assertDatabaseHas('support_indicator_items', ['id' => $first->id]);
        $this->assertDatabaseHas('support_activity_entries', [
            'id' => $entry->id,
            'support_indicator_item_id' => $first->id,
        ]);
    }

    public function test_omitting_support_criteria_on_update_removes_existing_template_rows(): void
    {
        $created = $this->postJson(route('report-structure.store'), $this->payload([
            ['sequence' => 1, 'activity_name' => 'ต้องถูกลบ', 'indicator' => 'ตัวชี้วัด', 'target_value' => 80, 'weight' => 100],
        ]))->assertCreated();

        $versionId = $created->json('data.id');
        $version = CriteriaVersion::with(['reportDatas', 'categories.evaluationLists'])->findOrFail($versionId);
        $category = $version->categories->first();
        $evaluationList = $category->evaluationLists->first();
        $payload = $this->payload([]);
        unset($payload['categories'][0]['evaluation_lists'][0]['support_criterias']);
        $payload['version_name'] = $version->version_name;
        $payload['report_datas'][0]['report_data_id'] = $version->reportDatas->first()->id;
        $payload['categories'][0]['categorie_id'] = $category->id;
        $payload['categories'][0]['evaluation_lists'][0]['evaluation_id'] = $evaluationList->id;

        $this->putJson(route('report-structure.update', $versionId), $payload)->assertOk();

        $this->assertDatabaseCount('support_criterias', 0);
    }

    private function payload(array $supportCriterias): array
    {
        return [
            'version_name' => 'Support Template '.uniqid(),
            'created_by' => $this->admin->id,
            'report_datas' => [[
                'report_title' => 'แบบประเมินสายสนับสนุน',
                'report_description' => null,
                'assessment_type' => 'support',
                'comment' => null,
            ]],
            'categories' => [[
                'main_categories' => 'ผลสัมฤทธิ์ของงาน',
                'sub_categories' => 'งานตามภารกิจ',
                'sequence' => 1,
                'evaluation_lists' => [[
                    'name' => 'รายการสายสนับสนุน',
                    'sum_score' => 100,
                    'sequence' => 1,
                    'annotation' => null,
                    'quantity_main_criterias' => [],
                    'quality_main_criterias' => [],
                    'support_criterias' => $supportCriterias,
                ]],
            ]],
        ];
    }
}
