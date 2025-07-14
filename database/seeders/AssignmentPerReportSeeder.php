<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Assignments;
use App\Models\AssignmentData;
use App\Models\Reports;
use App\Models\User;
use App\Models\EvidenceAnswer;
use App\Models\EvaluationList;
use App\Models\QuantitySubCriteria;
use App\Models\QuantityScore;
use App\Models\QualitySubCriteria;
use App\Models\QualityScore;

class AssignmentPerReportSeeder extends Seeder
{
    public function run(): void
    {
        // 1. Create N Reports (example: 5)
        $reports = Reports::factory()->count(5)->create();

        $assignmentData = AssignmentData::factory()->create();

        // 2. For each Report, create AssignmentData + Assignments + related scoring
        foreach ($reports as $report) {

            // // Create AssignmentData
            // $assignmentData = AssignmentData::factory()->create();

            // Pick existing users or create new
            $evaluatee = User::inRandomOrder()->first();
            $evaluator = User::where('id', '!=', $evaluatee->id)->inRandomOrder()->first();

            // Create Assignments linking Report and AssignmentData (one-to-one per report)
            $assignment = Assignments::create([
                'assignment_data_id' => $assignmentData->id,
                'report_id' => $report->id,
                'evaluatee' => $evaluatee->id,
                'evaluator' => $evaluator->id,
            ]);

            // EvidenceAnswer for each EvaluationList (for this report)
            foreach (EvaluationList::all() as $evalList) {
                EvidenceAnswer::factory()->create([
                    'evaluation_list_id' => $evalList->id,
                    'report_id'          => $report->id,
                ]);
            }

            // QuantityScore for each QuantitySubCriteria (for this report)
            $quantitySubs = QuantitySubCriteria::all();
            $maxSum = 40;
            $currentSum = 0;
            $remaining = count($quantitySubs);

            foreach ($quantitySubs as $i => $qsub) {
                $remaining = count($quantitySubs) - $i;
                $maxForThis = min($maxSum - $currentSum, 40); // cannot exceed 40
                // For the last subcriteria, use all the remaining points; else, random between 1 and the limit
                if ($remaining == 1) {
                    $scoreD = max($maxSum - $currentSum, 0);
                } else {
                    $scoreD = fake()->numberBetween(1, max($maxForThis - ($remaining - 1), 1));
                }
                // Avoid negative or over-allocating
                $scoreD = max(0, min($scoreD, $maxForThis));
                $currentSum += $scoreD;

                QuantityScore::factory()->create([
                    'quantity_sub_criteria_id' => $qsub->id,
                    'report_id'                => $report->id,
                    'score_D'                  => $scoreD,
                ]);
            }

            // QualityScore for each QualitySubCriteria (for this report)
            foreach (QualitySubCriteria::all() as $qsub) {
                QualityScore::factory()->create([
                    'quality_sub_criteria_id' => $qsub->id,
                    'report_id'               => $report->id,
                ]);
            }
        }
    }
}
