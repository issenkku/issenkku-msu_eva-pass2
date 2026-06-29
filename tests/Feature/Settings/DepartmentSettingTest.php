<?php

namespace Tests\Feature\Settings;

use Tests\TestCase;
use App\Models\User;
use App\Models\Setting\Departments;
use App\Models\Setting\Positions;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Role;
use Illuminate\Foundation\Testing\RefreshDatabase;

class DepartmentSettingTest extends TestCase
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

        // Create departments and positions that will be globally available in the test DB
        $this->department = \Database\Factories\DepartmentFactory::new()->create();
        $this->position = \Database\Factories\PositionFactory::new()->create();

        // Create admin user using above department and position
        $this->admin = User::factory()->create([
            'employee_id'   => 'ADMIN001',
            'password'      => Hash::make('password'),
            'status'        => 'active',
            'department_id' => $this->department->id,
            'position_id'   => $this->position->id,
        ]);
        $this->admin->assignRole('admin');
    }
    
    public function test_admin_can_store_department()
    {
        $response = $this->actingAs($this->admin, 'web')
            ->get(route('departments.index'));

        $token = session()->token();

        $data = [
            '_token'        => $token,
            'department_name' => 'ภาควิชาวิทยาการคอมพิวเตอร์',
        ];

        $this->actingAs($this->admin, 'web')
            ->post(route('departments.store'), $data)
            ->assertRedirect(route('departments.index'))
            ->assertSessionHas('success', 'เพิ่มข้อมูลเรียบร้อยแล้ว');

        $this->assertDatabaseHas('departments', [
            'department_name' => 'ภาควิชาวิทยาการคอมพิวเตอร์',
        ]);
    }

    public function test_admin_cannot_store_duplicate_department_name()
    {
        $response = $this->actingAs($this->admin, 'web')
            ->get(route('departments.index'));

        $token = session()->token();

        Departments::create(['department_name' => 'วิศวกรรมศาสตร์']);

        $data = [
            '_token'        => $token,
            'department_name' => 'วิศวกรรมศาสตร์',
        ];

        $this->actingAs($this->admin, 'web')
            ->post(route('departments.store'), $data)
            ->assertSessionHasErrors(['department_name']);
    }

    public function test_admin_can_update_department()
    {
        $response = $this->actingAs($this->admin, 'web')
            ->get(route('departments.index'));

        $token = session()->token();

        $department = Departments::create(['department_name' => 'เดิม']);

        $data = [
            '_token'        => $token,
            'department_name' => 'อัปเดตชื่อใหม่',
        ];

        $this->actingAs($this->admin, 'web')
            ->put(route('departments.update', $department->id), $data)
            ->assertRedirect(route('departments.index'))
            ->assertSessionHas('success', 'อัปเดตข้อมูลเรียบร้อยแล้ว');

        $this->assertDatabaseHas('departments', [
            'id' => $department->id,
            'department_name' => 'อัปเดตชื่อใหม่',
        ]);
    }

    public function test_admin_can_delete_department()
    {
        $response = $this->actingAs($this->admin, 'web')
            ->get(route('departments.index'));

        $token = session()->token();

        $department = Departments::create(['department_name' => 'ลบได้']);

        $this->actingAs($this->admin, 'web')
            ->delete(route('departments.destroy', $department->id),[
                '_token' => $token,
            ])
            ->assertRedirect(route('departments.index'))
            ->assertSessionHas('success', 'ลบข้อมูลเรียบร้อยแล้ว');

        $this->assertDatabaseMissing('departments', [
            'id' => $department->id,
        ]);
    }

    public function test_admin_can_bulk_delete_departments()
    {
        $departments = collect([
            Departments::create(['department_name' => 'ลบหลายรายการ 1']),
            Departments::create(['department_name' => 'ลบหลายรายการ 2']),
        ]);

        $this->actingAs($this->admin, 'web')
            ->delete(route('departments.bulk-destroy'), [
                'ids' => $departments->pluck('id')->all(),
            ])
            ->assertRedirect(route('departments.index'))
            ->assertSessionHas('success', 'ลบแผนกที่เลือกเรียบร้อยแล้ว 2 รายการ');

        $this->assertDatabaseMissing('departments', ['id' => $departments[0]->id]);
        $this->assertDatabaseMissing('departments', ['id' => $departments[1]->id]);
    }
}
