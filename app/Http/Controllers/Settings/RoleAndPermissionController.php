<?php

namespace App\Http\Controllers\Settings;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Support\AuditLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
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

        $role = Role::create([
            'name' => $request->name,
            'guard_name' => 'web',
        ]);
        $role->syncPermissions($request->permissions ?? []);

        if ($request->expectsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Role created.',
                'html' => [
                    'row' => view('user.role-management.partials.index-table-row', [
                        'index' => Role::count(),
                        'role' => $role->load('permissions'),
                    ])->render(),
                ],
                'state' => ['id' => $role->id],
            ], 201);
        }

        return redirect()->route('roles.index')->with('success', 'Role created.');
    }

    public function show(Role $role)
    {
        return view('roles.show', compact('role'));
    }

    // {
    //         'user_id' => 'required|exists:users,id',
    //         'role' => 'required|exists:roles,name',

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

        $roleNameBefore = $role->name;
        $permissionsBefore = $role->permissions()->pluck('name')->sort()->values()->all();
        $usersBefore = DB::table('model_has_roles')
            ->where('role_id', $role->id)
            ->where('model_type', User::class)
            ->pluck('model_id')
            ->map(fn ($id) => (int) $id)
            ->sort()
            ->values()
            ->all();

        $role->update(['name' => $request->name]);
        $role->syncPermissions($request->permissions ?? []);

        // Get IDs of selected users
        $selectedUserIds = collect($request->users ?? [])
            ->map(fn ($id) => (int) $id)
            ->all();

        // Assign this role to newly selected users (if they don't already have it)
        foreach ($selectedUserIds as $userId) {
            $user = User::find($userId);
            if (! $user->hasRole($role->name)) {
                $user->assignRole($role->name);
            }
        }

        // Optionally: Remove role from users who are no longer selected
        $previousUsers = DB::table('model_has_roles')
            ->where('role_id', $role->id)
            ->where('model_type', User::class)
            ->pluck('model_id')
            ->map(fn ($id) => (int) $id)
            ->all();
        $toRemove = array_diff($previousUsers, $selectedUserIds);

        foreach ($toRemove as $userId) {
            $user = User::find($userId);
            $user->removeRole($role->name);
        }

        $assignedUserIds = DB::table('model_has_roles')
            ->where('role_id', $role->id)
            ->where('model_type', User::class)
            ->pluck('model_id')
            ->map(fn ($id) => (int) $id)
            ->sort()
            ->values()
            ->all();

        AuditLog::record('สิทธิ์การใช้งาน', 'แก้ไขบทบาทและสิทธิ์', [
            'role_id' => $role->id,
            'role_name_before' => $roleNameBefore,
            'role_name_after' => $role->name,
            'permissions_before' => $permissionsBefore,
            'permissions_after' => $role->permissions()->pluck('name')->sort()->values()->all(),
            'user_ids_before' => $usersBefore,
            'assigned_user_ids' => $assignedUserIds,
        ], $role, $request->user());

        return redirect()->route('roles.index')->with('success', 'Role updated.');
    }

    public function destroy(Role $role)
    {
        $roleId = $role->id;
        $role->delete();

        if (request()->expectsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Role deleted successfully.',
                'state' => ['deleted_ids' => [$roleId]],
            ]);
        }

        return redirect()->route('roles.index')->with(['message' => 'Role deleted successfully.']);
    }
}
