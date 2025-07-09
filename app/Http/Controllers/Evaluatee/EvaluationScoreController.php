<?php

namespace App\Http\Controllers\Evaluatee;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Report;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use App\Models\QuantityScore;
use App\Models\QualityScore;
use App\Models\EvidenceAnswer;
use Exception;
use Illuminate\Support\Facades\DB;

class EvaluationScoreController extends Controller
{
    protected $allowedEditStatuses = ['Assigned', 'Draft'];
    
    protected function checkReportEditableStatus(Report $report, $action)
    {
        if (!in_array($report->status, $this->allowedEditStatuses)) {
            return response()->json([
                'message' => "Cannot {$action}. Report must be in Assigned or Draft status."
            ], 403);
        }

        return null; // ถ้าผ่านการตรวจสอบ
    }

    public function storeEvaluationScores(Request $request, $reportId)
    {
        try {
            $reportId = is_array($reportId) ? $reportId[0] : (int) $reportId;
            $report = Report::findOrFail($reportId);

            $statusCheck = $this->checkReportEditableStatus($report, 'process evaluation scores');
            if ($statusCheck) return $statusCheck;

            $request->merge([
                'evidence_list' => collect($request->input('evidence_list'))
                    ->filter(fn($item) => !empty($item['link'])) // Only keep filled links
                    ->values()
                    ->all(),
            ]);

            $validated = $request->validate([
                'quantity_list' => 'required|array',
                'quantity_list.*.quantity_sub_criteria_id' => 'required|integer|exists:quantity_sub_criterias,id',
                'quantity_list.*.score_C' => 'nullable|numeric',
                
                'quality_list' => 'required|array',
                'quality_list.*.quality_sub_criteria_id' => 'required|integer|exists:quality_sub_criterias,id',
                'quality_list.*.score' => 'nullable|numeric',
                
                'evidence_list' => 'required|array',
                'evidence_list.*.evaluation_list_id' => 'required|integer|exists:evaluation_lists,id',
                'evidence_list.*.link' => 'nullable|string',

                'status' => 'required|string|in:Draft,Pending,Assigned,Submitted',
            ]);

            DB::beginTransaction();

            // Delete existing records for this report
            QuantityScore::where('report_id', $reportId)->delete();
            QualityScore::where('report_id', $reportId)->delete();
            EvidenceAnswer::where('report_id', $reportId)->delete();

            // Create fresh records
            foreach ($validated['quantity_list'] as $item) {
                $subCriteriaId = is_array($item['quantity_sub_criteria_id']) 
                    ? $item['quantity_sub_criteria_id'][0] 
                    : (int) $item['quantity_sub_criteria_id'];
                    
                $subCriteria = \App\Models\QuantitySubCriteria::find($subCriteriaId);
                $scoreC = $item['score_C'] ?? null;

                if ($scoreC === null) {
                    continue; // skip this item
                }

                $scoreD = null;
                if ($scoreC !== null && $subCriteria && $subCriteria->score_b != 0) {
                    $scoreD = ($subCriteria->score_a * $scoreC) / $subCriteria->score_b;
                }

                QuantityScore::create([
                    'quantity_sub_criteria_id' => $subCriteriaId,
                    'report_id' => $reportId,
                    'score_C' => $scoreC,
                    'score_D' => $scoreD,
                ]);
            }

            foreach ($validated['quality_list'] as $item) {
                $score = $item['score'] ?? null;

                if ($score === null) {
                    continue; // skip this item
                }
                $subCriteriaId = is_array($item['quality_sub_criteria_id']) 
                    ? $item['quality_sub_criteria_id'][0] 
                    : (int) $item['quality_sub_criteria_id'];

                QualityScore::create([
                    'quality_sub_criteria_id' => $subCriteriaId,
                    'report_id' => $reportId,
                    'score' => $item['score'] ?? null,
                ]);
            }

            foreach ($validated['evidence_list'] as $item) {
                $link = trim($item['link'] ?? '');

                if ($link === '') {
                    continue; // skip this item
                }

                $evaluationListId = is_array($item['evaluation_list_id']) 
                    ? $item['evaluation_list_id'][0] 
                    : (int) $item['evaluation_list_id'];

                EvidenceAnswer::create([
                    'evaluation_list_id' => $evaluationListId,
                    'report_id' => $reportId,
                    'link' => $item['link'] ?? null,
                ]);
            }

            $status = $validated['status'];
            $report->status = $status;
            $report->save();

            DB::commit();

            $message = $status === 'Draft' ? 'บันทึกข้อมูลเรียบร้อยแล้ว' : 'ส่งรายงานเรียบร้อยแล้ว';
            return redirect()->route('dashboard')->with('success', $message);

        } catch (Exception $e) {
            DB::rollback();
            return response()->json(['message' => 'Error processing evaluation scores', 'error' => $e->getMessage()], 500);
        }
    }
}
