<?php

// ไฟล์คลาสของระบบ: app/Http/Controllers/Setting/JobLevelsController.php

namespace App\Http\Controllers\Setting;

use App\Http\Controllers\Controller;
use App\Models\Setting\JobLevel;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class JobLevelsController extends Controller
{
    private function hasSortOrderColumn(): bool
    {
        return Schema::hasColumn('job_levels', 'sort_order');
    }

    public function index(Request $request)
    {
        $sort = $request->input('sort', 'manual');
        $hasSortOrder = $this->hasSortOrderColumn();

        $jobLevels = JobLevel::query()
            ->when($request->filled('search'), function ($query) use ($request) {
                $search = trim($request->input('search'));

                $query->where('name', 'like', "%{$search}%");
            });

        match ($sort) {
            'manual' => $hasSortOrder
                ? $jobLevels->orderBy('sort_order')->orderBy('id')
                : $jobLevels->orderBy('id'),
            'name_asc' => $jobLevels->orderBy('name'),
            'name_desc' => $jobLevels->orderByDesc('name'),
            'oldest' => $jobLevels->orderBy('id'),
            default => $jobLevels->orderByDesc('id'),
        };

        $jobLevels = $jobLevels->paginate(10)->withQueryString();

        return view('Job Level.index', compact('jobLevels'));
    }

    public function store(Request $request)
    {
        $hasSortOrder = $this->hasSortOrderColumn();

        $validated = $request->validate([
            'name' => 'required|string|max:255|unique:job_levels,name',
        ], [
            'name.required' => 'กรุณากรอกชื่อระดับตำแหน่งงาน',
            'name.unique' => 'ชื่อระดับตำแหน่งงานนี้มีอยู่แล้วในระบบ',
        ]);

        if ($hasSortOrder) {
            $validated['sort_order'] = (JobLevel::max('sort_order') ?? 0) + 1;
        }

        JobLevel::create($validated);

        return redirect()->route('job-level.index')->with('success', 'เพิ่มข้อมูลระดับตำแหน่งงานเรียบร้อยแล้ว');
    }

    public function update(Request $request, $id)
    {
        $jobLevel = JobLevel::find($id);
        if (! $jobLevel) {
            return redirect()->route('job-level.index')->with('error', 'ไม่พบข้อมูลระดับตำแหน่งงานที่ต้องการแก้ไข อาจถูกลบไปแล้ว');
        }

        $validated = $request->validate([
            'name' => 'required|string|max:255|unique:job_levels,name,'.$jobLevel->id,
        ], [
            'name.required' => 'กรุณากรอกชื่อระดับตำแหน่งงาน',
            'name.unique' => 'ชื่อระดับตำแหน่งงานนี้มีอยู่แล้วในระบบ',
        ]);

        $jobLevel->update($validated);

        return redirect()->route('job-level.index')->with('success', 'อัปเดตข้อมูลระดับตำแหน่งงานเรียบร้อยแล้ว');
    }

    public function destroy($id)
    {
        $jobLevel = JobLevel::find($id);
        if (! $jobLevel) {
            return redirect()->route('job-level.index')->with('error', 'ไม่พบข้อมูลระดับตำแหน่งงานที่ต้องการลบ อาจถูกลบไปแล้ว');
        }

        $userCount = $jobLevel->users()->count();

        if ($userCount > 0) {
            return redirect()->route('job-level.index')->with(
                'error',
                "ไม่สามารถลบระดับตำแหน่งงาน {$jobLevel->name} ได้ เนื่องจากยังมีการผูกกับผู้ใช้ {$userCount} รายการ"
            );
        }

        $jobLevel->delete();

        return redirect()->route('job-level.index')->with('success', 'ลบข้อมูลระดับตำแหน่งงานเรียบร้อยแล้ว');
    }

    public function reorder(Request $request)
    {
        if (! $this->hasSortOrderColumn()) {
            return response()->json(['message' => 'sort_order column is unavailable'], 200);
        }

        $validated = $request->validate([
            'ids' => ['required', 'array', 'min:1'],
            'ids.*' => ['integer', 'distinct', 'exists:job_levels,id'],
            'start_order' => ['required', 'integer', 'min:1'],
        ]);

        DB::transaction(function () use ($validated) {
            foreach ($validated['ids'] as $offset => $id) {
                JobLevel::whereKey($id)->update([
                    'sort_order' => $validated['start_order'] + $offset,
                ]);
            }
        });

        return response()->json(['message' => 'reordered']);
    }
}
