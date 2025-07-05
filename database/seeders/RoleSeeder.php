<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class RoleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        //create roles
        $adminRole = Role::create(['name' => 'admin']);
        $managerRole = Role::create(['name' => 'ผู้บริหาร']);
        $evaluatorRole = Role::create(['name' => 'ผู้ประเมิน']);
        $supportEvaluateeRole = Role::create(['name' => 'ผู้รับการประเมินฝ่ายสนับสนุน']);
        $academicEvaluateeRole = Role::create(['name' => 'ผู้รับการประเมินฝ่ายวิชาการ']);

        // Create permissions
        $dashboardPermission = Permission::create(['name' => 'Employee Dashboard']);
        $admindashboardPermission = Permission::create(['name' => 'Admin Dashboard']);
        $employeeManageMentPermission = Permission::create(['name' => 'Employee Management']);

        // Assign permissions to roles
        $adminRole->givePermissionTo(
            $admindashboardPermission,
            $employeeManageMentPermission
        );
        $supportEvaluateeRole->givePermissionTo($dashboardPermission);
        $academicEvaluateeRole->givePermissionTo($dashboardPermission);

        $admin = User::find(1);
        if ($admin) {
            $admin->assignRole($adminRole);
        }
        $user = User::find(2);
        if ($user) {
            $user->assignRole($academicEvaluateeRole);
        }
    }
}
