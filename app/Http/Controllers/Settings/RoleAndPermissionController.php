<?php

namespace App\Http\Controllers\Settings;

use App\Http\Controllers\Controller;
use App\Models\User;
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
        $roles = Role::with('permissions')->get();
        $permissions = Permission::all();

        return view('user.role-management.index', compact('roles', 'permissions'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|unique:roles,name',
            'permissions' => 'nullable|array',
        ]);

        $role = Role::create(['name' => $request->name]);
        $role->syncPermissions($request->permissions ?? []);

        return redirect()->route('roles.index')->with('success', 'Role created.');
    }

    public function show(Role $role)
    {
        return view('roles.show', compact('role'));
    }

    // public function assignRole(Request $request)
    // {
    //     $request->validate([
    //         'user_id' => 'required|exists:users,id',
    //         'role' => 'required|exists:roles,name',
    //     ]);

    //     $user = User::findOrFail($request->user_id);
    //     $user->syncRoles([$request->role]);

    //     return back()->with('success', 'Role assigned to user.');
    // }

    public function edit(Role $role)
    {
        $permissions = Permission::all();
        $users = User::all();

        $rolePermissions = $role->permissions->pluck('name')->toArray();
        $assignedUsers = $role->users->pluck('id')->toArray(); // users with this role

        return view('user.role-management.edit-role', compact('role', 'permissions', 'rolePermissions', 'users', 'assignedUsers'));
    }

    public function update(Request $request, Role $role)
    {
        $request->validate([
            'name' => 'required|string',
            'permissions' => 'nullable|array',
            'users' => 'nullable|array',
        ]);

        $role->update(['name' => $request->name]);
        $role->syncPermissions($request->permissions ?? []);

        // Get IDs of selected users
        $selectedUserIds = $request->users ?? [];

        // Assign this role to newly selected users (if they don't already have it)
        foreach ($selectedUserIds as $userId) {
            $user = User::find($userId);
            if (! $user->hasRole($role->name)) {
                $user->assignRole($role->name);
            }
        }

        // Optionally: Remove role from users who are no longer selected
        $previousUsers = $role->users()->pluck('id')->toArray();
        $toRemove = array_diff($previousUsers, $selectedUserIds);

        foreach ($toRemove as $userId) {
            $user = User::find($userId);
            $user->removeRole($role->name);
        }

        return redirect()->route('roles.index')->with('success', 'Role updated.');
    }

    public function destroy(Role $role)
    {
        $role->delete();

        return redirect()->route('roles.index')->with(['message' => 'Role deleted successfully.']);
    }
}
