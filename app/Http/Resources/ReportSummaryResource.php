<?php

// ไฟล์คลาสของระบบ: app/Http/Resources/ReportSummaryResource.php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class ReportSummaryResource extends JsonResource
{
    public function toArray($request): array
    {
        return [
            'id' => $this->id,
            'report_data_id' => $this->report_data_id,
            'criteria_version' => $this->whenLoaded('reportData') && $this->reportData->criteriaVersion
                ? new CriteriaVersionResource($this->reportData->criteriaVersion)
                : null,
            'status' => $this->status,
            'assignments' => new AssignmentResource($this->whenLoaded('assignments')),
        ];
    }
}
