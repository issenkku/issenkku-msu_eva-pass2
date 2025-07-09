<?php

namespace App\Http\Controllers;

use App\Models\{Report, QuantityScore, QualityScore, EvidenceAnswer, Reports};
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Facades\DB;
use App\Http\Resources\{
    ReportResource,
    ReportSummaryResource,
    QuantityScoreResource,
    QualityScoreResource,
    EvidenceAnswerResource
};

class ReportController extends Controller
{
    protected $allowedEditStatuses = ['Assigned', 'Draft'];

    public function index()
    {
        return ReportSummaryResource::collection(Reports::all());
    }

    public function show($id)
    {
        try {
            $report = Reports::with(['quantityScores', 'qualityScores', 'evidenceAnswers'])->findOrFail($id);
            return new ReportResource($report);
        } catch (ModelNotFoundException $e) {
            return response()->json(['message' => 'Report not found'], 404);
        }
    }

    public function store(Request $request)
    {
        try {
            $this->validateStatus($request);

            $validated = $request->validate([
                'report_data_id' => 'required|integer|exists:report_datas,id',
                'status'         => 'required|string|in:Assigned,Draft,Pending,Completed',
            ]);

            $report = Reports::create($validated);
            return new ReportResource($report);
        } catch (ValidationException $e) {
            return $this->validationErrorResponse($e);
        }
    }

    public function update(Request $request, $id)
    {
        try {
            $report = Reports::findOrFail($id);
            $this->validateStatus($request);

            $validated = $request->validate([
                'report_data_id' => 'sometimes|required|integer|exists:report_datas,id',
                'status'         => 'sometimes|required|string|in:Assigned,Draft,Pending,Completed',
            ]);

            $report->update($validated);
            return new ReportResource($report);
        } catch (ModelNotFoundException $e) {
            return response()->json(['message' => 'Report not found'], 404);
        } catch (ValidationException $e) {
            return $this->validationErrorResponse($e);
        }
    }

    public function destroy($id)
    {
        try {
            $report = Reports::findOrFail($id);
            $report->delete();
            return response()->json(['message' => 'Report deleted successfully']);
        } catch (ModelNotFoundException $e) {
            return response()->json(['message' => 'Report not found'], 404);
        }
    }

    protected function validateStatus(Request $request)
    {
        if ($request->has('status') && !in_array($request->status, ['Assigned', 'Draft', 'Pending', 'Completed'])) {
            abort(response()->json([
                'message' => 'Invalid status value',
                'errors' => [
                    'status' => ['Status must be one of: Assigned, Draft, Pending, Completed']
                ]
            ], 422));
        }
    }

    protected function checkReportEditableStatus(Reports $report, $action)
    {
        if (!in_array($report->status, $this->allowedEditStatuses)) {
            return response()->json([
                'message' => "Cannot {$action}. Report must be in Assigned or Draft status."
            ], 403);
        }

        return null;
    }

    protected function validationErrorResponse(ValidationException $e)
    {
        return response()->json([
            'message' => 'Validation failed',
            'errors' => $e->errors()
        ], 422);
    }

    public function addQuantityScores(Request $request, $reportId)
    {
        try {
            $report = Reports::findOrFail($reportId);
            if ($resp = $this->checkReportEditableStatus($report, 'add Quantity score')) return $resp;

            $validated = $request->validate([
                'quantity_list' => 'required|array',
                'quantity_list.*.quantity_sub_criteria_id' => 'required|integer|exists:quantity_sub_criterias,id',
                'quantity_list.*.score_C' => 'nullable|numeric',
                'quantity_list.*.score_D' => 'nullable|numeric',
            ]);

            $created = collect($validated['quantity_list'])->map(function ($item) use ($reportId) {
                return QuantityScore::create([
                    'quantity_sub_criteria_id' => $item['quantity_sub_criteria_id'],
                    'report_id' => $reportId,
                    'score_C' => $item['score_C'] ?? null,
                    'score_D' => $item['score_D'] ?? null,
                ]);
            });

            return QuantityScoreResource::collection($created);
        } catch (ModelNotFoundException $e) {
            return response()->json(['message' => 'Report not found'], 404);
        }
    }

    public function updateQuantityScores(Request $request, $reportId)
    {
        try {
            $report = Reports::findOrFail($reportId);
            if ($resp = $this->checkReportEditableStatus($report, 'update quantity scores')) return $resp;

            $validated = $request->validate([
                'quantity_list' => 'required|array',
                'quantity_list.*.quantity_sub_criteria_id' => 'required|integer|exists:quantity_sub_criterias,id',
                'quantity_list.*.score_C' => 'nullable|numeric',
                'quantity_list.*.score_D' => 'nullable|numeric',
            ]);

            $updated = collect();

            foreach ($validated['quantity_list'] as $item) {
                $result = DB::table('quantity_scores')
                    ->where('quantity_sub_criteria_id', $item['quantity_sub_criteria_id'])
                    ->where('report_id', $reportId)
                    ->update([
                        'score_C' => $item['score_C'],
                        'score_D' => $item['score_D'],
                        'updated_at' => now()
                    ]);

                if ($result) {
                    $score = QuantityScore::where('quantity_sub_criteria_id', $item['quantity_sub_criteria_id'])
                        ->where('report_id', $reportId)
                        ->first();
                    if ($score) $updated->push($score);
                }
            }

            return response()->json([
                'message' => 'Quantity scores updated successfully',
                'updated' => $updated->count(),
                'data' => QuantityScoreResource::collection($updated)
            ]);
        } catch (ModelNotFoundException $e) {
            return response()->json(['message' => 'Report not found'], 404);
        } catch (\Exception $e) {
            return response()->json(['message' => 'Failed to update quantity scores', 'error' => $e->getMessage()], 500);
        }
    }

    public function addQualityScores(Request $request, $reportId)
    {
        try {
            $report = Reports::findOrFail($reportId);
            if ($resp = $this->checkReportEditableStatus($report, 'add Quality score')) return $resp;

            $validated = $request->validate([
                'quality_list' => 'required|array',
                'quality_list.*.quality_sub_criteria_id' => 'required|integer|exists:quality_sub_criterias,id',
                'quality_list.*.score' => 'nullable|numeric',
            ]);

            $created = collect($validated['quality_list'])->map(function ($item) use ($reportId) {
                return QualityScore::create([
                    'quality_sub_criteria_id' => $item['quality_sub_criteria_id'],
                    'report_id' => $reportId,
                    'score' => $item['score'] ?? null,
                ]);
            });

            return QualityScoreResource::collection($created);
        } catch (ModelNotFoundException $e) {
            return response()->json(['message' => 'Report not found'], 404);
        }
    }

    public function updateQualityScores(Request $request, $reportId)
    {
        try {
            $report = Reports::findOrFail($reportId);
            if ($resp = $this->checkReportEditableStatus($report, 'update quality scores')) return $resp;

            $validated = $request->validate([
                'quality_list' => 'required|array',
                'quality_list.*.quality_sub_criteria_id' => 'required|integer|exists:quality_sub_criterias,id',
                'quality_list.*.score' => 'nullable|numeric',
            ]);

            $updated = collect();

            foreach ($validated['quality_list'] as $item) {
                $result = DB::table('quality_scores')
                    ->where('quality_sub_criteria_id', $item['quality_sub_criteria_id'])
                    ->where('report_id', $reportId)
                    ->update([
                        'score' => $item['score'],
                        'updated_at' => now()
                    ]);

                if ($result) {
                    $score = QualityScore::where('quality_sub_criteria_id', $item['quality_sub_criteria_id'])
                        ->where('report_id', $reportId)
                        ->first();
                    if ($score) $updated->push($score);
                }
            }

            return response()->json([
                'message' => 'Quality scores updated successfully',
                'updated' => $updated->count(),
                'data' => QualityScoreResource::collection($updated)
            ]);
        } catch (ModelNotFoundException $e) {
            return response()->json(['message' => 'Report not found'], 404);
        } catch (\Exception $e) {
            return response()->json(['message' => 'Failed to update quality scores', 'error' => $e->getMessage()], 500);
        }
    }

    public function addEvidenceAnswers(Request $request, $reportId)
    {
        try {
            $report = Reports::findOrFail($reportId);
            if ($resp = $this->checkReportEditableStatus($report, 'add Evidence answers')) return $resp;

            $validated = $request->validate([
                'evidence_list' => 'required|array',
                'evidence_list.*.evaluation_list_id' => 'required|integer|exists:evaluation_lists,id',
                'evidence_list.*.link' => 'nullable|string',
            ]);

            $created = collect($validated['evidence_list'])->map(function ($item) use ($reportId) {
                return EvidenceAnswer::create([
                    'evaluation_list_id' => $item['evaluation_list_id'],
                    'report_id' => $reportId,
                    'link' => $item['link'] ?? null,
                ]);
            });

            return EvidenceAnswerResource::collection($created);
        } catch (ModelNotFoundException $e) {
            return response()->json(['message' => 'Report not found'], 404);
        }
    }

    public function updateEvidenceAnswers(Request $request, $reportId)
    {
        try {
            $report = Reports::findOrFail($reportId);
            if ($resp = $this->checkReportEditableStatus($report, 'update evidence answers')) return $resp;

            $validated = $request->validate([
                'evidence_list' => 'required|array',
                'evidence_list.*.evaluation_list_id' => 'required|integer|exists:evaluation_lists,id',
                'evidence_list.*.link_old' => 'nullable|string',
                'evidence_list.*.link_new' => 'nullable|string',
            ]);

            $updated = collect();

            foreach ($validated['evidence_list'] as $item) {
                $result = DB::table('evidence_answers')
                    ->where('evaluation_list_id', $item['evaluation_list_id'])
                    ->where('report_id', $reportId)
                    ->where('link', $item['link_old'])
                    ->update([
                        'link' => $item['link_new'],
                        'updated_at' => now()
                    ]);

                if ($result) {
                    $evidenceAnswer = EvidenceAnswer::where('evaluation_list_id', $item['evaluation_list_id'])
                        ->where('report_id', $reportId)
                        ->first();
                    if ($evidenceAnswer) $updated->push($evidenceAnswer);
                }
            }

            return response()->json([
                'message' => 'Evidence answers updated successfully',
                'updated' => $updated->count(),
                'data' => EvidenceAnswerResource::collection($updated)
            ]);
        } catch (ModelNotFoundException $e) {
            return response()->json(['message' => 'Report not found'], 404);
        } catch (\Exception $e) {
            return response()->json(['message' => 'Failed to update evidence answers', 'error' => $e->getMessage()], 500);
        }
    }
}