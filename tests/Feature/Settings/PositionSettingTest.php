<?php

namespace Tests\Feature\Settings;

use Tests\TestCase;
use App\Models\User;
use App\Models\Setting\Positions;
use App\Models\Setting\Departments;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Schema;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;

class PositionSettingTest extends TestCase
{
    use RefreshDatabase;

    protected User $admin;
    protected Departments $department;
    protected Positions $position;

    protected function setUp(): void
    {
        parent::setUp();

        Role::create(['name' => 'admin']);
        Role::create(['name' => 'staff']);

        $this->department = \Database\Factories\DepartmentFactory::new()->create();
        $this->position = \Database\Factories\PositionFactory::new()->create();

        $this->admin = User::factory()->create([
            'employee_id'   => 'ADMIN001',
            'password'      => Hash::make('password'),
            'status'        => 'active',
            'department_id' => $this->department->id,
            'position_id'   => $this->position->id,
        ]);
        $this->admin->assignRole('admin');
    }

    public function test_admin_can_store_position()
    {
        $response = $this->actingAs($this->admin, 'web')
            ->get(route('positions.index'));

        $token = session()->token();

        $data = [
            '_token'  => $token,
            'name' => 'อาจารย์',
        ];

        $this->actingAs($this->admin, 'web')
            ->post(route('positions.store'), $data)
            ->assertRedirect(route('positions.index'))
            ->assertSessionHas('success', 'เพิ่มข้อมูลเรียบร้อยแล้ว');

        $this->assertDatabaseHas('positions', [
            'name' => 'อาจารย์',
        ]);
    }

    public function test_admin_cannot_store_duplicate_position_name()
    {
        $response = $this->actingAs($this->admin, 'web')
            ->get(route('positions.index'));

        $token = session()->token();

        Positions::create(['name' => 'เจ้าหน้าที่']);

        $data = [
            '_token'  => $token,
            'name' => 'เจ้าหน้าที่'
        ];

        $this->actingAs($this->admin, 'web')
            ->post(route('positions.store'), $data)
            ->assertSessionHasErrors(['name']);
    }

    public function test_admin_can_update_position()
    {
        $response = $this->actingAs($this->admin, 'web')
            ->get(route('positions.index'));

        $token = session()->token();

        $position = Positions::create(['name' => 'เดิม']);

        $data = [
            '_token'  => $token,
            'name' => 'ใหม่'
        ];

        $this->actingAs($this->admin, 'web')
            ->put(route('positions.update', $position->id), $data)
            ->assertRedirect(route('positions.index'))
            ->assertSessionHas('success', 'อัปเดตข้อมูลเรียบร้อยแล้ว');

        $this->assertDatabaseHas('positions', [
            'id' => $position->id,
            'name' => 'ใหม่',
        ]);
    }

    public function test_admin_can_delete_position()
    {
        $response = $this->actingAs($this->admin, 'web')
            ->get(route('positions.index'));

        $token = session()->token();

        $position = Positions::create(['name' => 'จะลบ']);

        $this->actingAs($this->admin, 'web')
            ->delete(route('positions.destroy', $position->id),[
                '_token' => $token,
            ])
            ->assertRedirect(route('positions.index'))
            ->assertSessionHas('success', 'ลบข้อมูลเรียบร้อยแล้ว');

        $this->assertDatabaseMissing('positions', [
            'id' => $position->id,
        ]);
    }

    public function test_admin_can_bulk_delete_positions()
    {
        $positions = collect([
            Positions::create(['name' => 'ลบตำแหน่งหลายรายการ 1']),
            Positions::create(['name' => 'ลบตำแหน่งหลายรายการ 2']),
        ]);

        $this->actingAs($this->admin, 'web')
            ->delete(route('positions.bulk-destroy'), [
                'ids' => $positions->pluck('id')->all(),
            ])
            ->assertRedirect(route('positions.index'))
            ->assertSessionHas('success', 'ลบตำแหน่งที่เลือกเรียบร้อยแล้ว 2 รายการ');

        $this->assertDatabaseMissing('positions', ['id' => $positions[0]->id]);
        $this->assertDatabaseMissing('positions', ['id' => $positions[1]->id]);
    }

    public function test_admin_can_bulk_delete_positions_when_evaluatee_assignment_column_is_missing()
    {
        Schema::shouldReceive('hasColumn')
            ->with('assignment_datas', 'evaluator_position_id')
            ->andReturn(true);
        Schema::shouldReceive('hasColumn')
            ->with('assignment_datas', 'evaluatee_position_id')
            ->andReturn(false);

        $positions = collect([
            Positions::create(['name' => 'คอลัมน์หาย 1']),
            Positions::create(['name' => 'คอลัมน์หาย 2']),
        ]);

        $this->actingAs($this->admin, 'web')
            ->delete(route('positions.bulk-destroy'), [
                'ids' => $positions->pluck('id')->all(),
            ])
            ->assertRedirect(route('positions.index'))
            ->assertSessionHas('success', 'ลบตำแหน่งที่เลือกเรียบร้อยแล้ว 2 รายการ');

        $this->assertDatabaseMissing('positions', ['id' => $positions[0]->id]);
        $this->assertDatabaseMissing('positions', ['id' => $positions[1]->id]);
    }
}
