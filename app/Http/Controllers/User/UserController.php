<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Models\Setting\Departments;
use App\Models\Setting\Positions;
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
        // เริ่มต้น Query Builder พร้อมกับ Eager Loading ที่จำเป็น
        $query = User::with(['position', 'roles']);

        // --- เพิ่ม Logic การค้นหา ---
        if ($request->filled('search')) {
            $searchTerm = $request->input('search');
            // กรองข้อมูลจากคอลัมน์ 'name' และสามารถเพิ่มคอลัมน์อื่นได้
            // เช่น ค้นหาจากรหัสพนักงานด้วย
            $query->where(function ($q) use ($searchTerm) {
                $q->where('name', 'like', '%' . $searchTerm . '%')
                  ->orWhere('employee_id', 'like', '%' . $searchTerm . '%');
            });
        }
        // -------------------------

        // --- Filter ที่มีอยู่เดิม ---
        if ($request->filled('department_id')) {
            $query->where('department_id', $request->department_id);
        }
        if ($request->filled('position_id')) {
            $query->where('position_id', $request->position_id);
        }
        if ($request->filled('personnel_type')) {
            // หมายเหตุ: ถ้า filter นี้มาจาก <x-filter> ที่คุณให้มาก่อนหน้า
            // ชื่อ name อาจจะเป็น 'personnel_type_id' ไม่ใช่ 'personnel_type'
            // กรุณาตรวจสอบให้ตรงกัน
            $query->where('personnel_type', $request->personnel_type);
        }
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }
        // -------------------------

        // ดึงข้อมูลพร้อม Pagination และส่งต่อ Query String ทั้งหมด
        $users = $query->latest()->paginate(10)->withQueryString();

        // ดึงข้อมูลสำหรับ Dropdown/Filter
        $departments = Departments::all();
        $positions = Positions::all();
        $roles = Role::all();
        $user = null; // สำหรับฟอร์มสร้างผู้ใช้ใหม่

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

