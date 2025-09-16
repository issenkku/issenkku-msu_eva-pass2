<?php

namespace Tests\Feature\Settings;

use Tests\TestCase;
use App\Models\User;
use App\Models\Setting\Settings;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Role;
use Illuminate\Foundation\Testing\RefreshDatabase;

class UniversitySettingTest extends TestCase
{
    use RefreshDatabase;
    
    protected User $admin;

    protected function setUp(): void
    {
        parent::setUp();

        Role::create(['name' => 'admin']);
        Role::create(['name' => 'staff']);

        $department = \Database\Factories\DepartmentFactory::new()->create();
        $position = \Database\Factories\PositionFactory::new()->create();

        $this->admin = User::factory()->create([
            'employee_id'   => 'ADMIN001',
            'password'      => Hash::make('password'),
            'status'        => 'active',
            'department_id' => $department->id,
            'position_id'   => $position->id,
        ]);
        $this->admin->assignRole('admin');
    }

    public function test_admin_can_store_valid_settings()
    {
        $response = $this->actingAs($this->admin, 'web')
            ->get(route('settings.index'));

        $token = session()->token();

        $data = [
            '_token'            => $token,
            'university'        => 'มหาวิทยาลัยธรรมศาสตร์',
            'faculty'           => 'วิศวกรรมศาสตร์',
            'notification_days' => 7,
        ];

        $this->actingAs($this->admin, 'web')
            ->post(route('settings.store'), $data)
            ->assertRedirect(route('settings.index'))
            ->assertSessionHas('success', 'บันทึกข้อมูลสำเร็จ!');

        $this->assertDatabaseHas('settings', [
            'university'        => 'มหาวิทยาลัยธรรมศาสตร์',
            'faculty'           => 'วิศวกรรมศาสตร์',
            'notification_days' => 7,
        ]);
    }

    public function test_store_fails_when_fields_are_empty()
    {
        $response = $this->actingAs($this->admin, 'web')
            ->get(route('settings.index'));

        $token = session()->token();

        $this->actingAs($this->admin, 'web')
            ->post(route('settings.store'), [
                '_token'        => $token,
                'university' => '',
                'faculty' => '',
                'notification_days' => '',
            ])
            ->assertSessionHasErrors(['university', 'faculty', 'notification_days']);

        $this->assertDatabaseCount('settings', 0);
    }

    public function test_store_fails_when_input_has_invalid_characters()
    {
        $response = $this->actingAs($this->admin, 'web')
            ->get(route('settings.index'));

        $token = session()->token();

        $invalidData = [
            '_token'            => $token,
            'university'        => 'มหาวิทยาลัย@123',
            'faculty'           => 'วิศวกรรมศาสตร์#',
            'notification_days' => 5,
        ];

        $this->actingAs($this->admin, 'web')
            ->post(route('settings.store'), $invalidData)
            ->assertSessionHasErrors(['university', 'faculty']);

        $this->assertDatabaseMissing('settings', [
            'university' => 'มหาวิทยาลัย@123',
        ]);
    }

    public function test_admin_can_update_existing_settings()
    {
        $response = $this->actingAs($this->admin, 'web')
            ->get(route('settings.index'));

        $token = session()->token();

        $existing = Settings::create([
            'university' => 'เดิม',
            'faculty' => 'เดิม',
            'notification_days' => 10,
        ]);

        $newData = [
            '_token'        => $token,
            'id' => $existing->id,
            'university' => 'จุฬาลงกรณ์มหาวิทยาลัย',
            'faculty' => 'บัญชี',
            'notification_days' => 3,
        ];

        $this->actingAs($this->admin, 'web')
            ->post(route('settings.store'), $newData)
            ->assertRedirect(route('settings.index'))
            ->assertSessionHas('success', 'อัปเดตข้อมูลสำเร็จ!');

        $this->assertDatabaseHas('settings', [
            'id' => $existing->id,
            'university' => 'จุฬาลงกรณ์มหาวิทยาลัย',
            'faculty' => 'บัญชี',
            'notification_days' => 3,
        ]);
    }
}
