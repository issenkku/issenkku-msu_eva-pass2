<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class CriteriaVersionResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray($request)
    {
        return [
            'id' => $this->id,
            'version_name' => $this->version_name,
            'created_by' => $this->created_by,
            'created_at' => $this->created_at?->format('Y-m-d'),
            'updated_at' => $this->updated_at?->format('Y-m-d H:i:s'),
        ];
    }
}
