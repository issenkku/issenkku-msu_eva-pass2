<?php

namespace App\Http\Controllers\Settings;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Illuminate\Support\Facades\DB;

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
            Permission::firstOrCreate(['name' => $permission]);
        }

        $admin = Role::firstOrCreate(['name' => 'admin']);
        $admin->givePermissionTo(Permission::all());

        $evaluator = Role::firstOrCreate(['name' => 'ผู้ประเมิน']);

        $evaluatee = Role::firstOrCreate(['name' => 'ผู้ถูกประเมิน']);

        $manager = Role::firstOrCreate(['name' => 'ผู้บริหาร']);
    }

    public function index()
    {
        $roles = Role::all();
        return view('', compact('roles'));
    }

    public function store(Request $request):RedirectResponse
    {
        $role = Role::create(['name' => $request->name]);

        foreach ($request->permission as $permission) {
            $role->givePermissionTo($permission);
        }

        foreach ($request->users as $user) {
            $user = User::find($user);
            $user->assignRole($role->name);
        }

        return redirect()->route('')->with('success', 'เพิ่มข้อมูลเรียบร้อยแล้ว');
    }

    public function update(Request $request, Role $role):RedirectResponse
    {
        $role = Role::where('id', $request->id)->first();
        $role->name = $request->name;
        $role->update();

        $role->syncPermissions($request->permission);

        DB::table('model_has_roles')->where('role_id', $request->id)->delete();

        foreach ($request->users as $user) {
            $user = User::find($user);
            $user->assignRole($role->name);
        }

        return redirect()->route('')->with('success', 'อัปเดตข้อมูลเรียบร้อยแล้ว');
    }

    public function destroy(Role $role)
    {
        $role->delete();
        return response()->json(['message' => 'Role deleted successfully.']);
    }
}
