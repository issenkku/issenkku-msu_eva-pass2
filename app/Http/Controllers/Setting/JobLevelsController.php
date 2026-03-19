<?php

namespace App\Http\Controllers\Setting;

use App\Http\Controllers\Controller;
use App\Models\Setting\JobLevel;
use Illuminate\Http\Request;

class JobLevelsController extends Controller
{
    public function index(Request $request)
    {
        $sort = $request->input('sort', 'latest');

        $jobLevels = JobLevel::query()
            ->when($request->filled('search'), function ($query) use ($request) {
                $search = trim($request->input('search'));

                $query->where('name', 'like', "%{$search}%");
            });

        match ($sort) {
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
        $validated = $request->validate([
            'name' => 'required|string|max:255|unique:job_levels,name',
        ], [
            'name.required' => 'กรุณากรอกชื่อระดับตำแหน่งงาน',
            'name.unique' => 'ชื่อระดับตำแหน่งงานนี้มีอยู่แล้วในระบบ',
        ]);

        JobLevel::create($validated);

        return redirect()->route('job-level.index')->with('success', 'เพิ่มข้อมูลระดับตำแหน่งงานเรียบร้อยแล้ว');
    }

    public function update(Request $request, $id)
    {
        $jobLevel = JobLevel::findOrFail($id);

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
        $jobLevel = JobLevel::findOrFail($id);

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
}
