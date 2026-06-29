<?php

namespace App\Http\Controllers\Workload;

use App\Http\Controllers\Controller;
use App\Http\Requests\Workload\StoreWorkloadFormFieldRequest;
use App\Http\Requests\Workload\UpdateWorkloadFormFieldRequest;
use App\Models\WorkloadForm;
use App\Models\WorkloadFormField;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Facades\Schema;

class WorkloadFormFieldController extends Controller
{
    public function index($workloadFormId)
    {
        try {
            $form = WorkloadForm::findOrFail($workloadFormId);

            return response()->json($form->fields);
        } catch (ModelNotFoundException $e) {
            return response()->json(['message' => 'Workload form not found'], 404);
        }
    }

    public function store(StoreWorkloadFormFieldRequest $request, $workloadFormId)
    {
        try {
            WorkloadForm::findOrFail($workloadFormId);

            $data = [
                'label' => $request->validated()['label'],
                'variable_name' => $request->validated()['variable_name'],
                'field_type' => $request->validated()['field_type'],
                'workload_form_id' => $workloadFormId,
            ];

            if (Schema::hasColumn('workload_form_fields', 'note')) {
                $data['note'] = $request->validated()['note'] ?? null;
            }

            if (Schema::hasColumn('workload_form_fields', 'default_value')) {
                $data['default_value'] = $request->validated()['default_value'] ?? null;
            }

            $field = WorkloadFormField::create($data);

            return response()->json($field, 201);
        } catch (ModelNotFoundException $e) {
            return response()->json(['message' => 'Workload form not found'], 404);
        }
    }

    public function update(UpdateWorkloadFormFieldRequest $request, $id)
    {
        try {
            $field = WorkloadFormField::findOrFail($id);
            $data = $request->validated();

            if (! Schema::hasColumn('workload_form_fields', 'note')) {
                unset($data['note']);
            }

            if (! Schema::hasColumn('workload_form_fields', 'default_value')) {
                unset($data['default_value']);
            }

            $field->update($data);

            return response()->json($field);
        } catch (ModelNotFoundException $e) {
            return response()->json(['message' => 'Workload form field not found'], 404);
        }
    }

    public function destroy($id)
    {
        try {
            $field = WorkloadFormField::findOrFail($id);
            $field->delete();

            return response()->json(['message' => 'Workload form field deleted successfully']);
        } catch (ModelNotFoundException $e) {
            return response()->json(['message' => 'Workload form field not found'], 404);
        }
    }
}
