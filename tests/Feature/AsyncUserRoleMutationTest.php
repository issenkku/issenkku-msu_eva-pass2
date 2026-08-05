<?php

use App\Models\Setting\Departments;
use App\Models\Setting\Positions;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;

uses(RefreshDatabase::class);

function asyncMutationAdmin(): User
{
    Role::findOrCreate('admin');

    $admin = User::factory()->create([
        'employee_id' => 'ASYNCADMIN',
        'status' => 'active',
    ]);
    $admin->assignRole('admin');

    return $admin;
}

function asyncUserPayload(Departments $department, Positions $position, array $overrides = []): array
{
    return array_merge([
        'prefix' => 'นาย',
        'name' => 'Async User',
        'employee_id' => 'ASYNC001',
        'password' => 'password123',
        'password_confirmation' => 'password123',
        'email' => 'async-user@example.com',
        'phone' => '0900000001',
        'personnel_type' => 'วิชาการ',
        'status' => 'active',
        'position_id' => $position->id,
        'department_id' => $department->id,
        'roles' => ['staff'],
    ], $overrides);
}

test('user mutations return JSON fragments without redirecting', function () {
    $admin = asyncMutationAdmin();
    Role::findOrCreate('staff');
    $department = \Database\Factories\DepartmentFactory::new()->create();
    $position = \Database\Factories\PositionFactory::new()->create();

    $create = $this->actingAs($admin, 'web')
        ->postJson(route('users.store'), asyncUserPayload($department, $position))
        ->assertCreated()
        ->assertJsonPath('success', true)
        ->assertJsonStructure(['message', 'html' => ['row'], 'state' => ['id']]);

    $user = User::findOrFail($create->json('state.id'));
    expect($create->json('html.row'))->toContain('data-resource-row', 'data-resource-id="'.$user->id.'"');

    $updatePayload = asyncUserPayload($department, $position, [
        'name' => 'Async User Updated',
        'password' => null,
        'password_confirmation' => null,
    ]);

    $this->putJson(route('users.update', $user->id), $updatePayload)
        ->assertOk()
        ->assertJsonPath('success', true)
        ->assertJsonPath('state.id', $user->id)
        ->assertJsonStructure(['html' => ['row']]);

    $this->deleteJson(route('users.destroy', $user->id))
        ->assertOk()
        ->assertJsonPath('success', true)
        ->assertJsonPath('state.deleted_ids.0', $user->id);
});

test('bulk user mutations return the affected ids as JSON', function () {
    $admin = asyncMutationAdmin();
    $users = User::factory()->count(2)->create();
    $ids = $users->pluck('id')->all();

    $this->actingAs($admin, 'web')
        ->patchJson(route('users.bulk-status'), [
            'user_ids' => $ids,
            'status' => 'inactive',
        ])
        ->assertOk()
        ->assertJsonPath('success', true)
        ->assertJsonPath('state.updated_ids', $ids)
        ->assertJsonPath('state.status', 'inactive');

    $this->deleteJson(route('users.bulk-destroy'), ['user_ids' => $ids])
        ->assertOk()
        ->assertJsonPath('success', true)
        ->assertJsonPath('state.deleted_ids', $ids);
});

test('role create and delete mutations return JSON without redirecting', function () {
    $admin = asyncMutationAdmin();

    $create = $this->actingAs($admin, 'web')
        ->postJson(route('roles.store'), ['name' => 'async-role'])
        ->assertCreated()
        ->assertJsonPath('success', true)
        ->assertJsonStructure(['message', 'html' => ['row'], 'state' => ['id']]);

    $role = Role::query()->findOrFail($create->json('state.id'));
    expect($create->json('html.row'))->toContain('data-resource-row', 'data-resource-id="'.$role->id.'"');

    $this->deleteJson(route('roles.destroy', $role))
        ->assertOk()
        ->assertJsonPath('success', true)
        ->assertJsonPath('state.deleted_ids.0', $role->id);
});

test('user mutation keeps the redirect fallback for native form submissions', function () {
    $admin = asyncMutationAdmin();
    Role::findOrCreate('staff');
    $department = \Database\Factories\DepartmentFactory::new()->create();
    $position = \Database\Factories\PositionFactory::new()->create();

    $this->actingAs($admin, 'web')
        ->post(route('users.store'), asyncUserPayload($department, $position, [
            'employee_id' => 'NATIVE001',
            'email' => 'native-user@example.com',
            'phone' => '0900000099',
        ]))
        ->assertRedirect(route('users.index'));
});

test('last active admin protection is returned as a JSON conflict', function () {
    $admin = asyncMutationAdmin();

    $this->actingAs($admin, 'web')
        ->deleteJson(route('users.destroy', $admin->id))
        ->assertConflict()
        ->assertJsonPath('success', false)
        ->assertJsonStructure(['message']);
});

test('user and role pages expose async table and delete hooks', function () {
    $admin = asyncMutationAdmin();

    $this->actingAs($admin, 'web')
        ->get(route('users.index'))
        ->assertOk()
        ->assertSee('data-async-table-region', false)
        ->assertSee('data-async-delete-form', false)
        ->assertSee('data-async-bulk-delete-form', false);

    $this->get(route('roles.index'))
        ->assertOk()
        ->assertSee('data-async-table-region', false)
        ->assertSee('data-async-delete-form', false);
});
