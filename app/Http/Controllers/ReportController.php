<?php

namespace App\Http\Controllers;

use App\Models\Reports;
use App\Models\QuantityScore;
use App\Models\QualityScore;
use App\Models\EvidenceAnswer;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Exception;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Facades\DB;

use App\Http\Resources\ReportResource;
use App\Http\Resources\ReportSummaryResource;
use App\Http\Resources\QuantityScoreResource;
use App\Http\Resources\QualityScoreResource;
use App\Http\Resources\EvidenceAnswerResource;

class ReportController extends Controller
{
    // สถานะที่อนุญาตให้แก้ไขข้อมูล
    protected $allowedEditStatuses = ['ASSIGNED', 'DRAFT'];

    // GET /reports
    public function index()
    {
        $reports = Reports::all();
        return ReportSummaryResource::collection($reports);
    }

    public function show($id)
    {
        try {
            $report = Reports::with(['assignments','quantityScores', 'qualityScores', 'evidenceAnswers'])->findOrFail($id);
            return new ReportResource($report);
        } catch (ModelNotFoundException $e) {
            return response()->json(['message' => 'Report not found'], 404);
        }
    }

    public function store(Request $request)
    {
        try {
            if ($request->has('status') && !in_array($request->status, ['ASSIGNED', 'DRAFT', 'PENDING', 'COMPLETED'])) {
                return response()->json([
                    'message' => 'Invalid status value',
                    'errors' => [
                        'status' => ['Status must be one of: ASSIGNED, DRAFT, PENDING, COMPLETED']
                    ]
                ], 422);
            }

            $validated = $request->validate([
                'report_data_id' => 'required|integer|exists:report_datas,id',
                'status'         => 'required|string|in:ASSIGNED,DRAFT,PENDING,COMPLETED',
            ]);

            $report = Reports::create($validated);
            return new ReportResource($report);
        } catch (ValidationException $e) {
            return response()->json([
                'message' => 'Validation failed',
                'errors' => $e->errors()
            ], 422);
        }
    }

    // PUT /reports/{id}
    public function update(Request $request, $id)
    {
        try {
            $report = Reports::findOrFail($id);
            if ($request->has('status') && !in_array($request->status, ['Assigned', 'Draft', 'Pending', 'Completed'])) {
                return response()->json([
                    'message' => 'Invalid status value',
                    'errors' => [
                        'status' => ['Status must be one of: ASSIGNED, DRAFT, PENDING, COMPLETED']
                    ]
                ], 422);
            }

            $validated = $request->validate([
                'report_data_id' => 'sometimes|required|integer|exists:report_datas,id',
                'status'         => 'sometimes|required|string|in:ASSIGNED,DRAFT,PENDING,COMPLETED',
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

    // DELETE /reports/{id}
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
        if (!in_array($report->status, $this->allowedEditStatuses)) {
            return response()->json([
                'message' => "Cannot {$action}. Report must be in ASSIGNED or DRAFT status."
            ], 403);
        }

        return null; // ถ้าผ่านการตรวจสอบ
    }

    // POST /reports/{reportId}/quantity-scores
    public function addQuantityScores(Request $request, $reportId)
    {
        try {
            $report = Reports::findOrFail($reportId);

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
                $subCriteria = \App\Models\QuantitySubCriteria::find($item['quantity_sub_criteria_id']);
                $scoreC = $item['score_C'] ?? null;

                // Calculate score_D using the formula
                $scoreD = null;
                if ($scoreC !== null && $subCriteria && $subCriteria->score_b != 0) {
                    $scoreD = ($subCriteria->score_a * $scoreC) / $subCriteria->score_b;
                }

                $quantityScore = QuantityScore::create([
                    'quantity_sub_criteria_id' => $subCriteria->id,
                    'report_id' => $reportId,
                    'score_C' => $scoreC,
                    'score_D' => $scoreD,
                ]);

                $created[] = $quantityScore;
            }
            return QuantityScoreResource::collection(collect($created));
        } catch (ModelNotFoundException $e) {
            return response()->json(['message' => 'Report not found'], 404);
        }
    }

    public function updateQuantityScores(Request $request, $reportId)
    {
        try {
            $report = Reports::findOrFail($reportId);

            // ตรวจสอบสถานะ report
            $statusCheck = $this->checkReportEditableStatus($report, 'update quantity scores');
            if ($statusCheck) return $statusCheck;

            $validated = $request->validate([
                'quantity_list' => 'required|array',
                'quantity_list.*.quantity_sub_criteria_id' => 'required|integer|exists:quantity_sub_criterias,id',
                'quantity_list.*.score_C' => 'nullable|numeric',
                'quantity_list.*.score_D' => 'nullable|numeric',
            ]);

            $updated = [];
            foreach ($validated['quantity_list'] as $item) {
                // อัปเดตโดยใช้ where clause ที่ระบุทั้งสองคอลัมน์ของ composite key
                $result = DB::table('quantity_scores')
                    ->where('quantity_sub_criteria_id', $item['quantity_sub_criteria_id'])
                    ->where('report_id', $reportId)
                    ->update([
                        'score_C' => $item['score_C'],
                        'score_D' => $item['score_D'],
                        'updated_at' => now()
                    ]);

                if ($result) {
                    // ดึงข้อมูลที่อัปเดตแล้ว
                    $score = QuantityScore::where('quantity_sub_criteria_id', $item['quantity_sub_criteria_id'])
                        ->where('report_id', $reportId)
                        ->first();
                    if ($score) {
                        $updated[] = $score;
                    }
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
            $report = Reports::findOrFail($reportId);

            // ตรวจสอบสถานะ report
            $statusCheck = $this->checkReportEditableStatus($report, 'add Quality score');
            if ($statusCheck) return $statusCheck;

            $validated = $request->validate([
                'quality_list' => 'required|array',
                'quality_list.*.quality_sub_criteria_id' => 'required|integer|exists:quality_sub_criterias,id',
                'quality_list.*.score' => 'nullable|numeric',
            ]);

            $created = [];
            foreach ($validated['quality_list'] as $item) {
                $quality_score = QualityScore::create([
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
            $report = Reports::findOrFail($reportId);

            // ตรวจสอบสถานะ report
            $statusCheck = $this->checkReportEditableStatus($report, 'update quality scores');
            if ($statusCheck) return $statusCheck;

            $validated = $request->validate([
                'quality_list' => 'required|array',
                'quality_list.*.quality_sub_criteria_id' => 'required|integer|exists:quality_sub_criterias,id',
                'quality_list.*.score' => 'nullable|numeric',
            ]);

            $updated = [];

            foreach ($validated['quality_list'] as $item) {
                // ตรวจสอบว่า quality score นี้เป็นของ report นี้หรือไม่ก่อนอัปเดต
                $result = DB::table('quality_scores')
                    ->where('quality_sub_criteria_id', $item['quality_sub_criteria_id'])
                    ->where('report_id', $reportId)
                    ->update([
                        'score' => $item['score'],
                        'updated_at' => now()
                    ]);

                if ($result) {
                    // ดึงข้อมูลที่อัปเดตแล้ว
                    $score = QualityScore::where('quality_sub_criteria_id', $item['quality_sub_criteria_id'])
                        ->where('report_id', $reportId)
                        ->first();
                    if ($score) {
                        $updated[] = $score;
                    }
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

    // POST /reports/{reportId}/evidence-answers
    public function addEvidenceAnswers(Request $request, $reportId)
    {
        try {
            $report = Reports::findOrFail($reportId);

            // ตรวจสอบสถานะ report
            $statusCheck = $this->checkReportEditableStatus($report, 'add Evidence answers');
            if ($statusCheck) return $statusCheck;

            $validated = $request->validate([
                'evidence_list' => 'required|array',
                'evidence_list.*.evaluation_list_id' => 'required|integer|exists:evaluation_lists,id',
                'evidence_list.*.link' => 'nullable|string',
            ]);

            $created = [];
            foreach ($validated['evidence_list'] as $item) {
                $evidence_ans = EvidenceAnswer::create([
                    'evaluation_list_id' => $item['evaluation_list_id'],
                    'report_id' => $reportId,
                    'link' => $item['link'] ?? null,
                ]);
                $created[] = $evidence_ans;
            }

            return EvidenceAnswerResource::collection(collect($created));
        } catch (ModelNotFoundException $e) {
            return response()->json(['message' => 'Report not found'], 404);
        }
    }

    public function updateEvidenceAnswers(Request $request, $reportId)
    {
        try {
            $report = Reports::findOrFail($reportId);

            // ตรวจสอบสถานะ report
            $statusCheck = $this->checkReportEditableStatus($report, 'update evidence answers');
            if ($statusCheck) return $statusCheck;

            $validated = $request->validate([
                'evidence_list' => 'required|array',
                'evidence_list.*.evaluation_list_id' => 'required|integer|exists:evaluation_lists,id',
                'evidence_list.*.link_old' => 'nullable|string',
                'evidence_list.*.link_new' => 'nullable|string',
            ]);

            $updated = [];

            foreach ($validated['evidence_list'] as $item) {
                // ตรวจสอบว่า quality score นี้เป็นของ report นี้หรือไม่ก่อนอัปเดต
                $result = DB::table('evidence_answers')
                    ->where('evaluation_list_id', $item['evaluation_list_id'])
                    ->where('report_id', $reportId)
                    ->where('link', $item['link_old'])
                    ->update([
                        'link' => $item['link_new'],
                        'updated_at' => now()
                    ]);

                if ($result) {
                    // ดึงข้อมูลที่อัปเดตแล้ว
                    $evidenceAnswer = EvidenceAnswer::where('evaluation_list_id', $item['evaluation_list_id'])
                        ->where('report_id', $reportId)
                        ->first();
                    if ($evidenceAnswer) {
                        $updated[] = $evidenceAnswer;
                    }
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