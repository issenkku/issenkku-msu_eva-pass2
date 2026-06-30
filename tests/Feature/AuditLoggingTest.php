<?php

use App\Models\Setting\Settings;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Maatwebsite\Excel\Facades\Excel;
use Spatie\Activitylog\Models\Activity;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

function auditAdmin(): User
{
    Role::findOrCreate('admin');

    $admin = User::factory()->create([
        'employee_id' => 'AUDITADMIN',
        'password' => Hash::make('password'),
        'status' => 'active',
    ]);
    $admin->assignRole('admin');

    return $admin;
}

function latestAudit(string $description): ?Activity
{
    return Activity::query()
        ->where('description', $description)
        ->latest()
        ->first();
}

test('failed login attempts are written to the activity audit log', function () {
    User::factory()->create([
        'employee_id' => 'EMPFAIL',
        'password' => Hash::make('correct-password'),
        'status' => 'active',
    ]);

    $this->postJson(route('login'), [
        'employee_id' => 'EMPFAIL',
        'password' => 'wrong-password',
    ])->assertUnauthorized();

    $activity = latestAudit('เข้าสู่ระบบไม่สำเร็จ');

    expect($activity)->not->toBeNull()
        ->and($activity->log_name)->toBe('ความปลอดภัย')
        ->and($activity->properties->get('employee_id'))->toBe('EMPFAIL')
        ->and($activity->properties->get('reason'))->toBe('invalid_credentials')
        ->and($activity->properties->has('password'))->toBeFalse();
});

test('role permission updates are written to the activity audit log', function () {
    $admin = auditAdmin();
    $role = Role::create(['name' => 'audited-role']);
    $permission = Permission::create(['name' => 'Audited Permission']);
    $user = User::factory()->create();

    $this->actingAs($admin, 'web')
        ->put(route('roles.update', $role), [
            'name' => 'audited-role-updated',
            'permissions' => [$permission->name],
            'users' => [$user->id],
        ])
        ->assertRedirect(route('roles.index'));

    $activity = latestAudit('แก้ไขบทบาทและสิทธิ์');

    expect($activity)->not->toBeNull()
        ->and($activity->log_name)->toBe('สิทธิ์การใช้งาน')
        ->and($activity->causer_id)->toBe($admin->id)
        ->and($activity->properties->get('role_name_before'))->toBe('audited-role')
        ->and($activity->properties->get('role_name_after'))->toBe('audited-role-updated')
        ->and($activity->properties->get('assigned_user_ids'))->toBe([$user->id]);
});

test('user role and password changes are written to the activity audit log', function () {
    $admin = auditAdmin();
    Role::create(['name' => 'staff']);
    Role::create(['name' => 'manager']);
    $user = User::factory()->create([
        'employee_id' => 'EMPCHANGE',
        'phone' => '0800000001',
    ]);
    $user->assignRole('staff');

    $this->actingAs($admin, 'web')
        ->put(route('users.update', $user->id), [
            'prefix' => $user->prefix,
            'name' => $user->name,
            'employee_id' => $user->employee_id,
            'phone' => '0800000002',
            'personnel_type' => $user->personnel_type,
            'bio' => $user->bio,
            'status' => $user->status,
            'position_id' => $user->position_id,
            'department_id' => $user->department_id,
            'email' => $user->email,
            'password' => 'new-secret',
            'password_confirmation' => 'new-secret',
            'roles' => ['manager'],
        ])
        ->assertRedirect(route('users.index'));

    $roleActivity = latestAudit('เปลี่ยนบทบาทผู้ใช้');
    $passwordActivity = latestAudit('เปลี่ยนรหัสผ่านผู้ใช้');

    expect($roleActivity)->not->toBeNull()
        ->and($roleActivity->log_name)->toBe('สิทธิ์การใช้งาน')
        ->and($roleActivity->properties->get('target_user_id'))->toBe($user->id)
        ->and($roleActivity->properties->get('roles_before'))->toBe(['staff'])
        ->and($roleActivity->properties->get('roles_after'))->toBe(['manager']);

    expect($passwordActivity)->not->toBeNull()
        ->and($passwordActivity->log_name)->toBe('ความปลอดภัย')
        ->and($passwordActivity->properties->get('target_user_id'))->toBe($user->id)
        ->and($passwordActivity->properties->has('password'))->toBeFalse()
        ->and($passwordActivity->properties->has('password_confirmation'))->toBeFalse();
});

test('bulk user deletion is written to the activity audit log', function () {
    $admin = auditAdmin();
    $users = User::factory()->count(2)->create();

    $this->actingAs($admin, 'web')
        ->delete(route('users.bulk-destroy'), [
            'user_ids' => [
                $users[0]->id,
                $users[1]->id,
            ],
        ])
        ->assertRedirect(route('users.index'));

    $activity = latestAudit('ลบผู้ใช้แบบกลุ่ม');

    expect($activity)->not->toBeNull()
        ->and($activity->log_name)->toBe('จัดการผู้ใช้')
        ->and($activity->causer_id)->toBe($admin->id)
        ->and($activity->properties->get('deleted_count'))->toBe(2)
        ->and($activity->properties->get('target_user_ids'))->toBe([$users[0]->id, $users[1]->id]);
});

test('settings updates are written to the activity audit log', function () {
    $admin = auditAdmin();
    $setting = Settings::create([
        'university' => 'Old University',
        'faculty' => 'Old Faculty',
        'notification_days' => 7,
        'use_white_background' => false,
    ]);

    $this->actingAs($admin, 'web')
        ->post(route('settings.store'), [
            'id' => $setting->id,
            'university' => 'New University',
            'faculty' => 'New Faculty',
            'notification_days' => 10,
            'use_white_background' => '1',
        ])
        ->assertRedirect(route('settings.index'));

    $activity = latestAudit('แก้ไขตั้งค่าระบบ');

    expect($activity)->not->toBeNull()
        ->and($activity->log_name)->toBe('ตั้งค่าระบบ')
        ->and($activity->causer_id)->toBe($admin->id)
        ->and(data_get($activity->properties->toArray(), 'changes.university.old'))->toBe('Old University')
        ->and(data_get($activity->properties->toArray(), 'changes.university.new'))->toBe('New University')
        ->and(data_get($activity->properties->toArray(), 'changes.notification_days.old'))->toBe(7)
        ->and(data_get($activity->properties->toArray(), 'changes.notification_days.new'))->toBe(10);
});

test('report exports are written to the activity audit log', function () {
    Excel::fake();
    $admin = auditAdmin();

    $this->actingAs($admin, 'web')
        ->get(route('admin.export.reports', ['year' => '2026', 'search' => 'demo']))
        ->assertOk();

    $activity = latestAudit('ส่งออกรายงานภาพรวม');

    expect($activity)->not->toBeNull()
        ->and($activity->log_name)->toBe('ส่งออกข้อมูล')
        ->and($activity->causer_id)->toBe($admin->id)
        ->and($activity->properties->get('export_type'))->toBe('dashboard')
        ->and(data_get($activity->properties->toArray(), 'filters.year'))->toBe('2026')
        ->and(data_get($activity->properties->toArray(), 'filters.search'))->toBe('demo');
});
