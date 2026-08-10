<?php

namespace Tests\Feature\Settings;

use Tests\TestCase;
use App\Models\User;
use App\Models\Setting\Departments;
use App\Models\Setting\Positions;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Role;
use Illuminate\Foundation\Testing\RefreshDatabase;

class SettingAccessTest extends TestCase
{
    use RefreshDatabase;

    protected User $admin;
    protected Departments $department;
    protected Positions $position;

    // must run vite before test
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

    public function test_admin_can_view_settings_index()
    {
        $this->actingAs($this->admin, 'web')
            ->get(route('settings.index'))
            ->assertStatus(200)
            ->assertViewIs('settings.index');
    }

    public function test_university_and_faculty_fields_are_not_shown_on_settings_index()
    {
        $this->actingAs($this->admin, 'web')
            ->get(route('settings.index'))
            ->assertStatus(200)
            ->assertDontSee('name="university"', false)
            ->assertDontSee('name="faculty"', false);
    }

    public function test_non_admin_cannot_access_settings_routes()
    {
        $department = \Database\Factories\DepartmentFactory::new()->create();
        $position = \Database\Factories\PositionFactory::new()->create();

        $staff = User::factory()->create([
            'employee_id'   => 'STAFF001',
            'department_id' => $department->id,
            'position_id'   => $position->id,
        ]);
        $staff->assignRole('staff');

        $this->actingAs($staff, 'web')
            ->get(route('settings.index'))
            ->assertForbidden();
    }

    public function test_admin_can_view_departments_index()
    {
        $this->actingAs($this->admin, 'web')
            ->get(route('departments.index'))
            ->assertStatus(200)
            ->assertViewIs('departments.index');
    }

    public function test_non_admin_cannot_access_departments_routes()
    {
        $department = \Database\Factories\DepartmentFactory::new()->create();
        $position = \Database\Factories\PositionFactory::new()->create();

        $staff = User::factory()->create([
            'employee_id'   => 'STAFF001',
            'department_id' => $department->id,
            'position_id'   => $position->id,
        ]);
        $staff->assignRole('staff');

        $this->actingAs($staff, 'web')
            ->get(route('departments.index'))
            ->assertForbidden();

        $this->actingAs($staff, 'web')
            ->post(route('departments.store'), ['department_name' => 'ห้ามเพิ่ม'])
            ->assertForbidden();
    }

    public function test_admin_can_view_positions_index()
    {
        $this->actingAs($this->admin, 'web')
            ->get(route('positions.index'))
            ->assertStatus(200)
            ->assertViewIs('positions.index');
    }

    public function test_non_admin_cannot_access_position_routes()
    {
        $department = \Database\Factories\DepartmentFactory::new()->create();
        $position = \Database\Factories\PositionFactory::new()->create();

        $staff = User::factory()->create([
            'employee_id'   => 'STAFF001',
            'department_id' => $department->id,
            'position_id'   => $position->id,
        ]);
        $staff->assignRole('staff');

        $this->actingAs($staff, 'web')
            ->get(route('positions.index'))
            ->assertForbidden();

        $this->actingAs($staff, 'web')
            ->post(route('positions.store'), ['name' => 'Unauthorized'])
            ->assertForbidden();
    }
}
