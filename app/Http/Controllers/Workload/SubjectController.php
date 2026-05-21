<?php

// ไฟล์คลาสของระบบ: app/Http/Controllers/Workload/SubjectController.php

namespace App\Http\Controllers\Workload;

use App\Http\Controllers\Controller;
use App\Http\Requests\Workload\StoreSubjectRequest;
use App\Http\Requests\Workload\UpdateSubjectRequest;
use App\Models\Subject;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class SubjectController extends Controller
{
    private function hasSortOrderColumn(): bool
    {
        return Schema::hasColumn('subjects', 'sort_order');
    }

    public function index(Request $request)
    {
        if ($request->expectsJson()) {
            return response()->json(Subject::all());
        }

        $sort = $request->input('sort', 'manual');
        $status = $request->input('status');
        $hasSortOrder = $this->hasSortOrderColumn();

        $subjects = Subject::query()
            ->when($request->filled('search'), function ($query) use ($request) {
                $search = trim($request->input('search'));

                $query->where(function ($subQuery) use ($search) {
                    $subQuery->where('code', 'like', "%{$search}%")
                        ->orWhere('name_th', 'like', "%{$search}%")
                        ->orWhere('name_en', 'like', "%{$search}%");
                });
            })
            ->when($status === 'active', fn ($query) => $query->where('is_active', true))
            ->when($status === 'inactive', fn ($query) => $query->where('is_active', false));

        match ($sort) {
            'manual' => $hasSortOrder
                ? $subjects->orderBy('sort_order')->orderBy('id')
                : $subjects->orderBy('id'),
            'latest' => $subjects->orderByDesc('id'),
            'oldest' => $subjects->orderBy('id'),
            'code_desc' => $subjects->orderByDesc('code'),
            'name_asc' => $subjects->orderBy('name_th'),
            'name_desc' => $subjects->orderByDesc('name_th'),
            default => $subjects->orderBy('code'),
        };

        $subjects = $subjects->paginate(10)->withQueryString();

        return view('subjects.index', compact('subjects'));
    }

    public function show($id)
    {
        try {
            return response()->json(Subject::findOrFail($id));
        } catch (ModelNotFoundException $e) {
            return response()->json(['message' => 'Subject not found'], 404);
        }
    }

    public function store(StoreSubjectRequest $request)
    {
        $validated = $request->validated();

        if ($this->hasSortOrderColumn()) {
            $validated['sort_order'] = (Subject::max('sort_order') ?? 0) + 1;
        }

        $subject = Subject::create($validated);

        if ($request->expectsJson()) {
            return response()->json($subject, 201);
        }

        $redirectTo = $request->input('redirect_to')
            ?? $request->query('redirect_to')
            ?? $request->headers->get('referer');

        if ($redirectTo) {
            return redirect()->to($redirectTo)->with('success', 'เพิ่มข้อมูลรายวิชาเรียบร้อยแล้ว');
        }

        return redirect()->route('subjects.index')->with('success', 'เพิ่มข้อมูลรายวิชาเรียบร้อยแล้ว');
    }

    public function update(UpdateSubjectRequest $request, $id)
    {
        try {
            $subject = Subject::findOrFail($id);
            $subject->update($request->validated());

            if ($request->expectsJson()) {
                return response()->json($subject);
            }

            $redirectTo = $request->input('redirect_to')
                ?? $request->query('redirect_to')
                ?? $request->headers->get('referer');

            if ($redirectTo) {
                return redirect()->to($redirectTo)->with('success', 'อัปเดตข้อมูลรายวิชาเรียบร้อยแล้ว');
            }

            return redirect()->route('subjects.index')->with('success', 'อัปเดตข้อมูลรายวิชาเรียบร้อยแล้ว');
        } catch (ModelNotFoundException $e) {
            if ($request->expectsJson()) {
                return response()->json(['message' => 'Subject not found'], 404);
            }

            $redirectTo = $request->input('redirect_to')
                ?? $request->query('redirect_to')
                ?? $request->headers->get('referer');

            if ($redirectTo) {
                return redirect()->to($redirectTo)->with('error', 'ไม่พบรายวิชาที่ต้องการแก้ไข');
            }

            return redirect()->route('subjects.index')->with('error', 'ไม่พบรายวิชาที่ต้องการแก้ไข');
        }
    }

    public function destroy($id)
    {
        try {
            $subject = Subject::findOrFail($id);
            $subject->delete();

            if (request()->expectsJson()) {
                return response()->json(['message' => 'Subject deleted successfully']);
            }

            $redirectTo = request()->input('redirect_to')
                ?? request()->query('redirect_to')
                ?? request()->headers->get('referer');

            if ($redirectTo) {
                return redirect()->to($redirectTo)->with('success', 'ลบข้อมูลรายวิชาเรียบร้อยแล้ว');
            }

            return redirect()->route('subjects.index')->with('success', 'ลบข้อมูลรายวิชาเรียบร้อยแล้ว');
        } catch (ModelNotFoundException $e) {
            if (request()->expectsJson()) {
                return response()->json(['message' => 'Subject not found'], 404);
            }

            $redirectTo = request()->input('redirect_to')
                ?? request()->query('redirect_to')
                ?? request()->headers->get('referer');

            if ($redirectTo) {
                return redirect()->to($redirectTo)->with('error', 'ไม่พบรายวิชาที่ต้องการลบ');
            }

            return redirect()->route('subjects.index')->with('error', 'ไม่พบรายวิชาที่ต้องการลบ');
        }
    }

    public function reorder(Request $request)
    {
        if (! $this->hasSortOrderColumn()) {
            return response()->json(['message' => 'sort_order column is unavailable'], 200);
        }

        $validated = $request->validate([
            'ids' => ['required', 'array', 'min:1'],
            'ids.*' => ['integer', 'distinct', 'exists:subjects,id'],
            'start_order' => ['required', 'integer', 'min:1'],
        ]);

        DB::transaction(function () use ($validated) {
            foreach ($validated['ids'] as $offset => $id) {
                Subject::whereKey($id)->update([
                    'sort_order' => $validated['start_order'] + $offset,
                ]);
            }
        });

        return response()->json(['message' => 'reordered']);
    }
}
