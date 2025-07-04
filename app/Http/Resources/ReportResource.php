<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ReportResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'report_data_id' => $this->report_data_id,
            'criteria_version' => $this->whenLoaded('reportData') && $this->reportData->criteriaVersion
                ? new CriteriaVersionResource($this->reportData->criteriaVersion)
                : null,
            'status' => $this->status,
            'created_at' => $this->created_at?->format('Y-m-d'),
            'updated_at' => $this->updated_at?->format('Y-m-d H:i:s'),
            'quantity_scores' => QuantityScoreResource::collection($this->whenLoaded('quantityScores')),
            'quality_scores' => QualityScoreResource::collection($this->whenLoaded('qualityScores')),
            'evidence_answers' => EvidenceAnswerResource::collection($this->whenLoaded('evidenceAnswers')),
            // ค่อยเพิ่ม resource อื่นๆ ที่ต้องการแสดงผลใน ReportResource นี้ เช่น จากตาราง ASSIGNMENTS หรือ ASSIGNMENTS
        ];
    }
}
