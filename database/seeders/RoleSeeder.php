<?php

namespace Database\Seeders;

use App\Models\User;
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
        // create roles
        $adminRole = Role::create(['name' => 'admin']);
        $managerRole = Role::create(['name' => 'ผู้บริหาร']);
        $evaluatorRole = Role::create(['name' => 'ผู้ประเมิน']);
        $evaluateeRole = Role::create(['name' => 'ผู้รับการประเมิน']);
        $directorRole = Role::create(['name' => 'กรรมการ']);

        // Create permissions
        $dashboardPermission = Permission::create(['name' => 'Employee Dashboard']);
        $admindashboardPermission = Permission::create(['name' => 'Admin Dashboard']);
        $employeeManageMentPermission = Permission::create(['name' => 'Employee Management']);

        // Assign permissions to roles
        $adminRole->givePermissionTo($admindashboardPermission,
            $employeeManageMentPermission);
        $evaluateeRole->givePermissionTo($dashboardPermission);

        // Assign role to user
        User::find(1)->assignRole($adminRole);
        User::find(2)->assignRole($evaluatorRole);
        User::find(3)->assignRole($evaluatorRole);
        User::find(4)->assignRole($evaluateeRole);
        User::find(5)->assignRole($managerRole);
        User::find(6)->assignRole($directorRole);
    }
}
