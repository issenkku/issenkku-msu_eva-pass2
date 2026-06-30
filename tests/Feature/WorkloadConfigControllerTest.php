<?php

use App\Models\Category;
use App\Models\CriteriaVersion;
use App\Models\EvaluationList;
use App\Models\QuantityMainCriteria;
use App\Models\QuantitySubCriteria;
use App\Models\User;
use App\Models\WorkloadFormField;
use App\Models\WorkloadFormItem;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Spatie\Activitylog\Models\Activity;
use Spatie\Permission\Models\Role;

test('workload config save skips item require subject when column is missing', function () {
    Role::create(['name' => 'admin']);
    $admin = User::factory()->create();
    $admin->assignRole('admin');

    $criteriaVersion = CriteriaVersion::factory()->create();
    $evaluationList = EvaluationList::factory()->create([
        'criteria_version_id' => $criteriaVersion->id,
    ]);
    $quantityMainCriteria = QuantityMainCriteria::factory()->create([
        'criteria_version_id' => $criteriaVersion->id,
    ]);
    $quantitySubCriteria = QuantitySubCriteria::factory()->create([
        'criteria_version_id' => $criteriaVersion->id,
        'evaluation_list_id' => $evaluationList->id,
        'quantity_main_criteria_id' => $quantityMainCriteria->id,
    ]);

    $groupId = DB::table('quantity_sub_criteria_groups')->insertGetId([
        'name' => 'Existing group',
        'sequence' => 1,
        'quantity_sub_criteria_id' => $quantitySubCriteria->id,
        'criteria_version_id' => $criteriaVersion->id,
        'evaluation_list_id' => $evaluationList->id,
    ]);

    $itemId = DB::table('quantity_sub_criteria_items')->insertGetId([
        'name' => 'Existing item',
        'sequence' => 1,
        'score_a' => 0,
        'score_b' => 0,
        'description' => null,
        'quantity_sub_criteria_group_id' => $groupId,
        'criteria_version_id' => $criteriaVersion->id,
        'evaluation_list_id' => $evaluationList->id,
    ]);

    try {
        Schema::table('quantity_sub_criteria_items', function ($table) {
            $table->dropColumn('require_subject');
        });

        expect(Schema::hasColumn('quantity_sub_criteria_items', 'require_subject'))->toBeFalse();

        $queries = [];
        DB::listen(function ($query) use (&$queries) {
            $queries[] = $query->sql;
        });

        $payload = [
            'quant_sub_criteria_id' => $quantitySubCriteria->id,
            'groups' => [
                [
                    'id' => $groupId,
                    'group_name' => 'Existing group updated',
                    'sequence' => 1,
                    'items' => [
                        [
                            'id' => $itemId,
                            'item_name' => 'Existing item updated',
                            'sequence' => 1,
                            'require_subject' => true,
                            'formula_logic' => '',
                            'fields' => [],
                            'form_items' => [],
                        ],
                    ],
                ],
            ],
        ];

        $this->actingAs($admin, 'web')
            ->postJson(route('workload-config.save'), $payload)
            ->assertOk()
            ->assertJson(['success' => true]);

        expect(collect($queries)->contains(
            fn (string $sql) => str_contains($sql, 'require_subject')
        ))->toBeFalse();

        $this->assertDatabaseHas('quantity_sub_criteria_items', [
            'id' => $itemId,
            'name' => 'Existing item updated',
        ]);
    } finally {
        Schema::table('quantity_sub_criteria_items', function ($table) {
            $table->boolean('require_subject')->default(false);
        });
    }
});

test('workload config save skips field note and default value when columns are missing', function () {
    Role::create(['name' => 'admin']);
    $admin = User::factory()->create();
    $admin->assignRole('admin');

    $criteriaVersion = CriteriaVersion::factory()->create();
    $evaluationList = EvaluationList::factory()->create([
        'criteria_version_id' => $criteriaVersion->id,
    ]);
    $quantityMainCriteria = QuantityMainCriteria::factory()->create([
        'criteria_version_id' => $criteriaVersion->id,
    ]);
    $quantitySubCriteria = QuantitySubCriteria::factory()->create([
        'criteria_version_id' => $criteriaVersion->id,
        'evaluation_list_id' => $evaluationList->id,
        'quantity_main_criteria_id' => $quantityMainCriteria->id,
    ]);

    try {
        Schema::table('workload_form_fields', function ($table) {
            $table->dropColumn('default_value');
        });
        Schema::table('workload_form_fields', function ($table) {
            $table->dropColumn('note');
        });

        expect(Schema::hasColumn('workload_form_fields', 'note'))->toBeFalse()
            ->and(Schema::hasColumn('workload_form_fields', 'default_value'))->toBeFalse();

        $queries = [];
        DB::listen(function ($query) use (&$queries) {
            $queries[] = $query->sql;
        });

        $payload = [
            'quant_sub_criteria_id' => $quantitySubCriteria->id,
            'groups' => [
                [
                    'id' => null,
                    'group_name' => 'New group',
                    'sequence' => 1,
                    'items' => [
                        [
                            'id' => null,
                            'item_name' => 'New item',
                            'sequence' => 1,
                            'require_subject' => false,
                            'formula_logic' => '',
                            'fields' => [
                                [
                                    'label' => 'จำนวน',
                                    'note' => 'คำอธิบาย',
                                    'default_value' => '0',
                                    'variable_name' => 'num',
                                    'field_type' => 'number',
                                ],
                            ],
                            'form_items' => [],
                        ],
                    ],
                ],
            ],
        ];

        $this->actingAs($admin, 'web')
            ->postJson(route('workload-config.save'), $payload)
            ->assertOk()
            ->assertJson(['success' => true]);

        expect(collect($queries)->contains(
            fn (string $sql) => str_contains($sql, 'note')
                || str_contains($sql, 'default_value')
        ))->toBeFalse();

        $this->assertDatabaseHas('workload_form_fields', [
            'label' => 'จำนวน',
            'variable_name' => 'num',
            'field_type' => 'number',
        ]);
    } finally {
        Schema::table('workload_form_fields', function ($table) {
            $table->text('note')->nullable();
            $table->text('default_value')->nullable();
        });
    }
});

test('workload config sub blocks stay within a bulk query budget', function () {
    Role::create(['name' => 'admin']);
    $admin = User::factory()->create();
    $admin->assignRole('admin');

    $criteriaVersion = CriteriaVersion::factory()->create();
    $evaluationList = EvaluationList::factory()->create([
        'criteria_version_id' => $criteriaVersion->id,
    ]);
    $quantityMainCriteria = QuantityMainCriteria::factory()->create([
        'criteria_version_id' => $criteriaVersion->id,
    ]);
    $quantitySubCriteria = QuantitySubCriteria::factory()->create([
        'criteria_version_id' => $criteriaVersion->id,
        'evaluation_list_id' => $evaluationList->id,
        'quantity_main_criteria_id' => $quantityMainCriteria->id,
    ]);

    collect(range(1, 2))->each(function (int $groupIndex) use ($criteriaVersion, $evaluationList, $quantitySubCriteria) {
        $groupId = DB::table('quantity_sub_criteria_groups')->insertGetId([
            'name' => 'Group '.$groupIndex,
            'sequence' => $groupIndex,
            'quantity_sub_criteria_id' => $quantitySubCriteria->id,
            'criteria_version_id' => $criteriaVersion->id,
            'evaluation_list_id' => $evaluationList->id,
        ]);

        collect(range(1, 2))->each(function (int $itemIndex) use ($groupId, $criteriaVersion, $evaluationList, $quantitySubCriteria, $groupIndex) {
            $itemId = DB::table('quantity_sub_criteria_items')->insertGetId([
                'name' => "Item {$groupIndex}.{$itemIndex}",
                'sequence' => $itemIndex,
                'score_a' => 1,
                'score_b' => 1,
                'description' => null,
                'quantity_sub_criteria_group_id' => $groupId,
                'criteria_version_id' => $criteriaVersion->id,
                'evaluation_list_id' => $evaluationList->id,
                'require_subject' => false,
            ]);

            $formId = DB::table('workload_forms')->insertGetId([
                'formula_logic' => 'a + b',
                'quantity_sub_criteria_id' => $quantitySubCriteria->id,
                'quantity_sub_criteria_item_id' => $itemId,
            ]);

            WorkloadFormField::create([
                'label' => 'Field '.$itemId,
                'variable_name' => 'field_'.$itemId,
                'field_type' => 'number',
                'workload_form_id' => $formId,
            ]);

            WorkloadFormItem::create([
                'label' => 'Form item '.$itemId,
                'score' => 1,
                'sequence' => 1,
                'workload_form_id' => $formId,
            ]);
        });
    });

    $queries = [];
    DB::listen(function ($query) use (&$queries) {
        $queries[] = $query->sql;
    });

    $response = $this->actingAs($admin, 'web')->getJson(route('workload-config.sub-blocks', [
        'quant_sub_criteria_id' => $quantitySubCriteria->id,
    ]));

    $response->assertOk()
        ->assertJsonStructure(['groups' => [['group', 'items']]]);

    expect(collect($queries)->count())->toBeLessThanOrEqual(8);
});

test('workload config save stays within a query budget for a single existing group and item', function () {
    Role::create(['name' => 'admin']);
    $admin = User::factory()->create();
    $admin->assignRole('admin');

    $category = Category::factory()->create();
    $criteriaVersion = CriteriaVersion::factory()->create([
        'created_by' => $admin->id,
    ]);
    $evaluationList = EvaluationList::factory()->create([
        'criteria_version_id' => $criteriaVersion->id,
        'categorie_id' => $category->id,
    ]);
    $quantityMainCriteria = QuantityMainCriteria::factory()->create([
        'criteria_version_id' => $criteriaVersion->id,
    ]);
    $quantitySubCriteria = QuantitySubCriteria::factory()->create([
        'criteria_version_id' => $criteriaVersion->id,
        'evaluation_list_id' => $evaluationList->id,
        'quantity_main_criteria_id' => $quantityMainCriteria->id,
    ]);

    $groupId = DB::table('quantity_sub_criteria_groups')->insertGetId([
        'name' => 'Group 1',
        'sequence' => 1,
        'quantity_sub_criteria_id' => $quantitySubCriteria->id,
        'criteria_version_id' => $criteriaVersion->id,
        'evaluation_list_id' => $evaluationList->id,
    ]);

    $itemId = DB::table('quantity_sub_criteria_items')->insertGetId([
        'name' => 'Item 1.1',
        'sequence' => 1,
        'score_a' => 1,
        'score_b' => 1,
        'description' => null,
        'quantity_sub_criteria_group_id' => $groupId,
        'criteria_version_id' => $criteriaVersion->id,
        'evaluation_list_id' => $evaluationList->id,
        'require_subject' => false,
    ]);

    $queries = [];
    DB::listen(function ($query) use (&$queries) {
        $queries[] = $query->sql;
    });

    $payload = [
        'quant_sub_criteria_id' => $quantitySubCriteria->id,
        'groups' => [
            [
                'id' => $groupId,
                'group_name' => 'Group 1 updated',
                'sequence' => 1,
                'items' => [
                    [
                        'id' => $itemId,
                        'item_name' => 'Item 1.1 updated',
                        'sequence' => 1,
                        'require_subject' => false,
                        'formula_logic' => '',
                        'fields' => [],
                        'form_items' => [],
                    ],
                ],
            ],
        ],
    ];

    $this->actingAs($admin, 'web')
        ->postJson(route('workload-config.save'), $payload)
        ->assertOk()
        ->assertJson(['success' => true]);

    expect(collect($queries)->count())->toBeLessThanOrEqual(22);
});

test('workload config save creates audit log with actor', function () {
    Role::create(['name' => 'admin']);
    $admin = User::factory()->create([
        'name' => 'Audit Admin',
        'email' => 'audit-admin@example.test',
    ]);
    $admin->assignRole('admin');

    $criteriaVersion = CriteriaVersion::factory()->create([
        'created_by' => $admin->id,
    ]);
    $evaluationList = EvaluationList::factory()->create([
        'criteria_version_id' => $criteriaVersion->id,
    ]);
    $quantityMainCriteria = QuantityMainCriteria::factory()->create([
        'criteria_version_id' => $criteriaVersion->id,
        'name' => 'ภาระงานหลัก',
    ]);
    $quantitySubCriteria = QuantitySubCriteria::factory()->create([
        'criteria_version_id' => $criteriaVersion->id,
        'evaluation_list_id' => $evaluationList->id,
        'quantity_main_criteria_id' => $quantityMainCriteria->id,
        'name' => '1.1 ภาระงานในหน้าที่',
    ]);

    $payload = [
        'quant_sub_criteria_id' => $quantitySubCriteria->id,
        'groups' => [
            [
                'id' => null,
                'group_name' => 'เกณฑ์ปริมาณย่อย',
                'sequence' => 1,
                'items' => [
                    [
                        'id' => null,
                        'item_name' => '1.1 ภาระงานในหน้าที่',
                        'sequence' => 1,
                        'require_subject' => false,
                        'formula_logic' => 'A * C / B',
                        'fields' => [
                            [
                                'label' => 'ค่าน้ำหนักคะแนน',
                                'variable_name' => 'A',
                                'field_type' => 'number',
                            ],
                            [
                                'label' => 'หน่วยภาระงานมาตรฐาน',
                                'variable_name' => 'B',
                                'field_type' => 'number',
                            ],
                            [
                                'label' => 'จำนวนภาระงาน',
                                'variable_name' => 'C',
                                'field_type' => 'number',
                            ],
                        ],
                        'form_items' => [],
                    ],
                ],
            ],
        ],
    ];

    $this->actingAs($admin, 'web')
        ->postJson(route('workload-config.save'), $payload)
        ->assertOk()
        ->assertJson(['success' => true]);

    $activity = Activity::query()
        ->where('log_name', 'ตั้งค่าภาระงาน')
        ->where('description', 'แก้ไขตั้งค่าภาระงาน')
        ->latest('id')
        ->first();

    expect($activity)->not->toBeNull()
        ->and($activity->causer_id)->toBe($admin->id)
        ->and($activity->causer_type)->toBe($admin->getMorphClass())
        ->and($activity->subject_id)->toBe($quantitySubCriteria->id)
        ->and($activity->subject_type)->toBe($quantitySubCriteria->getMorphClass())
        ->and($activity->properties->get('quantity_sub_criteria_name'))->toBe('1.1 ภาระงานในหน้าที่')
        ->and($activity->properties->get('groups_count'))->toBe(1)
        ->and($activity->properties->get('items_count'))->toBe(1);
});
