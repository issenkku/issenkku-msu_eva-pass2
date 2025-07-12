<?php

namespace App\Http\Controllers\Evaluatee;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Reports;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use App\Models\QuantityScore;
use App\Models\QualityScore;
use App\Models\EvidenceAnswer;
use Exception;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;

class EvaluationScoreController extends Controller
{
    protected $allowedEditStatuses = ['Assigned', 'Draft'];
    
    protected function checkReportEditableStatus(Reports $report, $action)
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
            $report = Reports::findOrFail($reportId);

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
            // $report->save();
            if($report->save() && $status === 'Pending') {
                $this->sendEvaluationCompletedMail($reportId);
            }

            DB::commit();

            $message = $status === 'Draft' ? 'บันทึกข้อมูลเรียบร้อยแล้ว' : 'ส่งรายงานเรียบร้อยแล้ว';

            return redirect('/evaluatee-dashboard')->with('success', $message);

        } catch (Exception $e) {
            DB::rollback();
            return response()->json(['message' => 'Error processing evaluation scores', 'error' => $e->getMessage()], 500);
        }
    }

    // อีเมลแจ้งเตือนเมื่อส่งแบบประเมิน
    private function sendEvaluationCompletedMail($reportId)
    {
        $report = \App\Models\Reports::with(['reportData', 'reportData.criteriaVersion'])->find($reportId);
        if (!$report) return;

        // สมมติว่าต้องการแจ้งเตือน evaluator (ผู้ประเมิน)
        $assignment = \App\Models\Assignments::where('report_id', $reportId)->first();
        if (!$assignment) return;
        $user = \App\Models\User::find($assignment->evaluator);
        $evaluatee = \App\Models\User::find($assignment->evaluatee);
        if (!$user || !$user->email) return;

        $mailData = [
            'name' => $user->name,
            'report_title' => optional($report->reportData)->report_title,
            'version_name' => optional(optional($report->reportData)->criteriaVersion)->version_name,
            'status' => $report->status,
            'evaluatee_name' => $evaluatee->name,
        ];

        \Mail::send('emails.evalautee_Pending', $mailData, function($message) use ($user) {
            $message->to($user->email, $user->name)
                ->subject('แจ้งเตือน: มีผู้ทำการประเมินส่งแบบประเมินให้คุณตรวจสอบ');
        });
    }
}
