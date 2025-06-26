<?php

namespace App\Http\Controllers\Settings;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class RoleAndPermissionController extends Controller
{
    public function setupRolesAndPermissions()
    {
        $permissions = [
            'Admin Dashboard',
            'Employee Management',
            'Access Management',
            'View Assesment',
            'Assesment Config',
            'Assesment Line Management',
            'Audit Trail',

            'Employee Dashboard',
            'Evaluatee History',
            'Supporter Form',
            'Academic Form',

            'Evaluator Dashboard',
            'Evaluations',
            'Evaluator History',
            'Log Storing',
        ];

        foreach ($permissions as $permission) {
            Permission::create(['name' => $permission]);
        }

        $admin = Role::firstOrCreate(['name' => 'admin']);
        $admin->givePermissionTo(Permission::all());

        $evaluator = Role::firstOrCreate(['name' => 'evaluator']);

        // $supporter_evaluatee;
    }
}
