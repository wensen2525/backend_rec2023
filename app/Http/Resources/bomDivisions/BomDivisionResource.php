<?php

namespace App\Http\Resources\bomDivisions;

use Illuminate\Http\Request;
use App\Http\Resources\regions\RegionResource;
use Illuminate\Http\Resources\Json\JsonResource;

class BomDivisionResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
    // dd('sdaf');
        return [
            'id' => $this->id,
            'div_init' => $this->div_init,
            'region' => $this->whenNotNull(new RegionResource($this->region)),
            'name' => $this->name,
            'description' => $this->description,
            'task_link' => $this->task_link,
            'created_at' => date('Y-m-d', strtotime($this->created_at)),
            'updated_at' => $this->updated_at == null ? null : date('Y-m-d', strtotime($this->created_at)),
            'link_line_group' => $this->link_line_group,
            'qr_line_group' => $this->qr_line_group,
        ];
    }
}
