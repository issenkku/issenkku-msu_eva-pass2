<?php

namespace App\Http\Controllers\Settings;


use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class RoleAndPermissionController extends Controller
{
    /**
     * เมธอด: setupRolesAndPermissions
     * จุดประสงค์: ประมวลผลคำขอ
     * อินพุต: ไม่มี
     * เอาต์พุต: ผลลัพธ์ตามการประมวลผล
     * @param void ไม่มีพารามิเตอร์
     * @return mixed ผลลัพธ์ของการทำงาน
     */
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

    /**
     * เมธอด: index
     * จุดประสงค์: แสดงหน้า user.role-management.index
     * อินพุต: ไม่มี
     * เอาต์พุต: หน้า user.role-management.index
     * @param void ไม่มีพารามิเตอร์
     * @return mixed ผลลัพธ์ของการทำงาน
     */
    public function index()
    {
        $roles = Role::with('permissions')->get();
        $permissions = Permission::all();

        return view('user.role-management.index', compact('roles', 'permissions'));
    }

    /**
     * เมธอด: store
     * จุดประสงค์: ตรวจสอบข้อมูลจากคำขอ บันทึกข้อมูล Role และเปลี่ยนเส้นทางไปที่ route roles.index
     * อินพุต: ข้อมูลจากคำขอ
     * เอาต์พุต: Redirect ไปที่ route roles.index
     * @param Request $request ค่าที่รับเข้ามา
     * @return mixed ผลลัพธ์ของการทำงาน
     */
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

    /**
     * เมธอด: show
     * จุดประสงค์: แสดงหน้า roles.show
     * อินพุต: โมเดล Role
     * เอาต์พุต: หน้า roles.show
     * @param Role $role ค่าที่รับเข้ามา
     * @return mixed ผลลัพธ์ของการทำงาน
     */
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

    /**
     * เมธอด: edit
     * จุดประสงค์: แสดงหน้า user.role-management.edit-role
     * อินพุต: โมเดล Role
     * เอาต์พุต: หน้า user.role-management.edit-role
     * @param Role $role ค่าที่รับเข้ามา
     * @return mixed ผลลัพธ์ของการทำงาน
     */
    public function edit(Role $role)
    {
        $permissions = Permission::all();
        $users = User::all();

        $rolePermissions = $role->permissions->pluck('name')->toArray();
        $assignedUsers = $role->users->pluck('id')->toArray(); // users with this role

        return view('user.role-management.edit-role', compact('role', 'permissions', 'rolePermissions', 'users', 'assignedUsers'));
    }

    /**
     * เมธอด: update
     * จุดประสงค์: ตรวจสอบข้อมูลจากคำขอ อัปเดตข้อมูล และเปลี่ยนเส้นทางไปที่ route roles.index
     * อินพุต: ข้อมูลจากคำขอ, โมเดล Role
     * เอาต์พุต: Redirect ไปที่ route roles.index
     * @param Request $request ค่าที่รับเข้ามา
     * @param Role $role ค่าที่รับเข้ามา
     * @return mixed ผลลัพธ์ของการทำงาน
     */
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

    /**
     * เมธอด: destroy
     * จุดประสงค์: ลบข้อมูล และเปลี่ยนเส้นทางไปที่ route roles.index
     * อินพุต: โมเดล Role
     * เอาต์พุต: Redirect ไปที่ route roles.index
     * @param Role $role ค่าที่รับเข้ามา
     * @return mixed ผลลัพธ์ของการทำงาน
     */
    public function destroy(Role $role)
    {
        $role->delete();

        return redirect()->route('roles.index')->with(['message' => 'Role deleted successfully.']);
    }
}
