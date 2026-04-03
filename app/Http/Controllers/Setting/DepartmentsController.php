<?php

namespace App\Http\Controllers\Setting;

use App\Http\Controllers\Controller;
use App\Models\Setting\Departments;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class DepartmentsController extends Controller
{
    private function hasSortOrderColumn(): bool
    {
        return Schema::hasColumn('departments', 'sort_order');
    }

    public function index(Request $request)
    {
        $sort = $request->input('sort', 'manual');
        $usage = $request->input('usage');
        $hasSortOrder = $this->hasSortOrderColumn();

        $departments = Departments::query()
            ->withCount('user')
            ->when($request->filled('search'), function ($query) use ($request) {
                $search = trim($request->input('search'));

                $query->where('department_name', 'like', "%{$search}%");
            })
            ->when($usage === 'used', fn ($query) => $query->has('user'))
            ->when($usage === 'unused', fn ($query) => $query->doesntHave('user'));

        match ($sort) {
            'manual' => $hasSortOrder
                ? $departments->orderBy('sort_order')->orderBy('id')
                : $departments->orderBy('id'),
            'name_asc' => $departments->orderBy('department_name'),
            'name_desc' => $departments->orderByDesc('department_name'),
            'most_users' => $departments->orderByDesc('user_count')->orderBy('department_name'),
            'least_users' => $departments->orderBy('user_count')->orderBy('department_name'),
            'oldest' => $departments->orderBy('id'),
            default => $departments->orderByDesc('id'),
        };

        $departments = $departments->paginate(10)->withQueryString();

        return view('departments.index', compact('departments'));
    }

    public function store(Request $request)
    {
        $hasSortOrder = $this->hasSortOrderColumn();

        $request->validate([
            'department_name' => 'required|string|max:255',
        ]);

        $existingDepartment = Departments::where('department_name', $request->department_name)->first();

        if ($existingDepartment) {
            return redirect()->back()
                ->withInput()
                ->withErrors(['department_name' => 'ชื่อแผนกนี้มีอยู่แล้วในระบบ กรุณาใช้ชื่ออื่น']);
        }

        $payload = [
            'department_name' => $request->department_name,
        ];

        if ($hasSortOrder) {
            $payload['sort_order'] = (Departments::max('sort_order') ?? 0) + 1;
        }

        Departments::create($payload);

        return redirect()->route('departments.index')->with('success', 'เพิ่มข้อมูลเรียบร้อยแล้ว');
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'department_name' => 'required|string|max:255',
        ]);

        $existingDepartment = Departments::where('department_name', $request->department_name)
            ->where('id', '!=', $id)
            ->first();

        if ($existingDepartment) {
            return redirect()->back()
                ->withInput()
                ->withErrors(['department_name' => 'ชื่อแผนกนี้มีอยู่แล้วในระบบ กรุณาใช้ชื่ออื่น']);
        }

        $department = Departments::findOrFail($id);
        $department->update([
            'department_name' => $request->department_name,
        ]);

        return redirect()->route('departments.index')->with('success', 'อัปเดตข้อมูลเรียบร้อยแล้ว');
    }

    public function destroy($id)
    {
        $department = Departments::findOrFail($id);

        $userCount = $department->user()->count();

        if ($userCount > 0) {
            return redirect()->route('departments.index')->with(
                'error',
                "ไม่สามารถลบหน่วยงาน {$department->department_name} ได้ เนื่องจากยังมีการผูกกับผู้ใช้ {$userCount} รายการ"
            );
        }

        $department->delete();

        return redirect()->route('departments.index')->with('success', 'ลบข้อมูลเรียบร้อยแล้ว');
    }

    public function reorder(Request $request)
    {
        if (! $this->hasSortOrderColumn()) {
            return response()->json(['message' => 'sort_order column is unavailable'], 200);
        }

        $validated = $request->validate([
            'ids' => ['required', 'array', 'min:1'],
            'ids.*' => ['integer', 'distinct', 'exists:departments,id'],
            'start_order' => ['required', 'integer', 'min:1'],
        ]);

        DB::transaction(function () use ($validated) {
            foreach ($validated['ids'] as $offset => $id) {
                Departments::whereKey($id)->update([
                    'sort_order' => $validated['start_order'] + $offset,
                ]);
            }
        });

        return response()->json(['message' => 'reordered']);
    }
}
