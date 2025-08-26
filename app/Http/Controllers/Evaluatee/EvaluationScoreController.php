<?php

namespace App\Http\Controllers\Evaluatee;

use App\Http\Controllers\Controller;
use App\Models\EvidenceAnswer;
use App\Models\QualityScore;
use App\Models\QuantityScore;
use App\Models\Reports;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class EvaluationScoreController extends Controller
{
    protected $allowedEditStatuses = ['Assigned', 'Draft'];

    protected function checkReportEditableStatus(Reports $report, $action)
    {
        if (! in_array($report->status, $this->allowedEditStatuses)) {
            return response()->json([
                'message' => "Cannot {$action}. Report must be in Assigned or Draft status.",
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
            if ($statusCheck) {
                return $statusCheck;
            }

            // --- Flatten evidence_list to array of {evaluation_list_id, link} ---
            $evidenceInput = $request->input('evidence_list', []);
            $evidenceFlat = [];
            foreach ($evidenceInput as $evalListId => $item) {
                if (isset($item['links']) && is_array($item['links'])) {
                    foreach ($item['links'] as $link) {
                        $link = trim($link);
                        if ($link !== '') {
                            $evidenceFlat[] = [
                                'evaluation_list_id' => $evalListId,
                                'link' => $link,
                            ];
                        }
                    }
                }
            }
            $request->merge(['evidence_list_flat' => $evidenceFlat]);
            // ------------------------------------------------------------

            $validated = $request->validate([
                'quantity_list' => 'nullable|array',
                'quantity_list.*.quantity_sub_criteria_id' => 'nullable|integer|exists:quantity_sub_criterias,id',
                'quantity_list.*.score_C' => 'nullable|numeric',
                'quantity_list.*.description' => 'nullable|string|max:1000',

                'quality_list' => 'nullable|array',
                'quality_list.*.quality_sub_criteria_id' => 'nullable|integer|exists:quality_sub_criterias,id',
                'quality_list.*.score' => 'nullable|numeric',

                'evidence_list_flat' => 'nullable|array',
                'evidence_list_flat.*.evaluation_list_id' => 'required|integer|exists:evaluation_lists,id',
                'evidence_list_flat.*.link' => 'required|string',

                'status' => 'required|string|in:Draft,Pending,Assigned,Submitted',
            ]);

            DB::beginTransaction();

            // Delete existing records for this report
            QuantityScore::where('report_id', $reportId)->delete();
            QualityScore::where('report_id', $reportId)->delete();
            EvidenceAnswer::where('report_id', $reportId)->delete();

            if (isset($validated['quantity_list'])) {
                foreach ($validated['quantity_list'] as $item) {
                    $subCriteriaId = is_array($item['quantity_sub_criteria_id'])
                        ? $item['quantity_sub_criteria_id'][0]
                        : (int) $item['quantity_sub_criteria_id'];

                    $subCriteria = \App\Models\QuantitySubCriteria::find($subCriteriaId);
                    $scoreC = $item['score_C'] ?? null;
                    $description = $item['description'] ?? null;

                    if ($scoreC === null) {
                        continue;
                    }

                    $scoreD = null;
                    if ($subCriteria && $subCriteria->score_b != 0) {
                        $scoreD = ($subCriteria->score_a * $scoreC) / $subCriteria->score_b;
                    }

                    QuantityScore::create([
                        'quantity_sub_criteria_id' => $subCriteriaId,
                        'report_id' => $reportId,
                        'score_C' => $scoreC,
                        'score_D' => $scoreD,
                        'description' => $description,
                    ]);
                }
            }

            // ✅ Quality loop with check
            if (isset($validated['quality_list'])) {
                foreach ($validated['quality_list'] as $item) {
                    $score = $item['score'] ?? null;
                    if ($score === null) {
                        continue;
                    }

                    $subCriteriaId = is_array($item['quality_sub_criteria_id'])
                        ? $item['quality_sub_criteria_id'][0]
                        : (int) $item['quality_sub_criteria_id'];

                    QualityScore::create([
                        'quality_sub_criteria_id' => $subCriteriaId,
                        'report_id' => $reportId,
                        'score' => $score,
                    ]);
                }
            }

            // Save all evidence links
            foreach ($validated['evidence_list_flat'] ?? [] as $item) {
                EvidenceAnswer::create([
                    'evaluation_list_id' => $item['evaluation_list_id'],
                    'report_id' => $reportId,
                    'link' => $item['link'],
                ]);
            }

            $status = $validated['status'];
            $report->status = $status;
            if ($report->save() && $status === 'Pending') {
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
        if (! $report) {
            return;
        }

        // สมมติว่าต้องการแจ้งเตือน evaluator (ผู้ประเมิน)
        $assignment = \App\Models\Assignments::where('report_id', $reportId)->first();
        if (! $assignment) {
            return;
        }
        $user = \App\Models\User::find($assignment->evaluator);
        $evaluatee = \App\Models\User::find($assignment->evaluatee);
        if (! $user || ! $user->email) {
            return;
        }

        $mailData = [
            'name' => $user->name,
            'report_title' => optional($report->reportData)->report_title,
            'version_name' => optional(optional($report->reportData)->criteriaVersion)->version_name,
            'status' => $report->status,
            'evaluatee_name' => $evaluatee->name,
        ];

        \Mail::send('emails.evalautee_Pending', $mailData, function ($message) use ($user) {
            $message->to($user->email, $user->name)
                ->subject('แจ้งเตือน: มีผู้ทำการประเมินส่งแบบประเมินให้คุณตรวจสอบ');
        });
    }
}
