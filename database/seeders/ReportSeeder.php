<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\EvidenceAnswer;
use App\Models\EvaluationList;
use App\Models\QuantitySubCriteria;
use App\Models\QuantityScore;
use App\Models\QualitySubCriteria;
use App\Models\QualityScore;
use App\Models\Report;

class ReportSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // 1. Create or get one Report (use id=1 if possible)
        // $report = Report::find(1);
        // if (!$report) {
        $report = Report::factory()->create([
            // 'id'     => 1,
            // 'status' => 'Assigned', // หรือเปลี่ยนตาม field ที่โมเดลคุณต้องการ
            // 'report_data_id' => ReportData::inRandomOrder()->first()?->id, // Uncomment if you have ReportData model
        ]);
        // }

        // 2. Create EvidenceAnswer for each existing EvaluationList
        $evaluationLists = EvaluationList::all();
        foreach ($evaluationLists as $evaluationList) {
            EvidenceAnswer::factory()->create([
                'evaluation_list_id' => $evaluationList->id,
                'report_id'          => $report->id,
            ]);
        }

        // 3. Create QuantityScore for each QuantitySubCriteria
        $quantitySubs = QuantitySubCriteria::all();
        foreach ($quantitySubs as $qsub) {
            QuantityScore::factory()->create([
                'quantity_sub_criteria_id' => $qsub->id,
                'report_id'                => $report->id,
            ]);
        }

        // 4. Create QualityScore for each QualitySubCriteria
        $qualitySubs = QualitySubCriteria::all();
        foreach ($qualitySubs as $qsub) {
            QualityScore::factory()->create([
                'quality_sub_criteria_id' => $qsub->id,
                'report_id'               => $report->id,
            ]);
        }
    }
}
