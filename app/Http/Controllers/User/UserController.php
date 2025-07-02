<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Models\Setting\Department;
use App\Models\Setting\Position;
use Illuminate\Http\RedirectResponse;
use Illuminate\Validation\Rules;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Hash;
use App\Models\User;

use Response;
use Spatie\Permission\Models\Role;

class UserController extends Controller
{
    //create page to add use

    public function store(Request $request):RedirectResponse{
        $request->validate([
            'prefix'=> 'required|string|max:10',
            'name' => 'required|string|max:100|unique:users,name',
            'employee_id'=> 'required|max:20|unique:users,employee_id',
            'password'=> ['required','max:50'],
            'email'=> 'required|string|lowercase|email:rfc,dns|max:50|unique:users,email',
            'phone'=> 'required|max:20|unique:users,phone',
            'personnel_type'=> 'required|string|max:100',
            'bio'=>'nullable|string|max:1000',
            'status'=>'required|max:20',
            'position_id'=> 'required|exists:positions,id',
            'department_id'=> 'required|exists:departments,id',
            'role' => 'nullable|string',
        ], [
            'name.unique' => 'ชื่อ-นามสกุลนี้ถูกใช้ไปแล้ว',
            'employee_id.unique' => 'รหัสพนักงานนี้ถูกใช้ไปแล้ว',
            'email.unique' => 'อีเมลนี้ถูกใช้ไปแล้ว',
            'phone.unique' => 'เบอร์โทรนี้ถูกใช้ไปแล้ว',
        ]);

        $user = User::create([
            'prefix' => $request->prefix,
            'name'=> $request->name,
            'employee_id'=> $request->employee_id,
            'password' => Hash::make($request->password),
            'email'=> $request->email,
            'phone'=> $request->phone,
            'personnel_type'=> $request->personnel_type,
            'bio'=> $request->bio,
            'status'=> $request->status,
            'position_id'=> $request->position_id,
            'department_id'=> $request->department_id,
        ]);

        // ป้องกัน assignRole ถ้าไม่มีค่า role
        if ($request->filled('role')) {
            $user->syncRoles([$request->role]);
        }

        return redirect()->route('users.index')->with('success', 'เพิ่มผู้ใช้เรียบร้อยแล้ว');
    }

    public function index(Request $request)
    {
        $query = User::with(['position', 'roles']);

        if ($request->filled('department_id')) {
            $query->where('department_id', $request->department_id);
        }
        if ($request->filled('position_id')) {
            $query->where('position_id', $request->position_id);
        }
        if ($request->filled('personnel_type')) {
            $query->where('personnel_type', $request->personnel_type);
        }
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $users = $query->paginate(10)->appends($request->query());
        $departments = Department::all();
        $positions = Position::all();
        $roles = Role::all();
        $user = null;

        return view('user.management.index', compact('users', 'departments', 'positions', 'roles', 'user'));
    }

    public function update(Request $request, User $user):RedirectResponse
    {
        $rules = ([
            'prefix'=> 'required|string|max:10',
            'name' => 'required|string|max:100',
            'phone'=> ['required', 'max:20', 
                        Rule::unique('users', 'phone')->ignore($user->id)],
            'personnel_type'=> 'required|string|max:100',
            'bio'=>'nullable|string|max:1000',
            'status'=>'required|max:20',
            'position_id'=> 'required|integer',
            'department_id'=> 'required|integer',
            'role' => 'nullable|string',
            'employee_id' => ['required', 'max:20',
                        Rule::unique('users', 'employee_id')->ignore($user->id),
            ],
            'email' => ['required', 'string', 'email:rfc,dns', 'max:50',
                        Rule::unique('users')->ignore($user->id),],
        ]);

        if ($request->filled('password')) {
            $rules['password'] = ['required', 'confirmed', 'max:50'];
        }

        $validated = $request->validate($rules);

        $user->fill(collect($validated)->except('password')->toArray());

        if ($request->filled('password')) {
            $user->password = Hash::make($request->input('password'));
        }

        $user->save();
        // syncRoles เพื่อบันทึกบทบาทที่เลือกไว้
        if ($request->filled('role')) {
            $user->syncRoles([$request->role]);
        }

        return redirect()->route('users.index')->with('success', 'อัปเดตข้อมูลเรียบร้อยแล้ว');
    }

    public function destroy(User $user)
    {
        $user->delete();
        return redirect()->route('users.index')->with('success', 'ลบเรียบร้อยแล้ว');
    }
}

