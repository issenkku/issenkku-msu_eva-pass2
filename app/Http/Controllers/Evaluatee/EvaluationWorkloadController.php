<?php

namespace App\Http\Controllers\Evaluatee;

use App\Http\Controllers\Controller;
use App\Models\Assignments;
use App\Models\QuantityScore;
use App\Models\QuantitySubCriteria;
use App\Models\Reports;
use App\Models\Subject;
use App\Models\WorkloadForm;
use App\Rules\ActiveQuantitySubCriteria;
use App\Services\PreviousWorkloadImportService;
use App\Support\EvaluateeWorkloadLiveData;
use App\Support\EvaluateeWorkloadModalData;
use Illuminate\Http\Request;

class EvaluationWorkloadController extends Controller
{
    private array $editableStatuses = ['Draft', 'Assigned'];

    public function index(Request $request, PreviousWorkloadImportService $previousWorkloadImporter)
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
        $importablePreviousReports = collect();

        if ($reportId) {
            $report = Reports::with([
                'reportData.criteriaVersion.quantityMainCriterias.quantitySubCriterias' => fn ($query) => $query
                    ->active(),
            ])->find($reportId);

            $readonly = ! $this->canEditReport($report);

            if ($report && ! $readonly && $request->user()) {
                $importablePreviousReports = $previousWorkloadImporter
                    ->candidatePreviousReports($report, (int) $request->user()->id);
            }

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

            $workloadForms = WorkloadForm::with(['fields', 'items', 'subCriteriaItem.group'])
                ->where('quantity_sub_criteria_id', $quantitySubCriteriaId)
                ->get();
        }

        $liveData = [
            'workloadEntriesByFormId' => collect(),
            'evidenceLinksByEntryId' => collect(),
            'workloadTotalScore' => 0.0,
            'workloadView' => [
                'requires_subject' => false,
                'groups' => collect(),
                'total_display' => '0.00',
            ],
        ];
        if ($reportId && $quantitySubCriteria) {
            $liveData = EvaluateeWorkloadLiveData::build(
                $quantitySubCriteria,
                $workloadForms,
                (int) $reportId,
            );
        }

        $workloadEntriesByFormId = $liveData['workloadEntriesByFormId'];
        $evidenceLinksByEntryId = $liveData['evidenceLinksByEntryId'];
        $workloadTotalScore = $liveData['workloadTotalScore'];
        $workloadView = $liveData['workloadView'];
        $savedWorkloadScoreC = null;

        if ($reportId && $quantitySubCriteriaId) {
            $savedWorkloadScoreC = QuantityScore::query()
                ->where('report_id', $reportId)
                ->where('quantity_sub_criteria_id', $quantitySubCriteriaId)
                ->value('score_C');
        }

        $workloadModal = EvaluateeWorkloadModalData::build(
            $quantitySubCriteria,
            $workloadForms,
            $subjects
        );

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
            'savedWorkloadScoreC' => $savedWorkloadScoreC,
            'subjects' => $subjects,
            'evidenceLinksByEntryId' => $evidenceLinksByEntryId,
            'workloadView' => $workloadView,
            'workloadModal' => $workloadModal,
            'importablePreviousReports' => $importablePreviousReports,
        ]);
    }

    public function storeWorkloadScore(Request $request)
    {
        $reportInput = $request->validate([
            'report_id' => 'required|integer|exists:reports,id',
        ]);

        $reportId = (int) $reportInput['report_id'];
        $report = Reports::findOrFail($reportId);
        $criteriaVersionId = (int) $report->reportData?->criteria_version_id;
        $validated = $request->validate([
            'quantity_sub_criteria_id' => [
                'required',
                'integer',
                new ActiveQuantitySubCriteria($criteriaVersionId),
            ],
        ]);
        $quantitySubCriteriaId = (int) $validated['quantity_sub_criteria_id'];

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
                return max(0, (float) ($entry->calculated_score ?? 0));
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

    public function importPreviousWorkload(Request $request, int $id, PreviousWorkloadImportService $importer)
    {
        $report = Reports::findOrFail($id);

        if (! $this->canEditReport($report)) {
            abort(403, 'Report is readonly');
        }

        $isAssignedToUser = Assignments::where('report_id', $report->id)
            ->where('evaluatee_id', $request->user()->id)
            ->exists();

        if (! $isAssignedToUser) {
            abort(403, 'Unauthorized evaluatee');
        }

        $validated = $request->validate([
            'source_report_id' => 'nullable|integer|exists:reports,id',
        ]);

        $result = $importer->importForReport(
            $report,
            (int) $request->user()->id,
            isset($validated['source_report_id']) ? (int) $validated['source_report_id'] : null
        );

        if (! $result['source_report_id']) {
            return redirect()
                ->back()
                ->with('info', 'ไม่พบข้อมูลจากรอบก่อนหน้าที่สามารถนำเข้าได้');
        }

        if ($result['copied_entries'] === 0) {
            return redirect()
                ->back()
                ->with('info', 'ข้อมูลจากรอบก่อนหน้ามีอยู่ในรอบนี้แล้ว');
        }

        return redirect()
            ->back()
            ->with('success', "นำเข้าข้อมูลจากรอบก่อนหน้า {$result['copied_entries']} รายการเรียบร้อยแล้ว");
    }

    private function canEditReport(?Reports $report): bool
    {
        return $report && in_array($report->status, $this->editableStatuses, true);
    }
}
