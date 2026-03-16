<?php

namespace App\Http\Controllers\Evaluatee;

use App\Http\Controllers\Controller;
use App\Models\EvidenceAnswer;
use App\Models\QuantityScore;
use App\Models\QuantitySubCriteria;
use App\Models\Reports;
use App\Models\Subject;
use App\Models\WorkloadEntry;
use App\Models\WorkloadForm;
use Illuminate\Http\Request;

class EvaluationWorkloadController extends Controller
{
    private array $editableStatuses = ['Draft', 'Assigned'];

    public function index(Request $request)
    {
        $reportId = $request->query('report_id');
        $quantitySubCriteriaId = $request->query('quantity_sub_criteria_id');

        $report = null;
        $readonly = true;
        $quantitySubCriterias = collect();
        $quantitySubCriteria = null;
        $workloadForms = collect();
        $subjects = Subject::where('is_active', true)
            ->orderBy('code')
            ->get();

        if ($reportId) {
            $report = Reports::with([
                'reportData.criteriaVersion.quantityMainCriterias.quantitySubCriterias',
            ])->find($reportId);

            $readonly = ! $this->canEditReport($report);

            if ($report && $report->reportData && $report->reportData->criteriaVersion) {
                $quantitySubCriterias = $report->reportData
                    ->criteriaVersion
                    ->quantityMainCriterias
                    ->flatMap(function ($main) {
                        return $main->quantitySubCriterias ?? collect();
                    });
            }
        }

        if ($readonly && $request->query('readonly') != 1 && $reportId) {
            return redirect()->route('evaluatee.workload', [
                'report_id' => $reportId,
                'quantity_sub_criteria_id' => $quantitySubCriteriaId,
                'readonly' => 1,
            ]);
        }

        if ($quantitySubCriteriaId) {
            $quantitySubCriteria = QuantitySubCriteria::with(['groups.items'])
                ->find($quantitySubCriteriaId);

            $workloadForms = WorkloadForm::with(['fields', 'items', 'subCriteriaItem'])
                ->where('quantity_sub_criteria_id', $quantitySubCriteriaId)
                ->get();
        }

        $workloadEntriesByFormId = collect();
        $workloadTotalScore = 0.0;
        if ($reportId && $workloadForms->isNotEmpty()) {
            $formIds = $workloadForms->pluck('id')->filter()->unique()->values();
            if ($formIds->isNotEmpty()) {
                $workloadEntriesByFormId = WorkloadEntry::with('subject')
                    ->where('report_id', $reportId)
                    ->whereIn('workload_form_id', $formIds)
                    ->orderByDesc('created_at')
                    ->get()
                    ->groupBy('workload_form_id');

                $workloadTotalScore = $workloadEntriesByFormId
                    ->flatten(1)
                    ->sum(function ($entry) {
                        return (float) ($entry->calculated_score ?? 0);
                    });
            }
        }

        $evidenceLinksByEntryId = collect();
        if ($reportId && $quantitySubCriteria && $quantitySubCriteria->evaluation_list_id) {
            $evidenceLinksByEntryId = EvidenceAnswer::where('report_id', $reportId)
                ->where('evaluation_list_id', $quantitySubCriteria->evaluation_list_id)
                ->orderByDesc('created_at')
                ->get()
                ->groupBy('workload_entry_id')
                ->map(fn($answers) => $answers->pluck('link')->filter()->values());
        }

        return view('evaluatee.evaluation-workload', [
            'reportId' => $reportId,
            'report' => $report,
            'readonly' => $readonly,
            'quantitySubCriteriaId' => $quantitySubCriteriaId,
            'quantitySubCriterias' => $quantitySubCriterias,
            'quantitySubCriteria' => $quantitySubCriteria,
            'workloadForms' => $workloadForms,
            'workloadEntriesByFormId' => $workloadEntriesByFormId,
            'workloadTotalScore' => $workloadTotalScore,
            'subjects' => $subjects,
            'evidenceLinksByEntryId' => $evidenceLinksByEntryId,
        ]);
    }

    public function storeWorkloadScore(Request $request)
    {
        $validated = $request->validate([
            'report_id' => 'required|integer|exists:reports,id',
            'quantity_sub_criteria_id' => 'required|integer|exists:quantity_sub_criterias,id',
        ]);

        $reportId = (int) $validated['report_id'];
        $quantitySubCriteriaId = (int) $validated['quantity_sub_criteria_id'];

        $report = Reports::find($reportId);
        if (! $report) {
            return redirect()->back()->with('error', 'ไม่พบรายงานที่ต้องการบันทึก');
        }

        if (! $this->canEditReport($report)) {
            return redirect()->back()->with('error', 'รายงานนี้อยู่ในโหมดอ่านอย่างเดียว ไม่สามารถบันทึกด้านปริมาณได้');
        }

        $formIds = WorkloadForm::where('quantity_sub_criteria_id', $quantitySubCriteriaId)
            ->pluck('id')
            ->filter()
            ->unique()
            ->values();

        $scoreC = 0.0;
        if ($formIds->isNotEmpty()) {
            $entries = WorkloadEntry::where('report_id', $reportId)
                ->whereIn('workload_form_id', $formIds)
                ->get();
            $scoreC = $entries->sum(function ($entry) {
                return (float) ($entry->calculated_score ?? 0);
            });
        }

        $subCriteria = QuantitySubCriteria::find($quantitySubCriteriaId);
        $scoreD = null;
        if ($subCriteria && (float) $subCriteria->score_b !== 0.0) {
            $scoreD = ($subCriteria->score_a * $scoreC) / $subCriteria->score_b;
        }

        QuantityScore::updateOrCreate(
            [
                'quantity_sub_criteria_id' => $quantitySubCriteriaId,
                'report_id' => $reportId,
            ],
            [
                'score_C' => $scoreC,
                'score_D' => $scoreD,
            ]
        );

        return redirect()
            ->back()
            ->with('success', 'บันทึกคะแนนภาระงานรวมเรียบร้อยแล้ว');
    }

    private function canEditReport(?Reports $report): bool
    {
        return $report && in_array($report->status, $this->editableStatuses, true);
    }
}
