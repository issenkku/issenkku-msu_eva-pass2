<?php

namespace App\Http\Controllers;

use App\Models\Report;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class ReportController extends Controller
{
    // GET /reports
    public function index()
    {
        return response()->json(Report::all());
    }

    // GET /reports/{id}
    public function show($id)
    {
        $report = Report::find($id);
        if (!$report) {
            return response()->json(['message' => 'Report not found'], 404);
        }
        return response()->json($report);
    }

    // POST /reports
    public function store(Request $request)
    {
        $validated = $request->validate([
            'report_data_id' => 'required|integer|exists:report_datas,id',
            'report_code'    => 'required|string|unique:reports,report_code|max:255',
            'status'         => 'required|string|max:255',
        ]);

        $report = Report::create($validated);

        return response()->json($report, 201);
    }

    // PUT /reports/{id}
    public function update(Request $request, $id)
    {
        $report = Report::find($id);
        if (!$report) {
            return response()->json(['message' => 'Report not found'], 404);
        }

        $validated = $request->validate([
            'report_data_id' => 'sometimes|required|integer|exists:report_datas,id',
            'report_code'    => [
                'sometimes',
                'required',
                'string',
                'max:255',
                Rule::unique('reports')->ignore($report->id),
            ],
            'status'         => 'sometimes|required|string|max:255',
        ]);

        $report->update($validated);

        return response()->json($report);
    }

    // DELETE /reports/{id}
    public function destroy($id)
    {
        $report = Report::find($id);
        if (!$report) {
            return response()->json(['message' => 'Report not found'], 404);
        }
        $report->delete();

        return response()->json(['message' => 'Report deleted successfully']);
    }
}
