<?php

use App\Models\AssignmentData;
use App\Models\CriteriaVersion;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;

uses(RefreshDatabase::class);

function asyncIndexAdmin(): User
{
    Role::findOrCreate('admin', 'web');
    $admin = User::factory()->create([
        'employee_id' => 'INDEXADMIN',
        'status' => 'active',
    ]);
    $admin->assignRole('admin');

    return $admin;
}

test('assignment deletion returns the deleted id and remaining total as JSON', function () {
    $admin = asyncIndexAdmin();
    $assignment = AssignmentData::factory()->create();
    AssignmentData::factory()->create();

    $this->actingAs($admin, 'web')
        ->deleteJson(route('assignment-data.destroy', $assignment))
        ->assertOk()
        ->assertJsonPath('success', true)
        ->assertJsonPath('state.deleted_ids.0', $assignment->id)
        ->assertJsonPath('state.total', 1);
});

test('assignment deletion retains a redirect fallback for native submissions', function () {
    $admin = asyncIndexAdmin();
    $assignment = AssignmentData::factory()->create();

    $this->actingAs($admin, 'web')
        ->delete(route('assignment-data.destroy', $assignment))
        ->assertRedirect(route('assignment-data.index'));
});

test('criteria copy creation returns a rendered card fragment', function () {
    $admin = asyncIndexAdmin();

    $response = $this->actingAs($admin, 'web')
        ->postJson(route('report-structure.store'), [
            'version_name' => 'Async Criteria Copy',
            'created_by' => $admin->id,
            'report_datas' => [[
                'report_title' => 'Async Report',
                'report_description' => null,
                'assessment_type' => 'quantity',
                'comment' => null,
            ]],
            'categories' => [[
                'main_categories' => 'Async Category',
                'sub_categories' => 'Async Subcategory',
                'sequence' => 1,
            ]],
        ])
        ->assertCreated()
        ->assertJsonPath('success', true)
        ->assertJsonStructure(['message', 'html' => ['row'], 'state' => ['id']]);

    expect($response->json('html.row'))->toContain(
        'data-resource-row',
        'data-resource-id="'.$response->json('state.id').'"',
        'Async Report',
    );
});

test('criteria deletion returns the deleted id instead of an empty response', function () {
    $admin = asyncIndexAdmin();
    $criteria = CriteriaVersion::factory()->create(['created_by' => $admin->id]);

    $this->actingAs($admin, 'web')
        ->deleteJson(route('report-structure.destroy', $criteria))
        ->assertOk()
        ->assertJsonPath('success', true)
        ->assertJsonPath('state.deleted_ids.0', $criteria->id);
});
