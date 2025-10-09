<?php

namespace App\Http\Controllers;

use App\Models\Report;
use App\Models\QuantityScore;
use App\Models\QualityScore;
use App\Models\EvidenceAnswer;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Exception;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

use App\Http\Resources\ReportResource;
use App\Http\Resources\ReportSummaryResource;
use App\Http\Resources\QuantityScoreResource;
use App\Http\Resources\QualityScoreResource;
use App\Http\Resources\EvidenceAnswerResource;

class ReportController extends Controller
{
    // สถานะที่อนุญาตให้แก้ไขข้อมูล
    protected $allowedEditStatuses = ['Assigned', 'Draft'];

    public function index()
    {
        $reports = Report::all();
        return ReportSummaryResource::collection($reports);
    }

    public function show($id)
    {
        try {
            $report = Reports::with(['assignments', 'quantityScores', 'qualityScores', 'evidenceAnswers'])->findOrFail($id);

            return new ReportResource($report);
        } catch (ModelNotFoundException $e) {
            return response()->json(['message' => 'Report not found'], 404);
        }
    }

    public function store(Request $request)
    {
        try {
            if ($request->has('status') && !in_array($request->status, ['Assigned', 'Draft', 'Pending', 'Completed'])) {
                return response()->json([
                    'message' => 'Invalid status value',
                    'errors' => [
                        'status' => ['Status must be one of: Assigned, Draft, Pending, Completed']
                    ]
                ], 422);
            }

            $validated = $request->validate([
                'report_data_id' => 'required|integer|exists:report_datas,id',
                'status' => 'required|string|in:ASSIGNED,DRAFT,PENDING,COMPLETED',
            ]);

            $report = Report::create($validated);
            return new ReportResource($report);
        } catch (ValidationException $e) {
            return response()->json([
                'message' => 'Validation failed',
                'errors' => $e->errors()
            ], 422);
        }
    }

    public function update(Request $request, $id)
    {
        try {
            $report = Report::findOrFail($id);
            if ($request->has('status') && !in_array($request->status, ['Assigned', 'Draft', 'Pending', 'Completed'])) {
                return response()->json([
                    'message' => 'Invalid status value',
                    'errors' => [
                        'status' => ['Status must be one of: Assigned, Draft, Pending, Completed']
                    ]
                ], 422);
            }

            $validated = $request->validate([
                'report_data_id' => 'sometimes|required|integer|exists:report_datas,id',
                'status' => 'sometimes|required|string|in:ASSIGNED,DRAFT,PENDING,COMPLETED',
            ]);

            $report->update($validated);

            return new ReportResource($report);
        } catch (ModelNotFoundException $e) {
            return response()->json(['message' => 'Report not found'], 404);
        } catch (ValidationException $e) {
            return response()->json([
                'message' => 'Validation failed',
                'errors' => $e->errors()
            ], 422);
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

    /**
     * ตรวจสอบว่า report อยู่ในสถานะที่สามารถแก้ไขได้หรือไม่
     */
    protected function checkReportEditableStatus(Reports $report, $action)
    {
        if (! in_array($report->status, $this->allowedEditStatuses)) {
            return response()->json([
                'message' => "Cannot {$action}. Report must be in ASSIGNED or DRAFT status.",
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
            $report = Report::findOrFail($reportId);

            // ตรวจสอบสถานะ report
            $statusCheck = $this->checkReportEditableStatus($report, 'add Quantity score');
            if ($statusCheck) return $statusCheck;

            $validated = $request->validate([
                'quantity_list' => 'required|array',
                'quantity_list.*.quantity_sub_criteria_id' => 'required|integer|exists:quantity_sub_criterias,id',
                'quantity_list.*.score_C' => 'nullable|numeric',
            ]);

            $created = [];
            foreach ($validated['quantity_list'] as $item) {
                $quantity_score = QuantityScore::create([
                    'quantity_sub_criteria_id' => $item['quantity_sub_criteria_id'],
                    'report_id' => $reportId,
                    'score_C' => $scoreC,
                    'score_D' => $scoreD,
                ]);
                $created[] = $quantity_score;
            }
            return QuantityScoreResource::collection(collect($created));
        } catch (ModelNotFoundException $e) {
            return response()->json(['message' => 'Report not found'], 404);
        }
    }

    public function updateQuantityScores(Request $request, $reportId)
    {
        try {
            $report = Report::findOrFail($reportId);

            // ตรวจสอบสถานะ report
            $statusCheck = $this->checkReportEditableStatus($report, 'update quantity scores');
            if ($statusCheck) return $statusCheck;

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
                        'updated_at' => now(),
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
                'updated' => count($updated),
                'data' => QuantityScoreResource::collection(collect($updated))
            ]);
        } catch (ModelNotFoundException $e) {
            return response()->json(['message' => 'Report not found'], 404);
        } catch (\Exception $e) {
            return response()->json(['message' => 'Failed to update quantity scores', 'error' => $e->getMessage()], 500);
        }
    }


    // POST /reports/{reportId}/quality-scores
    public function addQualityScores(Request $request, $reportId)
    {
        try {
            $report = Report::findOrFail($reportId);

            // ตรวจสอบสถานะ report
            $statusCheck = $this->checkReportEditableStatus($report, 'add Quality score');
            if ($statusCheck) return $statusCheck;

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
                $created[] = $quality_score;
            }
            return QualityScoreResource::collection(collect($created));
        } catch (ModelNotFoundException $e) {
            return response()->json(['message' => 'Report not found'], 404);
        }
    }

    public function updateQualityScores(Request $request, $reportId)
    {
        try {
            $report = Report::findOrFail($reportId);

            // ตรวจสอบสถานะ report
            $statusCheck = $this->checkReportEditableStatus($report, 'update quality scores');
            if ($statusCheck) return $statusCheck;

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
                        'updated_at' => now(),
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
                'updated' => count($updated),
                'data' => QualityScoreResource::collection(collect($updated))
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
            $report = Report::findOrFail($reportId);

            // ตรวจสอบสถานะ report
            $statusCheck = $this->checkReportEditableStatus($report, 'add Evidence answers');
            if ($statusCheck) return $statusCheck;

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
            $report = Report::findOrFail($reportId);

            // ตรวจสอบสถานะ report
            $statusCheck = $this->checkReportEditableStatus($report, 'update evidence answers');
            if ($statusCheck) return $statusCheck;

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
                        'updated_at' => now(),
                    ]);

                if ($result) {
                    $evidenceAnswer = EvidenceAnswer::where('evaluation_list_id', $item['evaluation_list_id'])
                        ->where('report_id', $reportId)
                        ->first();
                    if ($evidenceAnswer) $updated->push($evidenceAnswer);
                }
            }

            return response()->json([
                'message' => 'Quality scores updated successfully',
                'updated' => count($updated),
                'data' => EvidenceAnswerResource::collection(collect($updated))
            ]);
        } catch (ModelNotFoundException $e) {
            return response()->json(['message' => 'Report not found'], 404);
        } catch (\Exception $e) {
            return response()->json(['message' => 'Failed to update evidence answers', 'error' => $e->getMessage()], 500);
        }
    }
}