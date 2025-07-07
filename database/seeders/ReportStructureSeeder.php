<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\CriteriaVersion;
use App\Models\ReportData;
use App\Models\Category;
use App\Models\EvaluationList;
use App\Models\QuantityMainCriteria;
use App\Models\QuantitySubCriteria;
use App\Models\QualityMainCriteria;
use App\Models\QualitySubCriteria;

class ReportStructureSeeder extends Seeder
{
    public function run()
    {
        // 1. Create CriteriaVersion
        $criteriaVersion = CriteriaVersion::factory()->create();

        // 2. Create related ReportData
        ReportData::factory()->create([
            'criteria_version_id' => $criteriaVersion->id,
        ]);

        // 3. Create two Categories for this CriteriaVersion
        $categories = [];
        foreach ([1, 2] as $catSeq) {
            $categories[$catSeq] = Category::factory()->create([
                'criteria_version_id' => $criteriaVersion->id,
                'sequence' => $catSeq,
            ]);
        }

        /*
         * --------- CATEGORY #1 ---------
         * 1 EvaluationList -> 1 QuantityMain -> Many QuantitySubCriterias
         */
        $el1 = EvaluationList::factory()->create([
            'categorie_id' => $categories[1]->id,
            'criteria_version_id' => $criteriaVersion->id,
            // 'sequence' => 1,
            'sum_score' => 40,

        ]);

        $qmc = QuantityMainCriteria::factory()->create([
            'criteria_version_id' => $criteriaVersion->id,
        ]);

        // Attach N QuantitySubCriterias to QuantityMainCriteria, link to EvaluationList, CriteriaVersion.
        foreach (range(1, 5) as $seq) {
            QuantitySubCriteria::factory()->create([
                'quantity_main_criteria_id' => $qmc->id,
                'criteria_version_id' => $criteriaVersion->id,
                'evaluation_list_id' => $el1->id,
                'sequence' => $seq,
            ]);
        }

        /*
         * --------- CATEGORY #2 ---------
         * Many EvaluationLists, each -> Many QualityMainCriterias, each -> Many QualitySubCriterias
         */

        $maxSumScore = 30;
        $evaluationListCount = 7;

        $scores = [];
        $remain = $maxSumScore;
        for ($i = 0; $i < $evaluationListCount - 1; $i++) {
            $min = 1; // Minimum score per EvaluationList
            $max = $remain - ($evaluationListCount - $i - 1) * $min;
            $val = rand($min, $max);
            $scores[] = $val;
            $remain -= $val;
        }
        $scores[] = $remain; // Last score takes the remainder


        for ($listIdx = 1; $listIdx <= $evaluationListCount; $listIdx++) {
            $el2 = EvaluationList::factory()->create([
                'categorie_id' => $categories[2]->id,
                'criteria_version_id' => $criteriaVersion->id,
                'sequence' => $listIdx,
                'sum_score' => $scores[$listIdx - 1], // Assign distributed sum_score
            ]);

            // ### Divide ratio to total 100 ###
            $mainCount = 5;
            $ratios = [];
            $remain = 100;
            for ($i = 0; $i < $mainCount - 1; $i++) {
                $min = 10; // Minimum ratio per QualityMainCriteria
                $max = $remain - ($mainCount - $i - 1) * $min;
                $val = rand($min, $max);
                $ratios[] = $val;
                $remain -= $val;
            }
            $ratios[] = $remain; // Last ratio takes the remainder

            foreach (range(1, $mainCount) as $seqm) {
                $qualityMain = QualityMainCriteria::factory()->create([
                    'criteria_version_id' => $criteriaVersion->id,
                    'sequence' => $seqm,
                    'ratio' => $ratios[$seqm - 1],
                ]);

                foreach (range(1, 3) as $seqs) {
                    QualitySubCriteria::factory()->create([
                        'quality_main_criteria_id' => $qualityMain->id,
                        'criteria_version_id' => $criteriaVersion->id,
                        'evaluation_list_id' => $el2->id,
                        'sequence' => $seqs,
                    ]);
                }
            }
        }
    }
}
