<?php

// ไฟล์คลาสของระบบ: app/Http/Controllers/Setting/PositionsController.php

namespace App\Http\Controllers\Setting;

use App\Http\Controllers\Controller;
use App\Models\AssignmentData;
use App\Models\Setting\Positions;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class PositionsController extends Controller
{
    private function hasSortOrderColumn(): bool
    {
        return Schema::hasColumn('positions', 'sort_order');
    }

    public function index(Request $request)
    {
        $sort = $request->input('sort', 'manual');
        $usage = $request->input('usage');
        $hasSortOrder = $this->hasSortOrderColumn();

        $positions = Positions::query()
            ->withCount('user')
            ->when($request->filled('search'), function ($query) use ($request) {
                $search = trim($request->input('search'));

                $query->where('name', 'like', "%{$search}%");
            })
            ->when($usage === 'used', fn ($query) => $query->has('user'))
            ->when($usage === 'unused', fn ($query) => $query->doesntHave('user'));

        match ($sort) {
            'manual' => $hasSortOrder
                ? $positions->orderBy('sort_order')->orderBy('id')
                : $positions->orderBy('id'),
            'name_asc' => $positions->orderBy('name'),
            'name_desc' => $positions->orderByDesc('name'),
            'most_users' => $positions->orderByDesc('user_count')->orderBy('name'),
            'least_users' => $positions->orderBy('user_count')->orderBy('name'),
            'oldest' => $positions->orderBy('id'),
            default => $positions->orderByDesc('id'),
        };

        $positions = $positions->paginate(10)->withQueryString();

        return view('positions.index', compact('positions'));
    }

    public function store(Request $request)
    {
        $hasSortOrder = $this->hasSortOrderColumn();

        $request->validate([
            'name' => 'required|string|max:255',
        ]);

        $existingPosition = Positions::where('name', $request->name)->first();

        if ($existingPosition) {
            return redirect()->back()
                ->withInput()
                ->withErrors(['name' => 'ชื่อตำแหน่งนี้มีอยู่แล้วในระบบ กรุณาใช้ชื่ออื่น']);
        }

        $payload = [
            'name' => $request->name,
        ];

        if ($hasSortOrder) {
            $payload['sort_order'] = (Positions::max('sort_order') ?? 0) + 1;
        }

        Positions::create($payload);

        return redirect()->route('positions.index')->with('success', 'เพิ่มข้อมูลเรียบร้อยแล้ว');
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'name' => 'required|string|max:255',
        ]);

        $existingPosition = Positions::where('name', $request->name)
            ->where('id', '!=', $id)
            ->first();

        if ($existingPosition) {
            return redirect()->back()
                ->withInput()
                ->withErrors(['name' => 'ชื่อตำแหน่งนี้มีอยู่แล้วในระบบ กรุณาใช้ชื่ออื่น']);
        }

        $positions = Positions::find($id);
        if (! $positions) {
            return redirect()->route('positions.index')->with('error', 'ไม่พบข้อมูลตำแหน่งที่ต้องการแก้ไข อาจถูกลบไปแล้ว');
        }

        $positions->update([
            'name' => $request->name,
        ]);

        return redirect()->route('positions.index')->with('success', 'อัปเดตข้อมูลเรียบร้อยแล้ว');
    }

    public function destroy($id)
    {
        $positions = Positions::find($id);
        if (! $positions) {
            return redirect()->route('positions.index')->with('error', 'ไม่พบข้อมูลตำแหน่งที่ต้องการลบ อาจถูกลบไปแล้ว');
        }

        $userCount = $positions->user()->count();
        $evaluatorAssignmentCount = AssignmentData::where('evaluator_position_id', $positions->id)->count();
        $evaluateeAssignmentCount = AssignmentData::where('evaluatee_position_id', $positions->id)->count();

        $bindings = [];

        if ($userCount > 0) {
            $bindings[] = "ผู้ใช้ {$userCount} รายการ";
        }

        if ($evaluatorAssignmentCount > 0) {
            $bindings[] = "รอบประเมินในฝั่งผู้ประเมิน {$evaluatorAssignmentCount} รายการ";
        }

        if ($evaluateeAssignmentCount > 0) {
            $bindings[] = "รอบประเมินในฝั่งผู้ถูกประเมิน {$evaluateeAssignmentCount} รายการ";
        }

        if ($bindings !== []) {
            return redirect()->route('positions.index')->with(
                'error',
                "ไม่สามารถลบตำแหน่ง {$positions->name} ได้ เนื่องจากยังมีการผูกกับ ".implode(', ', $bindings)
            );
        }

        $positions->delete();

        return redirect()->route('positions.index')->with('success', 'ลบข้อมูลเรียบร้อยแล้ว');
    }

    public function reorder(Request $request)
    {
        if (! $this->hasSortOrderColumn()) {
            return response()->json(['message' => 'sort_order column is unavailable'], 200);
        }

        $validated = $request->validate([
            'ids' => ['required', 'array', 'min:1'],
            'ids.*' => ['integer', 'distinct', 'exists:positions,id'],
            'start_order' => ['required', 'integer', 'min:1'],
        ]);

        DB::transaction(function () use ($validated) {
            foreach ($validated['ids'] as $offset => $id) {
                Positions::whereKey($id)->update([
                    'sort_order' => $validated['start_order'] + $offset,
                ]);
            }
        });

        return response()->json(['message' => 'reordered']);
    }
}
