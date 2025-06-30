<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Models\Department;
use App\Models\Position;
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
            'name' => 'required|string|max:100',
            'employee_id'=> 'required|max:20|unique:users,employee_id',
            'password'=> ['required','max:50', Rules\Password::defaults()],
            'email'=> 'required|string|lowercase|email:rfc,dns|max:50|unique:users,email',
            'phone'=> 'required|max:20|regex:/^[0-9]{10}$/|unique:users,phone',
            'personnel_type'=> 'required|string|max:100',
            'bio'=>'nullable|string|max:1000',
            'status'=>'required|max:20',
            'position_id'=> 'required|integer',
            'department_id'=> 'required|integer',
            'role' => 'string',
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

        $user->assignRole($request->role);

        return redirect()->route('users.index')->with('success', 'เพิ่มผู้ใช้เรียบร้อยแล้ว');
    }

    public function index()
    {
        $users = User::with('position')->paginate(10);
        $departments = Department::all();
        $positions = Position::all();
        $roles = Role::all();
        return view('user.management.index', compact('users', 'departments', 'positions', 'roles'));
    }

    public function update(Request $request, User $user):RedirectResponse
    {
        $rules = ([
            'prefix'=> 'required|string|max:10',
            'name' => 'required|string|max:100',
            'phone'=> ['required', 'max:20','regex:/^[0-9]{10}$/', 
                        Rule::unique('users', 'phone')->ignore($user->id)],
            'personnel_type'=> 'required|string|max:100',
            'bio'=>'nullable|string|max:1000',
            'status'=>'required|max:20',
            'position_id'=> 'required|integer',
            'department_id'=> 'required|integer',
            'role' => '',
            'employee_id' => ['required', 'max:20',
                        Rule::unique('users', 'employee_id')->ignore($user->id),
            ],
            'email' => ['required', 'string', 'email:rfc,dns', 'max:50',
                        Rule::unique('users')->ignore($user->id),],
        ]);

        if ($request->filled('password')) {
            $rules['password'] = ['required', 'confirmed', 'max:50', Rules\Password::defaults()];
        }

        $validated = $request->validate($rules);

        $user->fill(collect($validated)->except('password')->toArray());

        if ($request->filled('password')) {
            $user->password = $request->input('password'); // no Hash::make here
        }

        $user->save();
        $user->assignRole($request->role);

        return redirect()->route('users.index')->with('success', 'อัปเดตข้อมูลเรียบร้อยแล้ว');
    }

    public function destroy(User $user)
    {
        $user->delete();
        return redirect()->route('users.index')->with('success', 'ลบเรียบร้อยแล้ว');
    }
}

