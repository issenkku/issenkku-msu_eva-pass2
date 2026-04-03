<?php

namespace App\Support;

class EvaluationScoreSummary
{
    public static function fromCategoryItems(array $categoryItems): array
    {
        $totalQuantityScore = 0.0;
        $totalQualityScore = 0.0;

        foreach ($categoryItems as $category) {
            foreach ($category['evaluation_lists'] ?? [] as $evaluationList) {
                foreach ($evaluationList['quantity_items'] ?? [] as $mainCriteria) {
                    foreach ($mainCriteria['sub_criterias'] ?? [] as $subCriteria) {
                        $totalQuantityScore += (float) ($subCriteria['score_d'] ?? 0);
                    }
                }

                $evaluationListQualityTotal = 0.0;
                foreach ($evaluationList['quality_items'] ?? [] as $mainCriteria) {
                    foreach ($mainCriteria['sub_criterias'] ?? [] as $subCriteria) {
                        $hasScore = isset($subCriteria['score']) && $subCriteria['score'] !== '' && $subCriteria['score'] !== null;
                        $isSelected = $hasScore || ($subCriteria['user_selected'] ?? false);

                        if ($isSelected) {
                            $evaluationListQualityTotal += $hasScore
                                ? (float) $subCriteria['score']
                                : (float) ($subCriteria['num_score'] ?? 0);
                        }
                    }
                }

                $listMaxScore = (float) ($evaluationList['sum_score'] ?? 0);
                if ($listMaxScore > 0 && $evaluationListQualityTotal > $listMaxScore) {
                    $evaluationListQualityTotal = $listMaxScore;
                }

                $totalQualityScore += $evaluationListQualityTotal;
            }
        }

        $maxQualityScore = 0.0;
        foreach ($categoryItems as $category) {
            foreach ($category['evaluation_lists'] ?? [] as $evaluationList) {
                if (!empty($evaluationList['quality_items'])) {
                    $maxQualityScore += (float) ($evaluationList['sum_score'] ?? 0);
                }
            }
        }

        if ($maxQualityScore > 0 && $totalQualityScore > $maxQualityScore) {
            $totalQualityScore = $maxQualityScore;
        }

        return [
            'quantity' => $totalQuantityScore,
            'quality' => $totalQualityScore,
            'total' => $totalQuantityScore + $totalQualityScore,
        ];
    }
}
