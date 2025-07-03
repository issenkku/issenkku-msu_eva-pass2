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
            'status' => $this->status,
            // ค่อยเพิ่ม resource อื่นๆ ที่ต้องการแสดงผลใน ReportResource นี้ เช่น จากตาราง ASSIGNMENTS หรือ ASSIGNMENTS

        ];
    }
}
