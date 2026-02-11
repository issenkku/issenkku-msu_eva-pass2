<?php

namespace App\Http\Controllers\Workload;

use App\Http\Controllers\Controller;
use App\Http\Requests\Workload\StoreWorkloadEntryRequest;
use App\Http\Requests\Workload\UpdateWorkloadEntryRequest;
use App\Models\WorkloadEntry;
use App\Models\WorkloadForm;
use App\Services\WorkloadFormulaEvaluator;
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
        $validated = $request->validated();
        $form = WorkloadForm::with('fields')->findOrFail($validated['workload_form_id']);
        $calculatedScore = app(WorkloadFormulaEvaluator::class)
            ->evaluate($form->formula_logic, $form->fields, $validated['field_values']);

        $validated['calculated_score'] = $calculatedScore;
        $entry = WorkloadEntry::create($validated);

        return response()->json($entry, 201);
    }

    public function update(UpdateWorkloadEntryRequest $request, $id)
    {
        try {
            $entry = WorkloadEntry::findOrFail($id);
            $validated = $request->validated();
            $workloadFormId = $validated['workload_form_id'] ?? $entry->workload_form_id;
            $fieldValues = $validated['field_values'] ?? $entry->field_values ?? [];

            $form = WorkloadForm::with('fields')->findOrFail($workloadFormId);
            $calculatedScore = app(WorkloadFormulaEvaluator::class)
                ->evaluate($form->formula_logic, $form->fields, $fieldValues);

            $validated['calculated_score'] = $calculatedScore;
            $entry->update($validated);

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
