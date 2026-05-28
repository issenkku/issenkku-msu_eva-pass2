<?php

namespace App\Http\Controllers\Workload;

use App\Http\Controllers\Controller;
use App\Http\Requests\Workload\StoreWorkloadFormRequest;
use App\Http\Requests\Workload\UpdateWorkloadFormRequest;
use App\Models\WorkloadForm;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\Request;

class WorkloadFormController extends Controller
{
    public function index(Request $request)
    {
        $query = WorkloadForm::with(['fields', 'items']);

        $quantitySubCriteriaId = $request->input('quantity_sub_criteria_id', $request->input('quant_sub_criteria_id'));
        $quantitySubCriteriaItemId = $request->input('quantity_sub_criteria_item_id');

        if (! is_null($quantitySubCriteriaId) && $quantitySubCriteriaId !== '') {
            $query->where('quantity_sub_criteria_id', $quantitySubCriteriaId);
        }
        if (! is_null($quantitySubCriteriaItemId) && $quantitySubCriteriaItemId !== '') {
            $query->where('quantity_sub_criteria_item_id', $quantitySubCriteriaItemId);
        }

        return response()->json($query->get());
    }

    public function show($id)
    {
        try {
            return response()->json(WorkloadForm::with(['fields', 'items'])->findOrFail($id));
        } catch (ModelNotFoundException $e) {
            return response()->json(['message' => 'Workload form not found'], 404);
        }
    }

    public function store(StoreWorkloadFormRequest $request)
    {
        $form = WorkloadForm::create($request->validated());

        return response()->json($form, 201);
    }

    public function update(UpdateWorkloadFormRequest $request, $id)
    {
        try {
            $form = WorkloadForm::findOrFail($id);
            $form->update($request->validated());

            return response()->json($form);
        } catch (ModelNotFoundException $e) {
            return response()->json(['message' => 'Workload form not found'], 404);
        }
    }

    public function destroy($id)
    {
        try {
            $form = WorkloadForm::findOrFail($id);
            $form->delete();

            return response()->json(['message' => 'Workload form deleted successfully']);
        } catch (ModelNotFoundException $e) {
            return response()->json(['message' => 'Workload form not found'], 404);
        }
    }
}
