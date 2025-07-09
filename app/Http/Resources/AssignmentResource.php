<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class AssignmentResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'evaluatee_id' => $this->evaluatee,
            'evaluatee_name' => $this->evaluateeUser ? $this->evaluateeUser->name : null,
            'evaluator_id' => $this->evaluator,
            'evaluator_name' => $this->evaluatorUser ? $this->evaluatorUser->name : null,
            'report_id'   => $this->report_id,
            'assignment_data_id' => $this->assignment_data_id,
            'evaluator_start' => $this->assignmentData->start_time?->format('Y-m-d H:i:s'),
            'evaluator_end' => $this->assignmentData->end_time?->format('Y-m-d H:i:s'),
            
        ];
    }
}
