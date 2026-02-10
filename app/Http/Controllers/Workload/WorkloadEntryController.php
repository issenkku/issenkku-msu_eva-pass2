<?php

namespace App\Http\Controllers\Workload;

use App\Http\Controllers\Controller;
use App\Http\Requests\Workload\StoreWorkloadEntryRequest;
use App\Http\Requests\Workload\UpdateWorkloadEntryRequest;
use App\Models\WorkloadEntry;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\Request;

class WorkloadEntryController extends Controller
{
    public function index(Request $request)
    {
        $query = WorkloadEntry::query();

        if ($request->filled('report_id')) {
            $query->where('report_id', $request->input('report_id'));
        }

        if ($request->filled('workload_form_id')) {
            $query->where('workload_form_id', $request->input('workload_form_id'));
        }

        if ($request->filled('subject_id')) {
            $query->where('subject_id', $request->input('subject_id'));
        }

        return response()->json($query->get());
    }

    public function show($id)
    {
        try {
            return response()->json(WorkloadEntry::findOrFail($id));
        } catch (ModelNotFoundException $e) {
            return response()->json(['message' => 'Workload entry not found'], 404);
        }
    }

    public function store(StoreWorkloadEntryRequest $request)
    {
        $entry = WorkloadEntry::create($request->validated());

        return response()->json($entry, 201);
    }

    public function update(UpdateWorkloadEntryRequest $request, $id)
    {
        try {
            $entry = WorkloadEntry::findOrFail($id);
            $entry->update($request->validated());

            return response()->json($entry);
        } catch (ModelNotFoundException $e) {
            return response()->json(['message' => 'Workload entry not found'], 404);
        }
    }

    public function destroy($id)
    {
        try {
            $entry = WorkloadEntry::findOrFail($id);
            $entry->delete();

            return response()->json(['message' => 'Workload entry deleted successfully']);
        } catch (ModelNotFoundException $e) {
            return response()->json(['message' => 'Workload entry not found'], 404);
        }
    }
}
