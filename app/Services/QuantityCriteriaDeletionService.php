<?php

namespace App\Services;

use App\Exceptions\QuantityCriteriaInUse;
use App\Models\EvaluationList;
use App\Models\QuantityMainCriteria;
use App\Models\QuantityScore;
use App\Models\QuantityScoreHistory;
use App\Models\User;
use App\Models\WorkloadForm;
use App\Support\AuditLog;
use Illuminate\Support\Facades\DB;

class QuantityCriteriaDeletionService
{
    /**
     * @return array{workload_forms: int, quantity_scores: int, quantity_score_histories: int}
     */
    public function dependencyCounts(EvaluationList $evaluationList): array
    {
        $subIds = $evaluationList->quantitySubCriterias()->pluck('id');

        return [
            'workload_forms' => WorkloadForm::whereIn('quantity_sub_criteria_id', $subIds)->count(),
            'quantity_scores' => QuantityScore::whereIn('quantity_sub_criteria_id', $subIds)->count(),
            'quantity_score_histories' => QuantityScoreHistory::whereIn(
                'quantity_sub_criteria_id',
                $subIds,
            )->count(),
        ];
    }

    /**
     * @return array{main_criteria: int, sub_criteria: int}
     */
    public function delete(EvaluationList $evaluationList, User $actor): array
    {
        return DB::transaction(function () use ($evaluationList, $actor): array {
            $subCriteria = $evaluationList->quantitySubCriterias()
                ->lockForUpdate()
                ->get(['id', 'quantity_main_criteria_id']);
            $dependencies = $this->dependencyCounts($evaluationList);

            if (array_sum($dependencies) > 0) {
                throw new QuantityCriteriaInUse($dependencies);
            }

            $mainIds = $subCriteria
                ->pluck('quantity_main_criteria_id')
                ->filter()
                ->unique()
                ->values();
            $deletedSubCount = $evaluationList->quantitySubCriterias()->delete();
            $orphanedMainQuery = QuantityMainCriteria::query()
                ->whereIn('id', $mainIds)
                ->whereDoesntHave('quantitySubCriterias');
            $deletedMainCount = $orphanedMainQuery->count();
            $orphanedMainQuery->delete();

            $deleted = [
                'main_criteria' => $deletedMainCount,
                'sub_criteria' => $deletedSubCount,
            ];

            AuditLog::record(
                'report_structure',
                'ลบข้อมูลเกณฑ์ปริมาณทั้งหมด',
                [
                    'evaluation_list_id' => $evaluationList->id,
                    'deleted_main_criteria' => $deletedMainCount,
                    'deleted_sub_criteria' => $deletedSubCount,
                ],
                $evaluationList,
                $actor,
            );

            return $deleted;
        });
    }
}
