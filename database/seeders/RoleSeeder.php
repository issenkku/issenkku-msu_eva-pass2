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
        $adminRole = Role::firstOrCreate(['name' => 'admin']);
        $managerRole = Role::firstOrCreate(['name' => 'ผู้บริหาร']);
        $evaluatorRole = Role::firstOrCreate(['name' => 'ผู้ประเมิน']);
        $evaluateeRole = Role::firstOrCreate(['name' => 'ผู้รับการประเมิน']);
        $directorRole = Role::firstOrCreate(['name' => 'กรรมการ']);

        // Create permissions
        $dashboardPermission = Permission::firstOrCreate(['name' => 'Employee Dashboard']);
        $admindashboardPermission = Permission::firstOrCreate(['name' => 'Admin Dashboard']);
        $employeeManageMentPermission = Permission::firstOrCreate(['name' => 'Employee Management']);

        // Assign permissions to roles
        $adminRole->givePermissionTo(
            $admindashboardPermission,
            $employeeManageMentPermission
        );
        $evaluateeRole->givePermissionTo($dashboardPermission);

        // Assign role to user by email
        $this->assignRoleByEmail('admin1@kkumail.com', $adminRole);
        $this->assignRoleByEmail('evaluator1@gmail.com', $evaluatorRole);
        $this->assignRoleByEmail('evaluator2@gmail.com', $evaluatorRole);
        $this->assignRoleByEmail('evaluatee1@gmail.com', $evaluateeRole);
        $this->assignRoleByEmail('evaluatee2@gmail.com', $evaluateeRole);
        $this->assignRoleByEmail('manager@gmail.com', $managerRole);
        $this->assignRoleByEmail('director@gmail.com', $directorRole);
    }

    private function assignRoleByEmail(string $email, Role $role): void
    {
        $user = User::where('email', $email)->first();
        if (! $user) {
            return;
        }
        $user->assignRole($role);
    }
}
