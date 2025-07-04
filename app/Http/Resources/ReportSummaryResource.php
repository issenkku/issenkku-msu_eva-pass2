<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
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
            'report_code' => $this->report_code,
            'status' => $this->status,
        ];
    }
}
